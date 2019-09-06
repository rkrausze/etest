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
 * Provide the user data for web-part.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
header('Content-Type: text/javascript');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$a  = optional_param('etest', 0, PARAM_INT);  // E-Ttest instance ID - it should be named as the first character of the module.

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

etest_read_details($etest);

$nexblock = count($etest->exblock);
?>
// data of topics
var Name = new Array("<?php print_string('intro', 'etest') ?>",
    "<?php print_string('START', 'etest') ?>"
<?php
for ($i = 0; $i < $nexblock; $i++) {
    echo ', "', $etest->exblock[$i]->name == '' ? print_string('exblock', 'etest').'_'.($i + 1) : $etest->exblock[$i]->name, '"';
}
?>);
var File = new Array("<?php echo etest_wwwfile($etest, 0, $etest->introref) ?>",
    "START"
<?php
for ($i = 0; $i < $nexblock; $i++) {
    echo ', ""';
}
?>);
var Deep = new Array(<?php echo join(", ", array_fill(0, 2 + $nexblock, 0))?>);
var nTopic = Name.length;
// media data
var Media = new Array(new Array(nTopic));
<?php
for ($i = 0; $i < $nexblock; $i++) {?>
Media[0][<?php echo $i + 2 ?>] = new Array(1);
    Media[0][<?php echo $i + 2 ?>][0] = new Array("", "dummy", "&nbsp;", 0);
<?php
} ?>
// Modi
var M_OVERVIEW = -1;
var M_VIDEO = -1;
var M_EXPERIMENT = -1;
var M_PUBLIKATION = -1;
var M_LINK = -1;
var M_FOLIE = -1;
var M_AUFGABE = -1;
var MediaTitle = new Array("etest");
var MediaTitleShort = new Array("etest");
var MediaStyle = new Array("1");
var MediaCatBase = new Array("-1");
// material
var Mat = new Array("0");
var nMat = 1;
var Sheets = new Array();
var nSheets = 0;
var Title = "<?php echo $etest->name ?>";
var AppName = "ctest";
var SaveType = 1;
var ContMode = 0;
var UsePlan = false;
var nosave = true;
var endTest = "<?php print_string('FINISH_TEST', 'etest'); ?>";
