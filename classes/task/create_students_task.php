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
 * Task for creating student accounts in Moodle based on external API data.
 *
 * This task fetches data from an external student API, filters out users who
 * are already registered in Moodle, and creates new student accounts.
 * It is scheduled to run automatically via Moodle's cron.
 *
 * @package   local_webstudents
 * @copyright 2025 Maxwell Souza <maxwell.hygor01@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_webstudents\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Task for creating students.
 *
 * This class represents the scheduled task for creating student accounts based on
 * data from an external API. It filters out already existing users and only
 * creates new ones. The task is executed by Moodle cron.
 *
 * @package local_webstudents
 * @copyright 2025 Maxwell Souza <maxwell.hygor01@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class create_students_task extends \core\task\scheduled_task {

    /**
     * Returns the name of the task that will appear in the cron job list.
     *
     * @return string The name of the task.
     */
    public function get_name() {
        return get_string('pluginname', 'local_webstudents');
    }

    /**
     * Executes the task to fetch student data and create new accounts.
     *
     * This method fetches the student data from an external API, filters out the students
     * already present in the Moodle system, and creates accounts for the remaining students.
     * It logs errors and exceptions during the user creation process.
     *
     * @return void
     */
    public function execute() {
        global $CFG;

        require_once($CFG->dirroot . '/local/webstudents/lib.php');

        $endpoint = get_config('local_webstudents', 'endpoint');

        $response = file_get_contents($endpoint);
        $studentsdata = json_decode($response, true);

        $newstudents = local_webstudents_filter_existing_users($studentsdata);

        if ($newstudents) {
            foreach ($newstudents as $student) {
                $username = clean_param($student['username'], PARAM_TEXT);
                $firstname = clean_param($student['firstname'], PARAM_TEXT);
                $lastname = clean_param($student['lastname'], PARAM_TEXT);
                $email = clean_param($student['email'], PARAM_TEXT);
                $secondemail = clean_param($student['second_email'], PARAM_TEXT);

                try {
                    local_webstudents_create($username, $firstname, $lastname, $email, $secondemail);
                } catch (Exception $e) {
                    mtrace("Error creating user: " . $e->getMessage());
                }
            }
        }
    }
}
