<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Resolve the band for the logged-in user from the server side
require_once __DIR__ . '/data.php';

$userId = $_SESSION['user_id'] ?? '';
$bandId = null;

if ($userId !== '') {
    $musician = find_musician_by_user_id($userId);
    if ($musician) {
        $band = get_active_band_for_musician($musician['id']);
        $bandId = $band['id'] ?? null;
    }
}

$file = __DIR__ . '/../../secure/events.json';

if (!file_exists($file)) {
    file_put_contents($file, '[]');
}

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: return only this band's events ──────────────────────────────────────
if ($method === 'GET') {
    if ($bandId === null) {
        echo json_encode([]);
        exit;
    }

    $data = json_decode(file_get_contents($file), true) ?: [];
    $data = array_values(array_filter($data, fn($e) => ($e['band_id'] ?? null) === $bandId));
    usort($data, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']));
    echo json_encode($data);
    exit;
}

// ── POST: mutate ─────────────────────────────────────────────────────────────
if ($method === 'POST') {
    // CSRF validation
    $headers = function_exists('apache_request_headers') ? apache_request_headers() : [];
    $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($headers['X-CSRF-Token'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'], $clientToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security token validation failed.']);
        exit;
    }

    if ($bandId === null) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You must be in a band to manage events.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $data   = json_decode(file_get_contents($file), true) ?: [];

    // ── Create ────────────────────────────────────────────────────────────────
    if ($action === 'create') {
        $title       = trim($input['title']       ?? '');
        $date        = trim($input['date']        ?? '');
        $time        = trim($input['time']        ?? '');
        $createdBy   = trim($input['created_by']  ?? '');
        $description = trim($input['description'] ?? '');
        $bandMembers = $input['band_members']      ?? [];

        if (strlen($title) < 3 || empty($date) || empty($time) || empty($createdBy)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input. Please fill all fields properly.']);
            exit;
        }

        $data[] = [
            'id'           => uniqid('evt_'),
            'band_id'      => $bandId,
            'title'        => $title,
            'date'         => $date,
            'time'         => $time,
            'created_by'   => $createdBy,
            'description'  => $description,
            'band_members' => array_values((array)$bandMembers),
            'is_public'    => (bool)($input['is_public'] ?? false),
        ];

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Event saved successfully.']);
        exit;
    }

    // ── Update ────────────────────────────────────────────────────────────────
    if ($action === 'update') {
        $id          = $input['id']           ?? '';
        $title       = trim($input['title']   ?? '');
        $date        = trim($input['date']    ?? '');
        $time        = trim($input['time']    ?? '');
        $createdBy   = trim($input['created_by']  ?? '');
        $description = trim($input['description'] ?? '');
        $bandMembers = $input['band_members']      ?? [];

        if (empty($id) || strlen($title) < 3 || empty($date) || empty($time) || empty($createdBy)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input for update.']);
            exit;
        }

        $found = false;
        foreach ($data as &$event) {
            if ($event['id'] === $id) {
                // Only allow editing events belonging to this band
                if (($event['band_id'] ?? null) !== $bandId) {
                    echo json_encode(['success' => false, 'message' => 'Permission denied.']);
                    exit;
                }
                $event['title']        = $title;
                $event['date']         = $date;
                $event['time']         = $time;
                $event['created_by']   = $createdBy;
                $event['description']  = $description;
                $event['band_members'] = array_values((array)$bandMembers);
                $event['is_public']    = (bool)($input['is_public'] ?? false);
                $found = true;
                break;
            }
        }
        unset($event);

        if (!$found) {
            echo json_encode(['success' => false, 'message' => 'Event not found.']);
            exit;
        }

        file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Event updated successfully.']);
        exit;
    }

    // ── Delete ────────────────────────────────────────────────────────────────
    if ($action === 'delete') {
        $id = $input['id'] ?? '';
        $data = array_filter($data, fn($e) => !($e['id'] === $id && ($e['band_id'] ?? null) === $bandId));

        file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Event deleted.']);
        exit;
    }
}
?>
