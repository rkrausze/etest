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
 * The main lib of the module.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// E-Test constants.
define("ETEST_NO",  "0");
define("ETEST_YES", "1");

// Separators within imploded data fields.
define("ETEST_X01", "$@ETESTX01@$");
define("ETEST_X02", "$@ETESTX02@$");

// E-Test flags.
define("ETEST_INTROLOC", 1);
define("ETEST_USESUBUSERS", 2);
define("ETEST_NOPRINTBUTTON", 4);
define("ETEST_NOCONTINUESESSION", 8);
define("ETEST_NORESULTDIAGRAM", 16);
define("ETEST_USEEXNAMES", 32);

// Exercise flags.
define("ETEST_EX_CONTINOUSPOINTS", "1");

/**
 * Returns the information on whether the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function etest_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted etest record
 **/
function etest_add_instance(stdClass $etest, mod_etest_mod_form $mform = null) {
    global $DB;

    etest_set_form_values($etest, $mform);

    $etest->timecreated = time();
    $etest->timemodified = time();
    etest_pack_subuserdata($etest);

    $id = $DB->insert_record('etest', $etest);

    if ( $id ) {
            $etest->id = $id;
            etest_save_details($etest);
            etest_save_grade($etest);
    }

    return $id;
}

/**
 * Updates an instance of the etest in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $etest An object from the form in mod_form.php
 * @param mod_etest_mod_form $mform
 * @return boolean Success/Fail
 */
function etest_update_instance(stdClass $etest, mod_etest_mod_form $mform = null) {
    global $DB;

    $etest->id = $etest->instance;
    etest_read_details($etest);
    etest_read_grade($etest);
    etest_set_form_values($etest, $mform);
    $etest->timemodified = time();
    etest_pack_subuserdata($etest);
    $res = $DB->update_record('etest', $etest);
    etest_save_details($etest);
    etest_save_grade($etest);
    return $res;
}

/**
 * Takes the values from the form and packs it into the etest-object
 **/
function etest_set_form_values(&$etest, mod_etest_mod_form $mform = null) {
    if ( !isset($etest->enabletimeopen) || $etest->enabletimeopen == 0 ) {
        $etest->timeopen = 0;
    } else {
        $etest->timeopen = make_timestamp(
            $etest->openyear, $etest->openmonth, $etest->openday,
            $etest->openhour, $etest->openminute, 0
        );
    }

    if ( !isset($etest->enabletimeclose) || $etest->enabletimeclose == 0 ) {
        $etest->timeclose = 0;
    } else {
        $etest->timeclose = make_timestamp(
            $etest->closeyear, $etest->closemonth, $etest->closeday,
            $etest->closehour, $etest->closeminute, 0
        );
    }

    // Introref.
    $context = context_module::instance($etest->coursemodule);
    $sourcefile = null;

    if ($etest->introref) {
        $options = array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1);
        file_save_draft_area_files($etest->introref, $context->id, 'mod_etest', 'sourcefile', 0, $options);

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_etest', 'sourcefile');

        // Do we need to remove the draft files ?
        // otherwise the "files" table seems to get full of "draft" records
        // $fs->delete_area_files($context->id, 'user', 'draft', $etest->sourceitemid);.
        $etest->introref = '';
        foreach ($files as $hash => $file) {
            // Main file.
            if ($file->get_sortorder() == 1) {
                $etest->introref = $file->get_filepath().$file->get_filename();
                $sourcefile = $file;
                break;
            }
            // We keep an eye on html-files, in case there is no main file marked ...
            if ( $etest->introref == '' && preg_match("/\.htm(l|)$/i", $file->get_filename()) > 0 ) {
                $etest->introref = $file->get_filepath().$file->get_filename();
                $sourcefile = $file;
            }
        }
        unset($fs, $files, $file, $hash, $options);

    }
    if (is_null($sourcefile) || $etest->introref == '' ) {
        // Sourcefile was missing or not a recognized type - shouldn't happen !!
    }

    // Exblock-ex.
    $mform->get_exercisedata($etest);

    // Now get the exalts.
    if ( isset($etest->exblock) ) {
        for ($i = count($etest->exblock) - 1; $i >= 0; $i--) {
            for ($j = count($etest->ex[$i]) - 1; $j >= 0; $j--) {
                if ( isset($etest->{'exalt_'.$i.'_'.$j}) ) {
                    $options = array('subdirs' => 1, 'maxbytes' => 0, 'maxfiles' => -1);
                    file_save_draft_area_files($etest->{'exalt_'.$i.'_'.$j}, $context->id, 'mod_etest', 'sourcefile',
                        $etest->ex[$i][$j]->id, $options);

                    $fs = get_file_storage();
                    $files = $fs->get_area_files($context->id, 'mod_etest', 'sourcefile', $etest->ex[$i][$j]->id);

                    // Do we need to remove the draft files ?
                    // otherwise the "files" table seems to get full of "draft" records
                    // $fs->delete_area_files($context->id, 'user', 'draft', $etest->sourceitemid);.

                    $filenames = array();
                    $exalt_old = isset($etest->exalt[$i][$j]) ? $etest->exalt[$i][$j] : array();
                    foreach ($files as $hash => $file) {
                        $exalt = (object)null;
                        $exalt->filename = $file->get_filepath().$file->get_filename();
                        if ( preg_match('/\.htm(|l)$/i', $exalt->filename) == 0 ) {
                            continue;
                        }
                        $exalt->ex = $etest->ex[$i][$j]->id;
                        // Check for reusable id's.
                        foreach (array_keys($exalt_old) as $oldkey) {
                            if ( $exalt->filename == $exalt_old[$oldkey]->filename ) {
                                $exalt->id = $exalt_old[$oldkey]->id;
                                unset($exalt_old[$oldkey]);
                            }
                        }
                        // Add to list.
                        $filenames[] = $exalt;
                    }
                    $etest->exalt[$i][$j] = $filenames;
                    unset($fs, $files, $file, $hash, $options);
                }
            }
        }
    }

    if ( $etest->maxsession == '' ) {
        $etest->maxsession = 0;
    }

    // Grading.
    $etest->grade = $mform->get_gradedata();

    $etest->flags = 0;

    // Subuserdata.
    if ( isset($etest->useSubusers) ) {
        $etest->flags |= ETEST_USESUBUSERS;
    }
    $etest->sudata = $mform->get_subuserdata();

    // Other flags.
    if ( isset($etest->noPrintButton) ) {
        $etest->flags |= ETEST_NOPRINTBUTTON;
    }

    if ( isset($etest->noContinueSession) ) {
        $etest->flags |= ETEST_NOCONTINUESESSION;
    }

    if ( isset($etest->noResultDiagram) ) {
        $etest->flags |= ETEST_NORESULTDIAGRAM;
    }

    if ( isset($etest->useExNames) ) {
        $etest->flags |= ETEST_USEEXNAMES;
    }

    // Printform.
    $etest->printform = $etest->printform['text'] != '' ? $etest->printform['format'].'|'.$etest->printform['text'] : '';
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function etest_delete_instance($id) {
    global $DB;

    if (! $etest = $DB->get_record("etest", array("id" => $id))) {
        return false;
    }

    $result = true;

    // Delete any dependent records here.

    if (! $DB->delete_records("etest", array("id" => $etest->id))) {
        $result = false;
    }

    etest_delete_details($id);

    return $result;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function etest_user_outline($course, $user, $mod, $etest) {
    global $DB;
    $report = null;
    if ($records = $DB->get_records_select("etest_session", "etest='$etest->id' AND userid='$user->id'", null,
             "starttime ASC", "starttime, points")) {
        $report = new stdClass();
        $points = array();
        foreach ($records as $record) {
            if (empty($report->time)) {
                $report->time = $record->starttime;
            }
            $points[] = isset($records->points) ? $records->points : "-";
        }
        if (empty($points)) {
            $report->time = 0;
            $report->info = get_string('noactivity', 'etest');
        } else {
            $report->info = get_string('points', 'quiz').': '.implode(', ', $points);
        }
    }
    return $report;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function etest_user_complete($course, $user, $mod, $etest) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in etest activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function etest_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false.
}

/**
 * Prepares the recent activity data
 *
 * This callback function is supposed to populate the passed array with
 * custom activity records. These records are then rendered into HTML via
 * {@link etest_print_recent_mod_activity()}.
 *
 * @param array $activities sequentially indexed array of objects with the 'cmid' property
 * @param int $index the index in the $activities to use for the next record
 * @param int $timestart append activity since this time
 * @param int $courseid the id of the course we produce the report for
 * @param int $cmid course module id
 * @param int $userid check for a particular user's activity only, defaults to 0 (all users)
 * @param int $groupid check for a particular group's activity only, defaults to 0 (all groups)
 * @return void adds items into $activities and increases $index
 */
function etest_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    // TODO.
}

/**
 * Prints single activity item prepared by {@see etest_get_recent_mod_activity()}
 * @return void
 */
function etest_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
}


/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function etest_cron () {
    global $CFG;

    return true;
}

/**
 * Returns all other caps used in the module
 *
 * @example return array('moodle/site:accessallgroups');
 * @return array
 */
function etest_get_extra_capabilities() {
    return array(); // TODO.
}

// Gradebook API.

/**
 * Is a given scale used by the instance of etest?
 *
 * This function returns if a scale is being used by one etest
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $etestid ID of an instance of this module
 * @return bool true if the scale is used by the given etest instance
 */
function etest_scale_used($etestid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of etest.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param $scaleid int
 * @return boolean true if the scale is used by any etest instance
 */
function etest_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Creates or updates grade item for the give etest instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $etest instance object with extra cmidnumber and modname property
 * @return void
 */
function etest_grade_item_update(stdClass $etest) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    /* @example */
    $item = array();
    $item['itemname'] = clean_param($etest->name, PARAM_NOTAGS);
    $item['gradetype'] = GRADE_TYPE_VALUE;
    $item['grademax']  = $etest->grade;
    $item['grademin']  = 0;

    grade_update('mod/etest', $etest->course, 'mod', 'etest', $etest->id, 0, null, $item);
}

/**
 * Update etest grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $etest instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function etest_update_grades(stdClass $etest, $userid = 0) {
    global $CFG, $DB;
    require_once($CFG->libdir.'/gradelib.php');

    /* @example */
    $grades = array(); // Populate array of grade objects indexed by userid.

    grade_update('mod/etest', $etest->course, 'mod', 'etest', $etest->id, 0, $grades);
}

// File API.

/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function etest_get_file_areas($course, $cm, $context) {
    return array(
        'sourcefile' => get_string('sourcefile', 'etest')
    );
}

/**
 * File browsing support for etest file areas
 *
 * @package mod_etest
 * @category files
 *
 * @param file_browser $browser
 * @param array $areas
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return file_info instance or null if not found
 */
function etest_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;
    if (has_capability('moodle/course:managefiles', $context)) {
        // No peaking here for students!!
        return null;
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    if (!$storedfile = $fs->get_file($context->id, 'mod_etest', $filearea, $itemid, $filepath, $filename)) {
        return null;
    }
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $filearea, $itemid, true, true, false);
}

/**
 * Serves the files from the etest file areas
 *
 * @package mod_etest
 * @category files
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the etest's context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 */
function etest_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        send_file_not_found();
    }

    if (!$etest = $DB->get_record('etest', array('id' => $cm->instance))) {
        send_file_not_found();
    }

    require_course_login($course, true, $cm);

    // TODO rk: now first arg used. Doest this disturb anyone?
    //     array_shift($args); // ignore itemid - caching only
    //     $fullpath = "/$context->id/mod_etest/$filearea/0/".implode('/', $args);.
    $fullpath = "/$context->id/mod_etest/$filearea/".implode('/', $args);

    $fs = get_file_storage();
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, $forcedownload); // Download MUST be forced - security!
}

// Navigation API.

/**
 * Extends the global navigation tree by adding etest nodes if there is a relevant content
 *
 * This can be called by an AJAX request so do not rely on $PAGE as it might not be set up properly.
 *
 * @param navigation_node $navref An object representing the navigation tree node of the etest module instance
 * @param stdClass $course
 * @param stdClass $module
 * @param cm_info $cm
 */
function etest_extend_navigation(navigation_node $navref, stdclass $course, stdclass $module, /* ... cm_info ... */ $cm) {
    // Moodle 2.0 has problems with cm_info.
    global $CFG, $DB;

    $etest = $DB->get_record('etest', array('id' => $cm->instance), '*', MUST_EXIST);

    if (has_capability('mod/etest:viewmyattempts', $cm->context) || has_capability('mod/etest:viewallattempts', $cm->context)) {
        $navref->add(
                        get_string('protocol', 'etest'),
                        new moodle_url('/mod/etest/prot/prot.php', array('etest' => $etest->id)),
                        navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Extends the settings navigation with the etest settings
 *
 * This function is called when the context for the page is a etest module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav {@link settings_navigation}
 * @param navigation_node $etestnode {@link navigation_node}
 */
function etest_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $etestnode=null) {
}

// Any other etest functions go here.  Each of them must have a name that starts with etest_.

function etest_read_details(&$etest) {
    global $DB;
    // Read exblocks.
    $etest->exblock = array();
    $etest->exaltHash = array();
    if ( !isset($etest->id) || $etest->id == '' ) {
        return $etest;
    }
    $res = $DB->get_records("etest_exblock", array("etest" => $etest->id), "pos");
    if ( $res !== false ) {
        $etest->exblock = etest_flat_array($res);
        // Read ex's.
        $etest->ex = array();
        for ($i = 0; $i < count($etest->exblock); $i++) {
            $res = $DB->get_records("etest_ex", array("exblock" => $etest->exblock[$i]->id), "pos");
            $etest->ex[$i] = ($res === false) ? array() : etest_flat_array($res);
        }
        // Read exalt's.
        $etest->exalt = array();
        for ($i = 0; $i < count($etest->exblock); $i++) {
            $etest->exalt[$i] = array();
            for ($j = 0; $j < count($etest->ex[$i]); $j++) {
                $res = $DB->get_records("etest_exalt", array("ex" => $etest->ex[$i][$j]->id));
                $etest->exalt[$i][$j] = ($res === false) ? array(): etest_flat_array($res);
                // Fill exaltHash.
                foreach ($etest->exalt[$i][$j] as $exalt) {
                    $etest->exaltHash[$exalt->id] = $exalt;
                }
            }
        }
    }
    return $etest;
}

function etest_save_details(&$etest) {
    if ( !isset($etest->exblock) || $etest->exblock === false) {
        return $etest;
    }
    // Save exblocks.
    for ($i = 0; $i < count($etest->exblock); $i++) {
        $etest->exblock[$i]->etest = $etest->id;
    }
    etest_update_insert_delete_records('etest_exblock', $etest->exblock, array('etest' => $etest->id), 'pos');
    // Save ex's.
    for ($i = 0; $i < count($etest->exblock); $i++) {
        etest_update_insert_delete_records('etest_ex', $etest->ex[$i], array('exblock' => $etest->exblock[$i]->id), 'pos');
    }
    // Save exalt's.
    for ($i = 0; $i < count($etest->exblock); $i++) {
        for ($j = 0; $j < count($etest->ex[$i]); $j++) {
            if ( isset($etest->exalt[$i][$j]) ) {
                etest_update_insert_delete_records('etest_exalt', $etest->exalt[$i][$j], array('ex' => $etest->ex[$i][$j]->id));
            }
        }
    }
    return $etest;
}


/**
 * @param string $table the table name
 * @param array $recs the records to insert
 * @param array $loadcond the condition to load the existing db-entries (to update/delete)
 * @param string $loadsort optional sort condition for returned fields
 * @return array the reloaded array; but tis is also updated directly in $recs
 */
function etest_update_insert_delete_records($table, &$recs, $loadcond, $loadsort = null) {
    global $DB;
    if ( !isset($recs) || $recs === false ) {
        return;
    }
    // Load existing db entries.
    $rec_old = array();
    if ( isset($loadcond) ) {
        $res = $DB->get_records($table, $loadcond);
        if ( $res !== false ) {
            $rec_old = $res;
        }
    }
    // Insert/update the records.
    foreach ($recs as $rec) {
        if ( isset($rec->id) && $rec->id != '' && $DB->record_exists($table, array('id' => $rec->id)) ) {
            $DB->update_record($table, $rec);
            unset($rec_old[$rec->id]);
        } else {
            $DB->insert_record($table, $rec);
        }
    }
    // Delete records not existing anymore.
    foreach ($rec_old as $id => $rec) {
        $DB->delete_records($table, array('id' => $id));
    }
    // Read again to get new ids of inserted.
    $res = $DB->get_records($table, $loadcond, $loadsort);
    if ( $res !== false ) {
        $recs = etest_flat_array($res);
    }
    return $recs;
}

/**
 * Convert a hash into a (flat) array (keeping the order).
 * @param array $arr the hash
 * @return the array
 */
function etest_flat_array($arr) {
    $res = array();
    foreach ($arr as $val) {
        array_push($res, $val);
    }
    return $res;
}

function etest_delete_details($id) {
    global $DB;
    $res = $DB->get_records('etest_exblock', array('etest' => $id), 'pos');
    if ( $res !== false ) {
        $exblock = etest_flat_array($res);
        if ( !$DB->delete_records('etest_exblock', array('etest' => $id)) ) {
            return false;
        }
        // Delete ex's.
        $ex = array();
        for ($i = 0; $i < count($exblock); $i++) {
            $res = $DB->get_records('etest_ex', array('exblock' => $exblock[$i]->id), 'pos');
            $ex[$i] = ($res === false) ? array() : etest_flat_array($res);
            if ( !$DB->delete_records('etest_ex', array('exblock' => $exblock[$i]->id)) ) {
                return false;
            }
        }
        // Delete exalt's.
        for ($i = 0; $i < count($exblock); $i++) {
            for ($j = 0; $j < count($ex[$i]); $j++) {
                if ( !$DB->delete_records('etest_exalt', array('ex' => $ex[$i][$j]->id)) ) {
                    return false;
                }
            }
        }
    }
    return true;
}

function etest_exblock_maxpoints($etest, $iexblock) {
    $points = 0;
    for ($j = 0; $j < count($etest->ex[$iexblock]); $j++) {
        $points += $etest->ex[$iexblock][$j]->points;
    }
    return $points;
}

function etest_maxpoints($etest) {
    $points = 0;
    for ($i = 0; $i < count($etest->exblock); $i++) {
        $points += etest_exblock_maxpoints($etest, $i);
    }
    return $points;
}

function etest_n_exercises($etest) {
    $n = 0;
    for ($i = 0; $i < count($etest->exblock); $i++) {
        $n += count($etest->ex[$i]);
    }
    return $n;
}

// Subuser data.

function etest_expand_subuserdata(&$etest) {
    $s = isset($etest->subuserdata) && $etest->subuserdata != '' ? $etest->subuserdata :
        get_string('firstname', 'etest').ETEST_X02.'text'.ETEST_X02.'true'.ETEST_X02.ETEST_X02.ETEST_X01.
        get_string('lastname', 'etest').ETEST_X02.'text'.ETEST_X02.'true'.ETEST_X02.ETEST_X02.ETEST_X01.
        get_string('birthday', 'etest').ETEST_X02.'date'.ETEST_X02.'false'.ETEST_X02.
        get_string('dd.mm.yyyy', 'etest').ETEST_X02.ETEST_X01;
    if ( !isset($etest->subuserdata) || $etest->subuserdata == '') {
        $etest->subuserprotname = '$'.get_string('firstname', 'etest').'$ $'.get_string('lastname', 'etest').'$';
    }
    $a = explode(ETEST_X01, $s);
    $etest->sudata = array();
    $c = 0;
    foreach ($a as $ai) {
        if ( $ai == "" ) {
            continue;
        }
        $b = explode(ETEST_X02, $ai.ETEST_X02.ETEST_X02);
        $etest->sudata[$c++] = array(
            "name" => $b[0],
            "type" => $b[1],
            "must" => ($b[2] == "true"),
            "comment" => $b[3],
            "data" => $b[4]);
    }
}

function etest_pack_subuserdata(&$etest) {
    if ( !isset($etest->sudata) ) {
        return;
    }
    $a = array();
    foreach ($etest->sudata as $id => $data) {
        $s = "";
        $s = $data["name"].ETEST_X02.$data["type"].ETEST_X02.($data["must"] ? "true" : "false").ETEST_X02.
            $data["comment"].ETEST_X02;
        if ( $data["type"] == "combo" ) {
            $s .= $data["data"];
        }
        $a[] = $s;
    }
    $etest->subuserdata = implode(ETEST_X01, $a);
}

function etest_new_session($etest, $sudata = false) {
    global $DB, $userid;
    $etest_session = (object)null;
    $etest_session->etest = $etest->id;
    $etest_session->userid = $userid;
    if ( $sudata !== false ) {
        $etest_session->data = $sudata;
    }
    $etest_session->starttime = time();
    $etest_session->excombi = etest_generate_excombi($etest);
    $etest_session->addpoints = 0;
    $etest_session->snapshot = ""; // TODO.
    $etest_session->corrstarttime = 0; // TODO.
    $id = $DB->insert_record('etest_session', $etest_session);
    $etest_session->id = $id;
    return $etest_session;
}

/**
 * Generated a new exercise combination.
 *
 * @param stdClass $etest the etest instance
 * @return string the exaltId's, separated by ; and , (blocks and exercises)
 */
function etest_generate_excombi($etest) {
    $res = "";
    for ($i = 0; $i < count($etest->exblock); $i++) {
        $ex = array();
        for ($j = 0; $j < count($etest->ex[$i]); $j++) {
            $n = count($etest->exalt[$i][$j]);
            $ex[$j] = ($n != 0) ? $etest->exalt[$i][$j][mt_rand(0, $n-  1)]->id : '';
        }
        $res .= (($i == 0) ? "" : ";").join(",", $ex);
    }
    return $res;
}

/**
 * Erzeugt aus der excombi einer session ein lineares Feld
 * mit den WWW-Referenzen der Aufgaben
 */
function etest_fill_exuser(&$etest, $excombi) {
    $exuser = array();
    $a = explode(';', $excombi);
    for ($i = 0; $i < count($a); $i++) {
        if ( $a[$i] != '' ) {
            $b = explode(',', $a[$i]);
            for ($j = 0; $j < count($b); $j++) {
                if ( $b[$j] != '' ) {
                    $exalt = $etest->exaltHash[$b[$j]];
                    array_push($exuser, etest_wwwfile($etest, $exalt->ex, $exalt->filename));
                }
            }
        }
    }
    return $exuser;
}

/**
 * Erzeugt aus der excombi einer session ein lineares Feld
 * mit den exalt-ID's der Aufgaben
 */
function etest_fill_exuser_exalt(&$etest, $excombi) {
    $exalt = array();
    $a = explode(';', $excombi);
    for ($i = 0; $i < count($a); $i++) {
        if ( $a[$i] != '' ) {
            $b = explode(',', $a[$i]);
            for ($j = 0; $j < count($b); $j++) {
                if ( $b[$j] != "" ) {
                    array_push($exalt, $b[$j]);
                }
            }
        }
    }
    return $exalt;
}

function etest_get_cmid(&$etest) {
    if ( isset($etest->coursemodule) ) {
        return context_module::instance($etest->coursemodule)->id;
    }
    return context_module::instance(get_coursemodule_from_instance('etest', $etest->id, $etest->course, false,
        MUST_EXIST)->id)->id;
}

/**
 * @param unknown_type $etest
 * @param unknown_type $itemId 0 for intro files, ex-id for exercises
 * @param unknown_type $reference the filename (incl. path)
 * @return string
 */
function etest_wwwfile($etest, $itemid, $reference) {
    global $CFG;
    return $CFG->wwwroot.'/pluginfile.php/'.etest_get_cmid($etest).'/mod_etest/sourcefile/'.$itemid.$reference;
}

// Grading.

function etest_read_grade(&$etest) {
    global $DB;
    // Read exblocks.
    $etest->grade = array();
    if ( !isset($etest->id) || $etest->id == '' ) {
        return $etest;
    }
    $res = $DB->get_records("etest_grade", array("etest" => $etest->id), "minpoints DESC");
    if ( $res !== false ) {
        $etest->grade = etest_flat_array($res);
    }
    return $etest;
}

/**
 * Saves the grades.
 * @param stdClass $etest E-Test instance. Will be upgraded (grade-ids)
 * @return void
 */
function etest_save_grade(stdClass &$etest) {
    foreach ($etest->grade as $grade) {
        $grade->etest = $etest->id;
    }
    etest_update_insert_delete_records('etest_grade', $etest->grade, array('etest' => $etest->id), 'minpoints DESC');
}

/**
 * Asign grade.
 * Delivers the index refering $etest->grade
 *
 * @param stdClass $etest
 * @param array $states
 * @param number $points
 * @param unknown $blockpoints
 * @param number $addpoints additional assigned points (for prot)
 * @return number index refering $etest->grade
 */
function etest_asign_grade(stdClass &$etest, $states, &$points, &$blockpoints, $addpoints = 0) {
    // Count points.
    $points = $addpoints;
    $blocksfailed = 0;
    $n = 0;
    $blockpoints = array();
    for ($i = 0; $i < count($etest->exblock); $i++) {
        $bp = 0;
        for ($j = 0; $j < count($etest->ex[$i]); $j++) {
            if ( !isset($states[$n++]) ) {
                continue;
            }
            $state = $states[$n - 1];
            if ( $state < 0 ) {
                $state = 0;
            }
            if ( $etest->ex[$i][$j]->flags & ETEST_EX_CONTINOUSPOINTS ) {
                $bp += round($etest->ex[$i][$j]->points * $state / 100);
            } else if ( $state > 98 ) {
                $bp += $etest->ex[$i][$j]->points;
            }
        }
        $points += $bp;
        $blockpoints[] = $bp;
        if ( $bp < $etest->exblock[$i]->minpoints ) {
            $blocksfailed++;
        }
    }
    // Now do the grading.
    $ngrades = count($etest->grade);
    for ($i = 0; $i < $ngrades; $i++) {
        if ( $points >= $etest->grade[$i]->minpoints ) {
            $igrade = $i;
            if ( $addpoints == 0 && $blocksfailed > 0 && $i < $ngrades - 1 ) {
                $igrade++;
            }
            return $igrade;
        }
    }
    return $ngrades-1;
}

function etest_displayname(stdClass &$etest, stdClass &$session, stdClass $user) {
    if ( ($etest->flags & ETEST_USESUBUSERS) && isset($etest->subuserprotname) && isset($session->data)) {
        $s = $etest->subuserprotname;
        $s = str_replace('$USER$', $user->firstname.' '.$user->lastname, $s);
        $s = str_replace('$SESSION$', $session->id, $s);
        $d = explode(ETEST_X01, $session->data);
        foreach ($etest->sudata as $fieldid => $field) {
            $s = str_replace('$'.$field['name'].'$', isset($d[$fieldid]) ? $d[$fieldid] : '?', $s);
        }
    } else {
        $s = $user->firstname.' '.$user->lastname.' ('.$session->id.')';
    }
    $session->displayname = $s;
    return $s;
}

function etest_subuser_entry(stdClass $etest, stdClass &$session, $fieldname, $defvalue = '') {
    if ( ($etest->flags & ETEST_USESUBUSERS) && isset($etest->subuserprotname) && isset($session->data)) {
        $d = explode(ETEST_X01, $session->data);
        foreach ($etest->sudata as $fieldid => $field) {
            if ( $field['name'] == $fieldname ) {
                return $d[$fieldid];
            }
        }
    }
    return $defvalue;
}

// Sometimes moodle design do strange things to modest emmbedd-page.

/**
 * Depending on general settings a regular Moodle header ($OUTPUT->header()) or a slim hand made header is returned.
 * Used to avoid undesired frames and margins at embedded or frametop pages implied by selected moodle desing.
 *
 * @param string $type type of emmbedded/frametop page, valid values are 'test' and 'prot'
 * @return string the resulting header
 */
function etest_safe_header($type) {
    global $CFG, $OUTPUT;
    $val = true;
    if ( $type == 'test' ) {
        $val = $CFG->etest_usedesignsfortest;
    } else if ( $type == 'prot' ) {
        $val = $CFG->etest_usedesignsforprot;
    }
    if ( $val ) {
        return $OUTPUT->header();
    } else {
        return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'.
            '<html>'.
            '<head>'.
            '<title>Moodle ETEST '.$type.'</title>'.
            '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.
            '<style>'.
            ' p, td, th, input, div, span, a { font-family: Arial, Helvetica; }'.
            ' table { border-collapse: collapse; border-spacing: 0; }'.
            ' table.generaltable { border: 1px solid #DDD; }'.
            ' th.header { vertical-align: top; background-color: #EEE; border: 1px solid #EEE; font-weight: bold; padding: .5em; '.
            '   vertical-align:top; font-size: 13px; }'.
            ' th.header a { text-decoration: none; }'.
            '.generaltable .cell { background-color: #FFF; border: 1px solid #EEE; border-collapse: collapse; padding: .5em; '.
            '   vertical-align:top; font-size: 13px; }'.
            '</style>'.
            '</head>'.
            '<body>';
    }
}

/**
 * Depending on general settings a regular Moodle footer ($OUTPUT->footer()) or a slim hand made footer is returned.
 * Used to avoid undesired frames and margins at embedded or frametop pages implied by selected moodle desing.
 *
 * @param string $type type of emmbedded/frametop page, valid values are 'test' and 'prot'
 * @return string the resulting footer
 */

function etest_safe_footer($type) {
    global $CFG, $OUTPUT;
    $val = true;
    if ( $type == 'test' ) {
        $val = $CFG->etest_usedesignsfortest;
    } else if ( $type == 'prot' ) {
        $val = $CFG->etest_usedesignsforprot;
    }
    if ( $val ) {
        return $OUTPUT->footer();
    } else {
        return '</body>'.
                '</html>';
    }
}

// Error handling.

$errorstring = "";

function etest_error($a, $b) {
    global $errorstring;
    echo '<p><font face="Arial" size="+1"><b>Fehler</b></font></p>',
        '<p><font face="Arial"><b>', $a, '</b></font></p>',
        '<p><font face="Arial">', $b, '</font></p>';
    $errorstring = $a;
}
