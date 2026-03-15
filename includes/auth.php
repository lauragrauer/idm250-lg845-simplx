<?php

if (!defined('API_REQUEST') && session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_api_key($env) {
    $valid_key = $env['X-API-KEY'];

    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_LOWER);

    if (!isset($headers['x-api-key']) || $headers['x-api-key'] != $valid_key) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
}

function is_logged_in() {
    return ($_SESSION['user_id'] ?? '') != '';
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
