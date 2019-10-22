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
 * Prints a list of etest instances.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);   // Course.

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$eventdata = array('context' => context_course::instance($id));
$event = \mod_etest\event\course_module_instance_list_viewed::create($eventdata);
$event->add_record_snapshot('course', $course);
$event->trigger();

$coursecontext = context_course::instance($course->id);

// Moodle 1.4+ requires sesskey to be passed in forms.
if (isset($USER->sesskey)) {
    $sesskey = '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />';
} else {
    $sesskey = '';
}

$PAGE->set_url('/mod/etest/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);
$PAGE->navbar->add(get_string('modulenameplural', 'etest'));

echo $OUTPUT->header();

// Get all required strings.

$stretests = get_string("modulenameplural", "etest");
$stretest  = get_string("modulename", "etest");

// Get all the appropriate data.

if (! $etests = get_all_instances_in_course("etest", $course)) {
    notice("There are no etests", "../../course/view.php?id=$course->id");
    die;
}

// Print the list of instances (your module will probably extend this).

$table = new html_table();

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'));
    $table->align = array('center');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'));
    $table->align = array('center');
} else {
    $table->head  = array();
    $table->align = array();
}

$strupdate = get_string('update');

$useupdatecolumn = has_capability('moodle/course:manageactivities', $coursecontext);
$usereportcolumn = has_capability('moodle/site:viewreports', $coursecontext) ||
                   has_capability('mod/etest:viewmyattempts', $coursecontext) ||
                   has_capability('mod/etest:viewallattempts', $coursecontext);

if ( $useupdatecolumn ) {
    array_push($table->head, $strupdate);
    array_push($table->align, "center");
}

array_push($table->head,
    get_string("name"),
    get_string("users")." / ".get_string("subusers", "etest")." / ".get_string("sessions", "etest"),
    get_string("archivedusers", 'etest')." / ".get_string("subusers", "etest")." / ".get_string("sessions", "etest")
);

array_push($table->align,
    "left", "left", 'left'
);

if ( $usereportcolumn ) {
    array_push($table->head, get_string("protocol", "etest"));
    array_push($table->align, "center");
}

foreach ($etests as $etest) {
    if (!$etest->visible) {
        // Show dimmed if the mod is hidden.
        $link = "<a class=\"dimmed\" href=\"view.php?id=$etest->coursemodule\">$etest->name</a>";
    } else {
        // Show normal if the mod is visible.
        $link = "<a href=\"view.php?id=$etest->coursemodule\">$etest->name</a>";
    }

    $data = array ();

    if ($course->format == "weeks" || $course->format == "topics") {
        array_push($data, $etest->section);
    }

    if ( $useupdatecolumn ) {
        $updatebutton = ''
        .   '<form '.(isset($CFG->framename) ? 'target="'.$CFG->framename.'"' : '')
        .   ' method="get" action="'.$CFG->wwwroot.'/course/mod.php">'
        .   '<input type="hidden" name="update" value="'.$etest->coursemodule.'" />'
        .   $sesskey
        .   '<input type="submit" value="'.$strupdate.'" />'
        .   '</form>';
        array_push($data, $updatebutton);
    }

    array_push($data, $link);

    // Number of users.
    array_push($data,
        $DB->count_records_select("etest_session", "etest = $etest->id AND archivetag IS NULL", null,
            "COUNT(DISTINCT userid)")." / ".
        $DB->count_records_select("etest_session", "etest = $etest->id AND archivetag IS NULL", null,
            "COUNT(DISTINCT data)")." / ".
        $DB->count_records_select("etest_session", "etest = $etest->id AND archivetag IS NULL"));
    // Number of archived users.
    array_push($data,
        $DB->count_records_select("etest_session", "etest = $etest->id AND archivetag IS NOT NULL", null,
            "COUNT(DISTINCT userid)")." / ".
        $DB->count_records_select("etest_session", "etest = $etest->id AND archivetag IS NOT NULL", null,
            "COUNT(DISTINCT data)")." / ".
        $DB->count_records_select("etest_session", "etest = $etest->id AND archivetag IS NOT NULL"));

    if ( $usereportcolumn ) {
        array_push($data,
        "<a href=\"prot/prot.php?id=$etest->coursemodule\" target=\"_blank\">".get_string("protocol", "etest")."</a>");
    }

    $table->data[] = $data;
}

echo '<br /><span id="markHeight"></span>';

echo $OUTPUT->heading(get_string('modulenameplural', 'etest'), 2);
echo html_writer::table($table);

?>
<script type="text/javascript">
//<![CDATA[
  function absoluteTop(obj)
  {
    var w = obj.offsetTop;
    while ( obj.offsetParent )
    {
      obj = obj.offsetParent;
      w += obj.offsetTop;
    }
    return w;
  }

  var headsize = absoluteTop(document.getElementById("markHeight"))+34;
  for (var i = 0; i < document.links.length; i++)
    if ( document.links[i].href.search(/prot\/prot.php/) != -1 )
      document.links[i].href = document.links[i].href+"&headsize="+headsize;

//]]>
</script>
<?php

// Finish the page.
echo $OUTPUT->footer();
