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
 * This page is displays the leveling.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('prot_util.php');

$PAGE->set_url('/mod/etest/prot/leveling.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

$sessions = etest_prot_get_sessions($etest);
etest_read_details($etest);

etest_read_grade($etest);
$ngrades = count($etest->grade);
if ( $ngrades > 0 ) {
    $origcourses = array_fill(0, $ngrades, 0);
    $rccourses = array_fill(0, $ngrades, 0);
} else {
    $origcourses = array();
    $rccourses = array();
}

// Table.
$table = new html_table();
$table->name = 'Leveling';
$table->head = array(get_string('users'), get_string('date'), get_string('time'));
$table->align = array('left', 'left', 'left');
$table->ordertype = array('a', 'd', 't');

// Subuser data.
if ( $etest->flags & ETEST_USESUBUSERS ) {
    etest_expand_subuserdata($etest);
    foreach ($etest->sudata as $field) {
        array_push($table->head, $field['name']);
        array_push($table->align, 'left');
        array_push($table->ordertype, '');
    }
}

array_push($table->head, get_string('points', 'etest'), get_string('grades', 'etest'));
array_push($table->align, 'left', 'left');
array_push($table->ordertype, 'n', '');

etest_read_grade($etest);
$ngrades = count($etest->grade);
if ( $ngrades > 0 ) {
    $origcourses = array_fill(0, $ngrades, 0);
    $rccourses = array_fill(0, $ngrades, 0);
} else {
    $origcourses = array();
    $rccourses = array();
}

foreach ($sessions as $session) {
    if ( !isset($session->excombi) || $session->excombi == '' ) {
        continue;
    }
    etest_prot_load_prot($session, $etest);
    $line = array($session->firstname." ".$session->lastname,
        strftime("%d.%m.%y", $session->starttime),
        strftime("%H:%M", $session->starttime));

    // Subuser data.
    if ( $etest->flags & ETEST_USESUBUSERS ) {
        $subdat = isset($session->data) ? explode(ETEST_X01, $session->data) : array();
        for ($i = 0; $i < count($etest->sudata); $i++) {
            array_push($line, isset($subdat[$i]) ? $subdat[$i] : '');
        }
    }

    etest_prot_summary_prot($session, $etest);

    array_push($line, $points, $origcourseshortname);

    $table->data[] = $line;

    if ( 0 <= $origcourse && $origcourse < $ngrades ) {
        $origcourses[$origcourse]++;
    }
    if ( 0 <= $rccourse && $rccourse < $ngrades ) {
        $rccourses[$rccourse]++;
    }
}

$tables = array($table);

if ( $format == 'showashtml' ) {
    echo etest_safe_header('prot');
    print_tables_html($tables);
    echo etest_safe_footer('prot');
} else if ( $format == 'downloadascsv' ) {
    print_tables_csv('Leveling', $tables);
} else if ( $format == 'downloadasexcel' ) {
    print_tables_xls('Leveling', $tables);
}
