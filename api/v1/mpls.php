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

$data   = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action !== 'confirm') {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request', 'details' => 'Unknown action']);
    exit;
}

$reference_number = $data['reference_number'] ?? '';

if (!$reference_number) {
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request', 'details' => 'Missing reference_number']);
    exit;
}

$stmt = $connection->prepare("SELECT * FROM mpls WHERE reference_number = ? LIMIT 1");
$stmt->bind_param('s', $reference_number);
$stmt->execute();
$mpl = $stmt->get_result()->fetch_assoc();

if (!$mpl) {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found', 'details' => 'MPL not found']);
    exit;
}

if ($mpl['status'] === 'confirmed') {
    http_response_code(409);
    echo json_encode(['error' => 'Conflict', 'details' => 'MPL already confirmed']);
    exit;
}

update_mpl_status($mpl['id'], 'confirmed');

$items = get_mpl_items($mpl['id']);

$units_moved = 0;
foreach ($items as $item) {
    move_inventory_unit($item['unit_id'], 'warehouse');
    $units_moved++;
}

log_event("MPL {$reference_number} confirmed by WMS — {$units_moved} units moved to warehouse");

echo json_encode([
    'success'     => true,
    'message'     => 'MPL confirmed',
    'units_moved' => $units_moved
]);