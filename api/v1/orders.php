<?php
ob_start();
define('API_REQUEST', true);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, x-api-key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once dirname(dirname(__DIR__)) . '/includes/db.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';
require_once dirname(dirname(__DIR__)) . '/includes/log.php';

ob_end_clean();

$env = require dirname(dirname(__DIR__)) . '/.env.php';
check_api_key($env);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action !== 'ship') {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request', 'details' => 'Unknown action']);
    exit;
}

$order_number = $data['order_number'] ?? '';
$shipped_at = $data['shipped_at'] ?? date('Y-m-d');

if (!$order_number) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request', 'details' => 'Missing order_number']);
    exit;
}

$order = get_order_by_id_by_number($order_number);

if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found', 'details' => 'Order not found']);
    exit;
}

if ($order['status'] === 'confirmed') {
    http_response_code(409);
    echo json_encode(['error' => 'Conflict', 'details' => 'Order already shipped']);
    exit;
}

$items = get_order_items($order['id']);

$stmt = $connection->prepare("
    INSERT INTO shipped_items (order_id, order_number, unit_id, sku, sku_description, shipped_at)
    VALUES (?, ?, ?, ?, ?, ?)
");
foreach ($items as $item) {
    $stmt->bind_param('isssss',
        $order['id'],
        $order_number,
        $item['unit_id'],
        $item['sku'],
        $item['description'],
        $shipped_at
    );
    $stmt->execute();
}

update_order_status_shipped($order['id'], 'confirmed', $shipped_at);

$units_shipped = 0;
foreach ($items as $item) {
    delete_inventory_unit($item['unit_id']);
    $units_shipped++;
}

log_event("Order {$order_number} shipped by WMS — {$units_shipped} units removed from inventory");

echo json_encode([
    'success' => true,
    'message' => 'Order marked as shipped',
    'units_shipped' => $units_shipped
]);
