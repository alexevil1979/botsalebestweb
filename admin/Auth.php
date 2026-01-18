<?php

namespace Admin;

use Core\Config;

class Auth
{
    private const SESSION_KEY = 'admin_auth';

    public static function login(string $username, string $password): bool
    {
        $adminUsername = Config::get('ADMIN_USERNAME', 'admin');
        $adminPassword = Config::get('ADMIN_PASSWORD', '');

        if ($username === $adminUsername && $password === $adminPassword) {
            $_SESSION[self::SESSION_KEY] = [
                'username' => $username,
                'logged_in' => true,
                'login_time' => time(),
            ];
            return true;
        }

        return false;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        if (!isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        $session = $_SESSION[self::SESSION_KEY];
        $lifetime = (int)Config::get('ADMIN_SESSION_LIFETIME', 3600);

        if (time() - $session['login_time'] > $lifetime) {
            self::logout();
            return false;
        }

        return $session['logged_in'] ?? false;
    }

    public static function requireAuth(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    public static function getCSRFToken(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCSRF(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
