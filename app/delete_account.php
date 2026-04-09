<?php
include 'includes/_login.php';
include 'includes/_pullinfo.php';

$title    = 'Delete Account | GitPushPlay';
$userId   = $gpUserId;
$user     = $gpUser;
$musician = $gpMusician;
$error    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirm = trim($_POST['confirm_delete'] ?? '');

    if (strtolower($confirm) !== 'delete') {
        $error = 'You must type "delete" exactly to confirm.';
    } else {
        if ($musician) {
            $memberships = read_memberships();
            foreach ($memberships as &$m) {
                if (($m['musicianId'] ?? '') === $musician['id']) {
                    $m['status'] = 'removed';
                }
            }
            unset($m);
            write_json(memberships_path(), $memberships);

            $musicians = read_musicians();
            $musicians = array_values(array_filter($musicians, fn($m) => ($m['id'] ?? '') !== $musician['id']));
            write_json(musicians_path(), $musicians);
        }

        delete_user($userId);

        $_SESSION = [];
        session_destroy();
        header('Location: login.php?deleted=1');
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
        <h1>Delete Account</h1>
        <p style="color:var(--text-secondary);margin-bottom:8px;">
            This will permanently delete your account, musician profile, and band memberships.
            <strong style="color:#ffb4b4;">This cannot be undone.</strong>
        </p>

        <?php if ($error !== ''): ?>
            <div class="auth-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="delete_account.php">
            <div class="auth-group">
                <label for="confirm_delete">Type <strong>delete</strong> to confirm</label>
                <input
                    type="text"
                    id="confirm_delete"
                    name="confirm_delete"
                    autocomplete="off"
                    placeholder="delete"
                    required
                >
            </div>

            <button class="auth-btn delete-confirm-btn" type="submit">
                Permanently Delete My Account
            </button>
        </form>

        <div class="auth-link">
            <a href="settings.php">Cancel — go back to Settings</a>
        </div>
    </section>
</main>

</body>
</html>
