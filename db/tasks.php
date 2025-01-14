<?php
defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_webstudents\task\create_students_task', // O nome da sua classe
        'blocking' => 0, // Se a tarefa deve ser executada de forma síncrona ou assíncrona
        'minute' => '*/10', // Executa a cada 10 minutos
        'hour' => '*', // Em todas as horas
        'day' => '*', // Em qualquer dia
        'month' => '*', // Em qualquer mês
        'dayofweek' => '*', // Em qualquer dia da semana
    ),
);
