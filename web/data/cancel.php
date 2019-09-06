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
 * Ask the user, if he/she really wants zu finish the E-Test.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/lib.php');

$PAGE->set_context(null);
$PAGE->set_url('/mod/etest/web/cancel.php'/*, array('id' => $cm->id)*/);
$PAGE->navbar->ignore_active();
$PAGE->set_pagelayout('embedded');
$PAGE->blocks->show_only_fake_blocks();
echo etest_safe_header('test');

?>
<p align="center">&nbsp;</p>
<p align="center">&nbsp;</p>
<p align="center"><?php print_string('cancel_question', 'etest'); ?></p>
<p align="center">
<script type="text/javascript">
    document.writeln("<?php print_string('cancel_state', 'etest'); ?>".
        replace(/#SOLVED#/, top.control.nSolved()).replace(/#TOTAL#/, top.control.ExUser.length));
</script></p>
<form method="POST">
    <p align="center">
        <input type="button" value="<?php print_string('cancel_button_yes', 'etest'); ?>" name="B3"
            onclick="top.control.StopTest()">&nbsp;
        <input type="button" value="<?php print_string('cancel_button_no', 'etest'); ?>" name="B4"
            onclick="top.control.ContinueTest()">
    </p>
</form>
<p align="center">&nbsp;</p>
<?php
echo etest_safe_footer('test');
