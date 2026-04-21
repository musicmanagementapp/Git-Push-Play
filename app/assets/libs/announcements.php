<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/data.php';

$userId   = $_SESSION['user_id'] ?? '';
$bandId   = null;
$isOwner  = false;
$musician = null;

if ($userId !== '') {
    $musician = find_musician_by_user_id($userId);
    if ($musician) {
        $band    = get_active_band_for_musician($musician['id']);
        $bandId  = $band['id'] ?? null;
        if ($bandId) {
            $mem     = get_membership_for_musician_in_band($musician['id'], $bandId);
            $isOwner = ($mem['role'] ?? '') === 'owner';
        }
    }
}

$file = __DIR__ . '/../../secure/announcements.json';
if (!file_exists($file)) file_put_contents($file, '[]');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($bandId === null) { echo json_encode([]); exit; }
    $data = json_decode(file_get_contents($file), true) ?: [];
    $data = array_values(array_filter($data, fn($a) => ($a['band_id'] ?? null) === $bandId));
    usort($data, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
    echo json_encode($data);
    exit;
}

if ($method === 'POST') {
    $headers     = function_exists('apache_request_headers') ? apache_request_headers() : [];
    $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($headers['X-CSRF-Token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $clientToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security token validation failed.']);
        exit;
    }

    if ($bandId === null) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You must be in a band.']);
        exit;
    }

    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    if ($action === 'create') {
        if (!$isOwner) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Only the band owner can post announcements.']);
            exit;
        }

        $title = trim($input['title'] ?? '');
        $notes = trim($input['notes'] ?? '');

        if (strlen($title) < 2) {
            echo json_encode(['success' => false, 'message' => 'Title must be at least 2 characters.']);
            exit;
        }

        $data   = json_decode(file_get_contents($file), true) ?: [];
        $data[] = [
            'id'         => bin2hex(random_bytes(8)),
            'band_id'    => $bandId,
            'title'      => $title,
            'notes'      => $notes,
            'posted_by'  => trim($musician['stageName'] ?? '') ?: trim(($musician['firstName'] ?? '') . ' ' . ($musician['lastName'] ?? '')),
            'created_at' => date('c'),
        ];

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Announcement posted.']);
        exit;
    }

    if ($action === 'delete') {
        if (!$isOwner) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Only the band owner can delete announcements.']);
            exit;
        }

        $id   = $input['id'] ?? '';
        $data = json_decode(file_get_contents($file), true) ?: [];
        $data = array_values(array_filter(
            $data,
            fn($a) => !($a['id'] === $id && ($a['band_id'] ?? null) === $bandId)
        ));

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Announcement deleted.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
?>
