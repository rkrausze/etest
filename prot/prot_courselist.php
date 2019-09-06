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
 * This page is the protocol page providing an overview of all courses/grades
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('prot_util.php');

$PAGE->set_url('/mod/etest/prot/prot_courselist.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

$sessions = etest_prot_get_sessions($etest);
etest_read_details($etest);
etest_expand_subuserdata($etest);

function new_course_table($name) {
    global $etest;
    $table = new html_table();
    $table->name = $name;
    $table->head = array(get_string('users'), get_string('date'), get_string('time'), get_string('points', 'etest'));
    $table->align = array("left", "left", "left", "left");
    $table->ordertype = array('a', 'd', 't', '');

    // Subuser data.
    if ( $etest->flags & ETEST_USESUBUSERS ) {
        etest_expand_subuserdata($etest);
        foreach ($etest->sudata as $field) {
            array_push($table->head, $field['name']);
            array_push($table->align, 'left');
            array_push($table->ordertype, '');
        }
    }
    return $table;
}

// Tables.
etest_read_grade($etest);
$tables = array();
$rctables = array();
for ($i = 0; $i < count($etest->grade); $i++) {
    $tables[$i] = new_course_table($etest->grade[$i]->shortname);
    $rctables[$i] = new_course_table("Recalc - ".$etest->grade[$i]->shortname);
}

// Whether recalc is ever used.
$rcused = false;

foreach ($sessions as $session) {
    if ( !isset($session->excombi) || $session->excombi == "" ) {
        continue;
    }
    etest_prot_load_prot($session, $etest);
    $line = array(
        $format == "showashtml" ?
       '<a href="javascript:parent.control.SelectHist(\''.$session->id.'\')" target="control"><B>'.
       $session->displayname.
       '</b></a>'
       : $session->displayname,
        $format == "showashtml" ?
         '<nobr><a href="javascript:parent.control.StartDate('.usergetmidnight($session->starttime).')" title="'.
         get_string('use_as_startdate', 'etest').'"><b>[</b></a> '.strftime("%d.%m.%y", $session->starttime).
         ' <a href="javascript:parent.control.EndDate('.usergetmidnight($session->starttime).')" title="'.
         get_string('use_as_enddate', 'etest').'"><b>]</b></a></nobr>'
         : strftime("%d.%m.%y", $session->starttime),
        strftime("%H:%M", $session->starttime));

    $p = etest_prot_summary_prot($session, $etest);

    array_push($line, $p->orig[0]);

    // Subuserdata.
    if ( $etest->flags & ETEST_USESUBUSERS ) {
        $subdat = isset($session->data) ? explode(ETEST_X01, $session->data) : array();
        for ($i = 0; $i < count($etest->sudata); $i++) {
            array_push($line, isset($subdat[$i]) ? $subdat[$i] : '');
        }
    }

    $tables[$p->orig[1]]->data[] = $line;

    $rcline = array_merge($line);
    $rcindex = $p->orig[1];
    if ( $p->recalc[0] != "" ) {
        $rcindex = $p->recalc[1];
        $rcused = true;
    }
    $rctables[$rcindex]->data[] = $line;
}

if ( $rcused == true ) {
    array_push($tables, $rctables);
}

// Remove empty tables.
$filledtables = array();
foreach ($tables as $table) {
    if ( isset($table->data) ) {
        $filledtables[] = $table;
    }
}

if ( $format == "showashtml" ) {
    echo etest_safe_header('prot');
    print_tables_html($filledtables);
    echo etest_safe_footer('prot');
} else if ( $format == "downloadascsv" ) {
    print_tables_csv("CourseList", $filledtables);
} else if ( $format == "downloadasexcel" ) {
    print_tables_xls("CourseList", $filledtables);
}
