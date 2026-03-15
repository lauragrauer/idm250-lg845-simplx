<?php
// sku stuff

function get_all_skus() {
    global $connection;
    $result = $connection->query("SELECT * FROM skus ORDER BY sku");
    $skus = [];
    while ($row = $result->fetch_assoc()) {
        $skus[] = $row;
    }
    return $skus;
}

function get_sku_by_id($id) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM skus WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function create_sku($data) {
    global $connection;
    $stmt = $connection->prepare("
        INSERT INTO skus
            (ficha, sku, description, uom_primary, piece_count,
             length_inches, width_inches, height_inches, weight_lbs, assembly, rate)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('isssiddddsd',
        $data['ficha'],
        $data['sku'],
        $data['description'],
        $data['uom_primary'],
        $data['piece_count'],
        $data['length_inches'],
        $data['width_inches'],
        $data['height_inches'],
        $data['weight_lbs'],
        $data['assembly'],
        $data['rate']
    );
    return $stmt->execute();
}

function update_sku($id, $data) {
    global $connection;
    $stmt = $connection->prepare("
        UPDATE skus SET
            ficha          = ?,
            sku            = ?,
            description    = ?,
            uom_primary    = ?,
            piece_count    = ?,
            length_inches  = ?,
            width_inches   = ?,
            height_inches  = ?,
            weight_lbs     = ?,
            assembly       = ?,
            rate           = ?
        WHERE id = ?
    ");
    $stmt->bind_param('isssiddddsdi',
        $data['ficha'],
        $data['sku'],
        $data['description'],
        $data['uom_primary'],
        $data['piece_count'],
        $data['length_inches'],
        $data['width_inches'],
        $data['height_inches'],
        $data['weight_lbs'],
        $data['assembly'],
        $data['rate'],
        $id
    );
    return $stmt->execute();
}

function delete_sku($id) {
    global $connection;
    $check = $connection->prepare("SELECT COUNT(*) AS c FROM inventory WHERE sku_id = ?");
    $check->bind_param('i', $id);
    $check->execute();
    if ($check->get_result()->fetch_assoc()['c'] > 0) return false;

    $stmt = $connection->prepare("DELETE FROM skus WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}


// inv

function get_inventory($location) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT i.unit_id, i.location, i.created_at,
               s.sku, s.description
        FROM   inventory i
        JOIN   skus s ON i.sku_id = s.id
        WHERE  i.location = ?
        ORDER  BY i.unit_id
    ");
    $stmt->bind_param('s', $location);
    $stmt->execute();
    $result = $stmt->get_result();
    $units = [];
    while ($row = $result->fetch_assoc()) {
        $units[] = $row;
    }
    return $units;
}

function add_inventory_unit($unit_id, $sku_id, $location) {
    global $connection;
    $stmt = $connection->prepare("
        INSERT INTO inventory (unit_id, sku_id, location) VALUES (?, ?, ?)
    ");
    $stmt->bind_param('sis', $unit_id, $sku_id, $location);
    return $stmt->execute();
}

function delete_inventory_unit($unit_id) {
    global $connection;
    $stmt = $connection->prepare("DELETE FROM inventory WHERE unit_id = ?");
    $stmt->bind_param('s', $unit_id);
    return $stmt->execute();
}

function move_inventory_unit($unit_id, $new_location) {
    global $connection;
    $stmt = $connection->prepare("UPDATE inventory SET location = ? WHERE unit_id = ?");
    $stmt->bind_param('ss', $new_location, $unit_id);
    return $stmt->execute();
}


// mpls

function get_all_mpls() {
    global $connection;
    $result = $connection->query("SELECT * FROM mpls ORDER BY created_at DESC");
    $mpls = [];
    while ($row = $result->fetch_assoc()) {
        $mpls[] = $row;
    }
    return $mpls;
}

function get_mpl_by_id($id) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM mpls WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_mpl_items($mpl_id) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT mi.unit_id,
               COALESCE(s.sku,         si.sku)             AS sku,
               COALESCE(s.description, si.sku_description) AS description
        FROM   mpl_items mi
        LEFT JOIN inventory i  ON mi.unit_id = i.unit_id
        LEFT JOIN skus      s  ON i.sku_id   = s.id
        LEFT JOIN shipped_items si ON mi.unit_id = si.unit_id
        WHERE  mi.mpl_id = ?
        ORDER  BY mi.unit_id
    ");
    $stmt->bind_param('i', $mpl_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

function create_mpl($data, $unit_ids) {
    global $connection;
    $stmt = $connection->prepare("
        INSERT INTO mpls (reference_number, trailer_number, expected_arrival)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param('sss',
        $data['reference_number'],
        $data['trailer_number'],
        $data['expected_arrival']
    );
    $stmt->execute();
    $mpl_id = $connection->insert_id;

    if ($unit_ids) {
        $stmt = $connection->prepare("INSERT INTO mpl_items (mpl_id, unit_id) VALUES (?, ?)");
        foreach ($unit_ids as $unit_id) {
            $stmt->bind_param('is', $mpl_id, $unit_id);
            $stmt->execute();
        }
    }

    return $mpl_id;
}

function update_mpl($id, $data) {
    global $connection;
    $stmt = $connection->prepare("
        UPDATE mpls SET
            reference_number = ?,
            trailer_number   = ?,
            expected_arrival = ?
        WHERE id = ?
    ");
    $stmt->bind_param('sssi',
        $data['reference_number'],
        $data['trailer_number'],
        $data['expected_arrival'],
        $id
    );
    return $stmt->execute();
}

function replace_mpl_items($mpl_id, $unit_ids) {
    global $connection;
    $stmt = $connection->prepare("DELETE FROM mpl_items WHERE mpl_id = ?");
    $stmt->bind_param('i', $mpl_id);
    $stmt->execute();

    if ($unit_ids) {
        $stmt = $connection->prepare("INSERT INTO mpl_items (mpl_id, unit_id) VALUES (?, ?)");
        foreach ($unit_ids as $unit_id) {
            $stmt->bind_param('is', $mpl_id, $unit_id);
            $stmt->execute();
        }
    }
}

function update_mpl_status($id, $status) {
    global $connection;
    $stmt = $connection->prepare("UPDATE mpls SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    return $stmt->execute();
}

function delete_mpl($id) {
    global $connection;
    $mpl = get_mpl_by_id($id);
    if (!$mpl || $mpl['status'] != 'draft') return false;

    $stmt = $connection->prepare("DELETE FROM mpl_items WHERE mpl_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $stmt = $connection->prepare("DELETE FROM mpls WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}


// orders

function get_all_orders() {
    global $connection;
    $result = $connection->query("SELECT * FROM orders ORDER BY created_at DESC");
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    return $orders;
}

function get_order_by_id($id) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function get_order_items($order_id) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT oi.unit_id,
               s.sku, s.description
        FROM   order_items oi
        JOIN   inventory   i  ON oi.unit_id = i.unit_id
        JOIN   skus        s  ON i.sku_id   = s.id
        WHERE  oi.order_id = ?
        ORDER  BY oi.unit_id
    ");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

function get_shipped_items_by_order($order_id) {
    global $connection;
    $stmt = $connection->prepare("
        SELECT unit_id,
               sku,
               sku_description AS description
        FROM   shipped_items
        WHERE  order_id = ?
        ORDER  BY unit_id
    ");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    return $items;
}

function create_order($data, $unit_ids) {
    global $connection;
    $stmt = $connection->prepare("
        INSERT INTO orders
            (order_number, ship_to_company, ship_to_street,
             ship_to_city, ship_to_state, ship_to_zip)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('ssssss',
        $data['order_number'],
        $data['ship_to_company'],
        $data['ship_to_street'],
        $data['ship_to_city'],
        $data['ship_to_state'],
        $data['ship_to_zip']
    );
    $stmt->execute();
    $order_id = $connection->insert_id;

    if ($unit_ids) {
        $stmt = $connection->prepare("INSERT INTO order_items (order_id, unit_id) VALUES (?, ?)");
        foreach ($unit_ids as $unit_id) {
            $stmt->bind_param('is', $order_id, $unit_id);
            $stmt->execute();
        }
    }

    return $order_id;
}

function update_order($id, $data) {
    global $connection;
    $stmt = $connection->prepare("
        UPDATE orders SET
            order_number    = ?,
            ship_to_company = ?,
            ship_to_street  = ?,
            ship_to_city    = ?,
            ship_to_state   = ?,
            ship_to_zip     = ?
        WHERE id = ?
    ");
    $stmt->bind_param('ssssssi',
        $data['order_number'],
        $data['ship_to_company'],
        $data['ship_to_street'],
        $data['ship_to_city'],
        $data['ship_to_state'],
        $data['ship_to_zip'],
        $id
    );
    return $stmt->execute();
}

function replace_order_items($order_id, $unit_ids) {
    global $connection;
    $stmt = $connection->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();

    if ($unit_ids) {
        $stmt = $connection->prepare("INSERT INTO order_items (order_id, unit_id) VALUES (?, ?)");
        foreach ($unit_ids as $unit_id) {
            $stmt->bind_param('is', $order_id, $unit_id);
            $stmt->execute();
        }
    }
}

function update_order_status($id, $status) {
    global $connection;
    $stmt = $connection->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $id);
    return $stmt->execute();
}

function delete_order($id) {
    global $connection;
    $order = get_order_by_id($id);
    if (!$order || $order['status'] != 'draft') return false;

    $stmt = $connection->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $stmt = $connection->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param('i', $id);
    return $stmt->execute();
}


// dashboard

function count_skus() {
    global $connection;
    $result = $connection->query("SELECT COUNT(*) AS c FROM skus");
    return $result->fetch_assoc()['c'];
}

function count_inventory($location) {
    global $connection;
    $stmt = $connection->prepare("SELECT COUNT(*) AS c FROM inventory WHERE location = ?");
    $stmt->bind_param('s', $location);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['c'];
}

function count_mpls() {
    global $connection;
    $result = $connection->query("SELECT COUNT(*) AS c FROM mpls");
    return $result->fetch_assoc()['c'];
}

function count_orders() {
    global $connection;
    $result = $connection->query("SELECT COUNT(*) AS c FROM orders");
    return $result->fetch_assoc()['c'];
}

function count_shipped_orders() {
    global $connection;
    $result = $connection->query("SELECT COUNT(*) AS c FROM orders WHERE status = 'confirmed'");
    return $result->fetch_assoc()['c'];
}

// extra

function get_order_by_id_by_number($order_number) {
    global $connection;
    $stmt = $connection->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
    $stmt->bind_param('s', $order_number);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function update_order_status_shipped($id, $status, $shipped_at = null) {
    global $connection;
    if ($shipped_at) {
        $stmt = $connection->prepare("UPDATE orders SET status = ?, shipped_at = ? WHERE id = ?");
        $stmt->bind_param('ssi', $status, $shipped_at, $id);
    } else {
        $stmt = $connection->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $id);
    }
    return $stmt->execute();
}