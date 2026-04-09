<?php
session_start();

$stageName    = $_SESSION['stage_name'] ?? '';
$title        = trim($stageName) !== '' ? $stageName . ' | Profile' : 'Artist Profile';
$description  = "Artist profile management dashboard for GitPushPlay";

$instrument   = $_SESSION['instrument'] ?? '';
$genre        = $_SESSION['genre'] ?? '';
$bandName     = $_SESSION['band_name'] ?? '';
$location     = $_SESSION['location'] ?? '';
$bio          = $_SESSION['bio'] ?? '';
$profileImage = $_SESSION['profile_image'] ?? '';

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

    <style>
        .profile-toast-container {
            position: fixed;
            top: 90px;
            right: 24px;
            z-index: 9999;
        }

        .profile-toast {
            min-width: 280px;
            max-width: 380px;
            padding: 0.95rem 1rem;
            border-radius: 14px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.28);
            backdrop-filter: blur(10px);
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.45s ease, transform 0.45s ease;
        }

        .profile-toast-success {
            background: rgba(0, 245, 212, 0.14);
            border: 1px solid rgba(0, 245, 212, 0.32);
            color: #d7fffa;
        }

        .profile-toast-error {
            background: rgba(255, 99, 132, 0.14);
            border: 1px solid rgba(255, 99, 132, 0.32);
            color: #ffe1e7;
        }

        .profile-toast-hide {
            opacity: 0;
            transform: translateY(-14px);
        }

        .profile-toast-close {
            background: transparent;
            border: none;
            color: inherit;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
            padding: 0;
        }

        .profile-toast-close:hover {
            opacity: 0.75;
        }

        .profile-page {
            width: 100%;
        }

        .profile-shell {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem 2rem;
            box-sizing: border-box;
        }

        .profile-grid {
            width: 100%;
        }

        .profile-card-wide {
            width: 100%;
        }

        .profile-header-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.4rem;
            width: 100%;
        }

        .profile-image-section {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .profile-upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.65rem;
        }

        .profile-avatar-wrap {
            width: 180px;
            height: 180px;
            margin: 0 auto 0.2rem auto;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .profile-avatar-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .profile-avatar-placeholder {
            font-size: 3rem;
            opacity: 0.7;
        }

        .profile-avatar-interactive {
            cursor: pointer;
        }

        .profile-photo-overlay {
            position: absolute;
            inset: 0;
            background: rgba(8, 17, 31, 0.58);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.2s ease;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            text-align: center;
        }

        .profile-avatar-interactive:hover .profile-photo-overlay,
        .profile-avatar-interactive:focus-within .profile-photo-overlay {
            opacity: 1;
        }

        .photo-action-area {
            display: none;
            flex-direction: column;
            gap: 0.4rem;
            width: 100%;
            max-width: 240px;
            margin-top: 0;
        }

        .photo-action-area.active {
            display: flex;
        }

        .photo-action-btn {
            width: 100%;
        }

        .photo-file-name {
            min-height: 20px;
            font-size: 0.85rem;
            opacity: 0.78;
            word-break: break-word;
            margin-top: 0.15rem;
            text-align: center;
        }

        .profile-main-details {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }

        .profile-inline-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-top: -0.1rem;
            width: 100%;
        }

        .profile-inline-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.3rem;
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            background: rgba(255,255,255,0.03);
            transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
            cursor: pointer;
            box-sizing: border-box;
        }

        .profile-inline-row:hover,
        .profile-inline-row:focus-within {
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.14);
            transform: translateY(-1px);
        }

        .profile-inline-left {
            width: 100%;
            min-width: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .profile-inline-label {
            display: block;
            width: 100%;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
            opacity: 0.72;
            text-align: center;
            letter-spacing: 0.02em;
        }

        .profile-inline-value {
            width: 100%;
            font-size: 0.98rem;
            line-height: 1.45;
            word-break: break-word;
            text-align: center;
        }

        .profile-placeholder {
            opacity: 0.6;
            font-style: italic;
        }

        .profile-edit-form {
            display: none;
            margin-top: 0.7rem;
            width: 100%;
        }

        .profile-edit-form.active {
            display: block;
        }

        .profile-edit-form input,
        .profile-edit-form textarea {
            width: 100%;
            max-width: 100%;
            padding: 0.75rem 0.9rem;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.06);
            color: inherit;
            outline: none;
            font: inherit;
            text-align: center;
            box-sizing: border-box;
        }

        .profile-edit-form textarea {
            resize: vertical;
            min-height: 110px;
            text-align: center;
        }

        .edit-form-actions {
            display: flex;
            justify-content: center;
            gap: 0.65rem;
            margin-top: 0.75rem;
            flex-wrap: wrap;
        }

        .primary-btn-inline,
        .secondary-btn-inline {
            border-radius: 10px;
            cursor: pointer;
            transition: 0.2s ease;
            font: inherit;
        }

        .primary-btn-inline {
            border: none;
            background: #4cc9f0;
            color: #08111f;
            font-weight: 700;
            padding: 0.72rem 1rem;
        }

        .secondary-btn-inline {
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(255,255,255,0.06);
            color: inherit;
            padding: 0.72rem 1rem;
        }

        .profile-page-title {
            text-align: center;
            width: 100%;
            margin: 0 0 0.1rem 0;
        }

        @media (max-width: 600px) {
            .profile-shell {
                padding: 0 0.75rem 1.5rem;
            }

            .profile-toast-container {
                top: 80px;
                right: 12px;
                left: 12px;
            }

            .profile-toast {
                min-width: 0;
                max-width: 100%;
            }

            .profile-avatar-wrap {
                width: 150px;
                height: 150px;
            }

            .profile-inline-row {
                padding: 0.8rem 0.75rem;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/_header.php'; ?>

<main class="profile-page">
    <section class="profile-shell">

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

        <section class="profile-grid">
            <article class="profile-card profile-card-wide">
                <h2 class="profile-page-title">
                    <?= htmlspecialchars(trim($stageName) !== '' ? $stageName : 'Your Profile') ?>
                </h2>

                <div class="profile-header-card">

                    <div class="profile-image-section">
                        <form class="profile-upload-form" action="profileImageUpload.php" method="post" enctype="multipart/form-data">
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
                                    class="primary-btn-inline photo-action-btn"
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
                                'band_name'  => ['label' => 'Band', 'value' => $bandName, 'placeholder' => 'Enter band name', 'type' => 'text'],
                                'genre'      => ['label' => 'Genre', 'value' => $genre, 'placeholder' => 'Enter genre', 'type' => 'text'],
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
                                              action="profileFieldUpdate.php"
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
                                                <button type="submit" class="primary-btn-inline">Save</button>
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
                                          action="profileFieldUpdate.php"
                                          method="post"
                                          onclick="event.stopPropagation();">
                                        <input type="hidden" name="field_name" value="bio">
                                        <textarea name="field_value" rows="5" placeholder="Enter bio"><?= htmlspecialchars($bio) ?></textarea>
                                        <div class="edit-form-actions">
                                            <button type="submit" class="primary-btn-inline">Save</button>
                                            <button type="button" class="secondary-btn-inline" onclick="cancelEdit('bio')">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </article>
        </section>
    </section>
</main>

<?php include 'includes/_footer.php'; ?>

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
</script>

</body>
</html>