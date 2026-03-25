<?php
session_start();
header('Content-Type: application/json');

// Generate a CSRF token 
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$file = __DIR__ . '/../../secure/events.json';

if (!file_exists($file)) {
    file_put_contents($file, '[]');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $data = json_decode(file_get_contents($file), true) ?: [];
    usort($data, function($a, $b) { return strtotime($a['date']) - strtotime($b['date']); });
    echo json_encode($data);
    exit;
}

if ($method === 'POST') {
    // CSRF Token Validation
    $headers = apache_request_headers();
    $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($headers['X-CSRF-Token'] ?? '');
    
    if (!hash_equals($_SESSION['csrf_token'], $clientToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security token validation failed.']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $data = json_decode(file_get_contents($file), true) ?: [];

    if ($action === 'create') {
        $title = trim($input['title'] ?? '');
        $date = trim($input['date'] ?? '');
        $createdBy = trim($input['created_by'] ?? '');

        if (strlen($title) < 3 || empty($date) || empty($createdBy)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input. Please fill all fields properly.']);
            exit;
        }

        $data[] = [
            'id' => uniqid('evt_'),
            'title' => htmlspecialchars($title), // Sanitize
            'date' => htmlspecialchars($date),
            'created_by' => htmlspecialchars($createdBy)
        ];
        
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Event saved successfully.']);
        exit;
    }

    if ($action === 'delete') {
        $id = $input['id'] ?? '';
        $data = array_filter($data, function($e) use ($id) { return $e['id'] !== $id; });
        
        file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Event deleted.']);
        exit;
    }
}
?>