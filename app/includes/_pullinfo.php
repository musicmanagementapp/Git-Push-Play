<?php

require_once __DIR__ . '/../assets/libs/auth.php';
require_once __DIR__ . '/../assets/libs/data.php';

$gpUserId = $_SESSION['user_id'] ?? '';

if ($gpUserId === '') {
    $gpFallback = find_user_by_username($_SESSION['username'] ?? '');
    if ($gpFallback) {
        $gpUserId = $gpFallback['id'] ?? '';
        $_SESSION['user_id'] = $gpUserId;
    }
    unset($gpFallback);
}

$gpUser = $gpUserId ? find_user_by_id($gpUserId) : null;

$gpMusician = $gpUserId ? find_musician_by_user_id($gpUserId) : null;

$gpBand        = null;
$gpMembership  = null;
$gpBandMembers = [];
$gpIsOwner     = false;

if ($gpMusician) {
    $gpBand = get_active_band_for_musician($gpMusician['id']);

    if ($gpBand) {
        $gpMembership  = get_membership_for_musician_in_band($gpMusician['id'], $gpBand['id']);
        $gpBandMembers = get_band_members($gpBand['id']);
        $gpIsOwner     = ($gpMembership['role'] ?? '') === 'owner';
    }
}

$gpServices   = $gpMusician ? get_services_for_musician($gpMusician['id']) : [];
$gpServiceMap = [];
foreach ($gpServices as $gpSvc) {
    $gpServiceMap[$gpSvc['serviceName']] = $gpSvc;
}
unset($gpSvc);
