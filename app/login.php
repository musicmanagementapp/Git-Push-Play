<?php
session_start();
require_once __DIR__ . '/assets/libs/auth.php';

if (isset($_SESSION['UserLogin']) && $_SESSION['UserLogin'] === 'Yes') {
    header('Location: index.php');
    exit();
}

$message = '';
$username = '';

if (isset($_GET['created']) && $_GET['created'] === '1') {
    $message = 'Account created successfully. Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = login_user($username, $password);
    $message = $result['message'];

    if ($result['success']) {
        header('Location: index.php');
        exit();
    }
}
$title = 'Login | GitPushPlay';
?>
<!doctype html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/_meta.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <main class="auth-shell">
        <section class="auth-card">
            <div style="justify-content: center">
                <h1>Login</h1>
                <p>Sign in with the account you created.</p>
            </div>
            <?php if ($message !== ''): ?>
                <div class="auth-message"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
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
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>

                <button class="auth-btn" type="submit">Login</button>
            </form>

            <div class="auth-link">
                <a href="create-account.php">Need an account? Create one</a>
            </div>
        </section>
    </main>
</body>
</html>