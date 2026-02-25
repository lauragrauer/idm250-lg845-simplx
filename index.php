<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/DB/skus.php';
require_once __DIR__ . '/functions/get_inventory.php';
require_once __DIR__ . '/DB/mpls.php';
require_once __DIR__ . '/DB/orders.php';

$skus = get_skus();
$internal = fetch_internal_inventory();
$warehouse = fetch_warehouse_inventory();
$mpls = get_mpls();
$orders = get_orders();
?>
<!DOCTYPE html>
<html>
<head>
    <title>CMS Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>CMS Dashboard</h1>
    <nav>
        <ul>
            <li><a href="skus.php">SKU Management (<?= count($skus) ?>)</a></li>
            <li><a href="inventory_internal.php">Internal Inventory (<?= count($internal) ?>)</a></li>
            <li><a href="inventory_warehouse.php">Warehouse Inventory (<?= count($warehouse) ?>)</a></li>
            <li><a href="mpls.php">MPL Records (<?= count($mpls) ?>)</a></li>
            <li><a href="orders.php">Order Records (<?= count($orders) ?>)</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
</body>
</html>
