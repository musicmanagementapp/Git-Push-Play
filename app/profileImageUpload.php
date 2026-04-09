<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: artist-profile.php');
    exit;
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

/*
Session save for now
can replace this with JSON save logic.
*/
$_SESSION['profile_image'] = $webPath;

$_SESSION['profile_success'] = "Profile picture uploaded successfully.";
header('Location: artist-profile.php');
exit;