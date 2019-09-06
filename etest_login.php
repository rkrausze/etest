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
 * Create the session.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$a  = optional_param('etest', 0, PARAM_INT);  // E-Test instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('etest', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $etest  = $DB->get_record('etest', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($a) {
    $etest  = $DB->get_record('etest', array('id' => $a), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $etest->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('etest', $etest->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course->id);

$userid = optional_param('user', 0, PARAM_INT);

etest_read_details($etest);

$usesubuser = (($etest->flags & ETEST_USESUBUSERS) != 0);

// Get parameters.

$appname = optional_param('AppName', "", PARAM_ALPHA);

if ( $usesubuser ) {
    etest_expand_subuserdata($etest);
    $sudata = "";
    foreach ($etest->sudata as $fieldid => $field) {
        $sudata .= ($sudata == '' ? '' : ETEST_X01).optional_param('data'.$fieldid, '', PARAM_RAW);
    }
}

$continuesession = optional_param('continueSession', '', PARAM_ALPHANUM);

$sessionid = '';

if ( $continuesession == 'new' ) { // Force a new session.
    ; // Do nothing (fall through).
} else if ( $continuesession != '' ) { // Continue a session.
    $sessionid = $continuesession;
} else { // Enter for the first time.
    // Load old user data.
    if ( $usesubuser ) {
        $userold = $DB->get_records_select('etest_session', 'etest=:etest AND '.$DB->sql_compare_text('data').' = :sudata',
            array('etest' => $etest->id, 'sudata' => $sudata));
    } else {
        $userold = $DB->get_records('etest_session', array('etest' => $etest->id, 'userid' => $userid));
    }
    $newsessionsallowed = $userold === false || !isset($etest->maxsession) || $etest->maxsession < 1 ||
        count($userold) < $etest->maxsession;
    // If there are already session then show it to the user.
    if ( $userold !== false && count($userold) > 0 ) {
        // Check, whether there are pending sessions.
        $s = '';
        if ( ($etest->flags & ETEST_NOCONTINUESESSION) == 0 ) {
            foreach ($userold as $old) {
                if ( !isset($old->points) ) {
                    $s .= '<tr><td>'.get_string('ses_exists1', 'etest', userdate($old->starttime)).
                          '</td><td><input type="button" value="'.get_string('ses_continue_button', 'etest').
                          '" onclick="document.fm.continueSession.value=\''.$old->id.'\';document.fm.submit()"></td></tr>';
                }
            }
        }
        if ( $s != '' ) {
            $s = get_string('ses_exists0', 'etest').'<table border="1" cellpadding="2" align="center">'.$s.'</table>';
            if ( $newsessionsallowed ) {
                $s .= get_string('ses_exists3', 'etest');
            }
        }
        if ( !$newsessionsallowed ) {
            $s .= get_string('maxSessionReached', 'etest', $etest->maxsession);
        }
        $PAGE->set_url('/mod/etest/etest_login.php', array('id' => $cm->id));
        $PAGE->navbar->ignore_active();
        $PAGE->set_pagelayout('embedded');
        $PAGE->blocks->show_only_fake_blocks();
        echo etest_safe_header('test');
?>
        <p align="center"><?php print_string('ses_exist', 'etest', etest_displayname($etest, $old, $USER)); ?></p><?php
        if ( $s != '' ) { ?>
            <p align="center"><font face="Arial"><?php echo $s; ?></font></p>
        <?php
        } ?>
        <form action="etest_login.php" name="fm" method="POST">
        <input type="hidden" name="AppName" value="<?php echo $appname ?>">
        <?php
        if ( $usesubuser ) { ?>
            <input type="hidden" name="suData" value="<?php echo str_replace(ETEST_X01, "###", $sudata); ?>">
        <?php
        } ?>
        <input type="hidden" name="continueSession" value="new">
        <input type="hidden" name="course" value="<?php echo $cm->id ?>">
        <input type="hidden" name="etest" value="<?php echo $etest->id ?>">
        <input type="hidden" name="user" value="<?php echo $userid ?>">
        <p align="center"><?php
        if ( $newsessionsallowed ) {?>
            <input type="submit" value="<?php print_string('ses_new_button', 'etest'); ?>">
<?php
        } ?>
            <input type="button" value="<?php print_string('ses_cancel_button', 'etest'); ?>"
                onclick="top.control.SetTopicText(top.control.PosLogin)">
        </p>
        </form><?php
        echo etest_safe_footer('test');
        exit;
    }
    // There are no old sessions, so fall through.
}

// Continue a session.
if ( $sessionid != '' ) {
    $etestsession = $DB->get_record('etest_session', array('id' => $sessionid));
    $exuser = etest_fill_exuser($etest, $etestsession->excombi);
    $exuserstate = array_fill(0, count($exuser), -2);
    $exuservalue = array_fill(0, count($exuser), "##empty##");
    $remainingtime = ($etest->timelimit == 0) ? 'no' : $etest->timelimit*60; // Time will be corrected by etest_load_oldsession.
    $oldfinal = false;
    etest_load_oldsession($etestsession);
    if ( $oldfinal !== false ) {
        etest_error(get_string('testOver', 'etest'), get_string('testOver_explain', 'etest'));
    }
} else { // New session.
    $etestsession = etest_new_session($etest, $usesubuser ? $sudata : false);
    $exuser = etest_fill_exuser($etest, $etestsession->excombi);
    $exuserstate = array_fill(0, count($exuser), -2);
    $remainingtime = ($etest->timelimit == 0) ? 'no' : $etest->timelimit * 60;
}

// Write back HTML.
if ( $errorstring == "" ) {
?>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body bgcolor="#FFFFFF">
<font face="Arial">loading ...</font>
<script type="text/javascript">
    var ExUser = new Array("<?php echo join("\", \n\"", $exuser) ?>");
    var <?php echo_number_array_jssafe("ExUserState", $exuserstate) ?>;
    var ExN = new Array(<?php
    $exnames = '';
    for ($i = 0; $i < count($etest->exblock); $i++) {
        $n = 0;
        for ($j = 0; $j < count($etest->ex[$i]); $j++) {
            $n += 1;
            $exnames .= ($exnames != '' ? ', ' : '').'"'.
                ((($etest->flags & ETEST_USEEXNAMES) != 0) ? $etest->ex[$i][$j]->name : get_string('ex', 'etest').' '.($j + 1)).'"';
        }
        if ( $i != 0 ) {
            echo ", ";
        }
        echo "'", $n, "'";
    }?>);
    var ExName = new Array(<?php echo $exnames ?>);
    var <?php echo_number_array_jssafe("ExAlt", etest_fill_exuser_exalt($etest, $etestsession->excombi)) ?>;
<?php
    if ( isset($exuservalue) ) {?>
    var ExValue    = new Array("<?php echo join('", "', $exuservalue) ?>");
<?php
    }
    if ( $usesubuser ) { ?>
    var UserNameN = "<?php echo etest_subuser_entry($etest, $etestsession, get_string('lastname', 'etest'), '??') ?>";
    var UserNameV = "<?php echo etest_subuser_entry($etest, $etestsession, get_string('firstname', 'etest'), '??') ?>";
    var UserGebDat = "<?php echo etest_subuser_entry($etest, $etestsession, get_string('birthday', 'etest'), '') ?>";
<?php
    } else { ?>
    var UserNameN = "<?php echo $USER->lastname ?>";
    var UserNameV = "<?php echo $USER->firstname ?>";
    var UserGebDat = "";
<?php
    } ?>
    var UserDisplayName = "<?php echo etest_displayname($etest, $etestsession, $USER) ?>";
    var usePrintButton = "<?php echo (($etest->flags & ETEST_NOPRINTBUTTON) != 0) ? "0" : "1" ?>";
    var useResultDiagram = "<?php echo (($etest->flags & ETEST_NORESULTDIAGRAM) != 0) ? "0" : "1" ?>";
    var UserData = new Array(<?php
    if ( $usesubuser ) {
        $d = explode(ETEST_X01, $etestsession->data);
        $first = true;
        foreach ($etest->sudata as $fieldid => $field) {
            if ( $first ) {
                $first = false;
            } else {
                echo ",\n  ";
            }
            echo "\"", $field["name"], "\", \"", isset($d[$fieldid]) ? $d[$fieldid] : "", "\"";
        }
    }?>);
    var RemainingTime = "<?php echo $remainingtime ?>";
    top.control.etest_session = <?php echo $etestsession->id ?>;
    top.control.ApplyData();
</script>
<?php
}
?>
</body>
<?php
/**
 * Load the data of a former attempt (from etest_action).
 *
 * @param unknown $etest_session the session whose data to load
 * @return boolean success or failed
 */
function etest_load_oldsession($etest_session) {
    global $DB, $exuserstate, $exuservalue, $etest, $remainingtime, $oldfinal;
    // ExUserHash to have a exaltId -> exNr.
    $exuserexalt = etest_fill_exuser_exalt($etest, $etest_session->excombi);
    $exuserhash = array();
    for ($i = 0; $i < count($exuserexalt); $i++) {
        $exuserhash[$exuserexalt[$i]] = $i;
    }
    // Read prot.
    $prot = $DB->get_records('etest_action', array('session' => $etest_session->id), 'starttime ASC, action ASC');
    if ( $prot === false ) {
        $prot = array();
    }
    $lasttimespan = 0;
    $logintime = -1;
    $usedtime = 0;
    foreach ($prot as $line) {
        if ( $line->action == 600 ) {// Login.
            if ( $logintime != -1 ) {
                $usedtime += $lasttimespan;
            }
            $logintime = $line->starttime;
        } else if ( $line->action == 601 ) {// Final.
            $oldfinal = $line->starttime;
            break;
        } else if ( $line->action == 602 ) {// Exercise.
            if ( isset($line->result) && isset($exuserhash[$line->exid]) ) {
                $exnr = $exuserhash[$line->exid];
                $exuserstate[$exnr] = $line->result;
                // Remove PR if existing.
                $exuservalue[$exnr] = "##reload##".preg_replace('/ [0-9,]*$/', '', $line->data);
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
    if ( $remainingtime !== 'no' ) {
        $remainingtime -= $usedtime;
    }
    return true;
}

/**
 * Echo an array for js.
 *
 * @param string $name the name of the array
 * @param array $arr the array itself
 */
function echo_number_array_jssafe($name, $arr) {
    if ( count($arr) == 1 ) {
        echo $name, " = new Array(1); ", $name, "[0] = ", $arr[0];
    } else {
        echo $name, " = new Array(", join(", ", $arr), ")";
    }
}
