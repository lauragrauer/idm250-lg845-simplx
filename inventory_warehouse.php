<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$units = get_inventory('warehouse');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Warehouse Inventory — Simplx</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <div class="main-content">

        <div class="header">
            <div class="header-title">Warehouse Inventory</div>
            <div class="header-description">WMS when they confirm our MPL stuff</div>
        </div>

        <div class="table-container" style="max-height:400px;overflow-y:auto;">
            <table>
                <thead>
                    <tr><th>Unit ID</th><th>SKU</th><th>Description</th><th>Moved</th></tr>
                </thead>
                <tbody>
                <?php if (!$units) { ?>
                    <tr><td colspan="4" class="text-empty">No units in warehouse yet.</td></tr>
                <?php } else { ?>
                    <?php foreach ($units as $u) { ?>
                    <tr>
                        <td><span class="unit-id"><?php echo htmlspecialchars($u['unit_id']) ?></span></td>
                        <td><?php echo htmlspecialchars($u['sku']) ?></td>
                        <td><?php echo htmlspecialchars($u['description']) ?></td>
                        <td><?php echo htmlspecialchars($u['created_at']) ?></td>
                    </tr>
                    <?php } ?>
                <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
</body>
</html>
