<?php
/**
 * _pullinfo.php
 * Include this file on any page that needs the logged-in user's data.
 * Requires a started session (from _login.php or session_start()).
 *
 * ============================================================
 * USAGE
 * ============================================================
 *
 * 1. Include at the top of your page (after _login.php):
 *
 *      <?php
 *      include 'includes/_pullinfo.php';
 *      ?>
 *
 * ============================================================
 * VARIABLES
 * ============================================================
 *
 * $gpUser  — the logged-in user record, or null if not found
 *
 *      $gpUser['id']        // "a1b2c3d4e5f6g7h8"
 *      $gpUser['username']  // "gitpushplay"
 *      $gpUser['email']     // "admin@gitpushplay.com"
 *      $gpUser['createdAt'] // "2025-01-15T10:30:00+00:00"
 *      $gpUser['lastLogin'] // "2025-03-20T08:00:00+00:00"
 *
 * ----------------------------------------------------------------
 *
 * $gpMusician  — the user's musician profile
 *
 *      $gpMusician['stageName'] // "GitPushPlay"
 *      $gpMusician['firstName'] // "Admin"
 *      $gpMusician['lastName']  // "Profile"
 *      $gpMusician['city']      // "Antarctica"
 *
 * ----------------------------------------------------------------
 *
 * $gpBand  — the user's current active band
 *
 *      $gpBand['name']       // "GitPushPlay"
 *      $gpBand['genre']      // "Rock"
 *      $gpBand['formedYear'] // "2025"
 *      $gpBand['joinCode']   // "GPP001"
 *
 * $gpMembership  — the user's membership in that band
 *
 *      $gpMembership['role']     // "owner" or "member"
 *      $gpMembership['joinedAt'] // "2025-02-01T12:00:00+00:00"
 *
 * $gpIsOwner  — true if the user is the band admin
 *
 *      if ($gpIsOwner) { // show edit controls }
 *
 * $gpBandMembers  — array of all active members, each with musician details
 *
 *      foreach ($gpBandMembers as $member) {
 *          echo $member['role'];                    // "owner" / "member"
 *          echo $member['musician']['stageName'];   // "GitPushPlay"
 *          echo $member['musician']['city'];        // "Antarctica"
 *      }
 *
 * ----------------------------------------------------------------
 *
 * $gpServices   — array of all linked streaming services
 * $gpServiceMap — same data, keyed by serviceName
 *
 *      // Check if a specific service is linked:
 *      if (!empty($gpServiceMap['spotify'])) {
 *          echo $gpServiceMap['spotify']['profileUrl'];    // "https://open.spotify.com/artist/gitpushplay"
 *          echo $gpServiceMap['spotify']['serviceUserId']; // "gitpushplay"
 *      }
 *
 *      // Loop all linked services:
 *      foreach ($gpServices as $svc) {
 *          echo $svc['serviceName']; // "spotify", "youtube", "soundcloud", "bandcamp"
 *          echo $svc['profileUrl'];
 *      }
 *
 * ============================================================
 * NULL SAFETY
 * ============================================================
 * $gpMusician, $gpBand, and $gpMembership can be null.
 * Always check before accessing them:
 *
 *      $stageName = $gpMusician['stageName'] ?? 'No stage name set';
 *      $bandName  = $gpBand['name']          ?? 'Not in a band';
 */

require_once __DIR__ . '/../assets/libs/auth.php';
require_once __DIR__ . '/../assets/libs/data.php';

// ---------------------------------------------------------------------------
// User
// ---------------------------------------------------------------------------

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

// ---------------------------------------------------------------------------
// Musician Profile
// ---------------------------------------------------------------------------

$gpMusician = $gpUserId ? find_musician_by_user_id($gpUserId) : null;

// ---------------------------------------------------------------------------
// Band + Membership
// ---------------------------------------------------------------------------

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

// ---------------------------------------------------------------------------
// Linked Services
// ---------------------------------------------------------------------------

$gpServices   = $gpMusician ? get_services_for_musician($gpMusician['id']) : [];
$gpServiceMap = [];
foreach ($gpServices as $gpSvc) {
    $gpServiceMap[$gpSvc['serviceName']] = $gpSvc;
}
unset($gpSvc);
