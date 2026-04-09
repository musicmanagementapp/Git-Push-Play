<?php
// include 'includes/_login.php'; // uncomment later when login flow is ready
session_start();

$title = "Artist Profile";
$description = "Artist profile management dashboard for GitPushPlay";

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
</head>
<body>

<?php include 'includes/_header.php'; ?>

<main class="profile-page">
    <section class="profile-shell">

        <!-- HERO -->
        <section class="profile-hero">
            <div style="text-align:center; width:100%;">
                <p class="profile-label">Artist Dashboard</p>
                <h1><?= htmlspecialchars($stageName) ?></h1>
                <p class="profile-subtitle">
                    Welcome back, <?= htmlspecialchars($artistName) ?>.
                </p>
            </div>
        </section>

        <section class="profile-grid">

            <!-- PROFILE OVERVIEW -->
            <article class="profile-card">
                <h2 style="text-align:center;">Profile Overview</h2>

                <div class="info-list" style="text-align:center;">

                    <div class="info-row">
                        <span class="info-key">Full Name</span><br>
                        <span class="info-value"><?= htmlspecialchars($artistName) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-key">Stage Name</span><br>
                        <span class="info-value"><?= htmlspecialchars($stageName) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-key">Email</span><br>
                        <span class="info-value"><?= htmlspecialchars($email) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-key">Role</span><br>
                        <span class="info-value"><?= htmlspecialchars($role) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-key">Band</span><br>
                        <span class="info-value"><?= htmlspecialchars($bandName) ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-key">Genre</span><br>
                        <span class="info-value"><?= htmlspecialchars($genre) ?></span>
                    </div>
                </div>
            </article>

            <!-- STREAMING STATS -->
            <article class="profile-card">
                <h2 style="text-align:center;">Streaming Stats</h2>

                <div style="text-align:center;">

                    <h3>Spotify</h3>
                    <p><strong>Monthly Listeners:</strong><br><?= htmlspecialchars($spotifyMonthlyListeners) ?></p>
                    <p><strong>Top Track:</strong><br><?= htmlspecialchars($spotifyTopTrack) ?></p>

                    <br>

                    <h3>YouTube</h3>
                    <p><strong>Subscribers:</strong><br><?= htmlspecialchars($youtubeSubscribers) ?></p>
                    <p><strong>Total Views:</strong><br><?= htmlspecialchars($youtubeViews) ?></p>

                    <p style="font-size:13px; color:gray;">
                        (Placeholder for future API integration)
                    </p>

                </div>
            </article>

            <!-- MANAGE PROFILE -->
            <article class="profile-card profile-card-wide">
                <h2 style="text-align:center;">Manage Profile</h2>

                <form action="#" method="post" style="max-width:600px; margin:auto;">

                    <p>
                        <label>Full Name</label><br>
                        <input type="text" name="artist_name" value="<?= htmlspecialchars($artistName) ?>" style="width:100%;">
                    </p>

                    <p>
                        <label>Stage Name</label><br>
                        <input type="text" name="stage_name" value="<?= htmlspecialchars($stageName) ?>" style="width:100%;">
                    </p>

                    <p>
                        <label>Email</label><br>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" style="width:100%;">
                    </p>

                    <p>
                        <label>Genre</label><br>
                        <input type="text" name="genre" value="<?= htmlspecialchars($genre) ?>" style="width:100%;">
                    </p>

                    <p>
                        <label>Band Name</label><br>
                        <input type="text" name="band_name" value="<?= htmlspecialchars($bandName) ?>" style="width:100%;">
                    </p>

                    <p>
                        <label>Location</label><br>
                        <input type="text" name="location" value="<?= htmlspecialchars($location) ?>" style="width:100%;">
                    </p>

                    <p>
                        <label>Artist Bio</label><br>
                        <textarea name="bio" rows="5" style="width:100%;"><?= htmlspecialchars($bio) ?></textarea>
                    </p>

                    <p style="text-align:center;">
                        <button type="submit" class="primary-btn">Update Profile</button>
                    </p>

                </form>
            </article>

        </section>
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
</script>

</body>
</html>
    
