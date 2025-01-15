# Moodle Plugin: Local WEBSTUDENTS

## Description
This plugin integrates Moodle with an external microservice to register students automatically in Moodle. It fetches user data in JSON format, including an optional second email field. This field can be enabled or disabled via the Moodle settings. Additionally, the plugin includes a scheduled task to periodically synchronize users based on the external data provided by the microservice, ensuring that all users are registered and up to date in Moodle.

**This plugin is ideal for institutions that rely on an external system to manage student registration and wish to keep their Moodle user data in sync with that system.**

## Installation
1. Copy the plugin folder to `moodle_root/local/`.
2. Access the Moodle admin panel to finalize the installation.
3. Configure the external API endpoint and the optional second email field in the plugin settings.

## Features
- Fetches user data from an external microservice.
- Registers users automatically in Moodle based on the provided data.
- Supports an optional `second_email` field, which can be enabled or disabled in Moodle settings.
- Includes a cron task to sync user data periodically.

## Requirements
- Moodle 4.x or higher.
- PHP 7.3 or higher.
- Admin permission.

## Usage
1. Set the correct endpoint for your API request.
2. Access the plugin settings page in Moodle: **Site administration -> Users -> WEBSERVICE Integration - STUDENTS**
3. Configure whether the `second_email` field should be used.
4. Confirm student registration.
5. The cron task will run periodically to keep student data synchronized between Moodle and the microservice.

## JSON Format
```json
{
    "students": [
          {
            "username": "unique_field1",
            "firstname": "Student",
            "lastname": "Moodle",
            "email": "user1@example.com",
            "second_email": "user1@another.com"
          },
          {
            "username": "unique_field2",
            "firstname": "Student",
            "lastname": "Moodle",
            "email": "user2@example.com",
            "second_email": "user2@another.com"
          }
    ]
}
```
## JSON Fields
- The `second_email` field is **optional**. If used, it will be registered for the student.
- The `idnumber` represents the unique identifier for the student in the external system.

## Error Handling
- Displays errors for invalid inputs, missing data, or incorrect API responses.

## File Structure
- `index.php`: Handles user interaction and workflow for registration.
- `lib.php`: Helper functions for processing and registering users.
- `lang/en/local_student_enrollment.php`: Language strings.
- `classes/task/sync_users_task.php`: Cron task responsible for periodic user synchronization.

## License
This project is licensed under the [GNU General Public License](https://www.gnu.org/licenses/gpl-3.0.html).

## Author
Maxwell H. S. Souza
