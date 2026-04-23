<?php
include 'includes/_login.php';
include 'includes/_pullinfo.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$gpMusician || !$gpBand || !$gpMembership) {
        header('Location: artist-profile.php');
        exit();
    }

    if ($gpIsOwner) {
        $error = 'You are the band owner and cannot leave. Transfer ownership or delete the band first.';
    } else {
        remove_band_member($gpMembership['id']);
        header('Location: artist-profile.php?left_band=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/_meta.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'includes/_header.php'; ?>

<main class="auth-shell">
    <section class="auth-card delete-confirm-card">
        <h1>Leave Band</h1>

        <?php if ($gpBand): ?>
        <p style="color:var(--text-secondary);margin-bottom:8px;font-size:14px;">
            You are about to leave <strong style="color:lavender;"><?php echo htmlspecialchars($gpBand['name']); ?></strong>.
            You can rejoin later with the invite code if you change your mind.
        </p>

        <?php if ($error !== ''): ?>
            <div class="auth-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!$gpIsOwner): ?>
        <form method="post" action="leave_band.php">
            <button class="auth-btn delete-confirm-btn" type="submit" style="background:rgba(200,100,50,0.2);border-color:rgba(200,100,50,0.5);">
                Leave <?php echo htmlspecialchars($gpBand['name']); ?>
            </button>
        </form>
        <?php else: ?>
            <p style="color:#ffb4b4;font-size:14px;">You are the band owner and cannot leave. Transfer ownership to another member first.</p>
        <?php endif; ?>

        <?php else: ?>
            <p style="color:var(--text-secondary);font-size:14px;">You are not currently in a band.</p>
        <?php endif; ?>

        <div class="auth-link">
            <a href="artist-profile.php" style="text-decoration:none;color:var(--text-secondary);font-size:14px;">Cancel — go back to Settings</a>
        </div>
    </section>
</main>
</body>
</html>
