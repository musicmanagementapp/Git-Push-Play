<?php
session_start();

require_once __DIR__ . '/includes/_pullinfo.php';
require_once __DIR__ . '/assets/libs/data.php';

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
|--------------------------------------------------------------------------
| Auto-create musician profile if missing
|--------------------------------------------------------------------------
*/
if (!$gpUser || empty($gpUser['id'])) {
    $_SESSION['profile_error'] = "User not found.";
    header('Location: artist-profile.php');
    exit;
}

if (!$gpMusician || empty($gpMusician['id'])) {
    $createResult = create_musician($gpUser['id'], []);
    if (!$createResult['success']) {
        $_SESSION['profile_error'] = $createResult['message'] ?? 'Could not create musician profile.';
        header('Location: artist-profile.php');
        exit;
    }

    // Reload musician after creation
    $gpMusician = find_musician_by_user_id($gpUser['id']);
    if (!$gpMusician || empty($gpMusician['id'])) {
        $_SESSION['profile_error'] = "Musician profile could not be loaded.";
        header('Location: artist-profile.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Musician fields
|--------------------------------------------------------------------------
*/
if (in_array($field, ['stage_name', 'location', 'instrument', 'bio'], true)) {
    $musicianFieldMap = [
        'stage_name' => 'stageName',
        'location'   => 'city',
        'instrument' => 'instrument',
        'bio'        => 'bio',
    ];

    $jsonField = $musicianFieldMap[$field];

    $result = update_musician($gpMusician['id'], [
        $jsonField => $value
    ]);

    if (!$result['success']) {
        $_SESSION['profile_error'] = $result['message'] ?? 'Could not update musician profile.';
        header('Location: artist-profile.php');
        exit;
    }

    $_SESSION['profile_success'] = "Profile updated successfully.";
    header('Location: artist-profile.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Band fields
|--------------------------------------------------------------------------
*/
if (in_array($field, ['band_name', 'genre'], true)) {
    if (!$gpBand || empty($gpBand['id'])) {
        $_SESSION['profile_error'] = "Active band not found.";
        header('Location: artist-profile.php');
        exit;
    }

    if (!$gpIsOwner) {
        $_SESSION['profile_error'] = "Only the band owner can update band information.";
        header('Location: artist-profile.php');
        exit;
    }

    $bandFieldMap = [
        'band_name' => 'name',
        'genre'     => 'genre',
    ];

    $jsonField = $bandFieldMap[$field];

    $result = update_band($gpBand['id'], [
        $jsonField => $value
    ]);

    if (!$result['success']) {
        $_SESSION['profile_error'] = $result['message'] ?? 'Could not update band.';
        header('Location: artist-profile.php');
        exit;
    }

    $_SESSION['profile_success'] = "Profile updated successfully.";
    header('Location: artist-profile.php');
    exit;
}

$_SESSION['profile_error'] = "Unable to update profile.";
header('Location: artist-profile.php');
exit;