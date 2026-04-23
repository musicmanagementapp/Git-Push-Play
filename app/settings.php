<?php
include 'includes/_login.php';
include 'includes/_pullinfo.php';

require_once __DIR__ . '/assets/libs/data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: artist-profile.php');
    exit;
}

$section  = $_POST['section'] ?? '';
$userId   = $gpUserId;
$musician = $gpMusician;
$band     = $gpBand;
$isOwner  = $gpIsOwner;

if ($section === 'account' && $gpUser) {
    $fields = [
        'email'    => trim($_POST['email']    ?? ''),
        'username' => trim($_POST['username'] ?? ''),
    ];

    $newPassword = $_POST['new_password']     ?? '';
    $confirmPw   = $_POST['confirm_password'] ?? '';

    if ($newPassword !== '') {
        if (strlen($newPassword) < 6) {
            $_SESSION['profile_error'] = 'New password must be at least 6 characters.';
            header('Location: artist-profile.php');
            exit;
        }
        if ($newPassword !== $confirmPw) {
            $_SESSION['profile_error'] = 'Passwords do not match.';
            header('Location: artist-profile.php');
            exit;
        }
        $fields['newPassword'] = $newPassword;
    }

    $result = update_user($userId, $fields);

    if ($result['success']) {
        $_SESSION['username']        = $fields['username'];
        $_SESSION['email']           = $fields['email'];
        $_SESSION['profile_success'] = $result['message'];
    } else {
        $_SESSION['profile_error'] = $result['message'];
    }

    header('Location: artist-profile.php');
    exit;
}

if ($section === 'band_create' && $musician && !$band) {
    $fields = [
        'name'       => trim($_POST['band_name']       ?? ''),
        'genre'      => trim($_POST['band_genre']      ?? ''),
        'formedYear' => trim($_POST['band_formedYear'] ?? ''),
    ];

    if ($fields['name'] === '') {
        $_SESSION['profile_error'] = 'Band name is required.';
    } else {
        $result = create_band($musician['id'], $fields);
        if ($result['success']) {
            $_SESSION['profile_success'] = $result['message'];
        } else {
            $_SESSION['profile_error'] = $result['message'];
        }
    }

    header('Location: artist-profile.php');
    exit;
}

if ($section === 'band_join' && $musician && !$band) {
    $code   = strtoupper(trim($_POST['join_code'] ?? ''));
    $result = join_band_by_code($code, $musician['id']);

    if ($result['success']) {
        $_SESSION['profile_success'] = $result['message'];
    } else {
        $_SESSION['profile_error'] = $result['message'];
    }

    header('Location: artist-profile.php');
    exit;
}

if ($section === 'band_edit' && $musician && $band && $isOwner) {
    $fields = [
        'name'       => trim($_POST['band_name']       ?? ''),
        'genre'      => trim($_POST['band_genre']      ?? ''),
        'formedYear' => trim($_POST['band_formedYear'] ?? ''),
    ];

    if ($fields['name'] === '') {
        $_SESSION['profile_error'] = 'Band name is required.';
    } else {
        $result = update_band($band['id'], $fields);
        if ($result['success']) {
            $_SESSION['profile_success'] = $result['message'];
        } else {
            $_SESSION['profile_error'] = $result['message'];
        }
    }

    header('Location: artist-profile.php');
    exit;
}

if ($section === 'band_remove' && $musician && $band && $isOwner) {
    $membershipId = $_POST['membership_id'] ?? '';
    $result       = remove_band_member($membershipId);

    if ($result['success']) {
        $_SESSION['profile_success'] = $result['message'];
    } else {
        $_SESSION['profile_error'] = $result['message'];
    }

    header('Location: artist-profile.php');
    exit;
}

if ($section === 'services' && $musician) {
    foreach (['spotify', 'youtube', 'soundcloud', 'bandcamp'] as $svcName) {
        $svcUserId = trim($_POST[$svcName . '_userId'] ?? '');
        $svcUrl    = trim($_POST[$svcName . '_url']    ?? '');
        if ($svcUserId !== '' || $svcUrl !== '') {
            upsert_linked_service($musician['id'], $svcName, [
                'serviceUserId' => $svcUserId,
                'profileUrl'    => $svcUrl,
            ]);
        }
    }

    $_SESSION['profile_success'] = 'Linked services saved.';
    header('Location: artist-profile.php');
    exit;
}

if ($section === 'disband' && $musician && $band && $isOwner) {
    $result = disband_band($band['id'], $musician['id']);

    if ($result['success']) {
        $_SESSION['profile_success'] = $result['message'];
    } else {
        $_SESSION['profile_error'] = $result['message'];
    }

    header('Location: artist-profile.php');
    exit;
}

header('Location: artist-profile.php');
exit;
