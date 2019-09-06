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
 * Final page of the test.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('course', 0, PARAM_INT); // Course_module ID, or
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
// output a UTF-8 Header, because Printfom may contain arbitrary UTF8
?><html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body bgcolor="#FFFFFF">
<?php
$userid = optional_param('user', 0, PARAM_INT);

require_login($course->id);

etest_read_details($etest);
etest_read_grade($etest);

// Get parameters.

$etestsessionid = optional_param('ETestSessionId', '', PARAM_ALPHANUM);

// Load user data.

$etestsession = $DB->get_record('etest_session', array('id' => $etestsessionid));

if ( $etestsession !== false ) {
    $exuserstate = explode(',', optional_param('ExUserState', '', PARAM_RAW));
    $points = 0;
    $blockpoints = 0;
    $igrade = etest_asign_grade($etest, $exuserstate, $points, $blockpoints);
    $grade = $etest->grade[$igrade];
    // TODO  UserAddData["Cause"] = Cause;
    $etestsession->grade = $grade->id;
    $etestsession->points = $points;
    $DB->update_record('etest_session', $etestsession);
} else {
    etest_error(
        "Keine Daten vorhanden", "Irritierender Weise gibt es zu ihren zurückgeschickten Daten keine passende Ausgangsdatei. ".
        "Bitte sagen sie einem Verantwortlichen Bescheid.");
};

// Write back HTML.
if ( $errorstring == "" ) {
    // Strip format "4|....".
    if ( $etest->printform != '' ) {
        if ( preg_match("/^(\d)\|/", $etest->printform, $b) === 1 ) {
            $etest->printform = substr($etest->printform, 2);
        }
    }

    // Wrapp safely.
    $printformpacked = "'".addslashes($etest->printform)."'";
    $printformpacked = str_replace("\r", "", $printformpacked);
    $printformpacked = str_replace("\n", "' + \r\n '", $printformpacked);
?>
receiving ...
<script type="text/javascript">
    top.control.CourseAsigned(<?php echo '"', $grade->longname, '", ', etest_maxpoints($etest),
        ', new Array("', join('", "', $blockpoints), '"), \'', addslashes($grade->addtext), '\', ', $printformpacked ?>);
</script>
<?php
}
?>
</body>