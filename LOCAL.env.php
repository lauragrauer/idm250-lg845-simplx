<?php
$env = [
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'lg845_db',
    'DB_USER' => 'root',
    'DB_PASS' => 'root',
    // 'DB_HOST' => 'localhost',
    // 'DB_NAME' => 'lg845_db',
    // 'DB_USER' => 'lg845',
    // 'DB_PASS' => '4v244nagodc1uFvo',

    // CMS's own API key (WMS sends this when calling back to CMS)
    // 'X-API-KEY' => 'simplx-key-2026',

    // WMS endpoint — where CMS sends MPLs and Orders
    'WMS_API_URL' => 'https://digmstudents.westphal.drexel.edu/~lg845/wms/functions/update_inventory.php',

    // API key the WMS expects (must match WMS .env.php 'X-API-KEY')
    'WMS_API_KEY' => 'wms-key-123'
];

if (!isset($connection)) {
    $connection = new mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);
    $connection->set_charset('utf8mb4');
}

return $env;
?>
