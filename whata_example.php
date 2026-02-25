<?php
$api_url = 'https://digmstudents.westphal.drexel.edu/~ps42/api/v1/products.php';
$api_key = 'demo-api-key-123';

$product_data = [
    'name' => 'Groundhogday Phil',
    'description' => 'there has been a lot of phil groundhog',
    'sku' => 'PHIL-001',
    'base_price' => 500.99
];

$options = [
    'http' => [
        'method' => 'POST',
        'header' => "X-API-KEY: $api_key\r\n" .
                    "Content-Type: application/json\r\n",
        'content' => json_encode($product_data)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($api_url, false, $context);

echo $response;
?>