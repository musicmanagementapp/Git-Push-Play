<?php
// include 'includes/_login.php'; // uncomment later when login flow is ready
session_start();

$title = "Artist Profile";
$description = "Artist profile management dashboard for GitPushPlay";

// session-backed placeholders
$artistName = $_SESSION['artist_name'] ?? $_SESSION['username'] ?? "Demo Artist";
$stageName  = $_SESSION['stage_name'] ?? "No Stage Name Yet";
$email      = $_SESSION['email'] ?? "demo@example.com";
$role       = $_SESSION['role'] ?? "Artist";
$genre      = $_SESSION['genre'] ?? "Genre not set";
$bandName   = $_SESSION['band_name'] ?? "No Band Assigned";
$location   = $_SESSION['location'] ?? "Location not set";
$bio        = $_SESSION['bio'] ?? "This is a placeholder artist bio.";

// placeholder streaming stats
$spotifyMonthlyListeners = "24,580";
$spotifyTopTrack = "Midnight Drive";
$youtubeSubscribers = "8,420";
$youtubeViews = "153,900";
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

<?php include 'includes/_footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>