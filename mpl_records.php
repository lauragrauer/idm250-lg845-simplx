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
    $mpl_id = $_POST['mpl_id'] ?? 0;

    if ($action == 'delete') {
        $deleted = delete_mpl($mpl_id);
        if ($deleted) {
            $_SESSION['message']      = 'MPL deleted.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message']      = 'Could not delete — only draft MPLs can be deleted.';
            $_SESSION['message_type'] = 'error';
        }
    } elseif ($action == 'send') {
        $mpl    = get_mpl_by_id($mpl_id);
        $items  = get_mpl_items($mpl_id);
        $result = send_mpl_to_wms($mpl, $items, $env);
        if ($result['success']) {
            update_mpl_status($mpl_id, 'sent');
            log_event("MPL {$mpl['reference_number']} sent to WMS");
            $_SESSION['message']      = 'MPL sent to WMS successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message']      = 'Error: ' . ($result['error'] ?? 'Could not reach WMS.');
            $_SESSION['message_type'] = 'error';
        }
    }

    header('Location: mpl_records.php', true, 303);
    exit;
}

$mpls = get_all_mpls();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MPL Records — Simplx</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <div class="main-content">

        <div class="header">
            <div class="header-title">MPL Records</div>
        </div>

        <div class="toolbar">
            <div class="toolbar-spacer"></div>
            <a href="mpl_form.php" class="btn btn-primary">+ Create MPL</a>
        </div>

        <?php if ($_SESSION['message'] ?? '') { ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?>"><?php echo $_SESSION['message'] ?></div>
            <?php $_SESSION['message'] = ''; $_SESSION['message_type'] = ''; ?>
        <?php } ?>

        <?php if (!$mpls) { ?>
            <div class="text-empty">No MPLs yet.</div>
        <?php } else { ?>
            <?php foreach ($mpls as $mpl) { ?>
                <?php $items = get_mpl_items($mpl['id']); ?>
                <div class="record-card">
                    <h3>
                        <?php echo htmlspecialchars($mpl['reference_number']) ?>
                        <span class="badge badge-<?php echo $mpl['status'] ?>"><?php echo $mpl['status'] ?></span>
                    </h3>
                    <div class="text-muted">
                        Trailer #: <?php echo htmlspecialchars($mpl['trailer_number'] ?? '—') ?>
                        &nbsp;|&nbsp;
                        Expected Arrival: <?php echo htmlspecialchars($mpl['expected_arrival'] ?: '—') ?>
                    </div>

                    <?php if ($items) { ?>
                    <div class="table-container record-table-wrap">
                        <table>
                            <thead>
                                <tr><th>Unit ID</th><th>SKU</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items as $item) { ?>
                                <tr>
                                    <td><span class="unit-id"><?php echo htmlspecialchars($item['unit_id']) ?></span></td>
                                    <td><?php echo htmlspecialchars($item['sku']) ?></td>
                                    <td><?php echo htmlspecialchars($item['description']) ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                        <div class="text-faint">No units in this MPL.</div>
                    <?php } ?>

                    <?php if ($mpl['status'] == 'draft') { ?>
                    <div class="record-actions">
                        <a href="mpl_form.php?id=<?php echo $mpl['id'] ?>" class="btn-edit">Edit</a>

                        <form method="POST" action="mpl_records.php" style="display:inline" onsubmit="return confirm('Delete this MPL?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="mpl_id" value="<?php echo $mpl['id'] ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>

                        <form method="POST" action="mpl_records.php" style="display:inline">
                            <input type="hidden" name="action" value="send">
                            <input type="hidden" name="mpl_id" value="<?php echo $mpl['id'] ?>">
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
