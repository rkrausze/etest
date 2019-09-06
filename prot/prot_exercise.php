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
 * This page is the protocol page providing an exercise. TODO Currently not linked.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');

$sessions = etest_prot_get_sessions($etest);
etest_read_details($etest);

MakeExercise($etest, $exaltid); // TODO currently not implemented/migrated.

function entrysort($a, $b) {
    return $b[0] - $a[0];
}

$tables = array();
for ($i = 0; $i < count($etest->exalt); $i++) {
    for ($j = 0; $j < count($etest->exalt[$i]); $j++) {
        for ($k = 0; $k < count($etest->exalt[$i][$j]); $k++) {
            if ( $exaltid == $etest->exalt[$i][$j][$k]->id ) {
                unset($table);
                $table->name = get_string('exentries', 'etest').': '.$etest->exblock[$i]->name.' - '.
                                $etest->ex[$i][$j]->name.' - #'.($k + 1).' ('.$etest->exalt[$i][$j][$k]->reference.')';
                $table->head = array(get_string('exfield', 'etest'), get_string('nusers', 'etest'),
                                get_string("exfieldentry", 'etest'));
                $table->align = array("left", "left", "left");
                $table->data = array();
                $table->anchor = $etest->exalt[$i][$j][$k]->id;
                if ( count($exentry[$i][$j][$k]) > 0 ) {
                    for ($l = 0; $l < count($exentry[$i][$j][$k]); $l++) {
                        if ( isset($exentry[$i][$j][$k][$l]) ) {
                            usort($exentry[$i][$j][$k][$l], "entrysort");
                            for ($m = 0; $m < count($exentry[$i][$j][$k][$l]); $m++) {
                                $table->data[] = array($l + 1, $exentry[$i][$j][$k][$l][$m][0],
                                    etest_prot_good_umlauts(str_replace('_' , ' ', $exentry[$i][$j][$k][$l][$m][1]).
                                        ($exentry[$i][$j][$k][$l][$m][0] == 1 ? "  [".$exentry[$i][$j][$k][$l][$m][2]."]" : "")));
                            }
                        }
                    }
                } else {
                    $table->data[] = array("0", "0", "-");
                }
                $tables[] = $table;
            }
        }
    }
}

if ( $format == "showashtml" ) {
    print_header();
    print_tables_html($tables);
    echo $errorstring;
} else if ( $format == "downloadascsv" ) {
    print_tables_csv("ExercseList", $tables);
} else if ( $format == "downloadasexcel" ) {
    print_tables_xls("ExerciseList", $tables);
}
