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
 * Library of functions for creating students.
 *
 * @package   local_webstudents
 * @copyright 2025 Maxwell Souza <maxwell.hygor01@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Creates a user based on the provided data and automatically generates a password.
 *
 * This function accepts user data and creates a user in Moodle using built-in functions.
 * It generates a random password for the user and can store an optional second email
 * in a custom profile field.
 *
 * @param string $username    Username for the new user. This must be unique.
 * @param string $firstname   First name of the new user.
 * @param string $lastname    Last name of the new user.
 * @param string $email       Primary email of the new user. Must be a valid email format.
 * @param string|null $secondemail Optional second email for the new user (stored in a custom profile field).
 *                                  If not provided, the second email field will not be populated.
 *
 * @return void
 *@throws moodle_exception If required data is missing, if the username already exists, or if
 *                          an error occurs during user creation or updating.
 *
 */
function local_webstudents_create($username, $firstname, $lastname, $email, $secondemail = null) {
    global $DB;

    if (empty($username) || empty($firstname) || empty($lastname) || empty($email)) {
        throw new moodle_exception('missingrequireddata', 'local_webstudents');
    }

    if ($DB->record_exists('user', ['username' => $username])) {
        throw new moodle_exception('useralreadyexists', 'local_webstudents');
    }

    $newuser = new stdClass();
    $newuser->username = $username;
    $newuser->firstname = $firstname;
    $newuser->lastname = $lastname;
    $newuser->email = $email;
    $newuser->second_email = $secondemail;
    $newuser->confirmed = 1;
    $newuser->auth = 'manual';
    $newuser->timecreated = time();
    $newuser->timemodified = time();

    $newuser->password = generate_password();

    try {
        create_user_record($newuser->username, $newuser->password);

        $user = $DB->get_record('user', ['username' => $newuser->username]);

        if ($user) {
            $user->firstname = $newuser->firstname;
            $user->lastname = $newuser->lastname;

            $user->email = $newuser->email;

            $user->profile_field_email_2aOpcao = $newuser->second_email;

            $DB->update_record('user', $user);

            if (!empty($newuser->second_email)) {
                $field = $DB->get_record('user_info_field', ['shortname' => 'email_2aOpcao']);

                if ($field) {
                    $profiledata = new stdClass();
                    $profiledata->userid = $user->id;
                    $profiledata->fieldid = $field->id;
                    $profiledata->data = $newuser->second_email;
                    $profiledata->dataformat = 1;

                    $DB->insert_record('user_info_data', $profiledata);
                } else {
                    throw new moodle_exception('fieldnotfound', 'local_webstudents', '', 'email_2aOpcao');
                }
            }

            $subject = get_string('welcomeuser', 'local_webstudents');
            $message = get_string('newuserpassword', 'local_webstudents', $newuser->password);

            email_to_user($user, get_admin(), $subject, $message);

            if (!empty($newuser->second_email)) {
                email_to_user($user, get_admin(), $subject, $message);
            }

        } else {
            throw new moodle_exception('userrecordnotfound', 'local_webstudents');
        }

    } catch (exception $e) {
        throw new moodle_exception('errorcreatinguser', 'local_webstudents', '', $e->getMessage());
    }
}

/**
 * Filters out users that already exist based on the username.
 *
 * This function checks an array of student data and filters out those that already exist
 * in the Moodle user database based on their username. It ensures that only new users
 * (whose usernames are not already registered) are included in the result.
 *
 * @param array $studentsdata Array of student data, each containing at least a 'username' key.
 *                            Each element in the array is expected to be an associative array.
 *
 * @return array Array of students (from the input array) whose usernames do not exist
 *               in the Moodle user database.
 *               Each element in the returned array will be a student array.
 *
 * @throws moodle_exception If there is a problem with reading or processing the student data.
 */
function local_webstudents_filter_existing_users($studentsdata) {
    global $DB;

    $newusers = [];

    foreach ($studentsdata as $student) {
        $username = clean_param($student['username'], PARAM_TEXT);

        if (!$DB->record_exists('user', ['username' => $username])) {
            $newusers[] = $student;
        }
    }

    return $newusers;
}
