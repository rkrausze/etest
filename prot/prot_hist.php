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
 * This page is the protocol page providing a list of the actions of a session.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('prot_util.php');

$PAGE->set_url('/mod/etest/prot/prot_hist.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();
echo etest_safe_header('prot');

$table = new html_table();

$session = $DB->get_record('etest_session', array('id' => $sessionid));

etest_read_details($etest);
?>
<html>

<head>
<title><?php print_string('userhist', 'etest'); ?>, User: <?php echo get_string("modulename", "etest"), " ", $etest->name ?></title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta http-equiv="expires" content="0">
<style type="text/css">
  td, p { font-size:10pt;font-family:Arial }
  td.login { font-weight:bold; color:#333333 }
  td.prot { font-weight:bold; color:#8A2BE2 }
  td.sheet { font-weight:bold; color:#7CFC00 }
  td.exercise { font-weight:bold; color:#228B22 }
  td.medium { font-weight:bold; color:#FF4500 }
  td.text { font-weight:bold; color:#000000 }
  td.textaction { font-weight:bold; color:#000000 }
  td.fb { font-weight:bold; color:#8B4513 }
  td.systerror { font-weight:bold; color:#2E8B57 }
  td.material { font-weight:bold; color:#FF008B }
  .exgood { font-weight:bold; color:#008000 }
  .exbad { font-weight:bold; color:#C00000 }
  .exmiddle { font-weight:bold; color:#EEA000 }
  a:active { font-weight:bold; color:#E00000; background-color:#8080F0; }
</style>
</head>

<body >
<table border="1">
    <tr>
        <td>
            <?php print_string('tdelta_in_s', 'etest')?>
        </td>
        <td>
            <?php print_string('action', 'etest')?>
        </td>
        <td>
            <?php print_string('comment', 'etest')?>
        </td>
    </tr>
<?php

function make_ex_name($exid) {
    global $etest, $exuserhash;
    $filestring = $etest->exaltHash[$exid]->filename;
    $j = strrpos($filestring, '/');
    if ( $j !== false ) {
        $filestring = substr($filestring, $j + 1);
    }
    if ( strlen($filestring) > 20 ) {
        $filestring = '<span title="'.$filestring.'">...'.substr($filestring, strlen($filestring) - 18).'</span>';
    }
    return '<B>'.$filestring."</B> <I>(".$exuserhash[$exid].")</I>";
}

etest_prot_load_prot($session, $etest);

$time = 0;
$date = 0;

setlocale (LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

$prot = $DB->get_records('etest_action', array('session' => $session->id), 'starttime ASC, action ASC');
if ( $prot === false ) {?>
    <tr><td colspan="3"><?php print_string('nodata', 'etest')?></td></tr>
<?php
} else {
    foreach ($prot as $line) {
        $datestring = date("M d Y H:i:s", $line->starttime);
        if ( $line->action == 600 ) { // Login.
            echo '<tr bgcolor="#CCCCCC"><td align=right title="', $datestring, '">0</td><td class="login">Login</td><td>',
                $datestring, " ", $line->data, '</td></tr>', "\r\n";
        } else {
            echo '<tr><td align=right title="', $datestring, '">', $line->starttime - $time, '</td>';
            if ( $line->action == 601 ) {
                echo '<td class="prot">', get_string('finalsend', 'etest'), '</td><td>', $line->data;
            } else if ( $line->action == 100 ) {
                echo '<td class="prot">', get_string('text', 'etest'), '</td><td>', $line->data;
            } else if ( $line->action == 602 ) {
                $datastring = preg_replace('/ [0-9,]*$/', '', $line->data);
                if ( $line->result == -1 ) {
                    echo '<td class="exercise">', get_string('exercise', 'etest'), '</td><td>', make_ex_name($line->exid),
                        ' <span class="exshow">', get_string('solutionDisplayed', 'etest'),
                        '</span> <A href="javascript:top.control.ShowHistExercise(\'',
                        $exuser[$exuserhash[$line->exid]], '\',\'\')">=></a> ', str_replace("_", " ", str_replace("|", " | ",
                        etest_prot_good_umlauts($datastring)));
                } else {
                    echo '<td class="exercise">', get_string('exercise', 'etest'), '</td><td>', make_ex_name($line->exid),
                        ' <span class="ex', ($line->result > 98 ? "good" : ($line->result > 0 ? "middle" : "bad")), '">',
                        $line->result,
                        '%</span> <A href="javascript:top.control.ShowHistExercise(\'', $exuser[$exuserhash[$line->exid]], '\',\'',
                        str_replace('%', "###", urlencode(etest_prot_good_umlauts($datastring))), '\')">=></a> ',
                        str_replace("_", " ", str_replace("|", " | ", etest_prot_good_umlauts($datastring)));
                }
            }
            echo '</td></tr>', "\r\n";
        }
        $time = $line->starttime;
    }
}
?>
</table>
<?php
if ( $errorstring != "" ) {
    echo '<font color="#FF0000"><b>', $errorstring, '</b></font>';
}
echo etest_safe_footer('prot');
