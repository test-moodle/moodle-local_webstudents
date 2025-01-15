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
 * Create users will external data.
 *
 * @package   local_webstudents
 * @copyright 2025 Maxwell Souza <maxwell.hygor01@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once('lib.php');

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new moodle_url('/local/webstudents/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_webstudents'));
$PAGE->set_heading(get_string('pluginname', 'local_webstudents'));

$endpoint = get_config('local_webstudents', 'endpoint');

$response = file_get_contents($endpoint);
$studentsdata = json_decode($response, true);

$newstudents = local_webstudents_filter_existing_users($studentsdata);

echo $OUTPUT->header();

if (optional_param('confirm', 0, PARAM_INT) === 1) {
    foreach ($newstudents as $student) {
        $username = clean_param($student['username'], PARAM_TEXT);
        $firstname = clean_param($student['firstname'], PARAM_TEXT);
        $lastname = clean_param($student['lastname'], PARAM_TEXT);
        $email = clean_param($student['email'], PARAM_TEXT);
        $secondemail = clean_param($student['second_email'], PARAM_TEXT);

        try {
            local_webstudents_create($username, $firstname, $lastname, $email, $secondemail);
        } catch (Exception $e) {
            echo $OUTPUT->header();
            echo html_writer::tag('p', get_string('studentscreationerror', 'local_webstudents') . ': ' . $e->getMessage());
            echo $OUTPUT->footer();
            die();
        }
    }

    echo html_writer::tag('h2', get_string('students_created', 'local_webstudents'));
    echo $OUTPUT->footer();
    die();
}

if (!empty($newstudents)) {
    echo html_writer::tag('h2', get_string('found_students', 'local_webstudents') . ': ' . count($newstudents));

    $studentshtml = '<ul>';
    foreach ($newstudents as $student) {
        $username = clean_param($student['username'], PARAM_TEXT);
        $studentshtml .= "<li>" . get_string('student_name', 'local_webstudents') . ": {$username}</li>";
    }
    $studentshtml .= '</ul>';

    echo $studentshtml;

    $confirmurl = new moodle_url('/local/webstudents/index.php', ['confirm' => 1, 'students' => json_encode($newstudents)]);
    echo html_writer::tag('p', html_writer::link($confirmurl, get_string('confirmcreate', 'local_webstudents'), ['class' => 'btn btn-primary']));
} else {
    echo html_writer::tag('p', get_string('no_students_found', 'local_webstudents'));
}

echo $OUTPUT->footer();

