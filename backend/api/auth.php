<?php
// Endpoint-et e autentikimit: login, logout, me.
// Thirren nga api/index.php me $action = 'login' | 'logout' | 'me'.

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/auth.php';

function handle_auth($method, $action)
{
    switch ($action) {
        case 'login':
            if ($method !== 'POST') {
                json_error(405, 'METHOD_NOT_ALLOWED', 'Përdorni POST.');
            }
            auth_login();
            break;

        case 'logout':
            if ($method !== 'POST') {
                json_error(405, 'METHOD_NOT_ALLOWED', 'Përdorni POST.');
            }
            auth_logout();
            break;

        case 'me':
            if ($method !== 'GET') {
                json_error(405, 'METHOD_NOT_ALLOWED', 'Përdorni GET.');
            }
            json_success(require_login());
            break;

        default:
            json_error(404, 'NOT_FOUND', 'Endpoint-i nuk ekziston.');
    }
}

function auth_login()
{
    $input    = get_json_input();
    $email    = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';

    if ($email === '' || $password === '') {
        json_error(422, 'VALIDATION_ERROR', 'Email dhe fjalëkalimi janë të detyrueshëm.');
    }

    $stmt = getPDO()->prepare(
        'SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Mesazh i njëjtë për email të gabuar dhe fjalëkalim të gabuar.
    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_error(401, 'INVALID_CREDENTIALS', 'Email ose fjalëkalim i gabuar.');
    }

    start_session_once();
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'        => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email'     => $user['email'],
        'role'      => $user['role'],
    ];

    json_success($_SESSION['user']);
}

function auth_logout()
{
    start_session_once();
    $_SESSION = [];
    session_destroy();
    json_success(['message' => 'U çkyçët me sukses.']);
}