<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action  = $_POST['action']  ?? 'add';
    $unit_id = $_POST['unit_id'] ?? '';
    $sku_id  = $_POST['sku_id']  ?? 0;

    if ($action == 'remove' && $unit_id) {
        $deleted = delete_inventory_unit($unit_id);
        if ($deleted) {
            $_SESSION['message']      = 'Unit removed from internal inventory.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message']      = 'Could not remove unit.';
            $_SESSION['message_type'] = 'error';
        }
    } elseif ($action == 'add' && $unit_id && $sku_id) {
        $added = add_inventory_unit($unit_id, $sku_id, 'internal');
        if ($added) {
            $_SESSION['message']      = 'Unit added to internal inventory.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message']      = 'Could not add unit — Unit ID may already exist.';
            $_SESSION['message_type'] = 'error';
        }
    }

    header('Location: inventory_internal.php', true, 303);
    exit;
}

$units = get_inventory('internal');
$skus  = get_all_skus();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Internal Inventory — Simplx</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <div class="main-content">

        <div class="header">
            <div class="header-title">Internal Inventory</div>
        </div>

        <?php if ($_SESSION['message'] ?? '') { ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?>"><?php echo $_SESSION['message'] ?></div>
            <?php $_SESSION['message'] = ''; $_SESSION['message_type'] = ''; ?>
        <?php } ?>

        <div class="form-card">
            <div class="section-label">Add Unit</div>
            <form method="POST" action="inventory_internal.php">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label>Unit ID *</label>
                        <input type="text" name="unit_id" class="form-control" placeholder="e.g. 48115044" required>
                    </div>
                    <div class="form-group">
                        <label>SKU *</label>
                        <select name="sku_id" class="form-control" required>
                            <option value="">— select SKU —</option>
                            <?php foreach ($skus as $s) { ?>
                                <option value="<?php echo $s['id'] ?>"><?php echo htmlspecialchars($s['sku']) ?> — <?php echo htmlspecialchars($s['description']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group form-group-btn">
                        <button type="submit" class="btn-save">Add Unit</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container" style="max-height:400px;overflow-y:auto;">
            <table>
                <thead>
                    <tr><th>Unit ID</th><th>SKU</th><th>Description</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php if (!$units) { ?>
                    <tr><td colspan="4" class="text-empty">No units in internal inventory yet.</td></tr>
                <?php } else { ?>
                    <?php foreach ($units as $u) { ?>
                    <tr>
                        <td><span class="unit-id"><?php echo htmlspecialchars($u['unit_id']) ?></span></td>
                        <td><?php echo htmlspecialchars($u['sku']) ?></td>
                        <td><?php echo htmlspecialchars($u['description']) ?></td>
                        <td>
                        <form method="POST" action="inventory_internal.php" style="display:inline" onsubmit="return confirm('Remove this unit?')">
                            <input type="hidden" name="action"  value="remove">
                            <input type="hidden" name="unit_id" value="<?php echo htmlspecialchars($u['unit_id']) ?>">
                            <button type="submit" class="btn-delete">Remove</button>
                        </form>
                        </td>
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
