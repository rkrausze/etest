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
 * This page starts the recalculation of exercise-entries (if an execise had to be corrected).
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');
require_once('prot_util.php');

$PAGE->set_url('/mod/etest/prot/prot_recalc1.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();
echo etest_safe_header('prot');

etest_read_details($etest);
$sessions = etest_prot_get_sessions($etest, " AND ses.id = ".$sessionid." ");
$session = current($sessions);
etest_prot_load_prot($session, $etest);

?>
User <?php echo $session->displayname ?>
<form action="prot_recalc2.php" name="fm" method="post">
    <input type="hidden" name="sessionid" value="">
    <input type="hidden" name="a" value="">
    <input type="hidden" name="recalcstates" value="">
<?php
for ($i = 0; $i < count($exuser); $i++) {
    echo '<input type="hidden" name="actionid', $i, '" value="', $exuseractionid[$i], '">',
         '<input type="hidden" name="pr', $i, '" value="">', "\r\n";
}
?>);
</form>
<script type="text/javascript">
var ExUser = new Array(<?php
for ($i = 0; $i < count($exuser); $i++) {
    echo ($i == 0 ? "" : ",\r\n"), '"', $exuser[$i], '"';
}
?>);
var ExValue = new Array(<?php
for ($i = 0; $i < count($exuser); $i++) {
    echo ($i == 0 ? "" : ",\r\n"), '"',
        ($exuservalue[$i] ? etest_prot_good_umlauts(str_replace('%', '###', $exuservalue[$i])) : "---"), '"';
}
?>);
var ExUserState = new Array(<?php
for ($i = 0; $i < count($exuser); $i++) {
    echo ($i == 0 ? "" : ", "), '"', $exuserstate[$i], '"';
}
?>);
var SessionId = <?php echo $sessionid ?>;
var a = <?php echo $a ?>;
var count = -1;
var sError = "";

function LoadNext(res) {
  if ( count >= 0 ) {
      //alert(res+" "+ExUserState[count]);
    if ( ExUserState[count] <= res /* || top.data.display.Type[0] != 2*/ ) { // dieser Test wg. Kreuzelunfall
      ExUserState[count] = res;
      if ( top.data.display.PR )
          document.fm["pr"+count].value = top.data.display.PR;
      //alert(document.fm["pr"+count].value);
    }
  }
  count++;
  if ( count >= ExUser.length ) { // Daten senden
    document.fm.sessionid.value = SessionId;
    document.fm.a.value = a;
    document.fm.recalcstates.value = ExUserState.join(",");
    document.fm.submit();
  }
  else // normal
  {
    if ( ExValue[count] != "---" )
      top.control.ShowHistExercise(ExUser[count], ExValue[count]);
    else
      setTimeout("LoadNext(-2)", 5);
  }
}

LoadNext();

</script>
<?php
echo etest_safe_footer('prot');
