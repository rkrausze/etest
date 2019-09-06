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
 * This page is allows the asigning of groups.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

header("Expires: 0");

require_once('protlib.php');

$PAGE->set_url('/mod/etest/prot/prot_grouping.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('frametop');
$PAGE->blocks->show_only_fake_blocks();

echo etest_safe_header('prot');

$groupdata  = optional_param('groupData', '', PARAM_TEXT);

?>
<?php print_string('groupingText1', 'etest') ?>
<form>
<table border="0">
<?php

function etest_print_grouping($id, $sql, $text, $data) {
    global $groupdata;
?>
    <tr>
        <td>
            <input type="checkbox" name="check<?php echo $id ?>"<?php echo strpos($groupdata, $data) !== false ? " checked" : "" ?>>
        </td>
        <td>
            <?php echo $text ?>
            <input type="hidden" name="sql<?php echo $id ?>" value="<?php echo $sql ?>">
            <input type="hidden" name="text<?php echo $id ?>" value="<?php echo $text ?>">
            <input type="hidden" name="data<?php echo $id ?>" value="<?php echo $data ?>">
        </td>
    </tr>
<?php
}

etest_print_grouping('firstname', '', get_string('firstname', 'etest'), 'firstname');
etest_print_grouping('lastname', '', get_string('lastname', 'etest'), 'lastname');

if ( $etest->flags & ETEST_USESUBUSERS ) {
    etest_expand_subuserdata($etest);

    for ($c = 0; true; $c++) {
        if ( !isset($etest->sudata[$c]) ) {
            break;
        }
        etest_print_grouping($c, ', ses.data as subdata', $etest->sudata[$c]['name'], "sub,$c");
    }
}
?>
</table>
    <input type="button" value="<?php print_string("cancel")?>" onclick="self.close()">
    <input type="button" value="<?php print_string("ok")?>" onclick="Action()">
</form>
<script type="text/javascript">
var f = document.forms[0];

var sql = "";
var text = "";
var data = "";

function Action() {
    Check("firstname");
    Check("lastname");
<?php
if ( $etest->flags & ETEST_USESUBUSERS ) { ?>
    for (var i = 0; f['data'+1*i]; i++)
        Check(i);
<?php
} ?>
    self.opener.GroupingReturn(sql, text, data);
    self.close();
}

function Check(id) {
    if ( f['check'+id].checked ) {
        if ( f['sql'+id].value != "" )
            sql = f['sql'+id].value;
        if ( text != "" )
            text += ", ";
        text += f['text'+id].value;
        if ( data != "" )
            data += ";";
        data += f['data'+id].value;
    }
}
</script>
<?php
echo etest_safe_footer('prot');