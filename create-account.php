<?php
session_start();
require_once __DIR__ . '/assets/libs/auth.php';

$message = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
    } else {
        $result = create_user($username, $password);
        $message = $result['message'];

        if ($result['success']) {
            header('Location: login.php?created=1');
            exit();
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <?php include __DIR__ . '/includes/_meta.php'; ?>
    <title>Create Account</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 2rem;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .auth-card h1 {
            margin-bottom: 1rem;
        }

        .auth-card p {
            margin-bottom: 1rem;
        }

        .auth-group {
            margin-bottom: 1rem;
        }

        .auth-group label {
            display: block;
            margin-bottom: 0.35rem;
        }

        .auth-group input {
            width: 100%;
            padding: 0.8rem;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.16);
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        .auth-btn {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }

        .auth-message {
            margin-bottom: 1rem;
            color: #ffb4b4;
        }

        .auth-link {
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/_header.php'; ?>

    <main class="auth-shell">
        <section class="auth-card">
            <h1>Create Account</h1>
            <p>Set up a username and password for the app.</p>

            <?php if ($message !== ''): ?>
                <div class="auth-message"><?php echo htmlspecialchars($message); ?></div>
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

                <button class="auth-btn" type="submit">Create Account</button>
            </form>

            <div class="auth-link">
                <a href="login.php">Already have an account? Log in</a>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/_footer.php'; ?>
</body>
</html>
