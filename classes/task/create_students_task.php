<?php

namespace local_webstudents\task;

defined('MOODLE_INTERNAL') || die();

class create_students_task extends \core\task\scheduled_task {

    /**
     * Definição do nome da tarefa para exibição no Cron
     *
     * @return string
     */
    public function get_name() {
        return get_string('pluginname', 'local_webstudents');
    }

    /**
     * Função principal para execução da tarefa
     */
    public function execute() {
        global $CFG;

        require_once($CFG->dirroot . '/local/webstudents/lib.php');

        $endpoint = get_config('local_webstudents', 'endpoint');

        $response = file_get_contents($endpoint);
        $studentsdata = json_decode($response, true);

        $new_students = local_webstudents_filter_existing_users($studentsdata);

        if ($new_students) {
            foreach ($new_students as $student) {
                $username = clean_param($student['username'], PARAM_TEXT);
                $firstname = clean_param($student['firstname'], PARAM_TEXT);
                $lastname = clean_param($student['lastname'], PARAM_TEXT);
                $email = clean_param($student['email'], PARAM_TEXT);
                $second_email = clean_param($student['second_email'], PARAM_TEXT);
                try {
                    local_webstudents_create($username, $firstname, $lastname, $email, $second_email);
                } catch (Exception $e) {
                    mtrace("Erro ao criar usuário: " . $e->getMessage());
                }
            }
        }
    }
}
