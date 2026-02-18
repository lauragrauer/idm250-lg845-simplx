<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // * = everyone can access

require_once('../db_connect.php');
require_once('../auth.php');

check_api_key($env);

$method = $_SERVER['REQUEST_METHOD'];
$id     = intval(basename($_SERVER['REQUEST_URI']));

if (!$id) { 
    http_response_code(400);
    echo json_encode(['error' => 'Bad Request', 'details' => 'Missing ID']);
    exit;
}

if ($method === 'GET') {

    $stmt = $connection->prepare("SELECT * FROM idm250 WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id); 
    $stmt->execute();
    $result  = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $var_query = $connection->prepare("SELECT * FROM product_variants WHERE product_id = ?");
        $var_query->bind_param('i', $id); 
        $var_query->execute();
        $vars_result        = $var_query->get_result();
        $vars               = $vars_result->fetch_assoc();
        $product['variants'] = $vars;

        echo json_encode(['success' => true, 'data' => $product]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }

} elseif ($method === 'PUT') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['sku']) && !isset($data['description']) && !isset($data['rate'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request']);
        exit;
    }

    $description = $connection->real_escape_string($data['description']);
    $stmt = $connection->prepare("UPDATE idm250 SET p.description = ? WHERE id = ? LIMIT 1");
    $stmt->bind_param('si', $description, $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error']);
    }

} elseif ($method === 'DELETE') {

    $stmt = $connection->prepare("DELETE FROM idm250 WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id); 

    if ($stmt->execute()) {
        http_response_code(204);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Server Error']);
    }

} else {

    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);

}

?>