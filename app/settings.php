<?php
include 'includes/_login.php';
include 'includes/_pullinfo.php';

$title       = 'Settings | GitPushPlay';
$description = 'Manage your account and musician profile settings.';

// Map _pullinfo.php variables to the names on this page
$userId      = $gpUserId;
$user        = $gpUser;
$musician    = $gpMusician;
$currentBand = $gpBand;
$bandMembers = $gpBandMembers;
$myMembership = $gpMembership;
$isOwner     = $gpIsOwner;
$services    = $gpServices;
$serviceMap  = $gpServiceMap;

$messages = [];

// -------------------------------------------------------------------------
// Handle POST
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';

    // -- Account Info --
    if ($section === 'account' && $user) {
        $fields = [
            'email'    => $_POST['email'] ?? '',
            'username' => $_POST['username'] ?? '',
        ];

        $newPassword = $_POST['new_password'] ?? '';
        $confirmPw   = $_POST['confirm_password'] ?? '';

        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                $messages['account'] = ['type' => 'error', 'text' => 'New password must be at least 6 characters.'];
                goto render;
            }
            if ($newPassword !== $confirmPw) {
                $messages['account'] = ['type' => 'error', 'text' => 'Passwords do not match.'];
                goto render;
            }
            $fields['newPassword'] = $newPassword;
        }

        $result = update_user($userId, $fields);
        $messages['account'] = ['type' => $result['success'] ? 'success' : 'error', 'text' => $result['message']];

        if ($result['success']) {
            $_SESSION['username'] = $fields['username'];
            $_SESSION['email']    = $fields['email'];
            $user = find_user_by_id($userId);
        }
    }

    // -- Musician Profile --
    if ($section === 'musician') {
        $fields = [
            'stageName' => $_POST['stageName'] ?? '',
            'firstName' => $_POST['firstName'] ?? '',
            'lastName'  => $_POST['lastName']  ?? '',
            'city'      => $_POST['city']       ?? '',
        ];

        if ($musician) {
            $result = update_musician($musician['id'], $fields);
        } else {
            $result = create_musician($userId, $fields);
        }

        $messages['musician'] = ['type' => $result['success'] ? 'success' : 'error', 'text' => $result['message']];

        if ($result['success']) {
            $musician = find_musician_by_user_id($userId);
        }
    }

    // -- Create Band --
    if ($section === 'band_create' && $musician && !$currentBand) {
        $fields = [
            'name'       => $_POST['band_name']       ?? '',
            'genre'      => $_POST['band_genre']      ?? '',
            'formedYear' => $_POST['band_formedYear']  ?? '',
        ];

        if (trim($fields['name']) === '') {
            $messages['band'] = ['type' => 'error', 'text' => 'Band name is required.'];
        } else {
            $result = create_band($musician['id'], $fields);
            $messages['band'] = ['type' => $result['success'] ? 'success' : 'error', 'text' => $result['message']];

            if ($result['success']) {
                $currentBand  = find_band_by_id($result['bandId']);
                $bandMembers  = get_band_members($currentBand['id']);
                $myMembership = get_membership_for_musician_in_band($musician['id'], $currentBand['id']);
                $isOwner      = true;
            }
        }
    }

    // -- Join Band --
    if ($section === 'band_join' && $musician && !$currentBand) {
        $code   = trim($_POST['join_code'] ?? '');
        $result = join_band_by_code($code, $musician['id']);
        $messages['band'] = ['type' => $result['success'] ? 'success' : 'error', 'text' => $result['message']];

        if ($result['success']) {
            $currentBand  = find_band_by_id($result['bandId']);
            $bandMembers  = get_band_members($currentBand['id']);
            $myMembership = get_membership_for_musician_in_band($musician['id'], $currentBand['id']);
            $isOwner      = false;
        }
    }

    // -- Edit Band --
    if ($section === 'band_edit' && $musician && $currentBand && $isOwner) {
        $fields = [
            'name'       => $_POST['band_name']      ?? '',
            'genre'      => $_POST['band_genre']     ?? '',
            'formedYear' => $_POST['band_formedYear'] ?? '',
        ];
        $result = update_band($currentBand['id'], $fields);
        $messages['band'] = ['type' => $result['success'] ? 'success' : 'error', 'text' => $result['message']];

        if ($result['success']) {
            $currentBand = find_band_by_id($currentBand['id']);
        }
    }

    // -- Remove Member --
    if ($section === 'band_remove' && $musician && $currentBand && $isOwner) {
        $membershipId = $_POST['membership_id'] ?? '';
        $result = remove_band_member($membershipId);
        $messages['band'] = ['type' => $result['success'] ? 'success' : 'error', 'text' => $result['message']];

        if ($result['success']) {
            $bandMembers = get_band_members($currentBand['id']);
        }
    }

    // -- Linked Services --
    if ($section === 'services' && $musician) {
        foreach (['spotify', 'youtube', 'soundcloud', 'bandcamp'] as $svcName) {
            $userId_field    = $_POST[$svcName . '_userId']  ?? '';
            $url_field       = $_POST[$svcName . '_url']     ?? '';
            if ($userId_field !== '' || $url_field !== '') {
                upsert_linked_service($musician['id'], $svcName, [
                    'serviceUserId' => $userId_field,
                    'profileUrl'    => $url_field,
                ]);
            }
        }
        $services   = get_services_for_musician($musician['id']);
        $serviceMap = [];
        foreach ($services as $svc) {
            $serviceMap[$svc['serviceName']] = $svc;
        }
        $messages['services'] = ['type' => 'success', 'text' => 'Linked services saved.'];
    }
}

render:
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/_header.php'; ?>

<main class="settings-page">
    <div class="settings-shell">
        <h1 class="settings-title">Settings</h1>

        <!-- =============================================================== -->
        <!-- ACCOUNT INFO                                                      -->
        <!-- =============================================================== -->
        <section class="settings-card">
            <h2>Account Info</h2>

            <?php if (isset($messages['account'])): ?>
                <div class="settings-message settings-message--<?= $messages['account']['type'] ?>">
                    <?= htmlspecialchars($messages['account']['text']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="settings.php">
                <input type="hidden" name="section" value="account">

                <div class="settings-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="<?= htmlspecialchars($user['username'] ?? $_SESSION['username'] ?? '') ?>"
                           required minlength="3">
                </div>

                <div class="settings-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($user['email'] ?? $_SESSION['email'] ?? '') ?>">
                </div>

                <fieldset class="settings-fieldset">
                    <legend>Change Password <span class="settings-optional">(leave blank to keep current)</span></legend>

                    <div class="settings-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" minlength="6">
                    </div>

                    <div class="settings-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </fieldset>

                <div class="settings-actions">
                    <button type="submit" class="primary-btn">Save Account Info</button>
                </div>
            </form>
        </section>

        <!-- =============================================================== -->
        <!-- MUSICIAN PROFILE                                                 -->
        <!-- =============================================================== -->
        <section class="settings-card">
            <h2>Musician Profile</h2>

            <?php if (isset($messages['musician'])): ?>
                <div class="settings-message settings-message--<?= $messages['musician']['type'] ?>">
                    <?= htmlspecialchars($messages['musician']['text']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="settings.php">
                <input type="hidden" name="section" value="musician">

                <div class="settings-group">
                    <label for="stageName">Stage Name</label>
                    <input type="text" id="stageName" name="stageName"
                           value="<?= htmlspecialchars($musician['stageName'] ?? '') ?>">
                </div>

                <div class="settings-row">
                    <div class="settings-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName"
                               value="<?= htmlspecialchars($musician['firstName'] ?? '') ?>">
                    </div>

                    <div class="settings-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName"
                               value="<?= htmlspecialchars($musician['lastName'] ?? '') ?>">
                    </div>
                </div>

                <div class="settings-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city"
                           value="<?= htmlspecialchars($musician['city'] ?? '') ?>">
                </div>

                <div class="settings-actions">
                    <button type="submit" class="primary-btn">Save Musician Profile</button>
                </div>
            </form>
        </section>

        <!-- =============================================================== -->
        <!-- BAND                                                             -->
        <!-- =============================================================== -->
        <section class="settings-card">
            <h2>Band</h2>

            <?php if (isset($messages['band'])): ?>
                <div class="settings-message settings-message--<?= $messages['band']['type'] ?>">
                    <?= htmlspecialchars($messages['band']['text']) ?>
                </div>
            <?php endif; ?>

            <?php if (!$musician): ?>
                <p class="settings-notice">Save a musician profile first to create or join a band.</p>

            <?php elseif (!$currentBand): ?>
                <div class="band-options">

                    <div class="band-option-card">
                        <h3>Create a Band</h3>
                        <p class="settings-notice" style="margin-bottom:16px;">Start a new band and invite others with a join code.</p>
                        <form method="post" action="settings.php">
                            <input type="hidden" name="section" value="band_create">
                            <div class="settings-group">
                                <label for="band_name">Band Name <span class="settings-optional">*</span></label>
                                <input type="text" id="band_name" name="band_name" required>
                            </div>
                            <div class="settings-group">
                                <label for="band_genre">Genre</label>
                                <input type="text" id="band_genre" name="band_genre">
                            </div>
                            <div class="settings-group">
                                <label for="band_formedYear">Year Formed</label>
                                <input type="number" id="band_formedYear" name="band_formedYear"
                                       min="1900" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
                            </div>
                            <div class="settings-actions">
                                <button type="submit" class="primary-btn">Create Band</button>
                            </div>
                        </form>
                    </div>

                    <div class="band-option-divider">or</div>

                    <div class="band-option-card">
                        <h3>Join a Band</h3>
                        <p class="settings-notice" style="margin-bottom:16px;">Enter the 6-character code from your band admin.</p>
                        <form method="post" action="settings.php">
                            <input type="hidden" name="section" value="band_join">
                            <div class="settings-group">
                                <label for="join_code">Band Join Code</label>
                                <input type="text" id="join_code" name="join_code"
                                       maxlength="6" style="text-transform:uppercase;letter-spacing:4px;font-size:18px;"
                                       placeholder="ABC123" required>
                            </div>
                            <div class="settings-actions">
                                <button type="submit" class="primary-btn">Join Band</button>
                            </div>
                        </form>
                    </div>

                </div>

            <?php else: ?>

                <!-- Band Info -->
                <?php if ($isOwner): ?>
                    <form method="post" action="settings.php">
                        <input type="hidden" name="section" value="band_edit">
                        <div class="band-header">
                            <div class="settings-group" style="flex:2;">
                                <label for="edit_band_name">Band Name</label>
                                <input type="text" id="edit_band_name" name="band_name"
                                       value="<?= htmlspecialchars($currentBand['name'] ?? '') ?>" required>
                            </div>
                            <div class="settings-group">
                                <label for="edit_band_genre">Genre</label>
                                <input type="text" id="edit_band_genre" name="band_genre"
                                       value="<?= htmlspecialchars($currentBand['genre'] ?? '') ?>">
                            </div>
                            <div class="settings-group">
                                <label for="edit_band_year">Year Formed</label>
                                <input type="number" id="edit_band_year" name="band_formedYear"
                                       min="1900" max="<?= date('Y') ?>"
                                       value="<?= htmlspecialchars($currentBand['formedYear'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="settings-actions" style="margin-bottom:20px;">
                            <button type="submit" class="primary-btn">Save Band Info</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="band-header">
                        <div class="band-info-row">
                            <span class="band-info-label">Band Name</span>
                            <span class="band-info-value"><?= htmlspecialchars($currentBand['name'] ?? '') ?></span>
                        </div>
                        <div class="band-info-row">
                            <span class="band-info-label">Genre</span>
                            <span class="band-info-value"><?= htmlspecialchars($currentBand['genre'] ?? '—') ?></span>
                        </div>
                        <div class="band-info-row">
                            <span class="band-info-label">Formed</span>
                            <span class="band-info-value"><?= htmlspecialchars($currentBand['formedYear'] ?? '—') ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Join Code -->
                <?php if ($isOwner): ?>
                    <div class="band-join-code-block">
                        <span class="band-join-code-label">Band Join Code</span>
                        <span class="band-join-code"><?= htmlspecialchars($currentBand['joinCode'] ?? '') ?></span>
                        <span class="band-join-code-hint">Share this with musicians you want to invite.</span>
                    </div>
                <?php endif; ?>

                <!-- Member List -->
                <div class="band-members-label">Members</div>
                <ul class="band-member-list">
                    <?php foreach ($bandMembers as $mem):
                        $mName = trim(
                            ($mem['musician']['stageName'] ?? '')
                            ?: (($mem['musician']['firstName'] ?? '') . ' ' . ($mem['musician']['lastName'] ?? ''))
                        ) ?: 'Unknown';
                        $mRole = $mem['role'] ?? 'member';
                        $isMe  = ($mem['musicianId'] ?? '') === ($musician['id'] ?? '');
                    ?>
                    <li class="band-member-row">
                        <div class="band-member-info">
                            <span class="band-member-name">
                                <?= htmlspecialchars($mName) ?>
                                <?php if ($isMe): ?><span class="band-member-you">(you)</span><?php endif; ?>
                            </span>
                            <span class="band-member-role band-member-role--<?= htmlspecialchars($mRole) ?>">
                                <?= htmlspecialchars(ucfirst($mRole)) ?>
                            </span>
                        </div>
                        <?php if ($isOwner && !$isMe): ?>
                            <form method="post" action="settings.php" style="margin:0;">
                                <input type="hidden" name="section" value="band_remove">
                                <input type="hidden" name="membership_id" value="<?= htmlspecialchars($mem['id']) ?>">
                                <button type="submit" class="band-remove-btn"
                                        onclick="return confirm('Remove <?= htmlspecialchars(addslashes($mName)) ?> from the band?')">
                                    Remove
                                </button>
                            </form>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

            <?php endif; ?>
        </section>

        <!-- =============================================================== -->
        <!-- LINKED SERVICES                                                  -->
        <!-- =============================================================== -->
        <section class="settings-card">
            <h2>Linked Services</h2>
            <p>These obviously don't do anything yet so and are just there until we decide on which one were using or if were going to use them at all</p>

            <?php if (!$musician): ?>
                <p class="settings-notice">Save a musician profile first to link streaming services.</p>
            <?php else: ?>

                <?php if (isset($messages['services'])): ?>
                    <div class="settings-message settings-message--<?= $messages['services']['type'] ?>">
                        <?= htmlspecialchars($messages['services']['text']) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="settings.php">
                    <input type="hidden" name="section" value="services">

                    <?php
                    $serviceLabels = [
                        'spotify'   => 'Spotify',
                        'youtube'   => 'YouTube',
                        'soundcloud'=> 'SoundCloud',
                        'bandcamp'  => 'Bandcamp',
                    ];
                    foreach ($serviceLabels as $key => $label):
                        $svc = $serviceMap[$key] ?? [];
                    ?>
                    <fieldset class="settings-fieldset">
                        <legend><?= $label ?></legend>
                        <div class="settings-row">
                            <div class="settings-group">
                                <label for="<?= $key ?>_userId"><?= $label ?> User / Artist ID</label>
                                <input type="text" id="<?= $key ?>_userId" name="<?= $key ?>_userId"
                                       value="<?= htmlspecialchars($svc['serviceUserId'] ?? '') ?>">
                            </div>
                            <div class="settings-group">
                                <label for="<?= $key ?>_url">Profile URL</label>
                                <input type="url" id="<?= $key ?>_url" name="<?= $key ?>_url"
                                       value="<?= htmlspecialchars($svc['profileUrl'] ?? '') ?>">
                            </div>
                        </div>
                    </fieldset>
                    <?php endforeach; ?>

                    <div class="settings-actions">
                        <button type="submit" class="primary-btn">Save Linked Services</button>
                    </div>
                </form>

            <?php endif; ?>
        </section>
        <section class="settings-card danger-zone">
         
            <div class="danger-row">
                <div class="danger-row-info">
                    <span class="danger-row-title">Log Out</span>
                    <span class="danger-row-desc">End your current session.</span>
                </div>
                <a href="logout.php" class="danger-btn danger-btn--soft">Log Out</a>
            </div>

            <div class="danger-row">
                <div class="danger-row-info">
                    <span class="danger-row-title">Delete Account</span>
                    <span class="danger-row-desc">Permanently remove your account and all associated data. This cannot be undone.</span>
                </div>
                <a href="delete_account.php" class="danger-btn danger-btn--hard">Delete Account</a>
            </div>
        </section>

    </div>
</main>

<?php include 'includes/_footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
