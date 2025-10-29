<?php
// Simple file-backed Notes API
// Endpoints:
// GET    /notes           - list all notes
// POST   /notes           - create a note (json body: title, content)
// GET    /notes/{id}      - get note
// PUT    /notes/{id}      - update note (json body: title?, content?)
// DELETE /notes/{id}      - delete note

header('Content-Type: application/json; charset=utf-8');

$baseDataDir = __DIR__ . '/data';
$dataFile = $baseDataDir . '/notes.json';

if (!is_dir($baseDataDir)) {
    mkdir($baseDataDir, 0777, true);
}
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

function readNotes($file) {
    $json = @file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function writeNotes($file, $notes) {
    file_put_contents($file, json_encode(array_values($notes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function sendJson($payload, $status = 200) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');
$segments = array_values(array_filter(explode('/', $path)));

// Simple router focusing on /notes
if (count($segments) === 0) {
    sendJson(["message" => "Welcome to the simple Notes API. Use /notes endpoint."], 200);
}

if ($segments[0] !== 'notes') {
    sendJson(["error" => "Not Found"], 404);
}

$notes = readNotes($dataFile);

// /notes
if (count($segments) === 1) {
    if ($method === 'GET') {
        sendJson($notes);
    }
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            sendJson(["error" => "Invalid JSON body"], 400);
        }
        $title = isset($input['title']) ? trim($input['title']) : '';
        $content = isset($input['content']) ? trim($input['content']) : '';
        if ($title === '') {
            sendJson(["error" => "Title is required"], 422);
        }
        $ids = array_column($notes, 'id');
        $nextId = $ids ? max($ids) + 1 : 1;
        $now = date(DATE_ATOM);
        $note = [
            'id' => $nextId,
            'title' => $title,
            'content' => $content,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $notes[] = $note;
        writeNotes($dataFile, $notes);
        sendJson($note, 201);
    }
    sendJson(["error" => "Method Not Allowed"], 405);
}

// /notes/{id}
$id = intval($segments[1] ?? 0);
$index = null;
foreach ($notes as $i => $n) {
    if ((int)$n['id'] === $id) { $index = $i; break; }
}

if ($index === null) {
    sendJson(["error" => "Note not found"], 404);
}

if ($method === 'GET') {
    sendJson($notes[$index]);
}

if ($method === 'PUT' || $method === 'PATCH') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        sendJson(["error" => "Invalid JSON body"], 400);
    }
    $updated = false;
    if (isset($input['title'])) {
        $notes[$index]['title'] = trim($input['title']);
        $updated = true;
    }
    if (isset($input['content'])) {
        $notes[$index]['content'] = trim($input['content']);
        $updated = true;
    }
    if ($updated) {
        $notes[$index]['updated_at'] = date(DATE_ATOM);
        writeNotes($dataFile, $notes);
        sendJson($notes[$index]);
    }
    sendJson(["error" => "Nothing to update"], 422);
}

if ($method === 'DELETE') {
    array_splice($notes, $index, 1);
    writeNotes($dataFile, $notes);
    http_response_code(204);
    exit;
}

sendJson(["error" => "Method Not Allowed"], 405);
