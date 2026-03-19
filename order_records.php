<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/api_client.php';
require_once __DIR__ . '/includes/log.php';

$env = require __DIR__ . '/.env.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $order_id = $_POST['order_id'] ?? 0;

    if ($action == 'delete') {
        $deleted = delete_order($order_id);
        if ($deleted) {
            $_SESSION['message'] = 'Order deleted.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Could not delete — only draft orders can be deleted.';
            $_SESSION['message_type'] = 'error';
        }
    } elseif ($action == 'send') {
        $order = get_order_by_id($order_id);
        $items = get_order_items($order_id);
        $result = send_order_to_wms($order, $items, $env);
        if ($result['success']) {
            update_order_status($order_id, 'sent');
            log_event("Order {$order['order_number']} sent to WMS");
            $_SESSION['message'] = 'Order sent to WMS successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Error: ' . ($result['error'] ?? 'Could not reach WMS.');
            $_SESSION['message_type'] = 'error';
        }
    }

    header('Location: order_records.php', true, 303);
    exit;
}

$orders = get_all_orders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders — Simplx</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <div class="main-content">

        <div class="header">
            <div class="header-title">Orders</div>
        </div>

        <div class="toolbar">
            <div class="toolbar-spacer"></div>
            <a href="order_form.php" class="btn btn-primary">+ Create Order</a>
        </div>

        <?php if ($_SESSION['message'] ?? '') { ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?>"><?php echo $_SESSION['message'] ?></div>
            <?php $_SESSION['message'] = ''; $_SESSION['message_type'] = ''; ?>
        <?php } ?>

        <?php if (!$orders) { ?>
            <div class="text-empty">No orders yet.</div>
        <?php } else { ?>
            <?php foreach ($orders as $order) { ?>
                <?php
                if ($order['status'] == 'confirmed') {
                    $items = get_shipped_items_by_order($order['id']);
                } else {
                    $items = get_order_items($order['id']);
                }
                ?>
                <div class="record-card">
                    <h3>
                        <?php echo htmlspecialchars($order['order_number']) ?>
                        <span class="badge badge-<?php echo $order['status'] ?>"><?php echo $order['status'] ?></span>
                    </h3>
                    <div class="text-muted">
                        Ship To: <?php echo htmlspecialchars($order['ship_to_company']) ?>
                        <?php if ($order['ship_to_street']) { ?>
                            — <?php echo htmlspecialchars($order['ship_to_street']) ?>,
                            <?php echo htmlspecialchars($order['ship_to_city']) ?>,
                            <?php echo htmlspecialchars($order['ship_to_state']) ?>
                            <?php echo htmlspecialchars($order['ship_to_zip']) ?>
                        <?php } ?>
                    </div>
                    <?php if ($items) { ?>
                    <div class="table-container record-table-wrap">
                        <table>
                            <thead>
                                <tr><th>Unit ID</th><th>SKU</th><th>Description</th><th>Shipped</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $item) { ?>
                                <tr>
                                    <td><span class="unit-id"><?php echo htmlspecialchars($item['unit_id']) ?></span></td>
                                    <td><?php echo htmlspecialchars($item['sku']) ?></td>
                                    <td><?php echo htmlspecialchars($item['description']) ?></td>
                                    <td><?php if ($order['shipped_at']) { echo htmlspecialchars($order['shipped_at']); } else { echo '—'; } ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                        <div class="text-faint">No units in this order.</div>
                    <?php } ?>

                    <?php if ($order['status'] == 'draft') { ?>
                    <div class="record-actions">
                        <a href="order_form.php?id=<?php echo $order['id'] ?>" class="btn-edit">Edit</a>

                        <form method="POST" action="order_records.php" style="display:inline" onsubmit="return confirm('Delete this order?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="order_id" value="<?php echo $order['id'] ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>

                        <form method="POST" action="order_records.php" style="display:inline">
                            <input type="hidden" name="action" value="send">
                            <input type="hidden" name="order_id" value="<?php echo $order['id'] ?>">
                            <button type="submit" class="btn-send" <?php if (!$items) echo 'disabled'; ?>>Send to WMS</button>
                        </form>
                    </div>
                    <?php } ?>

                </div>
            <?php } ?>
        <?php } ?>

    </div>
</div>
</body>
</html>
