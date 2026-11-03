<?php
// Funksione ndihmëse për përgjigje JSON me format të njëjtë.

function json_response($status, $data)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_success($data = null, $status = 200)
{
    json_response($status, [
        'success' => true,
        'data'    => $data,
    ]);
}

function json_error($status, $code, $message, $details = null)
{
    $body = [
        'success' => false,
        'error'   => $code,
        'message' => $message,
    ];
    if ($details !== null) {
        $body['details'] = $details;
    }
    json_response($status, $body);
}

// Lexon trupin e kërkesës si JSON dhe e kthen si array.
function get_json_input()
{
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        json_error(400, 'INVALID_JSON', 'Trupi i kërkesës nuk është JSON i vlefshëm.');
    }
    return $data;
}