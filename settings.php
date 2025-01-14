<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('users', new admin_externalpage(
        'local_webstudents',
        get_string('pluginname', 'local_webstudents'),
        new moodle_url('/local/webstudents/index.php')
    ));

    $settings = new admin_settingpage('local_webstudents_settings', get_string('pluginname', 'local_webstudents'));

    $settings->add(new admin_setting_configtext(
        'local_webstudents/endpoint',
        get_string('endpoint', 'local_webstudents'),
        get_string('endpoint_desc', 'local_webstudents'),
        '',
        PARAM_URL
    ));

    $ADMIN->add('localplugins', $settings);
}
