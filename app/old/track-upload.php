<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/data.php';

$userId = $_SESSION['user_id'] ?? '';
$bandId = null;
$musician = null;

if ($userId !== '') {
    $musician = find_musician_by_user_id($userId);
    if ($musician) {
        $band   = get_active_band_for_musician($musician['id']);
        $bandId = $band['id'] ?? null;
    }
}

$tracksFile = __DIR__ . '/../../secure/tracks.json';
$uploadsDir = __DIR__ . '/../../uploads/';

if (!file_exists($tracksFile)) {
    file_put_contents($tracksFile, '[]');
}

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: return this band's tracks ───────────────────────────────────────────
if ($method === 'GET') {
    if ($bandId === null) {
        echo json_encode([]);
        exit;
    }
    $data = json_decode(file_get_contents($tracksFile), true) ?: [];
    $data = array_values(array_filter($data, fn($t) => ($t['band_id'] ?? null) === $bandId));
    echo json_encode($data);
    exit;
}

// ── POST ─────────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    // CSRF — check header first, then POST field (for multipart uploads)
    $headers     = function_exists('apache_request_headers') ? apache_request_headers() : [];
    $clientToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($headers['X-CSRF-Token'] ?? '');
    if (empty($clientToken)) {
        $clientToken = $_POST['csrf_token'] ?? '';
    }

    if (!hash_equals($_SESSION['csrf_token'], $clientToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Security token validation failed.']);
        exit;
    }

    if ($bandId === null) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You must be in a band to manage tracks.']);
        exit;
    }

    // ── Upload ────────────────────────────────────────────────────────────────
    if (isset($_FILES['track'])) {
        $file = $_FILES['track'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Upload error code: ' . $file['error']]);
            exit;
        }

        $allowedExts = ['mp3', 'wav', 'ogg', 'flac', 'm4a', 'aac'];
        $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExts)) {
            echo json_encode(['success' => false, 'message' => 'Only audio files are allowed (mp3, wav, ogg, flac, m4a, aac).']);
            exit;
        }

        if ($file['size'] > 50 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'File too large. Maximum 50 MB.']);
            exit;
        }

        $bandDir = $uploadsDir . $bandId . '/';
        if (!is_dir($bandDir)) {
            mkdir($bandDir, 0755, true);
        }

        $trackId  = bin2hex(random_bytes(8));
        $filename = $trackId . '.' . $ext;
        $destPath = $bandDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            echo json_encode(['success' => false, 'message' => 'Could not save file to disk.']);
            exit;
        }

        $uploaderName = '';
        if ($musician) {
            $uploaderName = trim($musician['stageName'] ?? '');
            if ($uploaderName === '') {
                $uploaderName = trim(($musician['firstName'] ?? '') . ' ' . ($musician['lastName'] ?? ''));
            }
        }

        $titleRaw = trim($_POST['title'] ?? '');
        if ($titleRaw === '') {
            $titleRaw = pathinfo($file['name'], PATHINFO_FILENAME);
        }

        $data = json_decode(file_get_contents($tracksFile), true) ?: [];
        $data[] = [
            'id'            => $trackId,
            'band_id'       => $bandId,
            'filename'      => $filename,
            'original_name' => $file['name'],
            'title'         => $titleRaw,
            'uploaded_by'   => $uploaderName,
            'created_at'    => date('c'),
        ];

        file_put_contents($tracksFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Track uploaded!', 'id' => $trackId]);
        exit;
    }

    // ── Delete (JSON body) ────────────────────────────────────────────────────
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $input['action'] ?? '';

    if ($action === 'delete') {
        $id   = $input['id'] ?? '';
        $data = json_decode(file_get_contents($tracksFile), true) ?: [];

        foreach ($data as $track) {
            if ($track['id'] === $id && ($track['band_id'] ?? null) === $bandId) {
                $filePath = $uploadsDir . $bandId . '/' . $track['filename'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                break;
            }
        }

        $data = array_values(array_filter(
            $data,
            fn($t) => !($t['id'] === $id && ($t['band_id'] ?? null) === $bandId)
        ));

        file_put_contents($tracksFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        echo json_encode(['success' => true, 'message' => 'Track deleted.']);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
?>
