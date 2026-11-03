<?php
// Endpoint-et e produkteve: lista, detaje, krijim, përditësim, fshirje.
// GET është publik; POST/PUT/DELETE kërkojnë admin.

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';

function handle_products($method, $id)
{
    if ($id !== null && !ctype_digit($id)) {
        json_error(400, 'INVALID_ID', 'ID duhet të jetë numër.');
    }
    $id = $id === null ? null : (int) $id;

    switch ($method) {
        case 'GET':
            $id === null ? products_list() : products_get($id);
            break;

        case 'POST':
            if ($id !== null) {
                json_error(405, 'METHOD_NOT_ALLOWED', 'POST nuk pranon ID.');
            }
            require_admin();
            products_create();
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                json_error(400, 'ID_REQUIRED', 'ID e produktit mungon.');
            }
            require_admin();
            products_update($id);
            break;

        case 'DELETE':
            if ($id === null) {
                json_error(400, 'ID_REQUIRED', 'ID e produktit mungon.');
            }
            require_admin();
            products_delete($id);
            break;

        default:
            json_error(405, 'METHOD_NOT_ALLOWED', 'Metoda nuk lejohet.');
    }
}

function products_list()
{
    $sql = 'SELECT p.id, p.category_id, c.name AS category, p.name,
                   p.description, p.price, p.stock
            FROM products p
            JOIN categories c ON c.id = p.category_id';
    $params = [];

    if (isset($_GET['category_id']) && ctype_digit($_GET['category_id'])) {
        $sql .= ' WHERE p.category_id = ?';
        $params[] = (int) $_GET['category_id'];
    }
    $sql .= ' ORDER BY p.id';

    $stmt = getPDO()->prepare($sql);
    $stmt->execute($params);
    json_success($stmt->fetchAll());
}

function find_product($id)
{
    $stmt = getPDO()->prepare(
        'SELECT p.id, p.category_id, c.name AS category, p.name,
                p.description, p.price, p.stock
         FROM products p
         JOIN categories c ON c.id = p.category_id
         WHERE p.id = ?'
    );
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function products_get($id)
{
    $product = find_product($id);
    if (!$product) {
        json_error(404, 'PRODUCT_NOT_FOUND', 'Produkti nuk ekziston.');
    }
    json_success($product);
}

// Validon të dhënat; $partial = true lejon fusha që mungojnë (përditësim).
function validate_product($input, $partial = false)
{
    $errors = [];
    $clean  = [];

    if (!$partial || array_key_exists('name', $input)) {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '' || mb_strlen($name) > 150) {
            $errors['name'] = 'Emri është i detyrueshëm (maksimumi 150 shkronja).';
        }
        $clean['name'] = $name;
    }

    if (!$partial || array_key_exists('price', $input)) {
        $price = $input['price'] ?? null;
        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Çmimi duhet të jetë numër >= 0.';
        }
        $clean['price'] = $price;
    }

    if (!$partial || array_key_exists('stock', $input)) {
        $stock = $input['stock'] ?? 0;
        if (filter_var($stock, FILTER_VALIDATE_INT) === false || $stock < 0) {
            $errors['stock'] = 'Stoku duhet të jetë numër i plotë >= 0.';
        }
        $clean['stock'] = $stock;
    }

    if (!$partial || array_key_exists('category_id', $input)) {
        $cat = $input['category_id'] ?? null;
        if (filter_var($cat, FILTER_VALIDATE_INT) === false) {
            $errors['category_id'] = 'Kategoria është e detyrueshme.';
        } else {
            $stmt = getPDO()->prepare('SELECT id FROM categories WHERE id = ?');
            $stmt->execute([(int) $cat]);
            if (!$stmt->fetch()) {
                $errors['category_id'] = 'Kategoria nuk ekziston.';
            }
        }
        $clean['category_id'] = $cat;
    }

    if (array_key_exists('description', $input)) {
        $clean['description'] = $input['description'] === null
            ? null
            : trim((string) $input['description']);
    }

    if ($errors) {
        json_error(422, 'VALIDATION_ERROR', 'Të dhëna të pavlefshme.', $errors);
    }
    return $clean;
}

function products_create()
{
    $data = validate_product(get_json_input());
    $pdo  = getPDO();

    $stmt = $pdo->prepare(
        'INSERT INTO products (category_id, name, description, price, stock)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        (int) $data['category_id'],
        $data['name'],
        $data['description'] ?? null,
        $data['price'],
        (int) $data['stock'],
    ]);

    json_success(find_product((int) $pdo->lastInsertId()), 201);
}

function products_update($id)
{
    if (!find_product($id)) {
        json_error(404, 'PRODUCT_NOT_FOUND',