
<?php
// Endpoint-et e produkteve: lista, detaje, krijim, perditesim, fshirje.
// GET eshte publik; POST/PUT/DELETE kerkojne admin.

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';

function handle_products($method, $id)
{
    if ($id !== null && !ctype_digit($id)) {
        json_error(400, 'INVALID_ID', 'ID duhet te jete numer.');
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
    $sql = 'SELECT p.id, p.category_id, c.name AS category,
                   p.name, p.description, p.price, p.stock
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
        'SELECT p.id, p.category_id, c.name AS category,
                p.name, p.description, p.price, p.stock
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

function validate_product($input, $partial = false)
{
    $errors = [];
    $clean = [];

    if (!$partial || array_key_exists('name', $input)) {
        $name = trim((string) ($input['name'] ?? ''));

        if ($name === '' || mb_strlen($name) > 150) {
            $errors['name'] = 'Emri eshte i detyrueshem (maksimumi 150 shkronja).';
        }

        $clean['name'] = $name;
    }

    if (!$partial || array_key_exists('price', $input)) {
        $price = $input['price'] ?? null;

        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = 'Cmimi duhet te jete numer >= 0.';
        }

        $clean['price'] = $price;
    }

    if (!$partial || array_key_exists('stock', $input)) {
        $stock = $input['stock'] ?? 0;

        if (filter_var($stock, FILTER_VALIDATE_INT) === false || $stock < 0) {
            $errors['stock'] = 'Stoku duhet te jete numer i plote >= 0.';
        }

        $clean['stock'] = $stock;
    }

    if (!$partial || array_key_exists('category_id', $input)) {
        $cat = $input['category_id'] ?? null;

        if (filter_var($cat, FILTER_VALIDATE_INT) === false) {
            $errors['category_id'] = 'Kategoria eshte e detyrueshme.';
        } else {
            $stmt = getPDO()->prepare(
                'SELECT id FROM categories WHERE id = ?'
            );
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
        json_error(
            422,
            'VALIDATION_ERROR',
            'Te dhenat jane te pavlefshme.',
            $errors
        );
    }

    return $clean;
}

function products_create()
{
    $data = validate_product(get_json_input());
    $pdo = getPDO();

    $stmt = $pdo->prepare(
        'INSERT INTO products
         (category_id, name, description, price, stock)
         VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        (int) $data['category_id'],
        $data['name'],
        $data['description'] ?? null,
        $data['price'],
        (int) $data['stock']
    ]);

    json_success(
        find_product((int) $pdo->lastInsertId()),
        201
    );
}

function products_update($id)
{
    if (!find_product($id)) {
        json_error(404, 'PRODUCT_NOT_FOUND', 'Produkti nuk ekziston.');
    }

    $data = validate_product(get_json_input(), true);

    if (!$data) {
        json_error(422, 'VALIDATION_ERROR', 'Nuk u dergua asnje fushe.');
    }

    $allowed = [
        'name',
        'price',
        'stock',
        'category_id',
        'description'
    ];

    $sets = [];
    $values = [];

    foreach ($allowed as $field) {
        if (array_key_exists($field, $data)) {
            $sets[] = $field . ' = ?';
            $values[] = $data[$field];
        }
    }

    $values[] = $id;

    $sql = 'UPDATE products SET ' . implode(', ', $sets) . ' WHERE id = ?';

    try {
        $stmt = getPDO()->prepare($sql);
        $stmt->execute($values);
    } catch (PDOException $e) {
        json_error(500, 'UPDATE_FAILED', 'Produkti nuk u perditesua.');
    }

    json_success(find_product($id));
}

function products_delete($id)
{
    if (!find_product($id)) {
        json_error(404, 'PRODUCT_NOT_FOUND', 'Produkti nuk ekziston.');
    }

    try {
        $stmt = getPDO()->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        json_error(
            409,
            'PRODUCT_IN_USE',
            'Produkti nuk mund te fshihet sepse perdoret ne porosi.'
        );
    }

    json_success([
        'message' => 'Produkti u fshi me sukses.',
        'id' => $id
    ]);
}
