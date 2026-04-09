<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: artist-profile.php');
    exit;
}

$field = $_POST['field_name'] ?? '';
$value = trim($_POST['field_value'] ?? '');

$allowedFields = [
    'stage_name',
    'instrument',
    'genre',
    'band_name',
    'location',
    'bio'
];

if (!in_array($field, $allowedFields, true)) {
    $_SESSION['profile_error'] = "Invalid profile field.";
    header('Location: artist-profile.php');
    exit;
}

/*
Session save for now
can replace this with JSON save logic.
*/
$_SESSION[$field] = $value;

$_SESSION['profile_success'] = "Profile updated successfully.";
header('Location: artist-profile.php');
exit;