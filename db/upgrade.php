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
 * This file keeps track of upgrades to the evaluaciones block
 *
 * Sometimes, changes between versions involve alterations to database structures
 * and other major things that may break installations.
 *
 * The upgrade function in this file will attempt to perform all the necessary
 * actions to upgrade your older installation to the current version.
 *
 * If there's something it cannot do itself, it will tell you what you need to do.
 *
 * The commands in here will all be database-neutral, using the methods of
 * database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package local
 * @subpackage sync
 * @copyright Hans Jeria (hansjeria@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @param int $oldversion
 * @param object $block
 */


function xmldb_local_sync_upgrade($oldversion) {
	global $CFG, $DB;

	$dbman = $DB->get_manager();
	
	if ($oldversion < 2016122701) {
	
		// Define table sync_data to be created.
		$table = new xmldb_table('sync_data');
	
		// Adding fields to table sync_data.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('academicperiodid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('categoryid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('campus', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);
		$table->add_field('campusshort', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);
		$table->add_field('type', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, null);
		$table->add_field('year', XMLDB_TYPE_INTEGER, '11', null, null, null, null);
		$table->add_field('semester', XMLDB_TYPE_CHAR, '50', null, null, null, null);
		$table->add_field('semesterlong', XMLDB_TYPE_CHAR, '50', null, null, null, null);
		$table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table sync_data.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	
		// Conditionally launch create table for sync_data.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// Define table sync_enrol to be created.
		$table = new xmldb_table('sync_enrol');
	
		// Adding fields to table sync_enrol.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('shortname', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('role', XMLDB_TYPE_CHAR, '45', null, XMLDB_NOTNULL, null, 'student');
		$table->add_field('user', XMLDB_TYPE_CHAR, '80', null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table sync_enrol.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
	
		// Conditionally launch create table for sync_enrol.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
	
		// Define table sync_course to be created.
		$table = new xmldb_table('sync_course');
	
		// Adding fields to table sync_course.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('syncid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('fullname', XMLDB_TYPE_CHAR, '150', null, XMLDB_NOTNULL, null, null);
		$table->add_field('shortname', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
		$table->add_field('idnumber', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, null);
	
		// Adding keys to table sync_course.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('sync_data_id', XMLDB_KEY_FOREIGN, array('syncid'), 'sync_data', array('id'));
	
		// Conditionally launch create table for sync_course.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
		
		$table = new xmldb_table('sync_data');
		$field = new xmldb_field('responsible', XMLDB_TYPE_CHAR, '80', null, null, null, null, 'timemodified');
		
		// Conditionally launch add field responsible.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		// Define field status to be added to sync_data.
		$table = new xmldb_table('sync_data');
		$field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'responsible');
		
		// Conditionally launch add field status.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
	
		// Sync savepoint reached.
		upgrade_plugin_savepoint(true, 2016122701, 'local', 'sync');
	}
	
	if ($oldversion < 2016122702) {
	
		// Define field academicperiodname to be added to sync_data.
		$table = new xmldb_table('sync_data');
		$field = new xmldb_field('academicperiodname', XMLDB_TYPE_CHAR, '200', null, null, null, null, 'academicperiodid');
	
		// Conditionally launch add field academicperiodname.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		// Define field academicperiodid to be dropped from sync_data.
		$table = new xmldb_table('sync_data');
		$field = new xmldb_field('semesterlong');
		
		// Conditionally launch drop field academicperiodid.
		if ($dbman->field_exists($table, $field)) {
			$dbman->drop_field($table, $field);
		}
		
		// Rename field dataid on table sync_course to 'dataid'.
		$table = new xmldb_table('sync_course');
		$field = new xmldb_field('syncid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'id');
		
		// Launch rename field dataid.
		$dbman->rename_field($table, $field, 'dataid');
			
		// Sync savepoint reached.
		upgrade_plugin_savepoint(true, 2016122702, 'local', 'sync');
	}
	
	if ($oldversion < 2016122703) {
	
		// Changing the default of field status on table sync_data to 0.
		$table = new xmldb_table('sync_data');
		$field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'responsible');
	
		// Launch change of default for field status.
		$dbman->change_field_default($table, $field);
	
		// Sync savepoint reached.
		upgrade_plugin_savepoint(true, 2016122703, 'local', 'sync');
	}
	
	if ($oldversion < 2016122801) {
	
		// Define field categoryid to be added to sync_course.
		$table = new xmldb_table('sync_course');
		$field = new xmldb_field('categoryid', XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, null, 'idnumber');
	
		// Conditionally launch add field categoryid.
		if (!$dbman->field_exists($table, $field)) {
			$dbman->add_field($table, $field);
		}
		
		// Changing nullability of field idnumber on table sync_course to null.
		$table = new xmldb_table('sync_course');
		$field = new xmldb_field('idnumber', XMLDB_TYPE_INTEGER, '12', null, null, null, null, 'shortname');
		
		// Launch change of nullability for field idnumber.
		$dbman->change_field_notnull($table, $field);
	
		// Sync savepoint reached.
		upgrade_plugin_savepoint(true, 2016122801, 'local', 'sync');
	}
	
	if ($oldversion < 2016122802) {
		
		// Define table sync_history to be created.
		$table = new xmldb_table('sync_history');
		
		// Adding fields to table sync_history.
		$table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
		$table->add_field('dataid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('executiondate', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('countcourses', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		$table->add_field('countenrols', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
		
		// Adding keys to table sync_history.
		$table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
		$table->add_key('dataid', XMLDB_KEY_FOREIGN, array('dataid'), 'sync_data', array('id'));
		
		// Conditionally launch create table for sync_history.
		if (!$dbman->table_exists($table)) {
			$dbman->create_table($table);
		}
			
		// Sync savepoint reached.
		upgrade_plugin_savepoint(true, 2016122802, 'local', 'sync');
	}
	
	if ($oldversion < 2016122803) {
	
		// Rename field course on table sync_enrol to NEWNAMEGOESHERE.
		$table = new xmldb_table('sync_enrol');
		$field = new xmldb_field('shortname', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'id');
	
		// Launch rename field course.
		$dbman->rename_field($table, $field, 'course');
	
		// Changing precision of field course on table sync_enrol to (200).
		$table = new xmldb_table('sync_enrol');
		$field = new xmldb_field('course', XMLDB_TYPE_CHAR, '200', null, XMLDB_NOTNULL, null, null, 'id');
		
		// Launch change of precision for field course.
		$dbman->change_field_precision($table, $field);
		
		// Changing precision of field course on table sync_enrol to (200).
		$table = new xmldb_table('sync_enrol');
		$field = new xmldb_field('user', XMLDB_TYPE_CHAR, '200', null, XMLDB_NOTNULL, null, null, 'id');
		
		// Launch change of precision for field course.
		$dbman->change_field_precision($table, $field);
			
		// Sync savepoint reached.
		upgrade_plugin_savepoint(true, 2016122803, 'local', 'sync');
	}
	
	if ($oldversion < 2016122804) {
	
		// Changing precision of field fullname on table sync_course to (400).
		$table = new xmldb_table('sync_course');
		$field = new xmldb_field('fullname', XMLDB_TYPE_CHAR, '400', null, XMLDB_NOTNULL, null, null, 'dataid');
	
		// Launch change of precision for field fullname.
		$dbman->change_field_precision($table, $field);
		
		// Changing precision of field shortname on table sync_course to (200).
		$table = new xmldb_table('sync_course');
		$field = new xmldb_field('shortname', XMLDB_TYPE_CHAR, '200', null, XMLDB_NOTNULL, null, null, 'fullname');
		
		// Launch change of precision for field shortname.
		$dbman->change_field_precision($table, $field);
		
		// Changing precision of field idnumber on table sync_course to (20).
		$table = new xmldb_table('sync_course');
		$field = new xmldb_field('idnumber', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'shortname');
		
		// Launch change of precision for field idnumber.
		$dbman->change_field_precision($table, $field);
		
		// Changing precision of field categoryid on table sync_course to (20).
		$table = new xmldb_table('sync_course');
		$field = new xmldb_field('categoryid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'idnumber');
		
		// Launch change of precision for field categoryid.
		$dbman->change_field_precision($table, $field);
	
		// Sync savepoint reached.
		upgrade_plugin_savepoint(true, 2016122804, 'local', 'sync');
	}

    if ($oldversion < 2020041300) {

        // Define table sync_result to be created.
        $table = new xmldb_table('sync_result');

        // Adding fields to table sync_result.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('executiondate', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('result', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table sync_result.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for sync_result.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Sync savepoint reached.
        upgrade_plugin_savepoint(true, 2020041300, 'local', 'sync');
    }
    if ($oldversion < 2021042700) {

        // Changing type of field idnumber on table sync_course to char.
        $table = new xmldb_table('sync_course');
        $field = new xmldb_field('idnumber', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'shortname');

        // Launch change of type for field idnumber.
        $dbman->change_field_type($table, $field);

        // Sync savepoint reached.
        upgrade_plugin_savepoint(true, 2021042700, 'local', 'sync');
    }
    
	return true;
}