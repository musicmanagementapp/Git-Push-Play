<?php

declare(strict_types=1);

function users_file_path(): string
{
    return __DIR__ . '/users.json';
}

function ensure_users_file_exists(): void
{
    $file = users_file_path();

    if (!file_exists($file)) {
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

function create_user(string $username, string $password): array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'Username and password are required.'
        ];
    }

    if (strlen($username) < 3) {
        return [
            'success' => false,
            'message' => 'Username must be at least 3 characters.'
        ];
    }

    if (strlen($password) < 6) {
        return [
            'success' => false,
            'message' => 'Password must be at least 6 characters.'
        ];
    }

    if (find_user_by_username($username) !== null) {
        return [
            'success' => false,
            'message' => 'That username already exists.'
        ];
    }

    $users = read_users();

    $users[] = [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('c')
    ];

    if (!write_users($users)) {
        return [
            'success' => false,
            'message' => 'Could not save the account.'
        ];
    }

    return [
        'success' => true,
        'message' => 'Account created successfully.'
    ];
}

function login_user(string $username, string $password): array
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'Username and password are required.'
        ];
    }

    $user = find_user_by_username($username);

    if ($user === null) {
        return [
            'success' => false,
            'message' => 'Invalid username or password.'
        ];
    }

    if (!isset($user['password']) || !password_verify($password, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Invalid username or password.'
        ];
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['UserLogin'] = 'Yes';
    $_SESSION['username'] = $user['username'];

    return [
        'success' => true,
        'message' => 'Login successful.'
    ];
}
