<?php

//this is for the purpose of seeing why im getting an error

// Enable ALL error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Log to see if we even get here
error_log("products.php started");

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Test 1: Check if db_connect.php loads
try {
    error_log("Attempting to load db_connect.php");
    require_once('../db_connect.php');
    error_log("db_connect.php loaded successfully");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB Connect failed', 'details' => $e->getMessage()]);
    exit;
}

// Test 2: Check if $env exists
if (!isset($env)) {
    http_response_code(500);
    echo json_encode(['error' => '$env not defined in db_connect.php']);
    exit;
}

// Test 3: Check if $connection exists
if (!isset($connection)) {
    http_response_code(500);
    echo json_encode(['error' => '$connection not defined in db_connect.php']);
    exit;
}

// Test 4: Check if auth.php loads
try {
    error_log("Attempting to load auth.php");
    require_once('../auth.php');
    error_log("auth.php loaded successfully");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Auth failed to load', 'details' => $e->getMessage()]);
    exit;
}

// Test 5: Check if function exists
if (!function_exists('check_api_key')) {
    http_response_code(500);
    echo json_encode(['error' => 'check_api_key function not found']);
    exit;
}

// Test 6: Try to run check_api_key
try {
    error_log("Attempting to check API key");
    check_api_key($env);
    error_log("API key check passed");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'API key check failed', 'details' => $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {

    $query = "SELECT p.id, p.name, p.base_price FROM products p";

    if (isset($_GET['category'])) {
        $category = $connection->real_escape_string($_GET['category']);
        $query .= " JOIN product_categories pc ON p.id = pc.product_id
                    JOIN categories c ON pc.category_id = c.id
                    WHERE c.name = '$category'";
    }

    $result = $connection->query($query);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Query failed', 'details' => $connection->error]);
        exit;
    }

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $products]);

} elseif ($method === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['base_price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request', 'details' => 'Missing required field(s)']);
        exit;
    }

    $name  = $connection->real_escape_string($data['name']);
    $price = floatval($data['base_price']);

    $stmt = $connection->prepare("INSERT INTO products (name, base_price) VALUES (?, ?)");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Prepare failed', 'details' => $connection->error]);
        exit;
    }
    
    $stmt->bind_param('sd', $name, $price);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'id' => $connection->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Execute failed', 'details' => $stmt->error]);
    }

} else {

    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);

}

?>