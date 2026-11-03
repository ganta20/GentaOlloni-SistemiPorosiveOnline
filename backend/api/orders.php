<?php
// Endpoint-et e porosive: lista, detaje, krijim nga shporta, ndryshim statusi.
// Të gjitha kërkojnë login. Klienti sheh vetëm porositë e veta.

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';

function handle_orders($method, $id)
{
    $user = require_login();

    if ($id !== null && !ctype_digit($id)) {
        json_error(400, 'INVALID_ID', 'ID duhet të jetë numër.');
    }
    $id = $id === null ? null : (int) $id;

    switch ($method) {
        case 'GET':
            $id === null ? orders_list($user) : orders_get($user, $id);
            break;

        case 'POST':
            if ($id !== null) {
                json_error(405, 'METHOD_NOT_ALLOWED', 'POST nuk pranon ID.');
            }
            orders_create($user);
            break;

        case 'PATCH':
        case 'PUT':
            if ($id === null) {
                json_error(400, 'ID_REQUIRED', 'ID e porosisë mungon.');
            }
            orders_update_status($user, $id);
            break;

        default:
            json_error(405, 'METHOD_NOT_ALLOWED', 'Metoda nuk lejohet.');
    }
}

// Kthen porosinë me artikujt dhe pagesën. Klienti e merr vetëm nëse është e tijja.
function find_order($user, $id)
{
    $pdo = getPDO();

    $sql    = 'SELECT o.id, o.user_id, o.address_id, o.status, o.total, o.created_at,
                      p.method AS payment_method, p.status AS payment_status
               FROM orders o
               LEFT JOIN payments p ON p.order_id = o.id
               WHERE o.id = ?';
    $params = [$id];

    if ($user['role'] !== 'admin') {
        $sql     .= ' AND o.user_id = ?';
        $params[] = $user['id'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $order = $stmt->fetch();
    if (!$order) {
        return null;
    }

    $stmt = $pdo->prepare(
        'SELECT oi.product_id, pr.name, oi.quantity, oi.unit_price
         FROM order_items oi
         JOIN products pr ON pr.id = oi.product_id
         WHERE oi.order_id = ?'
    );
    $stmt->execute([$id]);
    $order['items'] = $stmt->fetchAll();

    return $order;
}

function orders_list($user)
{
    $sql    = 'SELECT id, user_id, address_id, status, total, created_at FROM orders';
    $params = [];

    if ($user['role'] !== 'admin') {
        $sql     .= ' WHERE user_id = ?';
        $params[] = $user['id'];
    }
    $sql .= ' ORDER BY id DESC';

    $stmt = getPDO()->prepare($sql);
    $stmt->execute($params);
    json_success($stmt->fetchAll());
}

function orders_get($user, $id)
{
    $order = find_order($user, $id);
    if (!$order) {
        // 404 edhe kur porosia i përket dikujt tjetër: nuk zbulojmë ekzistencën.
        json_error(404, 'ORDER_NOT_FOUND', 'Porosia nuk ekziston.');
    }
    json_success($order);
}

function orders_create($user)
{
    $input = get_json_input();

    $addressId = $input['address_id'] ?? null;
    if (filter_var($addressId, FILTER_VALIDATE_INT) === false) {
        json_error(422, 'VALIDATION_ERROR', 'Adresa është e detyrueshme.', [
            'address_id' => 'Jepni një address_id të vlefshëm.',
        ]);
    }

    $pdo = getPDO();

    try {
        $pdo->beginTransaction();

        // Adresa duhet t'i përkasë këtij përdoruesi.
        $stmt = $pdo->prepare('SELECT id FROM addresses WHERE id = ? AND user_id = ?');
        $stmt->execute([(int) $addressId, $user['id']]);
        if (!$stmt->fetch()) {
            $pdo->rollBack();
            json_error(422, 'INVALID_ADDRESS', 'Adresa nuk ekziston ose nuk është e juaja.');
        }

        // Shporta e përdoruesit; FOR UPDATE bllokon rreshtat e produkteve gjatë transaksionit.
        $stmt = $pdo->prepare(
            'SELECT ci.product_id, ci.quantity, p.name, p.price, p.stock
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             WHERE ci.user_id = ?
             FOR UPDATE'
        );
        $stmt->execute([$user['id']]);
        $cart = $stmt->fetchAll();

        if (!$cart) {
            $pdo->rollBack();
            json_error(422, 'CART_EMPTY', 'Shporta është bosh.');
        }

        // Kontrollo stokun dhe llogarit totalin në server (jo nga klienti).
        $total = 0;
        foreach ($cart as $row) {
            if ($row['quantity'] > $row['stock']) {
                $pdo->rollBack();
                json_error(409, 'INSUFFICIENT_STOCK',
                    'Stoku i pamjaftueshëm për: ' . $row['name'], [
                        'product_id' => (int) $row['product_id'],
                        'requested'  => (int) $row['quantity'],
                        'available'  => (int) $row['stock'],
                    ]);
            }
            $total += $row['quantity'] * $row['price'];
        }

        $stmt = $pdo->prepare(
            "INSERT INTO orders (user_id, address_id, status, total) VALUES (?, ?, 'pending', ?)"
        );
        $stmt->execute([$user['id'], (int) $addressId, $total]);
        $orderId = (int) $pdo->lastInsertId();

        $insertItem = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
        );
        $reduceStock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');

        foreach ($cart as $row) {
            // unit_price ruan çmimin në momentin e porosisë.
            $insertItem->execute([$orderId, $row['product_id'], $row['quantity'], $row['price']]);
            $reduceStock->execute([$row['quantity'], $row['product_id']]);
        }

        $stmt = $pdo->prepare("INSERT INTO payments (order_id, method, status) VALUES (?, 'cash_on_delivery', 'pending')");
        $stmt->execute([$orderId]);

        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([$user['id']]);

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        json_error(500, 'ORDER_FAILED', 'Porosia nuk u krijua.');
    }

    json_success(find_order($user, $orderId), 201);
}

function orders_update_status($user, $id)
{
    $input  = get_json_input();
    $status = $input['status'] ?? '';
    $valid  = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

    if (!in_array($status, $valid, true)) {
        json_error(422, 'VALIDATION_ERROR', 'Status i pavlefshëm.', ['allowed' => $valid]);
    }

    $order = find_order($user, $id);
    if (!$order) {
        json_error(404, 'ORDER_NOT_FOUND', 'Porosia nuk ekziston.');
    }

    // Klienti mund vetëm ta anulojë porosinë e vet kur është pending.
    if ($user['role'] !== 'admin') {
        if ($status !== 'cancelled') {
            json_error(403, 'FORBIDDEN', 'Mund vetëm ta anuloni porosinë.');
        }
        if ($order['status'] !== 'pending') {
            json_error(409, 'CANNOT_CANCEL', 'Vetëm porositë në pritje mund të anulohen.');
        }
    }

    if ($order['status'] === $status) {
        json_success($order);
    }
    if (in_array($order['status'], ['cancelled', 'delivered'], true)) {
        json_error(409, 'ORDER_FINAL', 'Porosia është e mbyllur dhe nuk ndryshohet.');
    }

    $pdo = getPDO();
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);

        // Anulimi kthen stokun.
        if ($status === 'cancelled') {
            $restock = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            foreach ($order['items'] as $item) {
                $restock->execute([$item['quantity'], $item['product_id']]);
            }
        }

        // Kur porosia dorëzohet, pagesa (cash on delivery) shënohet e paguar.
        if ($status === 'delivered') {
            $stmt = $pdo->prepare("UPDATE payments SET status = 'paid' WHERE order_id = ?");
            $stmt->execute([$id]);
        }

        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        json_error(500, 'UPDATE_FAILED', 'Statusi nuk u ndryshua.');
    }

    json_success(find_order($user, $id));
}