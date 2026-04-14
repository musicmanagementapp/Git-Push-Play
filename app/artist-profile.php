<?php
include 'includes/_login.php';
include 'includes/_pullinfo.php';

$stageName = $gpMusician['stageName'] ?? '';
$title = trim($stageName) !== '' ? $stageName . ' | Profile' : 'Artist Profile';
$description = "Artist profile management dashboard for GitPushPlay";

$instrument   = $gpMusician['instrument'] ?? '';
$genre        = $gpBand['genre'] ?? '';
$bandName     = $gpBand['name'] ?? '';
$location     = $gpMusician['city'] ?? '';
$bio          = $gpMusician['bio'] ?? '';
$profileImage = $gpMusician['profileImage'] ?? '';

$successMessage = $_SESSION['profile_success'] ?? '';
$errorMessage   = $_SESSION['profile_error'] ?? '';

unset($_SESSION['profile_success'], $_SESSION['profile_error']);

function displayValue($value, $placeholder) {
    return trim($value) !== '' ? $value : $placeholder;
}

$hasProfileImage = !empty($profileImage) && file_exists(__DIR__ . '/' . $profileImage);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/artist-page.css">
</head>
<body>

<?php include 'includes/_header.php'; ?>

<main class="profile-page">

    <?php if ($successMessage !== ''): ?>
        <div class="profile-toast-container">
            <div class="profile-toast profile-toast-success" id="success-toast">
                <span><?= htmlspecialchars($successMessage) ?></span>
                <button type="button" class="profile-toast-close" onclick="closeToast('success-toast')" aria-label="Close message">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="profile-toast-container">
            <div class="profile-toast profile-toast-error" id="error-toast">
                <span><?= htmlspecialchars($errorMessage) ?></span>
                <button type="button" class="profile-toast-close" onclick="closeToast('error-toast')" aria-label="Close message">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <section class="settings-slider-shell">
        <h2 class="settings-slider-title">
            <?= htmlspecialchars(trim($stageName) !== '' ? $stageName : 'Your Profile') ?>
        </h2>

        <div class="settings-slider-wrapper">
            <div class="settings-slider-track">
                <div class="settings-slider-inner" id="settingsSliderInner">


                    <div class="settings-slide">
                        <div class="settings-slide-card">

                            <div class="profile-header-card">

                                <div class="profile-image-section">
                                    <form class="profile-upload-form" action="/assets/libs/profileImageUpload.php" method="post" enctype="multipart/form-data">
                                        <div class="profile-avatar-wrap profile-avatar-interactive" onclick="triggerPhotoPicker()">
                                            <?php if ($hasProfileImage): ?>
                                                <img
                                                    src="<?= htmlspecialchars($profileImage) ?>"
                                                    alt="Profile Picture"
                                                    id="profileImagePreview"
                                                >
                                                <div class="profile-photo-overlay">
                                                    <span>Change Photo</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="profile-avatar-placeholder" id="profileAvatarPlaceholder">🎵</div>
                                                <img
                                                    src=""
                                                    alt="Profile Picture Preview"
                                                    id="profileImagePreview"
                                                    style="display:none;"
                                                >
                                                <div class="profile-photo-overlay">
                                                    <span>Add Photo</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <input
                                            type="file"
                                            id="profile_image_file"
                                            name="profile_image_file"
                                            accept=".jpg,.jpeg,.png,.webp,.gif"
                                            style="display:none;"
                                            onchange="handleFileSelect(this)"
                                        >

                                        <div id="photoActionArea" class="photo-action-area">
                                            <button
                                                type="button"
                                                class="primary-btn photo-action-btn"
                                                onclick="triggerPhotoPicker()"
                                                id="uploadBtn"
                                            >
                                                <?= $hasProfileImage ? 'Change Photo' : 'Add Photo' ?>
                                            </button>

                                            <button
                                                type="submit"
                                                class="secondary-btn-inline photo-action-btn"
                                                id="savePhotoBtn"
                                            >
                                                Save Photo
                                            </button>
                                        </div>

                                        <div id="fileNamePreview" class="photo-file-name"></div>
                                    </form>
                                </div>

                                <div class="profile-main-details">
                                    <div class="profile-inline-list">

                                        <?php
                                        $fields = [
                                            'stage_name' => ['label' => 'Stage Name', 'value' => $stageName, 'placeholder' => 'Enter stage name', 'type' => 'text'],
                                            'instrument' => ['label' => 'Instrument', 'value' => $instrument, 'placeholder' => 'Enter instrument', 'type' => 'text'],
                                            'band_name'  => ['label' => 'Band', 'value' => $bandName, 'placeholder' => 'Join or create a band first', 'type' => 'text'],
                                            'genre'      => ['label' => 'Genre', 'value' => $genre, 'placeholder' => 'Join or create a band first', 'type' => 'text'],
                                            'location'   => ['label' => 'Location', 'value' => $location, 'placeholder' => 'Enter location', 'type' => 'text'],
                                        ];

                                        foreach ($fields as $fieldName => $fieldData):
                                            $isEmpty = trim($fieldData['value']) === '';
                                        ?>
                                            <div class="profile-inline-row" onclick="toggleEdit('<?= htmlspecialchars($fieldName) ?>')">
                                                <div class="profile-inline-left">
                                                    <span class="profile-inline-label"><?= htmlspecialchars($fieldData['label']) ?></span>

                                                    <div class="profile-inline-value <?= $isEmpty ? 'profile-placeholder' : '' ?>">
                                                        <?= htmlspecialchars(displayValue($fieldData['value'], $fieldData['placeholder'])) ?>
                                                    </div>

                                                    <form class="profile-edit-form"
                                                          id="form-<?= htmlspecialchars($fieldName) ?>"
                                                          action="assets/libs/profileFieldUpdate.php"
                                                          method="post"
                                                          onclick="event.stopPropagation();">
                                                        <input type="hidden" name="field_name" value="<?= htmlspecialchars($fieldName) ?>">
                                                        <input
                                                            type="<?= htmlspecialchars($fieldData['type']) ?>"
                                                            name="field_value"
                                                            value="<?= htmlspecialchars($fieldData['value']) ?>"
                                                            placeholder="<?= htmlspecialchars($fieldData['placeholder']) ?>"
                                                        >
                                                        <div class="edit-form-actions">
                                                            <button type="submit" class="primary-btn">Save</button>
                                                            <button type="button" class="secondary-btn-inline" onclick="cancelEdit('<?= htmlspecialchars($fieldName) ?>')">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="profile-inline-row" onclick="toggleEdit('bio')">
                                            <div class="profile-inline-left">
                                                <span class="profile-inline-label">Bio</span>

                                                <div class="profile-inline-value <?= trim($bio) === '' ? 'profile-placeholder' : '' ?>">
                                                    <?= nl2br(htmlspecialchars(displayValue($bio, "Enter bio"))) ?>
                                                </div>

                                                <form class="profile-edit-form"
                                                      id="form-bio"
                                                      action="assets/libs/profileFieldUpdate.php"
                                                      method="post"
                                                      onclick="event.stopPropagation();">
                                                    <input type="hidden" name="field_name" value="bio">
                                                    <textarea name="field_value" rows="5" placeholder="Enter bio"><?= htmlspecialchars($bio) ?></textarea>
                                                    <div class="edit-form-actions">
                                                        <button type="submit" class="primary-btn">Save</button>
                                                        <button type="button" class="secondary-btn-inline" onclick="cancelEdit('bio')">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="settings-slide">
                        <div class="settings-slide-card">
                            <h2>Account Info</h2>

                            <?php if (isset($messages['account'])): ?>
                                <div class="sld-msg sld-msg--<?= $messages['account']['type'] ?>">
                                    <?= htmlspecialchars($messages['account']['text']) ?>
                                </div>
                            <?php endif; ?>

                            <form method="post" action="settings.php">
                                <input type="hidden" name="section" value="account">

                                <div class="sld-group">
                                    <label for="sld_username">Username</label>
                                    <input type="text" id="sld_username" name="username"
                                           value="<?= htmlspecialchars($gpUser['username'] ?? $_SESSION['username'] ?? '') ?>"
                                           required minlength="3">
                                </div>

                                <div class="sld-group">
                                    <label for="sld_email">Email</label>
                                    <input type="email" id="sld_email" name="email"
                                           value="<?= htmlspecialchars($gpUser['email'] ?? $_SESSION['email'] ?? '') ?>">
                                </div>

                                <fieldset class="sld-fieldset">
                                    <legend>Change Password <span class="sld-optional">(leave blank to keep current)</span></legend>

                                    <div class="sld-group">
                                        <label for="sld_new_password">New Password</label>
                                        <input type="password" id="sld_new_password" name="new_password" minlength="6">
                                    </div>

                                    <div class="sld-group">
                                        <label for="sld_confirm_password">Confirm New Password</label>
                                        <input type="password" id="sld_confirm_password" name="confirm_password">
                                    </div>
                                </fieldset>

                                <div class="sld-actions">
                                    <button type="submit" class="primary-btn">Save Account Info</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="settings-slide">
                        <div class="settings-slide-card">
                            <h2>Band</h2>

                            <?php if (isset($messages['band'])): ?>
                                <div class="sld-msg sld-msg--<?= $messages['band']['type'] ?>">
                                    <?= htmlspecialchars($messages['band']['text']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!$gpMusician): ?>
                                <p class="sld-notice">Save a musician profile first to create or join a band.</p>

                            <?php elseif (!$gpBand): ?>
                                <div class="sld-band-options">
                                    <div class="sld-band-option-card">
                                        <h3>Create a Band</h3>
                                        <p class="sld-notice">Start a new band and invite others with a join code.</p>
                                        <form method="post" action="settings.php">
                                            <input type="hidden" name="section" value="band_create">
                                            <div class="sld-group">
                                                <label for="sld_band_name">Band Name <span class="sld-optional">*</span></label>
                                                <input type="text" id="sld_band_name" name="band_name" required>
                                            </div>
                                            <div class="sld-group">
                                                <label for="sld_band_genre">Genre</label>
                                                <input type="text" id="sld_band_genre" name="band_genre">
                                            </div>
                                            <div class="sld-group">
                                                <label for="sld_band_year">Year Formed</label>
                                                <input type="number" id="sld_band_year" name="band_formedYear"
                                                       min="1900" max="<?= date('Y') ?>" placeholder="<?= date('Y') ?>">
                                            </div>
                                            <div class="sld-actions">
                                                <button type="submit" class="primary-btn">Create Band</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="sld-band-divider">or</div>

                                    <div class="sld-band-option-card">
                                        <h3>Join a Band</h3>
                                        <p class="sld-notice">Enter the 6-character code from your band admin.</p>
                                        <form method="post" action="settings.php">
                                            <input type="hidden" name="section" value="band_join">
                                            <div class="sld-group">
                                                <label for="sld_join_code">Band Join Code</label>
                                                <input type="text" id="sld_join_code" name="join_code"
                                                       maxlength="6" style="text-transform:uppercase;letter-spacing:4px;font-size:18px;"
                                                       placeholder="ABC123" required>
                                            </div>
                                            <div class="sld-actions">
                                                <button type="submit" class="primary-btn">Join Band</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            <?php else: ?>

                                <?php if ($gpIsOwner): ?>
                                    <form method="post" action="settings.php">
                                        <input type="hidden" name="section" value="band_edit">
                                        <div class="sld-band-header">
                                            <div class="sld-group">
                                                <label for="sld_edit_band_name">Band Name</label>
                                                <input type="text" id="sld_edit_band_name" name="band_name"
                                                       value="<?= htmlspecialchars($gpBand['name'] ?? '') ?>" required>
                                            </div>
                                            <div class="sld-group">
                                                <label for="sld_edit_band_genre">Genre</label>
                                                <input type="text" id="sld_edit_band_genre" name="band_genre"
                                                       value="<?= htmlspecialchars($gpBand['genre'] ?? '') ?>">
                                            </div>
                                            <div class="sld-group">
                                                <label for="sld_edit_band_year">Year Formed</label>
                                                <input type="number" id="sld_edit_band_year" name="band_formedYear"
                                                       min="1900" max="<?= date('Y') ?>"
                                                       value="<?= htmlspecialchars($gpBand['formedYear'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="sld-actions" style="margin-bottom:14px;">
                                            <button type="submit" class="primary-btn">Save Band Info</button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="sld-band-header">
                                        <div class="sld-band-info-row">
                                            <span class="sld-band-info-label">Band Name</span>
                                            <span class="sld-band-info-value"><?= htmlspecialchars($gpBand['name'] ?? '') ?></span>
                                        </div>
                                        <div class="sld-band-info-row">
                                            <span class="sld-band-info-label">Genre</span>
                                            <span class="sld-band-info-value"><?= htmlspecialchars($gpBand['genre'] ?? '—') ?></span>
                                        </div>
                                        <div class="sld-band-info-row">
                                            <span class="sld-band-info-label">Formed</span>
                                            <span class="sld-band-info-value"><?= htmlspecialchars($gpBand['formedYear'] ?? '—') ?></span>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($gpIsOwner): ?>
                                    <div class="sld-join-code-block">
                                        <span class="sld-join-code-label">Band Join Code</span>
                                        <span class="sld-join-code"><?= htmlspecialchars($gpBand['joinCode'] ?? '') ?></span>
                                        <span class="sld-join-code-hint">Share this with musicians you want to invite.</span>
                                    </div>
                                <?php endif; ?>

                                <div class="sld-members-label">Members</div>
                                <ul class="sld-member-list">
                                    <?php foreach ($gpBandMembers as $mem):
                                        $mName = trim(
                                            ($mem['musician']['stageName'] ?? '')
                                            ?: (($mem['musician']['firstName'] ?? '') . ' ' . ($mem['musician']['lastName'] ?? ''))
                                        ) ?: 'Unknown';
                                        $mRole = $mem['role'] ?? 'member';
                                        $isMe  = ($mem['musicianId'] ?? '') === ($gpMusician['id'] ?? '');
                                    ?>
                                    <li class="sld-member-row">
                                        <div class="sld-member-info">
                                            <span class="sld-member-name">
                                                <?= htmlspecialchars($mName) ?>
                                                <?php if ($isMe): ?><span class="sld-member-you">(you)</span><?php endif; ?>
                                            </span>
                                            <span class="sld-member-role"><?= htmlspecialchars(ucfirst($mRole)) ?></span>
                                        </div>
                                        <?php if ($gpIsOwner && !$isMe): ?>
                                            <form method="post" action="settings.php" style="margin:0;">
                                                <input type="hidden" name="section" value="band_remove">
                                                <input type="hidden" name="membership_id" value="<?= htmlspecialchars($mem['id']) ?>">
                                                <button type="submit" class="sld-remove-btn"
                                                        onclick="return confirm('Remove <?= htmlspecialchars(addslashes($mName)) ?> from the band?')">
                                                    Remove
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>

                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="settings-slide">
                        <div class="settings-slide-card">
                            <h2>Linked Services</h2>

                            <?php if (!$gpMusician): ?>
                                <p class="sld-notice">Save a musician profile first to link streaming services.</p>
                            <?php else: ?>

                                <?php if (isset($messages['services'])): ?>
                                    <div class="sld-msg sld-msg--<?= $messages['services']['type'] ?>">
                                        <?= htmlspecialchars($messages['services']['text']) ?>
                                    </div>
                                <?php endif; ?>

                                <form method="post" action="settings.php">
                                    <input type="hidden" name="section" value="services">

                                    <?php
                                    $sldServiceLabels = [
                                        'spotify'    => 'Spotify',
                                        'youtube'    => 'YouTube',
                                        'soundcloud' => 'SoundCloud',
                                        'bandcamp'   => 'Bandcamp',
                                    ];
                                    foreach ($sldServiceLabels as $key => $label):
                                        $svc = $gpServiceMap[$key] ?? [];
                                    ?>
                                    <fieldset class="sld-fieldset">
                                        <legend><?= $label ?></legend>
                                        <div class="sld-row">
                                            <div class="sld-group">
                                                <label for="sld_<?= $key ?>_userId"><?= $label ?> Artist ID</label>
                                                <input type="text" id="sld_<?= $key ?>_userId" name="<?= $key ?>_userId"
                                                       value="<?= htmlspecialchars($svc['serviceUserId'] ?? '') ?>">
                                            </div>
                                            <div class="sld-group">
                                                <label for="sld_<?= $key ?>_url">Profile URL</label>
                                                <input type="url" id="sld_<?= $key ?>_url" name="<?= $key ?>_url"
                                                       value="<?= htmlspecialchars($svc['profileUrl'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </fieldset>
                                    <?php endforeach; ?>

                                    <div class="sld-actions">
                                        <button type="submit" class="primary-btn">Save Linked Services</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="settings-slide">
                        <div class="settings-slide-card">
                            <h2>Account Actions</h2>

                            <div class="sld-danger-row">
                                <div class="sld-danger-row-info">
                                    <span class="sld-danger-row-title">Log Out</span>
                                    <span class="sld-danger-row-desc">End your current session.</span>
                                </div>
                                <a href="logout.php" class="sld-danger-btn-hard">Log Out</a>
                            </div>

                            <div class="sld-danger-row">
                                <div class="sld-danger-row-info">
                                    <span class="sld-danger-row-title">Delete Account</span>
                                    <span class="sld-danger-row-desc">Permanently remove your account and all associated data. This cannot be undone.</span>
                                </div>
                                <a href="delete_account.php" class="sld-danger-btn-hard">Delete Account</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="settings-slider-nav">
            <button class="slider-arrow" id="sliderPrev" aria-label="Previous">&#8249;</button>
            <div class="slider-dots" id="sliderDots">
                <button class="slider-dot active" data-index="0" aria-label="Musician Profile"></button>
                <button class="slider-dot" data-index="1" aria-label="Account Info"></button>
                <button class="slider-dot" data-index="2" aria-label="Band"></button>
                <button class="slider-dot" data-index="3" aria-label="Linked Services"></button>
                <button class="slider-dot" data-index="4" aria-label="Account Actions"></button>
            </div>
            <button class="slider-arrow" id="sliderNext" aria-label="Next">&#8250;</button>
        </div>
    </section>

</main>

<script src="assets/js/main.js"></script>

<script>
    function closeAllEditForms() {
        const forms = document.querySelectorAll('.profile-edit-form');
        forms.forEach(form => form.classList.remove('active'));
    }

    function toggleEdit(fieldName) {
        const form = document.getElementById('form-' + fieldName);
        if (!form) return;

        const isActive = form.classList.contains('active');
        closeAllEditForms();

        if (!isActive) {
            form.classList.add('active');
            const input = form.querySelector('input[type="text"], textarea');
            if (input) {
                input.focus();
                if (input.select && input.tagName !== 'TEXTAREA') {
                    input.select();
                }
            }
        }
    }

    function cancelEdit(fieldName) {
        const form = document.getElementById('form-' + fieldName);
        if (form) {
            form.classList.remove('active');
        }
    }

    function closeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (!toast) return;

        toast.classList.add('profile-toast-hide');

        setTimeout(() => {
            const container = toast.closest('.profile-toast-container');
            if (container) {
                container.remove();
            } else {
                toast.remove();
            }
        }, 450);
    }

    function triggerPhotoPicker() {
        const input = document.getElementById('profile_image_file');
        if (input) {
            input.click();
        }
    }

    function handleFileSelect(input) {
        const file = input.files[0];
        const preview = document.getElementById('fileNamePreview');
        const actionArea = document.getElementById('photoActionArea');
        const uploadBtn = document.getElementById('uploadBtn');
        const imagePreview = document.getElementById('profileImagePreview');
        const placeholder = document.getElementById('profileAvatarPlaceholder');

        if (file) {
            preview.textContent = file.name;
            actionArea.classList.add('active');
            uploadBtn.textContent = "Choose Different Photo";

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    if (imagePreview) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }

                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                };

                reader.readAsDataURL(file);
            }
        }
    }

    setTimeout(() => {
        closeToast('success-toast');
        closeToast('error-toast');
    }, 3000);

    // ── Settings Slider ──
    (function () {
        const inner  = document.getElementById('settingsSliderInner');
        const dots   = document.querySelectorAll('.slider-dot');
        const prev   = document.getElementById('sliderPrev');
        const next   = document.getElementById('sliderNext');
        const total  = dots.length;
        let current  = 0;

        function goTo(index) {
            current = (index + total) % total;
            inner.style.transform = `translateX(-${current * 100}%)`;
            dots.forEach((d, i) => d.classList.toggle('active', i === current));
        }

        prev.addEventListener('click', () => goTo(current - 1));
        next.addEventListener('click', () => goTo(current + 1));
        dots.forEach(dot => dot.addEventListener('click', () => goTo(Number(dot.dataset.index))));

        // Touch / swipe support
        let touchStartX = 0;
        inner.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
        inner.addEventListener('touchend', e => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) goTo(diff > 0 ? current + 1 : current - 1);
        }, { passive: true });
    })();
</script>

</body>
</html>