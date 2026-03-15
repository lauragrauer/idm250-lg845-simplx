<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$sku_count       = count_skus();
$internal_count  = count_inventory('internal');
$warehouse_count = count_inventory('warehouse');
$mpl_count       = count_mpls();
$order_count     = count_orders();
$shipped_count   = count_shipped_orders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — Simplx CMS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <div class="main-content">
        <div class="header">
            <div class="header-title">Dashboard</div>
            <div class="header-description">Welcome back, <?php echo htmlspecialchars($_SESSION['username']) ?></div>
        </div>
        <div class="stats-row">
            <div class="stat-card"><div class="stat-label">SKUs</div><div class="stat-value blue"><?php echo $sku_count ?></div></div>
            <div class="stat-card"><div class="stat-label">Internal Units</div><div class="stat-value"><?php echo $internal_count ?></div></div>
            <div class="stat-card"><div class="stat-label">Warehouse Units</div><div class="stat-value teal"><?php echo $warehouse_count ?></div></div>
            <div class="stat-card"><div class="stat-label">MPLs</div><div class="stat-value"><?php echo $mpl_count ?></div></div>
            <div class="stat-card"><div class="stat-label">Orders</div><div class="stat-value blue"><?php echo $order_count ?></div></div>
            <div class="stat-card"><div class="stat-label">Shipped Orders</div><div class="stat-value teal"><?php echo $shipped_count ?></div></div>
        </div>
    </div>
</div>
</body>
</html>