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
 * This file keeps track of upgrades to the etest module.
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package    mod_etest
 * @copyright  2012 Rüdiger Krauße
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrades the DB from older versions.
 * @param int $oldversion the currently installed version
 * @return bool sucess or failed
 */
function xmldb_etest_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    // Moodle v2.2.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.3.0 release upgrade line.
    // Put any upgrade step following this.

    if ( $oldversion < 2013062100 ) {

        // Introduce backupable separators.

        // In etest subuserdata.
        $countetests = $DB->count_records('etest');
        $etests = $DB->get_recordset('etest');

        $pbar = new progress_bar('etestupdate1', 500, true);
        $i = 0;
        foreach ($etests as $etest) {
            $i++;
            $etest->subuserdata = str_replace("\x01", "$@ETESTX01@$", $etest->subuserdata);
            $etest->subuserdata = str_replace("\x02", "$@ETESTX02@$", $etest->subuserdata);
            $DB->update_record('etest', $etest);
            $pbar->update($i, $countetests, "Updating separators in etests ($i/$countetests)");
        }
        $etests->close();

        // In session data.
        $countsessions = $DB->count_records('etest_session');
        $sessions = $DB->get_recordset('etest_session');

        $pbar = new progress_bar('etestupdate2', 500, true);
        $i = 0;
        foreach ($sessions as $session) {
            $i++;
            $session->data = str_replace("\x01", "$@ETESTX01@$", $session->data);
            $DB->update_record('etest_session', $session);
            $pbar->update($i, $countsessions, "Updating separators in sessions ($i/$countsessions)");
        }
        $sessions->close();
    }

    if ($oldversion < 2013112600) {

        // Changing type of field result on table etest_action to number
        $table = new xmldb_table('etest_action');
        $field = new xmldb_field('result', XMLDB_TYPE_NUMBER, '6, 2', null, null, null, null, 'duration');

        // Launch change of type for field result
        $dbman->change_field_type($table, $field);

        // etest savepoint reached
        upgrade_mod_savepoint(true, '2013112600', 'etest');
    }
    return true;
}
