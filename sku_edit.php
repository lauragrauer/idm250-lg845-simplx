<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/api_client.php';

$id = $_GET['id'] ?? 0;
$sku = get_sku_by_id($id);

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'ficha' => $_POST['ficha'] ?? '',
        'sku' => $_POST['sku'] ?? '',
        'description' => $_POST['description'] ?? '',
        'uom_primary' => $_POST['uom_primary'] ?? 'BUNDLE',
        'piece_count' => $_POST['piece_count'] ?? 0,
        'length_inches' => $_POST['length_inches'] ?? 0,
        'width_inches' => $_POST['width_inches'] ?? 0,
        'height_inches' => $_POST['height_inches'] ?? 0,
        'weight_lbs' => $_POST['weight_lbs'] ?? 0,
        'assembly' => $_POST['assembly'] ?? 'false',
        'rate' => $_POST['rate'] ?? 0,
    ];

    if (!$data['sku'] || !$data['description']) {
        $error = 'SKU Code and Description are required.';
    } else {
        update_sku($id, $data);
        $env = require __DIR__ . '/.env.php';
        $synced = sync_sku_to_wms($data, $env);
        if ($synced['success']) {
            $_SESSION['message'] = 'SKU updated and synced to WMS.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'SKU updated locally, but WMS sync failed.';
            $_SESSION['message_type'] = 'warning';
        }
        header('Location: sku_management.php', true, 303);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit SKU — Simplx</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <div class="main-content">

        <div class="header">
            <div class="header-title">Edit SKU</div>
        </div>

        <?php if ($error) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error) ?></div>
        <?php } ?>

        <div class="form-card">
        <form method="POST" action="sku_edit.php?id=<?php echo $id ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Ficha</label>
                    <input type="number" name="ficha" class="form-control" value="<?php echo htmlspecialchars($sku['ficha'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>SKU Code *</label>
                    <input type="text" name="sku" class="form-control" value="<?php echo htmlspecialchars($sku['sku'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Description *</label>
                <input type="text" name="description" class="form-control" value="<?php echo htmlspecialchars($sku['description'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Unit of Measure</label>
                    <select name="uom_primary" class="form-control">
                        <option value="BUNDLE" <?php if ($sku['uom_primary'] == 'BUNDLE') echo 'selected'; ?>>Bundle</option>
                        <option value="PALLET" <?php if ($sku['uom_primary'] == 'PALLET') echo 'selected'; ?>>Pallet</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Piece Count</label>
                    <input type="number" name="piece_count" class="form-control" value="<?php echo htmlspecialchars($sku['piece_count'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Dimensions (inches)</label>
                <div class="dimensions-group">
                    <div class="dimension-field">
                        <label>Length</label>
                        <input type="number" step="any" name="length_inches" class="form-control" value="<?php echo htmlspecialchars($sku['length_inches'] ?? '') ?>">
                    </div>
                    <div class="dimension-field">
                        <label>Width</label>
                        <input type="number" step="any" name="width_inches" class="form-control" value="<?php echo htmlspecialchars($sku['width_inches'] ?? '') ?>">
                    </div>
                    <div class="dimension-field">
                        <label>Height</label>
                        <input type="number" step="any" name="height_inches" class="form-control" value="<?php echo htmlspecialchars($sku['height_inches'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Weight (lbs)</label>
                    <input type="number" step="any" name="weight_lbs" class="form-control" value="<?php echo htmlspecialchars($sku['weight_lbs'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Rate</label>
                    <input type="number" step="any" name="rate" class="form-control" value="<?php echo htmlspecialchars($sku['rate'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Assembly Required</label>
                <select name="assembly" class="form-control">
                    <option value="false" <?php if ($sku['assembly'] == 'false') echo 'selected'; ?>>False</option>
                    <option value="true"  <?php if ($sku['assembly'] == 'true')  echo 'selected'; ?>>True</option>
                </select>
            </div>

            <div class="form-actions">
                <a href="sku_management.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>

        </form>
        </div>

    </div>
</div>
</body>
</html>
