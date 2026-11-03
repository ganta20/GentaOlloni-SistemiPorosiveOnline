<?php
// Lidhja me databazën përmes PDO.
// Për XAMPP lokal: përdoruesi root pa fjalëkalim.

require_once __DIR__ . '/../helpers/response.php';

function getPDO()
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=localhost;dbname=porosi_online;charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, 'root', '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            json_error(500, 'DB_CONNECTION_FAILED', 'Lidhja me databazën dështoi.');
        }
    }

    return $pdo;
}