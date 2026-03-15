<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/api_client.php';

$error = '';
// add SKU form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // collect all form fields, use ?? to safely fall back if a field is missing
    $data = [
        'ficha'         => $_POST['ficha']         ?? '',
        'sku'           => $_POST['sku']           ?? '',
        'description'   => $_POST['description']   ?? '',
        'uom_primary'   => $_POST['uom_primary']   ?? 'BUNDLE',
        'piece_count'   => $_POST['piece_count']   ?? 0,
        'length_inches' => $_POST['length_inches'] ?? 0,
        'width_inches'  => $_POST['width_inches']  ?? 0,
        'height_inches' => $_POST['height_inches'] ?? 0,
        'weight_lbs'    => $_POST['weight_lbs']    ?? 0,
        'assembly'      => $_POST['assembly']      ?? 'false',
        'rate'          => $_POST['rate']          ?? 0,
    ];

    if (!$data['sku'] || !$data['description']) {
        $error = 'SKU Code and Description are required.';
    } else {
        create_sku($data);
        $env    = require __DIR__ . '/.env.php';
        $synced = sync_sku_to_wms($data, $env);
        if ($synced['success']) {
            $_SESSION['message']      = 'SKU created + linked to WMS.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message']      = 'SKU created locally, but WMS sync failed.';
            $_SESSION['message_type'] = 'warning';
        }
        header('Location: sku_management.php', true, 303);
        exit;
    }
}

// fetch all SKUs from the database to display in box thing
$skus = get_all_skus();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SKU Management — Simplx</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <div class="main-content">

        <div class="header">
            <div class="header-title">SKU Management</div>
        </div>

        <?php if ($error) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error) ?></div>
        <?php } ?>

        <?php if ($_SESSION['message'] ?? '') { ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?>"><?php echo $_SESSION['message'] ?></div>
            <?php $_SESSION['message'] = ''; $_SESSION['message_type'] = ''; ?>
        <?php } ?>

        <div class="form-card">
            <div class="section-label">Add SKU</div>
            <form method="POST" action="sku_management.php">
                <div class="form-row">
                    <div class="form-group">
                        <label>Ficha</label>
                        <input type="number" name="ficha" class="form-control" placeholder="e.g. 445">
                    </div>
                    <div class="form-group">
                        <label>SKU Code *</label>
                        <input type="text" name="sku" class="form-control" placeholder="e.g. 1720830-0789" required>
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. ALDER RED SEL 4/4 RGH KD" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>UOM</label>
                        <select name="uom_primary" class="form-control">
                            <option value="BUNDLE">Bundle</option>
                            <option value="PALLET">Pallet</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Piece Count</label>
                        <input type="number" name="piece_count" class="form-control" placeholder="e.g. 140">
                    </div>
                    <div class="form-group">
                        <label>Length (in)</label>
                        <input type="number" step="any" name="length_inches" class="form-control" placeholder="L">
                    </div>
                    <div class="form-group">
                        <label>Width (in)</label>
                        <input type="number" step="any" name="width_inches" class="form-control" placeholder="W">
                    </div>
                    <div class="form-group">
                        <label>Height (in)</label>
                        <input type="number" step="any" name="height_inches" class="form-control" placeholder="H">
                    </div>
                    <div class="form-group">
                        <label>Weight (lbs)</label>
                        <input type="number" step="any" name="weight_lbs" class="form-control" placeholder="e.g. 2180.55">
                    </div>
                    <div class="form-group">
                        <label>Rate</label>
                        <input type="number" step="any" name="rate" class="form-control" placeholder="e.g. 17.64">
                    </div>
                    <div class="form-group">
                        <label>Assembly</label>
                        <select name="assembly" class="form-control">
                            <option value="false">False</option>
                            <option value="true">True</option>
                        </select>
                    </div>
                    <div class="form-group form-group-btn">
                        <button type="submit" class="btn-save">Add SKU</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-container" style="max-height:420px;overflow-y:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Ficha</th><th>SKU</th><th>Description</th><th>UOM</th>
                        <th>Pcs</th><th>L × W × H (in)</th><th>Weight (lbs)</th>
                        <th>Assembly</th><th>Rate</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$skus) { ?>
                    <tr><td colspan="10" class="text-empty">No SKUs yet.</td></tr>
                <?php } else { ?>
                    <?php foreach ($skus as $row) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ficha']) ?></td>
                        <td><span class="unit-id"><?php echo htmlspecialchars($row['sku']) ?></span></td>
                        <td><?php echo htmlspecialchars($row['description']) ?></td>
                        <td><span class="badge badge-cms"><?php echo htmlspecialchars($row['uom_primary']) ?></span></td>
                        <td><?php echo htmlspecialchars($row['piece_count']) ?></td>
                        <td><?php echo $row['length_inches'] ?> × <?php echo $row['width_inches'] ?> × <?php echo $row['height_inches'] ?></td>
                        <td><?php echo $row['weight_lbs'] ?></td>
                        <td><?php echo htmlspecialchars($row['assembly']) ?></td>
                        <td>$<?php echo $row['rate'] ?></td>
                        <td>
                            <a href="sku_edit.php?id=<?php echo $row['id'] ?>" class="btn-edit">Edit</a>
                            <form method="POST" action="sku_delete.php" style="display:inline" onsubmit="return confirm('Delete this SKU?')">
                                <input type="hidden" name="id" value="<?php echo $row['id'] ?>">
                                <button type="submit" class="btn-delete">Delete</button>
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
