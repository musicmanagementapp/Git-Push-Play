<?php

declare(strict_types=1);

function users_file_path(): string
{
    return __DIR__ . '/../../secure/users.json';
}

function ensure_users_file_exists(): void
{
    $file = users_file_path();

    if (!file_exists($file)) {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
    }
}

function read_users(): array
{
    ensure_users_file_exists();

    $json = file_get_contents(users_file_path());
    $users = json_decode($json, true);

    return is_array($users) ? $users : [];
}

function write_users(array $users): bool
{
    return file_put_contents(
        users_file_path(),
        json_encode($users, JSON_PRETTY_PRINT)
    ) !== false;
}

function generate_id(): string
{
    return bin2hex(random_bytes(8));
}

function find_user_by_username(string $username): ?array
{
    $users = read_users();

    foreach ($users as $user) {
        if (
            isset($user['username']) &&
            strtolower($user['username']) === strtolower($username)
        ) {
            return $user;
        }
    }

    return null;
}

function find_user_by_id(string $id): ?array
{
    $users = read_users();

    foreach ($users as $user) {
        if (isset($user['id']) && $user['id'] === $id) {
            return $user;
        }
    }

    return null;
}

function create_user(string $username, string $password, string $email = ''): array
{
    $username = trim($username);
    $email    = trim($email);

    if ($username === '' || $password === '') {
        return ['success' => false, 'message' => 'Username and password are required.'];
    }

    if (strlen($username) < 3) {
        return ['success' => false, 'message' => 'Username must be at least 3 characters.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
    }

    if (find_user_by_username($username) !== null) {
        return ['success' => false, 'message' => 'That username already exists.'];
    }

    $users = read_users();

    $users[] = [
        'id'           => generate_id(),
        'email'        => $email,
        'username'     => $username,
        'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
        'createdAt'    => date('c'),
        'lastLogin'    => null,
    ];

    if (!write_users($users)) {
        return ['success' => false, 'message' => 'Could not save the account.'];
    }

    return ['success' => true, 'message' => 'Account created successfully.'];
}

function update_user(string $id, array $fields): array
{
    $users = read_users();
    $found = false;

    $allowed = ['email', 'username'];

    foreach ($users as &$user) {
        if (isset($user['id']) && $user['id'] === $id) {
            foreach ($allowed as $key) {
                if (array_key_exists($key, $fields)) {
                    $user[$key] = trim((string) $fields[$key]);
                }
            }

            if (!empty($fields['newPassword'])) {
                $user['passwordHash'] = password_hash($fields['newPassword'], PASSWORD_DEFAULT);
            }

            $found = true;
            break;
        }
    }
    unset($user);

    if (!$found) {
        return ['success' => false, 'message' => 'User not found.'];
    }

    if (!write_users($users)) {
        return ['success' => false, 'message' => 'Could not save changes.'];
    }

    return ['success' => true, 'message' => 'Account updated successfully.'];
}

function delete_user(string $id): bool
{
    $users   = read_users();
    $filtered = array_values(array_filter($users, fn($u) => ($u['id'] ?? '') !== $id));

    if (count($filtered) === count($users)) {
        return false;
    }

    return write_users($filtered);
}

function login_user(string $username, string $password): array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return ['success' => false, 'message' => 'Username and password are required.'];
    }

    $user = find_user_by_username($username);

    if ($user === null) {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }

    $hash = $user['passwordHash'] ?? $user['password'] ?? '';

    if ($hash === '' || !password_verify($password, $hash)) {
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Migrate old accounts (no id/email/passwordHash) and update lastLogin
    $users = read_users();
    foreach ($users as &$u) {
        if (strtolower($u['username'] ?? '') === strtolower($user['username'])) {
            if (empty($u['id'])) {
                $u['id'] = generate_id();
            }
            if (!isset($u['passwordHash']) && isset($u['password'])) {
                $u['passwordHash'] = $u['password'];
                unset($u['password']);
            }
            if (!isset($u['createdAt']) && isset($u['created_at'])) {
                $u['createdAt'] = $u['created_at'];
                unset($u['created_at']);
            }
            if (!isset($u['email']))     $u['email']     = '';
            if (!isset($u['lastLogin'])) $u['lastLogin'] = null;

            $u['lastLogin'] = date('c');
            $user = $u;
            break;
        }
    }
    unset($u);
    write_users($users);

    $_SESSION['UserLogin'] = 'Yes';
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'] ?? '';

    return ['success' => true, 'message' => 'Login successful.'];
}
