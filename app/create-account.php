<?php
session_start();
require_once __DIR__ . '/assets/libs/auth.php';

$message = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
    } else {
        $result = create_user($username, $password, $email);
        $message = $result['message'];

        if ($result['success']) {
            header('Location: login.php?created=1');
            exit();
        }
    }
}

$title = 'Create Account | GitPushPlay';
?>

<!doctype html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/_meta.php'; ?>

    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-subtext {
    margin-bottom: 1.5rem;
    color: #9ca3af;
}

.auth-optional {
    font-size: 12px;
    color: #9ca3af;
    margin-left: 4px;
}

.auth-link2 a {
    font-family: 'Belleza', sans-serif;
    text-decoration: none;
}

.auth-group {
    margin-bottom: 1.5rem;
}
</style>

    <link href="https://fonts.googleapis.com/css2?family=Belleza&family=Clicker+Script&family=Imperial+Script&family=Rouge+Script&display=swap" rel="stylesheet">
</head>

<body>
    <main class="auth-shell">
        <section class="auth-card">
            <h1>Create Account</h1>
            <p class="auth-subtext">Set up a username and password for the app.</p>

            <?php if ($message !== ''): ?>
                <div class="auth-message">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="auth-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        value="<?php echo htmlspecialchars($username); ?>"
                        required
                    >
                </div>

                <div class="auth-group">
                    <label for="email">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                    >
                </div>

                <div class="auth-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>

                <div class="auth-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        required
                    >
                </div>

                <button class="auth-btn" type="submit">
                    Create Account
                </button>
            </form>

            <div class="auth-link2">
                <a href="login.php" style="font-size: 22px">Already have an account? Log in</a>
            </div>
        </section>
    </main>
</body>
</html>