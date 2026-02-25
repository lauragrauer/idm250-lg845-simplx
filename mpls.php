<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/DB/mpls.php';
require_once __DIR__ . '/DB/mpl_items.php';
require_once __DIR__ . '/DB/skus.php';
require_once __DIR__ . '/functions/get_inventory.php';
require_once __DIR__ . '/functions/create_mpl.php';
require_once __DIR__ . '/functions/update_inventory.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $data = [
            'reference_number' => $_POST['reference_number'],
            'trailer_number' => $_POST['trailer_number'],
            'expected_arrival' => $_POST['expected_arrival']
        ];
        $unit_ids = $_POST['units'] ?? [];

        if (!empty($data['reference_number']) && !empty($unit_ids)) {
            $mpl_id = create_mpl($data, $unit_ids);
            $message = "MPL created (ID: $mpl_id)";
        } else {
            $message = "Error: Reference number and at least one unit required.";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'send') {
        $mpl_id = intval($_POST['mpl_id']);
        $mpl = get_mpl($mpl_id);

        if ($mpl && $mpl['status'] === 'draft') {
            $mpl_items = get_mpl_items($mpl_id);

            $items = [];
            foreach ($mpl_items as $item) {
                $sku_data = get_sku_by_code($item['sku']);
                $items[] = [
                    'unit_id' => $item['unit_id'],
                    'sku' => $item['sku'],
                    'sku_details' => [
                        'ficha' => $sku_data['ficha'],
                        'sku' => $sku_data['sku'],
                        'description' => $sku_data['description'],
                        'uom_primary' => $sku_data['uom_primary'],
                        'piece_count' => $sku_data['piece_count'],
                        'length_inches' => $sku_data['length_inches'],
                        'width_inches' => $sku_data['width_inches'],
                        'height_inches' => $sku_data['height_inches'],
                        'weight_lbs' => $sku_data['weight_lbs'],
                        'assembly' => $sku_data['assembly'],
                        'rate' => $sku_data['rate']
                    ]
                ];
            }

            $response = send_mpl_to_wms($mpl, $items);

            if (isset($response['success']) && $response['success']) {
                update_mpl_status($mpl_id, 'sent');
                $message = "MPL sent to WMS successfully.";
            } else {
                $error_detail = $response['details'] ?? $response['error'] ?? 'Unknown error';
                $message = "WMS Error: " . $error_detail;
            }
        }
    }
}

$mpls = get_mpls();
$internal_units = fetch_internal_inventory();
?>
<!DOCTYPE html>
<html>
<head><title>CMS - MPLs</title></head>
<body>
    <h1>MPL Records</h1>
    <p><a href="index.php">Back to Dashboard</a></p>

    <?php if ($message): ?>
        <p><b><?= $message ?></b></p>
    <?php endif; ?>

    <h2>Create New MPL</h2>
    <form action="mpls.php" method="POST">
        <input type="hidden" name="action" value="create">
        <div>
            <label for="reference_number">Reference Number</label><br>
            <input type="text" name="reference_number" id="reference_number" required>
        </div>
        <div>
            <label for="trailer_number">Trailer Number</label><br>
            <input type="text" name="trailer_number" id="trailer_number" required>
        </div>
        <div>
            <label for="expected_arrival">Expected Arrival</label><br>
            <input type="date" name="expected_arrival" id="expected_arrival" required>
        </div>
        <fieldset>
            <h3>Select Internal Inventory Units</h3>
            <?php if (empty($internal_units)): ?>
                <p>No internal inventory available.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($internal_units as $unit): ?>
                    <li>
                    <input type="checkbox" name="units[]" value="<?= $unit['unit_id'] ?>">
                    <?= $unit['unit_id'] ?> - <?= $unit['sku'] ?> (<?= $unit['description'] ?>)
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </fieldset>
        <div>
            <button type="submit">Create MPL</button>
        </div>
    </form>

    <hr>

    <h2>All MPLs (<?= count($mpls) ?>)</h2>
    <?php if (empty($mpls)): ?>
        <p>No MPLs yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Reference #</th>
                <th>Trailer #</th>
                <th>Expected Arrival</th>
                <th>Status</th>
                <th>Items</th>
                <th>Action</th>
            </tr>
            <?php foreach ($mpls as $m): ?>
            <?php $items = get_mpl_items($m['id']); ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= $m['reference_number'] ?></td>
                <td><?= $m['trailer_number'] ?></td>
                <td><?= $m['expected_arrival'] ?></td>
                <td><?= $m['status'] ?></td>
                <td>
                    <?= count($items) ?> unit(s)
                    <ul>
                    <?php foreach ($items as $item): ?>
                    <li><?= $item['unit_id'] ?> - <?= $item['sku'] ?> (<?= $item['description'] ?? '' ?>)</li>
                    <?php endforeach; ?>
                    </ul>
                    </td>
                <td>
                    <?php if ($m['status'] === 'draft'): ?>
                        <form action="mpls.php" method="POST">
                        <input type="hidden" name="action" value="send">
                        <input type="hidden" name="mpl_id" value="<?= $m['id'] ?>">
                        <button type="submit">Send to WMS</button>
                        </form>
                    <?php else: ?>
                    <?= $m['status'] ?>
                    <?php endif; ?>
            </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>