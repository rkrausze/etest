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

$infostring = ''; // Result and error messages.

$table = new html_table();

$sessions = etest_prot_get_sessions($etest);

etest_read_details($etest);

// Table.
$table->name = get_string('usermatrix', 'etest');

$table->head = array();
$table->align = array();
$table->ordertype = array();
if ( $format == 'showashtml' ) {
    array_push($table->head, '<input type="checkbox" name="cbAll" onclick="top.control.SwitchAll(this.checked)"/>');
    array_push($table->align, 'center');
    array_push($table->ordertype, '');
}
array_push($table->head, get_string('users'), get_string('date'), get_string('time'), get_string('duration', 'etest'));
array_push($table->align , 'left', 'left', 'left', 'left');
array_push($table->ordertype, 'a', 'd', 't', 'u');

$nexercises = etest_n_exercises($etest);
for ($i = 0; $i < $nexercises; $i++) {
    array_push($table->head, 'Ex '.($i + 1));
    array_push($table->align, 'center');
    array_push($table->ordertype, '');
}

array_push($table->head, get_string('worse', 'etest'), get_string('origPts', 'etest'), get_string('origGrade', 'etest'),
    get_string('recalcPts', 'etest'), get_string('recalcGrade', 'etest'),
    get_string('oldPts', 'etest'), get_string('oldGrade', 'etest'));
array_push($table->align, 'center', 'center', 'center', 'center', 'center', 'center', 'center');
array_push($table->ordertype, '', '', '', '', '', '', '');

// Subuser data.
if ( $etest->flags & ETEST_USESUBUSERS ) {
	etest_expand_subuserdata($etest);
	foreach ($etest->sudata as $field) {
		array_push($table->head, $field['name']);
		array_push($table->align, 'left');
		array_push($table->ordertype, '');
	}
}

etest_read_grade($etest);
$ngrades = count($etest->grade);
if ( $ngrades > 0 ) {
	$origcourses = array_fill(0, $ngrades, 0);
	$rccourses = array_fill(0, $ngrades, 0);
} else {
	$origcourses = array();
	$rccourses = array();
}

// Whether recalc is ever used.
$rcused = false;

// If no groups, then make the dummy-group.
$showemptylists = false;
if ( count($groups) == 0 ) {
	$groups = array("");
	$showemptylists = true;
}
etest_prot_make_exercise_list($etest);

$exMap = array();
if ( isset($etest->exblock) ) {
	for ($i = count($etest->exblock) - 1; $i >= 0; $i--) {
		for ($j = count($etest->ex[$i]) - 1; $j >= 0; $j--) {
			$ex = $etest->ex[$i][$j];
			$exMap[$ex->id] = $ex;
		}
	}
}

// Exercise headers
for ($i = 0; $i < count($etest->exalt); $i++) {
	for ($j = 0; $j < count($etest->exalt[$i]); $j++) {
		for ($k = 0; $k < count($etest->exalt[$i][$j]); $k++) {
			$exaltid1 = $etest->exalt[$i][$j][$k]->id;
			if ( $exaltid == '' || $exaltid == $exaltid1 ) {
				$filename = $etest->exalt[$i][$j][$k]->filename;
				if ( $format == "showashtml" && $exaltid == '' ) {
					$filename = '<a href="javascript:parent.control.ExerciseEntriesSingle('.$etest->exalt[$i][$j][$k]->id.
					')" title="'.get_string("show_exercise", "etest").'">'.$filename.'</a>';
				}
				array_push($table->head, $filename);
				array_push($table->align, 'left');
				array_push($table->ordertype, '');
				$curexentry = @$exentry[$exaltid1][''];
				if ( isset($curexentry) && count($curexentry) > 0 ) {
					for ($l = 0; $l < count($curexentry); $l++) {
						array_push($table->head, 'Feld'.($l+1));
						array_push($table->align, 'left');
						array_push($table->ordertype, '');
					}
				}
			}
		}
	}
}

$i = 0;
$exentryAll = $exentry;
foreach ($sessions as $session) {
	if ( !isset($session->excombi) || $session->excombi == "" ) {
		continue;
	}
	etest_prot_load_prot($session, $etest);
	$line = array();
	if ( $format == "showashtml" ) {
		array_push($line, '<input type="checkbox" name="cb_'.$session->id.'" value="'.$session->displayname.'"'.
						(optional_param('cb_'.$session->id, '', PARAM_ALPHANUM) != '' ? ' checked="checked"' : '').'/>');
	}
	array_push($line,
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

	array_push($line, $p->duration);

	if ( $inarchive ) {
		array_push($line, $session->archivetag);
	}

	if ( $format == "showashtml" ) {
		for ($i = 0; $i < count($p->ex); $i++) {
			$addstring = $p->exBest[$i];
			if ( $addstring != '' ) {
				$addstring = ' ['.$addstring.']';
			}
			array_push($line, $p->ex[$i].$addstring);
		}
	} else {
		for ($i = 0; $i < count($p->ex); $i++) {
			array_push($line, $p->ex[$i]);
		}
	}
	array_push($line, $p->worsed);

	array_push($line, $p->orig[0], $p->orig[2]);
	array_push($line, $p->recalc[0], $p->recalc[2]);
	array_push($line, $p->old[0], $p->old[2]);

	if ( $p->recalc[0] != "" ) {
		$rcused = true;
	}

	// Subuser data.
	if ( $etest->flags & ETEST_USESUBUSERS ) {
		$subdat = isset($session->data) ? explode(ETEST_X01, $session->data) : array();
		for ($i = 0; $i < count($etest->sudata); $i++) {
			array_push($line, isset($subdat[$i]) ? $subdat[$i] : '');
		}
	}

	if ( 0 <= $origcourse && $origcourse < $ngrades ) {
		$origcourses[$origcourse]++;
	}
	if ( $p->recalc[0] != "" && 0 <= $rccourse && $rccourse < $ngrades ) {
		$rccourses[$rccourse]++;
	}
	
	// Exercise data
	$exentry = array();
	etest_prot_read_exercise_hist($session, $etest);
	
	$cntPEx = 0;
	for ($i = 0; $i < count($etest->exalt); $i++) {
		for ($j = 0; $j < count($etest->exalt[$i]); $j++) {
			for ($k = 0; $k < count($etest->exalt[$i][$j]); $k++) {
				$exaltid1 = $etest->exalt[$i][$j][$k]->id;
				if ( $exaltid == '' || $exaltid == $exaltid1 ) {
					$filename = $etest->exalt[$i][$j][$k]->filename;
					if ( $format == "showashtml" && $exaltid == '' ) {
						$filename = '<a href="javascript:parent.control.ExerciseEntriesSingle('.$etest->exalt[$i][$j][$k]->id.
						')" title="'.get_string("show_exercise", "etest").'">'.$filename.'</a>';
					}

					// map percent to Points
					$exIsUsed = isset($curexentryAll) && count($curexentryAll) > 0 &&
						isset($exentry[$exaltid1]['']) && count($exentry[$exaltid1]['']) > 0;
							
					if ( $exIsUsed ) {
						$state = $p->ex[$cntPEx++];
						$ex = $exMap[$etest->exalt[$i][$j][$k]->ex];
						$bp = 0;
						if ( $ex->flags & ETEST_EX_CONTINOUSPOINTS ) {
							$bp = round($ex->points * $state / 100);
						} else if ( $state > 98 ) {
							$bp = $ex->points;
						}
						array_push($line, $bp);
					}
					else {
						array_push($line, 0);
					}

					$curexentryAll = @$exentryAll[$exaltid1][''];
					if ( isset($curexentryAll) && count($curexentryAll) > 0 ) {
						$curexentry = @$exentry[$exaltid1][''];
						if ( isset($curexentry) && count($curexentry) > 0 ) {
							for ($l = 0; $l < count($curexentryAll); $l++) {
								array_push($line, $curexentry[$l][0][3]);
							}
						}
						else { // not used by user
							for ($l = 0; $l < count($curexentryAll); $l++) {
								array_push($line, 0);
							}
						}
					}
				}
			}
		}
	}
	
	$table->data[] = $line;
}

$taborig = new html_table();
$taborig->name = get_string('orig-data', 'etest');
$taborig->head = array(get_string('users'), get_string('grades', 'etest'));
$taborig->align = array("left", "left");
for ($i = 0; $i < $ngrades; $i++) {
	$taborig->data[] = array($origcourses[$i], $etest->grade[$i]->shortname);
}

$tabrecalc = new html_table();
$tabrecalc->name = get_string('recalc-data', 'etest');
$tabrecalc->head = array(get_string('users'), get_string('grades', 'etest'));
$tabrecalc->align = array('left', 'left');
for ($i = 0; $i < $ngrades; $i++) {
	$tabrecalc->data[] = array($rccourses[$i], $etest->grade[$i]->shortname);
}

$tables = array($table, $taborig);
if ( $rcused == true ) {
	array_push($tables, $tabrecalc);
}

if ( $format == "showashtml" ) {
	echo etest_safe_header('prot');
	if ( $infostring != '' ) {
		echo $OUTPUT->box($infostring, 'noticebox');
	}
	?>
<form name="fm" method="POST" target="_self" action="prot_userlist.php">
	<input type="hidden" name="a" value="<?php echo $etest->id ?>">
	<input type="hidden" name="sortinfo" value="<?php echo $sortinfo ?>">
	<?php print_tables_html(array($table)); ?>
</form>
<?php
	echo etest_safe_footer('prot');
} else if ( $format == "downloadascsv" ) {
	print_tables_csv("Userlist", $tables);
} else if ( $format == "downloadasexcel" ) {
	print_tables_xls("Userlist", $tables);
}
