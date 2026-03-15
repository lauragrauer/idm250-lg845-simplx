<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id    = $_GET['id'] ?? 0;
$order = [];
if ($id) {
    $order = get_order_by_id($id);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'order_number'    => $_POST['order_number']    ?? '',
        'ship_to_company' => $_POST['ship_to_company'] ?? '',
        'ship_to_street'  => $_POST['ship_to_street']  ?? '',
        'ship_to_city'    => $_POST['ship_to_city']    ?? '',
        'ship_to_state'   => $_POST['ship_to_state']   ?? '',
        'ship_to_zip'     => $_POST['ship_to_zip']     ?? '',
    ];
    $unit_ids = $_POST['units'] ?? [];

    if (!$unit_ids) {
        $error = 'At least one unit must be selected.';
    } else {
        if ($id) {
            $existing = get_order_by_id($id);
            if (!$existing || $existing['status'] != 'draft') {
                $_SESSION['message']      = 'Only draft orders can be edited.';
                $_SESSION['message_type'] = 'error';
                header('Location: order_records.php', true, 303);
                exit;
            }
            update_order($id, $data);
            replace_order_items($id, $unit_ids);
            $_SESSION['message']      = 'Order updated.';
            $_SESSION['message_type'] = 'success';
        } else {
            create_order($data, $unit_ids);
            $_SESSION['message']      = 'Order created.';
            $_SESSION['message_type'] = 'success';
        }
        header('Location: order_records.php', true, 303);
        exit;
    }
}

$selected = [];
if ($id) {
    foreach (get_order_items($id) as $item) {
        $selected[$item['unit_id']] = true;
    }
}

$warehouse_units = get_inventory('warehouse');

$page_title = 'Create Order';
if ($id) { $page_title = 'Edit Order'; }
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

        <?php if ($error) { ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error) ?></div>
        <?php } ?>

        <form method="POST" action="order_form.php<?php if ($id) echo "?id=$id"; ?>">

            <div class="form-row">
                <div class="form-group">
                    <label>Order Number *</label>
                    <input type="text" name="order_number" class="form-control"
                           value="<?php echo htmlspecialchars($order['order_number'] ?? '') ?>"
                           placeholder="e.g. ORD-2026-001" required>
                </div>
                <div class="form-group">
                    <label>Ship To Company *</label>
                    <input type="text" name="ship_to_company" class="form-control"
                           value="<?php echo htmlspecialchars($order['ship_to_company'] ?? '') ?>"
                           placeholder="e.g. Simplx INC" required>
                </div>
            </div>

            <div class="form-group">
                <label>Street Address</label>
                <input type="text" name="ship_to_street" class="form-control"
                       value="<?php echo htmlspecialchars($order['ship_to_street'] ?? '') ?>"
                       placeholder="e.g. 123 Main St">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="ship_to_city" class="form-control"
                           value="<?php echo htmlspecialchars($order['ship_to_city'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>State</label>
                    <select name="ship_to_state" class="form-control">
                        <option value="">— select —</option>
                        <?php
                        $states = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY'];
                        foreach ($states as $s) {
                            $selected_state = ($order['ship_to_state'] ?? '') == $s ? 'selected' : '';
                            echo "<option value=\"$s\" $selected_state>$s</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>ZIP</label>
                    <input type="text" name="ship_to_zip" class="form-control"
                           value="<?php echo htmlspecialchars($order['ship_to_zip'] ?? '') ?>"
                           maxlength="9" pattern="\d{1,9}" inputmode="numeric">
                </div>
            </div>

            <div class="section-label">Select Units to Ship</div>

            <?php if (!$warehouse_units) { ?>
                <div class="text-muted">No warehouse units available.</div>
            <?php } else { ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>Unit ID</th><th>SKU</th><th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($warehouse_units as $unit) { ?>
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
                <a href="order_records.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">
                    <?php if ($id) { echo 'Save Changes'; } else { echo 'Create Order'; } ?>
                </button>
            </div>

        </form>
        </div>

    </div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function () {
    document.querySelectorAll('.unit-cb').forEach(cb => cb.checked = this.checked);
});
</script>

</body>
</html>