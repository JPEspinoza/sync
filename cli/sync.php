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
 *
 *
 * @package    local
 * @subpackage sync
 * @copyright  2016-2017 Hans Jeria (hansjeria@gmail.com)
 * @copyright  2017 Mark Michaelsen (mmichaelsen678@gmail.com)
 * @copyright  2017 Mihail Pozarski (mpozarski944@gmail.com)
 * @copyright  2019 Joaquín Cerda (joaquin.cerda@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true); // Comment this line to execute on web
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . "/config.php");
require_once($CFG->dirroot . "/local/sync/locallib.php");
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/course/lib.php');

global $DB, $CFG;

// Now get cli options
list($options, $unrecognized) = cli_get_params(array(
    'help' => false,
    'debug' => false,
    'academicperiodid' => 0,
    'verbose'=>false
), array(
    'h' => 'help',
    'd' => 'debug',
    'a' => 'academicperiodid',
    'v'=>'verbose'
));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
// Text to the sync console
if ($options['help']) {
    $help =
        // Todo: localize - to be translated later when everything is finished
        "Sync Omega to get the courses and users (students and teachers).
	Options:
	-h, --help              Print out this help
	-d, --debug             Enable debug mode for omega sync
	-a, --academicperiodid  Enable sync for one academicperiod
	-v, --verbose           Enable verbose mode for database sync
	Example:
	\$sudo /usr/bin/php /local/sync/cli/tester.php";
    echo $help;
    die();
}
//heading
cli_heading('Omega Sync Database'); // TODO: localize
echo "\nStarting at ".date("F j, Y, G:i:s")."\n";

$status = sync_get_execution_status();
print_r($status);
if ($status->result > 0) {
    mtrace('sync_omega failed, synchronisation stopped');
    sync_generate_mail($options, null, null, null, 4);
    exit(0);
}

// Add External BDD Enrolments to this cli
mtrace("******************** Starting External Database Enrol ********************");
if (!enrol_is_enabled('database')) {
    cli_error('enrol_database plugin is disabled, synchronisation stopped', 2);
    sync_generate_mail($options, null, null, null, 3);
}

if (empty($options['verbose'])) {
    $trace = new null_progress_trace();
} else {
    $trace = new text_progress_trace();
}

//die;
/** @var enrol_database_plugin $enrol  */
$enrol = enrol_get_plugin('database');
$result = 0;

//print_r("Sync Courses");
mtrace ("**** Creating Courses ****");
$result = $result | $enrol->sync_courses($trace);

mtrace ("**** Enroling Users Courses ****");
$result = $result | $enrol->sync_enrolments($trace);

sync_generate_mail($options, null, null, null, 2);

echo "\nEnding at ".date("F j, Y, G:i:s")."\n";
exit($result);
