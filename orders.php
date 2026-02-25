<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/DB/orders.php';
require_once __DIR__ . '/DB/order_items.php';
require_once __DIR__ . '/DB/shipped_items.php';
require_once __DIR__ . '/functions/get_inventory.php';
require_once __DIR__ . '/functions/create_order.php';
require_once __DIR__ . '/functions/update_inventory.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $data = [
            'order_number' => $_POST['order_number'],
            'ship_to_company' => $_POST['ship_to_company'],
            'ship_to_street' => $_POST['ship_to_street'],
            'ship_to_city' => $_POST['ship_to_city'],
            'ship_to_state' => $_POST['ship_to_state'],
            'ship_to_zip' => $_POST['ship_to_zip']
        ];
        $unit_ids = $_POST['units'] ?? [];

        if (!empty($data['order_number']) && !empty($unit_ids)) {
            $order_id = create_order($data, $unit_ids);
            $message = "Order created (ID: $order_id)";
        } else {
            $message = "Error: Order number and at least one unit required.";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'send') {
        $order_id = intval($_POST['order_id']);
        $order = get_order($order_id);

        if ($order && $order['status'] === 'draft') {
            $order_items = get_order_items($order_id);

            $items = [];
            foreach ($order_items as $item) {
                $items[] = ['unit_id' => $item['unit_id']];
            }

            $response = send_order_to_wms($order, $items);

            if (isset($response['success']) && $response['success']) {
                update_order_status($order_id, 'sent');
                $message = "Order sent to WMS successfully.";
            } else {
                $error_detail = $response['details'] ?? $response['error'] ?? 'Unknown error';
                $message = "WMS Error: " . $error_detail;
            }
        }
    }
}

$orders = get_orders();
$warehouse_units = fetch_warehouse_inventory();
?>
<!DOCTYPE html>
<html>
<head><title>CMS - Orders</title></head>
<body>
    <h1>Order Records</h1>
    <p><a href="index.php">Back to Dashboard</a></p>

    <?php if ($message): ?>
        <p><b><?= $message ?></b></p>
    <?php endif; ?>

    <h2>Create New Order</h2>
    <form action="orders.php" method="POST">
        <input type="hidden" name="action" value="create">
        <div>
            <label for="order_number">Order Number</label><br>
            <input type="text" name="order_number" id="order_number" required>
        </div>
        <div>
            <label for="ship_to_company">Ship To Company</label><br>
            <input type="text" name="ship_to_company" id="ship_to_company" required>
        </div>
        <div>
            <label for="ship_to_street">Street</label><br>
            <input type="text" name="ship_to_street" id="ship_to_street" required>
        </div>
        <div>
            <label for="ship_to_city">City</label><br>
            <input type="text" name="ship_to_city" id="ship_to_city" required>
        </div>
        <div>
            <label for="ship_to_state">State</label><br>
            <input type="text" name="ship_to_state" id="ship_to_state" required>
        </div>
        <div>
            <label for="ship_to_zip">Zip</label><br>
            <input type="text" name="ship_to_zip" id="ship_to_zip" required>
        </div>
        <fieldset>
            <legend>Select Warehouse Inventory Units</legend>
            <?php if (empty($warehouse_units)): ?>
                <p>No warehouse inventory available.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($warehouse_units as $unit): ?>
                        <li>
                            <input type="checkbox" name="units[]" value="<?= $unit['unit_id'] ?>">
                            <?= $unit['unit_id'] ?> - <?= $unit['sku'] ?> (<?= $unit['description'] ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </fieldset>
        <div>
            <button type="submit">Create Order</button>
        </div>
    </form>

    <hr>

    <h2>All Orders (<?= count($orders) ?>)</h2>
    <?php if (empty($orders)): ?>
        <p>No orders yet.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Order #</th>
                <th>Ship To</th>
                <th>Status</th>
                <th>Shipped At</th>
                <th>Items</th>
                <th>Action</th>
            </tr>
            <?php foreach ($orders as $o): ?>
            <?php
            if ($o['status'] === 'shipped') {
                $items = get_shipped_items($o['id']);
            } else {
                $items = get_order_items($o['id']);
            }
            ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= $o['order_number'] ?></td>
                <td><?= $o['ship_to_company'] ?>, <?= $o['ship_to_city'] ?> <?= $o['ship_to_state'] ?></td>
                <td><?= $o['status'] ?></td>
                <td><?= $o['shipped_at'] ?? '-' ?></td>
                <td>
                    <?= count($items) ?> unit(s)
                    <ul>
                        <?php foreach ($items as $item): ?>
                        <li><?= $item['unit_id'] ?> - <?= $item['sku'] ?? '' ?> (<?= $item['description'] ?? '' ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                </td>
                <td>
                    <?php if ($o['status'] === 'draft'): ?>
                        <form action="orders.php" method="POST">
                            <input type="hidden" name="action" value="send">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <button type="submit">Send to WMS</button>
                        </form>
                    <?php else: ?>
                        <?= $o['status'] ?>
                    <?php endif; ?>
            </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>