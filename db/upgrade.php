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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_blobstorebackend
 * @copyright  2026 YOURNAME
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the blobstorebackend local plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_local_blobstorebackend_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026021801) {

        // Define table blobstorebackend to be created.
        $table = new xmldb_table('blobstorebackend');

        // Adding fields to table blobstorebackend.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, true, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, null, null);
        $table->add_field('context1', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, false, null, null);
        $table->add_field('context2', XMLDB_TYPE_CHAR, '255', null, null, false, null, null);
        $table->add_field('context3', XMLDB_TYPE_CHAR, '255', null, null, false, null, null);
        $table->add_field('context4', XMLDB_TYPE_CHAR, '255', null, null, false, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, false, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, '0', null);

        // Adding keys to table blobstorebackend.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        // $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Adding indexes to table blobstorebackend.
        $table->add_index('context1', XMLDB_INDEX_NOTUNIQUE, ['context1']);
        // $table->add_index('context2', XMLDB_INDEX_NOTUNIQUE, ['context2']);
        // $table->add_index('context3', XMLDB_INDEX_NOTUNIQUE, ['context3']);
       //   $table->add_index('context4', XMLDB_INDEX_NOTUNIQUE, ['context4']);
        $table->add_index('userid_idx', XMLDB_INDEX_NOTUNIQUE, ['userid']);

        // Conditionally launch create table for blobstorebackend.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // blobstorebackend savepoint reached.
        upgrade_plugin_savepoint(true, 2026021801, 'local', 'blobstorebackend');
	}

    if ($oldversion < 2026030400) {

        // Queue an adhoc task to import old data from the filesystem into the database.
        require_once($CFG->dirroot . '/local/blobstorebackend/classes/task/import_from_disk.php');
        $task = new \local_blobstorebackend\task\import_from_disk();
        \core\task\manager::queue_adhoc_task($task);

        upgrade_plugin_savepoint(true, 2026030400, 'local', 'blobstorebackend');

    }

    return true;
}
