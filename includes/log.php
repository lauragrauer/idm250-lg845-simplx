<?php

function log_event($message) {
    $log_path = dirname(__DIR__) . '/logs/cms.log';
    $log_dir = dirname($log_path);

    if (!is_dir($log_dir))
        mkdir($log_dir, 0755, true);

    file_put_contents($log_path, date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}
