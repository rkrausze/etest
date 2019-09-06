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
 * The implementation steps of backup.
 *
 * @package    mod_etest
 * @subpackage backup-moodle2
 * @category   backup
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define all the backup steps that will be used by the backup_etest_activity_task
 */

/**
 * Define the complete etest structure for backup, with file and id annotations
 *
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_etest_activity_structure_step extends backup_activity_structure_step {

    /**
     * Overwritten definition of structure.
     * @see backup_structure_step::define_structure()
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $etest = new backup_nested_element('etest', array('id'), array(
            'name', 'intro', 'introformat',
            'timeopen', 'timeclose', 'introref', 'timelimit', 'flags', 'subuserdata', 'subuserprotname', 'maxsession',
            'printform', 'password', 'subnet', 'timecreated', 'timemodified'));

        $exblocks = new backup_nested_element('exblocks');

        $exblock = new backup_nested_element('exblock', array('id'), array(
            'name', 'minpoints', 'pos'));

        $exs = new backup_nested_element('exs');

        $ex = new backup_nested_element('ex', array('id'), array(
            'name', 'points', 'flags', 'pos'));

        $exalts = new backup_nested_element('exalts');

        $exalt = new backup_nested_element('exalt', array('id'), array(
            'filename'));

        $grades = new backup_nested_element('grades');

        $grade = new backup_nested_element('grade', array('id'), array(
            'shortname', 'longname', 'addtext', 'minpoints'));

        $sessions = new backup_nested_element('sessions');

        $session = new backup_nested_element('session', array('id'), array(
            'userid', 'data', 'starttime', 'endtime', 'excombi', 'addpoints', 'points', 'grade', 'exstates',
            'recalcstates', 'recalcdate', 'recalcgrade', 'recalcpoints', 'archivetag', 'snapshot', 'corrstarttime'));

        $actions = new backup_nested_element('actions');

        $action = new backup_nested_element('action', array('id'), array(
            'starttime', 'action', 'exid', 'timestamp', 'duration' , 'result', 'data'));

        // Build the tree.
        $etest->add_child($exblocks);
        $exblocks->add_child($exblock);

        $exblock->add_child($exs);
        $exs->add_child($ex);

        $ex->add_child($exalts);
        $exalts->add_child($exalt);

        $etest->add_child($grades);
        $grades->add_child($grade);

        $etest->add_child($sessions);
        $sessions->add_child($session);

        $session->add_child($actions);
        $actions->add_child($action);

        // Define sources.
        $etest->set_source_table('etest', array('id' => backup::VAR_ACTIVITYID));

        $exblock->set_source_table('etest_exblock', array('etest' => backup::VAR_PARENTID));

        $ex->set_source_table('etest_ex', array('exblock' => backup::VAR_PARENTID));

        $exalt->set_source_table('etest_exalt', array('ex' => backup::VAR_PARENTID));

        $grade->set_source_table('etest_grade', array('etest' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {
            $session->set_source_table('etest_session', array('etest' => backup::VAR_ACTIVITYID));

            $action->set_source_table('etest_action', array('session' => backup::VAR_PARENTID));
        }

        // Define id annotations.
        $session->annotate_ids('user', 'userid');

        // Define file annotations
        $etest->annotate_files('mod_etest', 'sourcefile', null); // Intro files.
        $ex->annotate_files('mod_etest', 'sourcefile', 'id'); // Exercise files.

        // Return the root element (etest), wrapped into standard activity structure.
        return $this->prepare_activity_structure($etest);
    }
}
