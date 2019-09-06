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
 * Prints a particular instance of etest
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$a  = optional_param('a', 0, PARAM_INT);  // E-Test instance ID - it should be named as the first character of the module.

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

if ( isset($_SESSION['ETEST_MOD_FORM_RELOAD']) ) {
    $s = $_SESSION['ETEST_MOD_FORM_RELOAD'];
    unset($_SESSION['ETEST_MOD_FORM_RELOAD']);
    redirect("$CFG->wwwroot/course/modedit.php?update=$cm->id&return=0&".$s);
}
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$eventdata = array();
$eventdata['objectid'] = $etest->id;
$eventdata['context'] = $context;

$event = \mod_etest\event\course_module_viewed::create($eventdata);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/etest/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($etest->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here.
echo $OUTPUT->header();

?>
<form action="etest.php" target="_blank">
<input type="hidden" name="action" value="START">
<input type="hidden" name="course" value="<?php echo $id; ?>">
<input type="hidden" name="etest" value="<?php echo $etest->id; ?>">
<input type="hidden" name="user" value="<?php echo $USER->id ?>">
<?php
echo $OUTPUT->heading($etest->name);

if ($etest->intro) { // Conditions to show the intro can change to look for own settings or whatever.
    echo $OUTPUT->box(format_module_intro('etest', $etest, $cm->id), 'generalbox mod_introbox', 'etestintro');
}
?>
<div align="center"><b>
  <input type="submit" value="<?php print_string('startbutton', 'etest') ?>">
</b></div>
</form>
<?php
// Finish the page.
echo $OUTPUT->footer();

