<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = $_GET['id'] ?? 0;
$mpl = [];
if ($id) {
    $mpl = get_mpl_by_id($id);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'reference_number' => $_POST['reference_number'] ?? '',
        'trailer_number' => $_POST['trailer_number'] ?? '',
        'expected_arrival' => $_POST['expected_arrival'] ?? '',
    ];
    $unit_ids = $_POST['units'] ?? [];

    if ($id) {
        $existing = get_mpl_by_id($id);
        if (!$existing || $existing['status'] != 'draft') {
            $_SESSION['message'] = 'Only draft MPLs can be edited.';
            $_SESSION['message_type'] = 'error';
            header('Location: mpl_records.php', true, 303);
            exit;
        }
        update_mpl($id, $data);
        replace_mpl_items($id, $unit_ids);
        $_SESSION['message'] = 'MPL updated.';
        $_SESSION['message_type'] = 'success';
    } else {
        create_mpl($data, $unit_ids);
        $_SESSION['message'] = 'MPL created.';
        $_SESSION['message_type'] = 'success';
    }

    header('Location: mpl_records.php', true, 303);
    exit;
}

$selected = [];
if ($id) {
    foreach (get_mpl_items($id) as $item) {
        $selected[$item['unit_id']] = true;
    }
}

$internal_units = get_inventory('internal');

$page_title = 'Create MPL';
if ($id) { $page_title = 'Edit MPL'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title ?> — Simplx</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <div class="main-content">

        <div class="header">
            <div class="header-title"><?php echo $page_title ?></div>
        </div>

        <div class="form-card">
        <form method="POST" action="mpl_form.php<?php if ($id) echo "?id=$id"; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Reference Number *</label>
                    <input type="text" name="reference_number" class="form-control"
                           value="<?php echo htmlspecialchars($mpl['reference_number'] ?? '') ?>"
                           placeholder="e.g. MPL-2026-001" required>
                </div>
                <div class="form-group">
                    <label>Trailer Number</label>
                    <input type="text" name="trailer_number" class="form-control"
                           value="<?php echo htmlspecialchars($mpl['trailer_number'] ?? '') ?>"
                           placeholder="e.g. 634477">
                </div>
                <div class="form-group">
                    <label>Expected Arrival</label>
                    <input type="date" name="expected_arrival" class="form-control"
                           value="<?php echo htmlspecialchars($mpl['expected_arrival'] ?? '') ?>">
                </div>
            </div>

            <div class="section-label">Select Units</div>

            <?php if (!$internal_units) { ?>
                <div class="text-muted">No internal units available.</div>
            <?php } else { ?>
            <div class="table-container" style="max-height:320px;overflow-y:auto;">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Unit ID</th><th>SKU</th><th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($internal_units as $unit) { ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="unit-cb" name="units[]"
                                       value="<?php echo htmlspecialchars($unit['unit_id']) ?>"
                                       <?php if ($selected[$unit['unit_id']] ?? false) echo 'checked'; ?>>
                            </td>
                            <td><span class="unit-id"><?php echo htmlspecialchars($unit['unit_id']) ?></span></td>
                            <td><?php echo htmlspecialchars($unit['sku']) ?></td>
                            <td><?php echo htmlspecialchars($unit['description']) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php } ?>

            <div class="form-actions">
                <a href="mpl_records.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">
                    <?php if ($id) { echo 'Save Changes'; } else { echo 'Create MPL'; } ?>
                </button>
            </div>

        </form>
        </div>

    </div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
    var checkboxes = document.querySelectorAll('.unit-cb');
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = this.checked;
    }
});
</script>

</body>
</html>
