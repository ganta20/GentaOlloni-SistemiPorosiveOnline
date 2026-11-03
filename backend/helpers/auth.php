<?php
// Funksione ndihmëse për autentikim dhe autorizim bazuar në sesion PHP.

require_once __DIR__ . '/response.php';

function start_session_once()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

// Kthen përdoruesin e kyçur (array) ose null.
function current_user()
{
    start_session_once();
    return $_SESSION['user'] ?? null;
}

// Kërkon që përdoruesi të jetë i kyçur, përndryshe 401.
function require_login()
{
    $user = current_user();
    if ($user === null) {
        json_error(401, 'UNAUTHENTICATED', 'Duhet të kyçeni për të vazhduar.');
    }
    return $user;
}

// Kërkon rol admin, përndryshe 403.
function require_admin()
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        json_error(403, 'FORBIDDEN', 'Ky veprim kërkon të drejta administratori.');
    }
    return $user;
}