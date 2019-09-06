<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Library for protocol.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Common data retrieval.
require_once("../../../config.php");
require_once("../lib.php");

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a  = optional_param('a', optional_param('etest', 0, PARAM_INT), PARAM_INT);  // E-Test ID.

$sessionid = optional_param('session', 0, PARAM_INT);

if ($id) {
    $cm      = get_coursemodule_from_id('etest', $id, 0, false, MUST_EXIST);
    $course  = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $etest   = $DB->get_record('etest', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($a) {
    $etest   = $DB->get_record('etest', array('id' => $a), '*', MUST_EXIST);
    $course  = $DB->get_record('course', array('id' => $etest->course), '*', MUST_EXIST);
    $cm      = get_coursemodule_from_instance('etest', $etest->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

$format = optional_param('format', 'showashtml', PARAM_ALPHA);
$sortinfo = optional_param('sortinfo', '', PARAM_TEXT);

require_login($course->id);
$coursecontext = @context_course::instance($course->id);

if ( ($etest->flags & ETEST_USESUBUSERS) ) {
    etest_expand_subuserdata($etest);
}

$sessionid = optional_param('sessionid', 0, PARAM_INT);

$from = optional_param('from', 0, PARAM_INT);
$till = optional_param('till', 0, PARAM_INT);

$exaltid = optional_param('exaltid', '', PARAM_TEXT);

// Grouping information.
$groupsql = optional_param('groupSql', '', PARAM_TEXT); // The SQL-statement für the query.
$grouptext = optional_param('groupText', '', PARAM_TEXT); // Displayable Text.
$groupdata = optional_param('groupData', '', PARAM_TEXT); // Data for configuring.

function etest_prot_get_sessions($etest, $addcondition = ' AND ses.archivetag IS NULL') {
    global $CFG, $DB, $USER, $from, $till, $groupsql, $grouptext, $coursecontext;
    $adduserlimit = ' AND u.id = -42'; // No one.
    if ( has_capability('mod/etest:viewallattempts', $coursecontext) ) {
        $adduserlimit = ''; // All.
    } else if ( has_capability('mod/etest:viewmyattempts', $coursecontext) ) {
        $adduserlimit = ' AND u.id = '.$USER->id; // Own only.
    }
    $sql = 'SELECT'.
        ' ses.*, '.
        ' u.id as userid,'.
        ' u.firstname,'.
        ' u.lastname'.
        $groupsql.
        ' FROM '.$CFG->prefix.'etest_session ses'.
        ' JOIN '.$CFG->prefix.'user u ON u.id = ses.userid'.
        ' WHERE ses.etest = '.$etest->id.
        $adduserlimit.
        ($from != 0 ? ' AND '.$from.' <= ses.starttime ' : '').
        ($till != 0 ? ' AND ses.starttime <= '.($till + 86400) : '').
        $addcondition;
    if (!$sessions = $DB->get_records_sql($sql)) {
        $sessions = array(); // Tablelib will handle saying 'Nothing to display' for us.
    }
    if ( $grouptext != '' ) {
        prepare_grouping($sessions);
    }
    // Make displayname.
    foreach ($sessions as $id => $session) {
        etest_displayname($etest, $session, $session);
    }
    return $sessions;
}

$exuserstate = 0; // Result.
$exuserbest = 0;  // Best result.
$exuservalue = 0; // Vaklues of input fields.
$exuserpr = 0; // PR PreciseResult.
$exuseractionid = 0; // Id of last entry for this esexise in etest_action (for recalc).
$usedtime = 0;
$logintime = 0;
$exuser = 0; // Www-files.
$exuserexalt = 0; // ExaltIds.
$exuserhash = 0; // ExaltId -> exNr.

$curprotsession = 0;

function etest_prot_load_prot($session, $etest) {
    global $DB, $curprotsession, $exuser, $exuserexalt, $exuserhash,
        $exuserstate, $exuserbest, $exuservalue, $exuserpr, $exuseractionid,
        $usedtime, $logintime, $remainingtime;
    $curprotsession = $session;
    // Load user data.
    $exuser = etest_fill_exuser($etest, $session->excombi);
    $exuserexalt = etest_fill_exuser_exalt($etest, $session->excombi);
    // ExUserHash to have a exaltId -> exNr.
    $exuserhash = array();
    for ($i = 0; $i < count($exuserexalt); $i++) {
        $exuserhash[$exuserexalt[$i]] = $i;
    }
    // Initialize.
    if ( count($exuser) > 0 ) {
        $exuservalue = array_fill(0, count($exuser), '');
        $exuserstate = array_fill(0, count($exuser), -2);
        $exuserbest = array_fill(0, count($exuser), -2);
        $exuserpr = array_fill(0, count($exuser), '');
        $exuseractionid = array_fill(0, count($exuser), '');
    } else {
        $exuservalue = array();
        $exuserstate = array();
        $exuserbest = array();
        $exuserpr = array();
        $exuseractionid = array();
    }
    $usedtime = 0;
    $logintime = -1;
    // Load prot.
    $prot = $DB->get_records('etest_action', array('session' => $session->id), 'starttime ASC, action ASC');
    if ( $prot === false ) {
        $prot = array();
    }
    $lasttimespan = 0;
    foreach ($prot as $line) {
        if ( $line->action == 600 ) { // Login.
            if ( $logintime != -1 ) {
                $usedtime += $lasttimespan;
            }
            $logintime = $line->starttime;
        } else if ( $line->action == 601 ) { // Final.
            break;
        } else if ( $line->action == 602 ) { // Exercise.
            if ( isset($line->result) && isset($exuserhash[$line->exid]) && isset($line->data) && $line->data != "") {
                $exnr = $exuserhash[$line->exid];
                $exuserstate[$exnr] = $line->result;
                if ( $line->result > $exuserbest[$exnr] ) {
                    $exuserbest[$exnr] = $line->result;
                }
                if ( preg_match('/ ([0-9,]*)$/', $line->data, $s) ) {
                    $exuservalue[$exnr] = substr($line->data, 0, strlen($line->data) - strlen($s[0]));
                    $exuserpr[$exnr] = $s[1];
                } else {
                    $exuservalue[$exnr] = $line->data;
                    $exuserpr[$exnr] = '';
                }
                $exuseractionid[$exnr] = $line->id;
            }
        } else if ( $line->action == 603 ) { // Time corrected by master.
            $token = explode(" ", $line->data);
            $remainingtime = $token[4];
            $usedtime = 0;
            $logintime = $line->starttime;
        }
        $lasttimespan = $line->starttime - $logintime;
    }
    $usedtime += $lasttimespan;
    $remainingtime -= $usedtime;
    return true;
}

$origcourse = 0; $rccourse = 0; // As Indizes to etest->grade.

function etest_prot_summary_prot($session, $etest) {
    global $exuser, $exuserstate, $exuservalue, $exuserbest, $usedtime, $logintime, $origcourse, $rccourse, $origcourseshortname,
        $points;
    $res = new stdClass();
    $res->duration = sec2str(round($usedtime));
    $worsed = 0;
    $res->ex = array();
    $res->exBest = array();
    $res->exRecalc = array();
    $rcexstate = isset($session->recalcstates) ? explode(',', $session->recalcstates) : array_fill(0, count($exuser), '');
    for ($i = 0; $i < count($exuser); $i++) {
        $res->ex[] = $exuserstate[$i];
        if ( $exuserstate[$i] < $exuserbest[$i] ) {
            $res->exBest[] = $exuserbest[$i];
            $worsed++;
        } else {
            $res->exBest[] = '';
        }
        $res->exRecalc[] = $rcexstate[$i];
    }
    $res->worsed = ($worsed != 0 ? $worsed."x" : "");
    $points = 0;
    $blockpoints = 0;
    $origcourse = etest_asign_grade($etest, $exuserstate, $points, $blockpoints);
    $origcourseshortname = (0 <= $origcourse && $origcourse < count($etest->grade)) ? $etest->grade[$origcourse]->shortname : "--";
    $res->orig = array($points, $origcourse, $origcourseshortname);
    if ( isset($session->recalcstates) ) {
        $pointsrc = 0;
        $blockpointsrc = 0;
        $rccourse = etest_asign_grade($etest, $rcexstate, $pointsrc, $blockpointsrc);
        $res->recalc = array($pointsrc, $rccourse, $etest->grade[$rccourse]->shortname);
    } else {
        $res->recalc = array('', '', '');
    }
    if ( isset($session->grade) ) {
        $igrade = gradeid2nr($etest, $session->grade);
        $res->old = array($session->points, $igrade, $etest->grade[$igrade]->shortname);
    } else {
        $res->old = array('', '', '');
    }
    return $res;
}

// Helper.

function etest_prot_good_umlauts($s) {
    // In former version here were Umlauts etc. corrected.
    return $s;
}

function sec2str($s) {
    global $format;
    if ( $s * 1 < 60 ) {
        return $format == 'showashtml' ? '<font color="#808080">'.$s.' sec.</font>' : $s.' sec';
    }
    $min = floor($s / 60);
    $s = $s % 60;
    if ( $s < 10 ) {
        $s = '0'.$s;
    }
    if ( $min < 60 ) {
        return $min.':'.$s.' min.';
    }
    $h = floor($min / 60);
    $min = $min % 60;
    if ( $min < 10 ) {
        $min = '0'.$min;
    }
    return $format == 'showashtml' ? '<b>'.$h.':'.$min.':'.$s.'h</b>' : $h.':'.$min.':'.$s.'h';
}

function gradeid2nr($etest, $gradeid) {
    foreach ($etest->grade as $i => $grade) {
        if ( $grade->id == $gradeid ) {
            return $i;
        }
    }
    return -1;
}

// Grouping.

$groups = array();

function prepare_grouping(&$sessions) {
    global $groups, $groupdata, $etest;
    $groupdataarray = array();
    $usesubdata = false;
    foreach (explode(';', $groupdata) as $entry) {
        $h = explode(',', $entry);
        $groupdataarray[] = $h;
        if ( $h[0] == 'sub' ) {
            $usesubdata = true;
        }
    }
    foreach ($sessions as $key => $ses) {
        $groupstring = '';
        $subdata = $usesubdata ? explode(ETEST_X01, $ses->subdata) : 0;
        foreach ($groupdataarray as $entry) {
            if ( $groupstring != '' ) {
                $groupstring .= ', ';
            }
            if ( $entry[0] == 'sub' ) {
                $groupstring .= isset($subdata[$entry[1]]) ? $subdata[$entry[1]] : '';
            } else if ( $entry[0] == 'firstname') {
                $groupstring .= (($etest->flags & ETEST_USESUBUSERS) != 0) ? $ses->subfirstname : $ses->firstname;
            } else if ( $entry[0] == 'lastname') {
                $groupstring .= (($etest->flags & ETEST_USESUBUSERS) != 0) ? $ses->sublastname : $ses->lastname;
            } else if ( $entry[0] == 'birthday') {
                $groupstring .= $ses->birthday;
            }
        }
        $sessions[$key]->group = $groupstring;
        $groups[$groupstring] = 1;
    }
    // Turn $groups from hash to a sorted array.
    $groups = array_keys($groups);
    sort($groups);
}

// ExerciseOverview.

function etest_prot_make_exercise_overview($etest, $sessions) {
    global $exuserexalt, $exuserstate;
    // Empty field.
    // exList: $exlist[exAltId] = array(nSelected, nSolved, nCorrectSolved, averagePercent).
    $exlist = array();
    // Read data.
    foreach ($sessions as $session) {
        etest_prot_load_prot($session, $etest);
        for ($u = 0; $u < count($exuserexalt); $u++) {
            if ( isset($exuserexalt[$u]) ) {
                $exaltid = $exuserexalt[$u];
                if ( !isset($exlist[$exaltid]) ) {
                    $exlist[$exaltid] = array(0, 0, 0, 0);
                }
                // Selected (used in excombi).
                $exlist[$exaltid][0]++;
                // Solved (attempted).
                if ( $exuserstate[$u] != -2 ) {
                    $exlist[$exaltid][1]++;
                    if ( $exuserstate[$u] >= 98 ) {
                        $exlist[$exaltid][2]++;
                    }
                    $exlist[$exaltid][3] += max($exuserstate[$u], 0);
                }
            }
        }
    }
    return $exlist;
}

// ExerciseList.

$exentry = array();

function etest_prot_make_exercise_list($etest) {
    global $exentry, $sessions;
    // Empty field.
    $exentry = array();
    foreach (array_keys($etest->exaltHash) as $exaltid) {
        $exentry[$exaltid] = array();
    }
    // Read data.
    foreach ($sessions as $session) {
        etest_prot_read_exercise_hist($session, $etest);
    }
}

function etest_prot_read_exercise_hist($session, $etest) {
    global $exuserexalt, $exuservalue, $exuserpr;
    $group = isset($session->group) ? $session->group : '';
    etest_prot_load_prot($session, $etest);
    for ($u = 0; $u < count($exuserexalt); $u++) {
        if ( $exuservalue[$u] ) {
            add_ex_entry($exuserexalt[$u], $group, $exuservalue[$u], $exuserpr[$u]);
        }
    }
}

function add_ex_entry($exaltid, $group, $s, $prstring) {
    $arr = explode('|', $s);
    $prarray = explode(',', $prstring);
    for ($l = 0; $l < count($arr); $l++) {
        // Formerly: if ( $arr[$l] != "" ) // could be an empty text field, so don't skip.
        add_ex_entry_at($exaltid, $group, $l, $arr[$l], $prstring != '' ? $prarray[$l] : '');
    }
}

function add_ex_entry_at($exaltid, $group, $l, $s, $prstring) {
    global $exentry, $curprotsession;
    if ( !isset($exentry[$exaltid][$group]) ) {
        $exentry[$exaltid][$group] = array();
    }
    if ( !isset($exentry[$exaltid][$group][$l]) ) {
        $exentry[$exaltid][$group][$l] = array();
    }
    $done = false;
    $m = 0;
    for ($m = 0; $m < count($exentry[$exaltid][$group][$l]); $m++) {
        if ( $exentry[$exaltid][$group][$l][$m][1] == $s ) {
            $exentry[$exaltid][$group][$l][$m][0]++;
            if ( $prstring != '' ) {
                $exentry[$exaltid][$group][$l][$m][3] = $prstring;
            }
            $done = true;
            break;
        }
    }
    if ( $done == false ) {
        $exentry[$exaltid][$group][$l][$m] = array(1, $s, $curprotsession->displayname, $prstring);
    }
}

function etest_report_date_selector($name, $selecteddate = "today", $sessions = false) {
    global $course;
    // Get all the possible dates.
    // Note that we are keeping track of real (GMT) time and user time
    // User time is only used in displays - all calcs and passing is GMT.

    $strftimedate = get_string("strftimedate");
    $strftimedaydate = get_string("strftimedaydate");
    $timenow = time(); // GMT.

    // What day is it now for the user, and when is midnight that day (in GMT).
    $timemidnight = $today = usergetmidnight($timenow);

    // Put today up the top of the list.
    $dates = array("$timemidnight" => get_string("today").", ".userdate($timenow, $strftimedate) );

    if (!$course->startdate or ($course->startdate > $timenow)) {
        $course->startdate = $course->timecreated;
    }

    if ($selecteddate == "today") {
        $selecteddate = $today;
    }

    echo '<select id="menu', $name, '" class="select menu', $name, '" name="', $name,
        '" onchange="if ( f.action ) { f.target = \'data\'; f.submit(); }">',
        '<option value="0"', $selecteddate == 0 ? ' selected="selected"' : '', '>', get_string("alldays"), '</option>', "\r\n";

    if ( $sessions === false ) {
        $numdates = 1;
        while ($timemidnight > $course->startdate and $numdates < 365) {
            $timemidnight = $timemidnight - 86400;
            $timenow = $timenow - 86400;
            echo '<option value="', $timemidnight, '"', $selecteddate == $timemidnight ? ' selected="selected"' : '', '>',
                userdate($timenow, $strftimedaydate), '</option>', "\r\n";
            $numdates++;
        }
    } else {
        $times = array();
        foreach ($sessions as $session) {
            $times[usergetmidnight($session->starttime)] = 1;
        }
        $atimes = array_keys($times);
        rsort($atimes);
        foreach ($atimes as $t) {
            echo '<option value="', $t, '"', $selecteddate == $t ? ' selected="selected"' : '', '>',
            userdate($t, $strftimedaydate), '</option>', "\r\n";
        }
    }
    echo '</select>';
}
