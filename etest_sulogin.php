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
 * Display subuser login or redirect if no subusers are used.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$a  = optional_param('etest', 0, PARAM_INT);  // E-Test instance ID - it should be named as the first character of the module.

if ($id) {
    $cm         = get_coursemodule_from_id('etest', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $etest  = $DB->get_record('etest', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($a) {
    $etest  = $DB->get_record('etest', array('id' => $a), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $etest->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('etest', $etest->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course->id);

$userid = optional_param('user', 0, PARAM_INT);

if ( ($etest->flags & ETEST_USESUBUSERS) == 0 ) {
    header('Location: etest_login.php?course='.$id.'&etest='.$etest->id.'&user='.$userid);
    exit;
}

// Display subuser form.
etest_expand_subuserdata($etest);

$PAGE->set_url('/mod/etest/su_login.php', array('id' => $cm->id));
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('embedded');
$PAGE->blocks->show_only_fake_blocks();
echo etest_safe_header('test');

?>
<p><?php print_string("fillFields", "etest") ?></p>
<form method="POST" action="etest_login.php" name="fm">
    <input type="hidden" name="mode" value="1"/>
    <input type="hidden" name="course" value="<?php echo $cm->id ?>"/>
    <input type="hidden" name="etest" value="<?php echo $etest->id ?>"/>
    <input type="hidden" name="user" value="<?php echo $userid ?>"/>
    <table border="0" cellspacing="0" cellpadding="2">
<?php
foreach ($etest->sudata as $fieldid => $field) {
    $idstring = 'data'.$fieldid;
    ?>
        <tr>
            <td style="white-space:nowrap; font-weight:bold;text-align:right;"><?php echo $field['name'] ?></td>
            <td style="text-align:left;"><?php
    if ($field['type'] != 'combo') {
        ?><input type="text" name="<?php echo $idstring ?>" size="29"/><?php
    } else {
        ?><select name="<?php echo $idstring ?>">
            <option value=""><?php print_string('combo_please_select', 'etest') ?></option><?php
        foreach (preg_split("/(\r|)\n/", $field["data"]) as $val) {
            echo '<option value="', $val, '">', $val, '</option>';
        }
        ?></select><?php
    }
            ?></td>
            <td style="text-align:left; font-style:italic;">
                <?php echo $field['comment'] == '' && $field['type'] == 'date' ?
                    get_string('dateComment', 'etest') : $field['comment']?>
            </td>
        </tr><?php
}
?>
        <tr>
            <td>&nbsp;</td>
            <td style="text-align:left;">
              <input type="button" value="<?php print_string("loginButton", "etest") ?>" name="B1" onclick="return Login();"/></td>
            <td>&nbsp;</td>
        </tr>
    </table>
</form>
<p><script type="text/javascript">

function CivilizeBlanks(s)
{
    return (""+s).replace(/^\s+/, "").replace(/\s+$/, "").replace(/\s+/, " ");
}

function Login()
{
<?php
foreach ($etest->sudata as $fieldid => $field) {
    $idstring = 'data'.$fieldid;
    if ($field["type"] == "text") {
?>
    document.forms["fm"].<?php echo $idstring ?>.value = CivilizeBlanks(document.forms["fm"].<?php echo $idstring ?>.value);
<?php
        if ( $field["must"] ) {?>
            if ( document.forms["fm"].<?php echo $idstring ?>.value == "" ) {
                alert("<?php echo get_string("missingField", 'etest', $field['name']); ?>");
                document.forms["fm"].<?php echo $idstring ?>.focus();
                return false;
            }
<?php
        }
    } else if ($field["type"] == "date") {
?>
    document.forms["fm"].<?php echo $idstring ?>.value = CivilizeBlanks(document.forms["fm"].<?php echo $idstring ?>.value);
<?php
        if ( $field["must"] ) {?>
            if ( document.forms["fm"].<?php echo $idstring ?>.value == "" ) {
                alert("<?php echo get_string("missingField", 'etest', $field['name']); ?>");
                document.forms["fm"].<?php echo $idstring ?>.focus();
                return false;
            }
<?php
        } ?>
        if ( document.forms["fm"].<?php echo $idstring ?>.value != "" &&
                !(/\d{1,2}\.\d{1,2}\.\d{2,4}/.test(document.forms["fm"].<?php echo $idstring ?>.value)) ) {
            alert("<?php echo get_string("formatField", 'etest', $field['name']); ?>");
            document.forms["fm"].<?php echo $idstring ?>.focus();
            return false;
        }
<?php
    } else if ($field["type"] == "combo") {
        if ($field["must"]) {
?>
            if ( document.forms["fm"].<?php echo $idstring ?>.value == "" ) {
                alert("<?php echo get_string("missingSelect", 'etest', $field['name']); ?>");
                document.forms["fm"].<?php echo $idstring ?>.focus();
                return false;
            }
<?php
        }
    }
}
?>
    document.fm.submit();
}

</script></p>
<?php
echo etest_safe_footer('test');
