<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // * = everyone can access

require_once('../db_connect.php');
require_once('../auth.php');

check_api_key($env);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    $query = "SELECT p.id, p.sku, p.description, p.rate FROM idm250 p";

    if (isset($_GET['search'])) {
        $search = $connection->real_escape_string($_GET['search']);
        $query .= " WHERE p.sku LIKE '%$search%' OR p.description LIKE '%$search%'";
    }

    $result = $connection->query($query);

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $products]);

} elseif ($method === 'POST') {

    // Take JSON and decode data from other team
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['p.sku']) || !isset($data['p.description'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request', 'details' => 'Missing required field(s)']);
        exit;
    }

    $sku         = $connection->real_escape_string($data['p.sku']);
    $description = $connection->real_escape_string($data['p.description']);
    $rate        = isset($data['p.rate']) ? floatval($data['p.rate']) : 0.0;

    $ficha         = isset($data['p.ficha']) ? $connection->real_escape_string($data['p.ficha']) : '';
    $uom_primary   = isset($data['p.uom_primary']) ? $connection->real_escape_string($data['p.uom_primary']) : '';
    $piece_count   = isset($data['p.piece_count']) ? intval($data['p.piece_count']) : 0;
    $length_inches = isset($data['p.length_inches']) ? floatval($data['p.length_inches']) : 0.0;
    $width_inches  = isset($data['p.width_inches']) ? floatval($data['p.width_inches']) : 0.0;
    $height_inches = isset($data['p.height_inches']) ? floatval($data['p.height_inches']) : 0.0;
    $weight_lbs    = isset($data['p.weight_lbs']) ? floatval($data['p.weight_lbs']) : 0.0;
    $assembly      = isset($data['p.assembly']) ? $connection->real_escape_string($data['p.assembly']) : '';

    $stmt = $connection->prepare("INSERT INTO idm250 (p.ficha, p.sku, p.description, p.uom_primary, p.piece_count, p.length_inches, p.width_inches, p.height_inches, p.weight_lbs, p.assembly, p.rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('sssidddddsd', $ficha, $sku, $description, $uom_primary, $piece_count, $length_inches, $width_inches, $height_inches, $weight_lbs, $assembly, $rate);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $connection->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error']);
    }

} else {

    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);

}