<?php
session_start();

require_once __DIR__ . '/includes/_pullinfo.php';
require_once __DIR__ . '/assets/libs/data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: artist-profile.php');
    exit;
}

if (!$gpUser || empty($gpUser['id'])) {
    $_SESSION['profile_error'] = "User not found.";
    header('Location: artist-profile.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Auto-create musician profile if missing
|--------------------------------------------------------------------------
*/
if (!$gpMusician || empty($gpMusician['id'])) {
    $createResult = create_musician($gpUser['id'], []);
    if (!$createResult['success']) {
        $_SESSION['profile_error'] = $createResult['message'] ?? 'Could not create musician profile.';
        header('Location: artist-profile.php');
        exit;
    }

    $gpMusician = find_musician_by_user_id($gpUser['id']);
    if (!$gpMusician || empty($gpMusician['id'])) {
        $_SESSION['profile_error'] = "Musician profile could not be loaded.";
        header('Location: artist-profile.php');
        exit;
    }
}

if (!isset($_FILES['profile_image_file']) || $_FILES['profile_image_file']['error'] === UPLOAD_ERR_NO_FILE) {
    $_SESSION['profile_error'] = "Please choose an image to upload.";
    header('Location: artist-profile.php');
    exit;
}

$file = $_FILES['profile_image_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['profile_error'] = "There was an error uploading your image.";
    header('Location: artist-profile.php');
    exit;
}

$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$maxSize = 3 * 1024 * 1024;

$detectedMimeType = mime_content_type($file['tmp_name']);
if (!in_array($detectedMimeType, $allowedMimeTypes, true)) {
    $_SESSION['profile_error'] = "Only JPG, PNG, WEBP, and GIF files are allowed.";
    header('Location: artist-profile.php');
    exit;
}

if ($file['size'] > $maxSize) {
    $_SESSION['profile_error'] = "Profile image must be 3 MB or smaller.";
    header('Location: artist-profile.php');
    exit;
}

function safeFileName($fileName) {
    $fileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
    return $fileName ?: 'profile_image';
}

$uploadDir = __DIR__ . '/assets/images/profile/';
$webPathDir = 'assets/images/profile/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$baseName = safeFileName(pathinfo($file['name'], PATHINFO_FILENAME));
$uniqueName = $baseName . '_' . time() . '.' . $extension;

$destination = $uploadDir . $uniqueName;
$webPath = $webPathDir . $uniqueName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    $_SESSION['profile_error'] = "Unable to upload the image. Please try again.";
    header('Location: artist-profile.php');
    exit;
}

$result = update_musician($gpMusician['id'], [
    'profileImage' => $webPath
]);

if (!$result['success']) {
    $_SESSION['profile_error'] = $result['message'] ?? "Could not save profile image.";
    header('Location: artist-profile.php');
    exit;
}

$_SESSION['profile_success'] = "Profile picture uploaded successfully.";
header('Location: artist-profile.php');
exit;