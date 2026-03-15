<?php

function api_request($url, $method, $data, $api_key) {
    $options = [
        'http' => [
            'method'  => $method,
            'header'  => "Content-Type: application/json\r\n" .
                         "x-api-key: " . $api_key . "\r\n",
            'content' => json_encode($data),
            'ignore_errors' => true
        ]
    ];

    $context  = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $result   = json_decode($response, true);

    return $result;
}

function send_mpl_to_wms($mpl, $items, $env) {

    $payload_items = [];
    foreach ($items as $r) {
        $payload_items[] = [
            'unit_id'     => $r['unit_id'],
            'sku'         => $r['sku'],
            'sku_details' => [
                'ficha'         => intval($r['ficha']),
                'sku'           => $r['sku'],
                'description'   => $r['description'],
                'uom_primary'   => $r['uom_primary'],
                'piece_count'   => intval($r['piece_count']),
                'length_inches' => $r['length_inches'],
                'width_inches'  => $r['width_inches'],
                'height_inches' => $r['height_inches'],
                'weight_lbs'    => $r['weight_lbs'],
                'assembly'      => $r['assembly'],
                'rate'          => $r['rate'],
            ],
        ];
    }

    $payload = [
        'reference_number' => $mpl['reference_number'],
        'trailer_number'   => $mpl['trailer_number'],
        'expected_arrival' => $mpl['expected_arrival'],
        'items'            => $payload_items,
    ];

    return api_request($env['WMS_API_URL'] . '/mpls.php', 'POST', $payload, $env['WMS_API_KEY']);
}

function sync_sku_to_wms($sku_data, $env) {
    return api_request($env['WMS_API_URL'] . '/skus.php', 'POST', $sku_data, $env['WMS_API_KEY']);
}

function send_order_to_wms($order, $items, $env) {

    $payload_items = [];
    foreach ($items as $r) {
        $payload_items[] = ['unit_id' => $r['unit_id']];
    }

    $payload = [
        'order_number'    => $order['order_number'],
        'ship_to_company' => $order['ship_to_company'],
        'ship_to_street'  => $order['ship_to_street'],
        'ship_to_city'    => $order['ship_to_city'],
        'ship_to_state'   => $order['ship_to_state'],
        'ship_to_zip'     => $order['ship_to_zip'],
        'items'           => $payload_items,
    ];

    return api_request($env['WMS_API_URL'] . '/orders.php', 'POST', $payload, $env['WMS_API_KEY']);
}