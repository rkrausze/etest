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
 * This page is the protocol page of the exercise overview.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('prot_util.php');

$PAGE->set_url('/mod/etest/prot/prot_exoverview.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

$sessions = etest_prot_get_sessions($etest);
etest_read_details($etest);

$exlist = etest_prot_make_exercise_overview($etest, $sessions);

$table = new html_table();
$table->name = get_string('exoverview', 'etest');
$table->head = array(get_string('exblock', 'etest'), get_string('ex', 'etest'), get_string("exalt", 'etest'),
    get_string('file', 'etest'), get_string('nSelected', 'etest'), get_string('nSolved', 'etest'),
    get_string('nCorrectSolved', 'etest'), get_string('averageSolved', 'etest'), get_string('averagePercent', 'etest'));
$table->align = array('left', 'left', 'left', 'left', 'center', 'center', 'center', 'center', 'center');
$table->data = array();
$table->ordertype = array('', '', '', 'a', '', '', '', 'p', 'p');

for ($i = 0; $i < count($etest->exalt); $i++) {
    for ($j = 0; $j < count($etest->exalt[$i]); $j++) {
        for ($k = 0; $k < count($etest->exalt[$i][$j]); $k++) {
            $exaltid = $etest->exalt[$i][$j][$k]->id;
            if ( isset($exlist[$exaltid]) ) {
                $table->data[] = array(
                    '['.($i + 1).'] '.$etest->exblock[$i]->name,
                    '['.($j + 1).'] '.$etest->ex[$i][$j]->name,
                    $k + 1,
                    $format == "showashtml"
                    ? '<nobr><a href="javascript:parent.control.ExerciseEntriesSingle('.$exaltid.')" title="'.
                        get_string('show_exercise', 'etest').'">'.$etest->exalt[$i][$j][$k]->filename.'</a></nobr>'
                    : $etest->exalt[$i][$j][$k]->filename,
                    $exlist[$exaltid][0],
                    $exlist[$exaltid][1],
                    $exlist[$exaltid][2],
                    $exlist[$exaltid][1] == 0 ? '--' : sprintf('%.2f%%', $exlist[$exaltid][2] * 100 / $exlist[$exaltid][1]),
                    $exlist[$exaltid][1] == 0 ? '--' : sprintf('%.2f%%', $exlist[$exaltid][3] / $exlist[$exaltid][1]));
            }
        }
    }
}

$tables = array($table);

if ( $format == 'showashtml' ) {
    echo etest_safe_header('prot');
    print_tables_html($tables);
    echo $errorstring;
    echo etest_safe_footer('prot');
} else if ( $format == 'downloadascsv' ) {
    print_tables_csv('ExercseList', $tables);
} else if ( $format == 'downloadasexcel' ) {
    print_tables_xls('ExerciseList', $tables);
}
