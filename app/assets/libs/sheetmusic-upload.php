<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/data.php';

$userId   = $_SESSION['user_id'] ?? '';
$bandId   = null;
$musician = null;

if ($userId !== '') {
    $musician = find_musician_by_user_id($userId);
    if ($musician) {
        $band   = get_active_band_for_musician($musician['id']);
        $bandId = $band['id'] ?? null;
    }
}

$sheetFile  = __DIR__ . '/../../secure/sheetmusic.json';
$uploadsDir = __DIR__ . '/../../uploads/';

if (!file_exists($sheetFile)) {
    file_put_contents($sheetFile, '[]');
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if ($bandId === null) { echo json_encode([]); exit; }
    $data = json_decode(file_get_contents($sheetFile), true) ?: [];
    $data = array_values(array_filter($data, fn($s) => ($s['band_id'] ?? null) === $bandId));
    echo json_encode($data);
    exit;
}

if ($method === 'POST') {
    $headers     = function_exists('apache_request_headers') ? apache_request_headers() : [];
    $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($headers['X-CSRF-Token'] ?? '');
    if (empty($clientToken)) $clientToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $clientToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security token validation failed.']);
        exit;
    }

    if ($bandId === null) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You must be in a band to manage sheet music.']);
        exit;
    }

    if (isset($_FILES['sheet'])) {
        $file = $_FILES['sheet'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $file['error']]);
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed.']);
            exit;
        }

        if ($file['size'] > 20 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum 20 MB.']);
            exit;
        }

        $bandDir = $uploadsDir . $bandId . '/sheets/';
        if (!is_dir($bandDir)) mkdir($bandDir, 0755, true);

        $sheetId  = bin2hex(random_bytes(8));
        $filename = $sheetId . '.pdf';
        $destPath = $bandDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            echo json_encode(['success' => false, 'message' => 'Could not save file to disk.']);
            exit;
        }

        $uploaderName = trim($musician['stageName'] ?? '');
        if ($uploaderName === '') {
            $uploaderName = trim(($musician['firstName'] ?? '') . ' ' . ($musician['lastName'] ?? ''));
        }

        $titleRaw = trim($_POST['title'] ?? '');
        if ($titleRaw === '') $titleRaw = pathinfo($file['name'], PATHINFO_FILENAME);

        $data   = json_decode(file_get_contents($sheetFile), true) ?: [];
        $data[] = [
            'id'            => $sheetId,
            'band_id'       => $bandId,
            'filename'      => $filename,
            'original_name' => $file['name'],
            'title'         => $titleRaw,
            'uploaded_by'   => $uploaderName,
            'created_at'    => date('c'),
        ];

        file_put_contents($sheetFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Sheet music uploaded!', 'id' => $sheetId]);
        exit;
    }

    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    if ($action === 'delete') {
        $id   = $input['id'] ?? '';
        $data = json_decode(file_get_contents($sheetFile), true) ?: [];

        foreach ($data as $sheet) {
            if ($sheet['id'] === $id && ($sheet['band_id'] ?? null) === $bandId) {
                $filePath = $uploadsDir . $bandId . '/sheets/' . $sheet['filename'];
                if (file_exists($filePath)) unlink($filePath);
                break;
            }
        }

        $data = array_values(array_filter(
            $data,
            fn($s) => !($s['id'] === $id && ($s['band_id'] ?? null) === $bandId)
        ));

        file_put_contents($sheetFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Sheet music deleted.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
?>
