<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? 0;

    if ($id) {
        if (delete_sku($id)) {
            $_SESSION['message'] = 'SKU deleted.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Could not delete — SKU may be in use by inventory.';
            $_SESSION['message_type'] = 'error';
        }
    }
}

header('Location: sku_management.php', true, 303);
exit;
