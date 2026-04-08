<?php
include 'includes/_login.php';

$title = "Artist Profile";
$description = "Artist profile management dashboard for GitPushPlay";

// --- Handle form submission ---
$updateMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['artist_name'] = htmlspecialchars($_POST['artist_name'] ?? $_SESSION['artist_name']);
    $_SESSION['stage_name']  = htmlspecialchars($_POST['stage_name'] ?? $_SESSION['stage_name']);
    $_SESSION['email']       = htmlspecialchars($_POST['email'] ?? $_SESSION['email']);
    $_SESSION['genre']       = htmlspecialchars($_POST['genre'] ?? $_SESSION['genre']);
    $_SESSION['band_name']   = htmlspecialchars($_POST['band_name'] ?? $_SESSION['band_name']);
    $_SESSION['location']    = htmlspecialchars($_POST['location'] ?? $_SESSION['location']);
    $_SESSION['bio']         = htmlspecialchars($_POST['bio'] ?? $_SESSION['bio']);

    $updateMessage = "Profile updated successfully!";
}

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
    <style>
        /* --- Main Page Layout --- */
        .profile-page { 
            padding: 40px 20px;
            font-family: "Belleza", sans-serif;
            min-height: calc(100vh - 100px);
        }

        /* --- Shell --- */
        .profile-shell {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        /* Hero Section */
        .profile-hero {
            background: black;
            opacity: 0.7;
            color: #fff;
            padding: 40px 20px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        .profile-hero h1 {
            font-size: 36px;
            margin: 10px 0;
        }

        .profile-hero .profile-subtitle {
            font-size: 18px;
            opacity: 0.85;
        }

        /* Grid for Cards */
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        /* Profile Card */
        .profile-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .profile-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }

        .profile-card h2 {
            margin-bottom: 20px;
            color: #7d4fff;
        }

        /* Info List */
        .info-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-row {
            background: #f8eafc;
            padding: 12px 15px;
            border-radius: 12px;
            text-align: center;
        }

        .info-key {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            color: #5a2b80;
        }

        /* Form Styling */
        .profile-card-wide form p {
            margin-bottom: 15px;
        }

        .profile-card-wide label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #7d4fff;
        }

        .profile-card-wide input,
        .profile-card-wide textarea {
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-family: "Belleza", sans-serif;
            font-size: 14px;
            outline: none;
            transition: border 0.2s, box-shadow 0.2s;
        }

        .profile-card-wide input:focus,
        .profile-card-wide textarea:focus {
            border-color: #7d4fff;
            box-shadow: 0 0 5px rgba(125,79,255,0.5);
        }

        .primary-btn {
            background: linear-gradient(90deg, #7d4fff, #ff914d);
            color: #fff;
            padding: 12px 25px;
            font-size: 16px;
            font-family: "Belleza", sans-serif;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.3s;
        }

        .primary-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        /* Streaming Stats */
        .profile-card h3 {
            margin-top: 20px;
            color: #7d4fff;
        }

        .profile-card p {
            font-size: 14px;
            color: #333;
        }
    </style>
</head>

<body>

<?php include 'includes/_header.php'; ?>

<main class="profile-page">
    <section class="profile-shell">

        <!-- HERO -->
        <section class="profile-hero">
            <p class="profile-label">Artist Dashboard</p>
            <h1><?= htmlspecialchars($stageName) ?></h1>
            <p class="profile-subtitle">
                Welcome back, <?= htmlspecialchars($artistName) ?>.
            </p>
        </section>

        <?php if (!empty($updateMessage)): ?>
            <p style="text-align:center; color: green; font-weight:bold; margin-bottom:20px;">
                <?= $updateMessage ?>
            </p>
        <?php endif; ?>

        <section class="profile-grid">

            <!-- PROFILE OVERVIEW -->
            <article class="profile-card">
                <h2>Profile Overview</h2>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-key">Full Name</span>
                        <span class="info-value"><?= htmlspecialchars($artistName) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Stage Name</span>
                        <span class="info-value"><?= htmlspecialchars($stageName) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Email</span>
                        <span class="info-value"><?= htmlspecialchars($email) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Role</span>
                        <span class="info-value"><?= htmlspecialchars($role) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Band</span>
                        <span class="info-value"><?= htmlspecialchars($bandName) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-key">Genre</span>
                        <span class="info-value"><?= htmlspecialchars($genre) ?></span>
                    </div>
                </div>
            </article>

            <!-- STREAMING STATS -->
            <article class="profile-card">
                <h2>Streaming Stats</h2>
                <div style="text-align:center;">
                    <h3>Spotify</h3>
                    <p><strong>Monthly Listeners:</strong><br><?= htmlspecialchars($spotifyMonthlyListeners) ?></p>
                    <p><strong>Top Track:</strong><br><?= htmlspecialchars($spotifyTopTrack) ?></p>

                    <h3>YouTube</h3>
                    <p><strong>Subscribers:</strong><br><?= htmlspecialchars($youtubeSubscribers) ?></p>
                    <p><strong>Total Views:</strong><br><?= htmlspecialchars($youtubeViews) ?></p>

                    <p style="font-size:13px; color:gray;">(Placeholder for future API integration)</p>
                </div>
            </article>

            <!-- MANAGE PROFILE -->
            <article class="profile-card profile-card-wide">
                <h2>Manage Profile</h2>
                <form action="#" method="post">
                    <p>
                        <label>Full Name</label>
                        <input type="text" name="artist_name" value="<?= htmlspecialchars($artistName) ?>">
                    </p>
                    <p>
                        <label>Stage Name</label>
                        <input type="text" name="stage_name" value="<?= htmlspecialchars($stageName) ?>">
                    </p>
                    <p>
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
                    </p>
                    <p>
                        <label>Genre</label>
                        <input type="text" name="genre" value="<?= htmlspecialchars($genre) ?>">
                    </p>
                    <p>
                        <label>Band Name</label>
                        <input type="text" name="band_name" value="<?= htmlspecialchars($bandName) ?>">
                    </p>
                    <p>
                        <label>Location</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($location) ?>">
                    </p>
                    <p>
                        <label>Artist Bio</label>
                        <textarea name="bio" rows="5"><?= htmlspecialchars($bio) ?></textarea>
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
</body>
</html>
    
