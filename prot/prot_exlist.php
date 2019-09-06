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
 * This page is the protocol page providing an overview of all users/subusers.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('prot_util.php');

$PAGE->set_url('/mod/etest/prot/prot_userlist.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

$cond = '';
if ( $exaltid != '' ) {
    if ( strpos(strtolower($CFG->dbtype), 'postgres') === false ) {
        $cond = " AND CONCAT(',', ses.excombi, ',') REGEXP '[,;]".$exaltid."[,;]' ";
    } else {
        $cond = " AND ',' || ses.excombi || ',' SIMILAR TO '%[,;]".$exaltid."[,;]%' ";
    }
}

$sessions = etest_prot_get_sessions($etest, ' AND ses.archivetag IS NULL'.$cond);
etest_read_details($etest);

etest_prot_make_exercise_list($etest);

/**
 * Simple numerical sort function.
 *
 * @param unknown $a first value
 * @param unknown $b second value
 * @return number th usual <, >, = 0
 */
function entrysort($a, $b) {
    return $b[0] - $a[0];
}

// If no groups, then make the dummy-group.
$showemptylists = false;
if ( count($groups) == 0 ) {
    $groups = array("");
    $showemptylists = true;
}

$tables = array();
for ($i = 0; $i < count($etest->exalt); $i++) {
    for ($j = 0; $j < count($etest->exalt[$i]); $j++) {
        for ($k = 0; $k < count($etest->exalt[$i][$j]); $k++) {
            $exaltid1 = $etest->exalt[$i][$j][$k]->id;
            if ( $exaltid == '' || $exaltid == $exaltid1 ) {
                $grc = 0;
                foreach ($groups as $group) {
                    unset($table);
                    $filename = $etest->exalt[$i][$j][$k]->filename;
                    if ( $format == "showashtml" && $exaltid == '' ) {
                        $filename = '<a href="javascript:parent.control.ExerciseEntriesSingle('.$etest->exalt[$i][$j][$k]->id.
                            ')" title="'.get_string("show_exercise", "etest").'">'.$filename.'</a>';
                    }
                    $table = new html_table();
                    $table->name = get_string('exentries', 'etest').': '.$etest->exblock[$i]->name.' - '.
                        $etest->ex[$i][$j]->name.' - #'.($k + 1).' ('.$filename.')';
                    if ( $grouptext != '' ) {
                        $table->name .= " [".get_string('group', 'etest').'('.$grouptext.'): '.$group."]";
                    }
                    $shortname = $etest->exalt[$i][$j][$k]->filename;
                    $lname = $grouptext == '' ? 29 : 24;
                    if ( strlen($shortname) > $lname ) {
                        $shortname = substr($shortname, -$lname);
                    }
                    if ( $grouptext != '' ) {
                        $shortname .= "_".$group;
                        if ( strlen($shortname) > 29 ) {
                            $shortname = substr($shortname, -29);
                        }
                    }
                    $shortname = preg_replace("/[\\/\\\\]/", "_", $shortname);
                    // Was formerly $shortname = preg_replace("/\\s/", "_", $shortname);.
                    $table->shortname = $shortname;
                    $table->head = array(get_string('exfield', 'etest'), get_string('nusers', 'etest'),
                                    get_string("exfieldentry", 'etest'), get_string("exfieldresult", 'etest'));
                    $table->align = array("left", "left", "left", "left");
                    $table->data = array();
                    $table->anchor = $exaltid1;
                    $table->ordertype = array('', '', 'a', '');
                    $curexentry = @$exentry[$exaltid1][$group];
                    if ( isset($curexentry) && count($curexentry) > 0 ) {
                        for ($l = 0; $l < count($curexentry); $l++) {
                            if ( isset($curexentry[$l]) ) {
                                usort($curexentry[$l], "entrysort");
                                for ($m = 0; $m < count($curexentry[$l]); $m++) {
                                    $value = etest_prot_good_umlauts(str_replace('_' , ' ', $curexentry[$l][$m][1]));
                                    if ( $format == "showashtml" && $curexentry[$l][$m][3] != '') {
                                        $value = '<span style="background-color:'.
                                                ($curexentry[$l][$m][3] >= 98 ? '#98FB98' : '#FF8080').'">'.$value.'</span>';
                                    }
                                    $table->data[] = array($l + 1, $curexentry[$l][$m][0],
                                        $value.($curexentry[$l][$m][0] == 1 ? "  [".$curexentry[$l][$m][2]."]" : ""),
                                        $curexentry[$l][$m][3]);
                                }
                            }
                        }
                    } else if ( !$showemptylists ) {
                        continue;
                    } else {
                        $table->data[] = array("0", "0", "-");
                    }
                    $tables[] = $table;
                }
            }
        }
    }
}

if ( $format == "showashtml" ) {
    echo etest_safe_header('prot');
    print_tables_html($tables);
    echo $errorstring;
    if ( $exaltid != '' ) {?>
<script type="text/javascript">
    window.open("<?php echo etest_wwwfile($etest, $etest->exaltHash[$exaltid]->ex, $etest->exaltHash[$exaltid]->filename) ?>",
        "display");
    top.control.f.target = 'data';
    top.control.f.exaltid.value = '';
</script><?php
    }
    echo etest_safe_footer('prot');
} else if ( $format == "downloadascsv" ) {
    print_tables_csv("ExercseList", $tables);
} else if ( $format == "downloadasexcel" ) {
    print_tables_xls("ExerciseList", $tables);
}
