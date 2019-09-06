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
 * Page displaying the reached points and grade.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');

$PAGE->set_context(null);
$PAGE->set_url('/mod/etest/web/data/final.php'/*, array('id' => $cm->id)*/);
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('embedded');
$PAGE->blocks->show_only_fake_blocks();
echo etest_safe_header('test');

?>
<script type="text/javascript">
    function outData(name, value) {
        if ( value != "" )
            document.writeln("<tr>",
                '<td><b>', name, ':</b></td>',
                '<td>', value, '</td>',
                '</tr>');
    }

</script>
<style>
    table.pillarRight tr td { padding: 0; text-align: right; border: 0; margin: 0; line-height:1px; }
    table.pillarLeft tr td { padding: 0; text-align: left; border: 0; margin: 0; line-height:1px; }
    table.pillar tr td { padding: 0; text-align: right; border: 0; margin: 0; line-height:1px; }
</style>
<p align="right">
<script type="text/javascript">
    document.write(top.control.Title);
</script>,
<?php echo userdate(time()); ?>
</p>
<form method="POST">
<table border="0" width="100%">
    <tr>
        <td>
            <table border="0">
<script type="text/javascript">
    outData("<?php print_string("name") ?>", top.control.UserNameN != "??" ? unescape(top.control.UserNameV)+" "+
        unescape(top.control.UserNameN) : unescape(top.control.UserDisplayName));
    if ( top.control.UserGebDat != "" )
        outData("<?php print_string("birthday", "etest") ?>", unescape(top.control.UserGebDat));
    for (var i = 0; i < top.control.UserData.length; i += 2)
        outData(top.control.UserData[i], top.control.UserData[i+1]);
</script>
            </table>
        </td>
        <td align="right">
<script type="text/javascript">
    if ( top.control.usePrintButton == 1 )
        document.write('<input type="button" value="<?php print_string('print_button', 'etest'); ?>"'+
            ' name="B" onclick="window.open(\'print.php\', \'_self\')">');
</script>
        </td>
    </tr>
</table>
</form>
<div style="text-align:center">
    <b>
    <script type="text/javascript">
        document.write(top.control.CourseName, " <br/>(", "<?php print_string('points_of', 'etest'); ?>".
            replace(/#POINTS#/, top.control.Points).replace(/#TOTALPOINTS#/, top.control.SumPoints), ")");
    </script>
    </b>
    <br><br>
</div>
<div style="text-align:center">
    <script type="text/javascript">
        document.write(top.control.useResultDiagram == 1 ? top.control.sDiagram() : top.control.sResultList());
    </script>
</div>
<?php
echo etest_safe_footer('test');
