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
 * The implementation of the steps for restore.
 *
 * @package    mod_etest
 * @subpackage backup-moodle2
 * @category   backup
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the restore steps that will be used by the restore_etest_activity_task
 */

/**
 * Structure step to restore one etest activity
 *
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_etest_activity_structure_step extends restore_activity_structure_step {

    /**
     * Defines the structur for restoring.
     * @see restore_structure_step::define_structure()
     */
    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('etest', '/activity/etest');
        $paths[] = new restore_path_element('etest_exblock', '/activity/etest/exblocks/exblock');
        $paths[] = new restore_path_element('etest_ex', '/activity/etest/exblocks/exblock/exs/ex');
        $paths[] = new restore_path_element('etest_exalt', '/activity/etest/exblocks/exblock/exs/ex/exalts/exalt');
        $paths[] = new restore_path_element('etest_grade', '/activity/etest/grades/grade');

        if ($userinfo) {
            $paths[] = new restore_path_element('etest_session', '/activity/etest/sessions/session');
            $paths[] = new restore_path_element('etest_action', '/activity/etest/sessions/session/actions/action');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restore the E-Test-Data for table etest.
     * @param unknown $data
     */
    protected function process_etest($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Insert the etest record.
        $newitemid = $DB->insert_record('etest', $data);
        // Immediately after inserting "activity" record, call this.
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restore the E-Test-Data for table etest_exblock.
     * @param unknown $data
     */
    protected function process_etest_exblock($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->etest = $this->get_new_parentid('etest');

        $newitemid = $DB->insert_record('etest_exblock', $data);
        $this->set_mapping('etest_exblock', $oldid, $newitemid);
    }

    /**
     * Restore the E-Test-Data for table etest_ex.
     * @param unknown $data
     */
    protected function process_etest_ex($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->exblock = $this->get_new_parentid('etest_exblock');

        $newitemid = $DB->insert_record('etest_ex', $data);
        $this->set_mapping('etest_ex', $oldid, $newitemid, true);
    }

    /**
     * Restore the E-Test-Data for table etest_exalt.
     * @param unknown $data
     */
    protected function process_etest_exalt($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->ex = $this->get_new_parentid('etest_ex');

        $newitemid = $DB->insert_record('etest_exalt', $data);
        $this->set_mapping('etest_exalt', $oldid, $newitemid);
    }

    /**
     * Restore the E-Test-Data for table etest_grade.
     * @param unknown $data
     */
    protected function process_etest_grade($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->etest = $this->get_new_parentid('etest');

        $newitemid = $DB->insert_record('etest_grade', $data);
        $this->set_mapping('etest_grade', $oldid, $newitemid);
    }

    /**
     * Restore the E-Test-Data for table etest_session.
     * @param unknown $data
     */
    protected function process_etest_session($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->etest = $this->get_new_parentid('etest');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->starttime = $this->apply_date_offset($data->starttime);
        $data->endtime = $this->apply_date_offset($data->endtime);
        $data->recalcdate = $this->apply_date_offset($data->recalcdate);

        // Remap excombi.
        if ( isset( $data->excombi) ) {
            $a = explode(';', $data->excombi);
            for ($i = 0; $i < count($a); $i++) {
                if ( $a[$i] != '' ) {
                    $b = explode(',', $a[$i]);
                    for ($j = 0; $j < count($b); $j++) {
                        if ( $b[$j] != '' ) {
                            $b[$j] = $this->get_mappingid('etest_exalt', $b[$j]);
                        }
                    }
                    $a[$i] = implode(',', $b);
                }
            }
            $data->excombi = implode(';', $a);
        }

        if ( isset($data->grade) ) {
            $data->grade = $this->get_mappingid('etest_grade', $data->grade);
        }

        $newitemid = $DB->insert_record('etest_session', $data);
        $this->set_mapping('etest_session', $oldid, $newitemid);
    }

    /**
     * Restore the E-Test-Data for table etest_action.
     * @param unknown $data
     */
    protected function process_etest_action($data) {
        global $DB;

        $data = (object)$data;

        $data->session = $this->get_new_parentid('etest_session');

        // Remap exid (contains exaltid).
        if ( isset($data->exid) ) {
            $data->exid = $this->get_mappingid('etest_exalt', $data->exid);
        }
        $newitemid = $DB->insert_record('etest_action', $data);
    }

    /**
     * Overwrite the work over afetr pure restoring.
     * We have to add the related files.
     * @see restore_structure_step::after_execute()
     */
    protected function after_execute() {
        // Add etest related files.
        // Intro files.
        $this->add_related_files('mod_etest', 'sourcefile', null);
        // Files for exercises.
        $this->add_related_files('mod_etest', 'sourcefile', 'etest_ex');

    }
}
