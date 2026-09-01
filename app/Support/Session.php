<?php

namespace App\Support;

class Session
{
    private static array $cliSession = [];

    public static function start(): void
    {
        if (php_sapi_name() === 'cli') {
            if (!isset($GLOBALS['_CLI_SESSION'])) {
                $GLOBALS['_CLI_SESSION'] = ['_flash' => []];
            }
            return;
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            session_set_cookie_params([
                'lifetime' => 7200,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }

        if (session_status() === PHP_SESSION_ACTIVE && !isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
    }

    public static function get(string $key, $default = null)
    {
        self::start();
        if (php_sapi_name() === 'cli') {
            return $GLOBALS['_CLI_SESSION'][$key] ?? $default;
        }
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        self::start();
        if (php_sapi_name() === 'cli') {
            $GLOBALS['_CLI_SESSION'][$key] = $value;
            return;
        }
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        self::start();
        if (php_sapi_name() === 'cli') {
            return isset($GLOBALS['_CLI_SESSION'][$key]);
        }
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        if (php_sapi_name() === 'cli') {
            unset($GLOBALS['_CLI_SESSION'][$key]);
            return;
        }
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, $value): void
    {
        self::start();
        if (php_sapi_name() === 'cli') {
            $GLOBALS['_CLI_SESSION']['_flash'][$key] = $value;
            return;
        }
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, $default = null)
    {
        self::start();
        if (php_sapi_name() === 'cli') {
            if (isset($GLOBALS['_CLI_SESSION']['_flash'][$key])) {
                $val = $GLOBALS['_CLI_SESSION']['_flash'][$key];
                unset($GLOBALS['_CLI_SESSION']['_flash'][$key]);
                return $val;
            }
            return $default;
        }
        if (isset($_SESSION['_flash'][$key])) {
            $val = $_SESSION['_flash'][$key];
            unset($_SESSION['_flash'][$key]);
            return $val;
        }
        return $default;
    }

    public static function user(): ?array
    {
        return self::get('user');
    }

    public static function setUser(array $user): void
    {
        self::set('user', $user);
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    public static function activeStudentId(): ?string
    {
        $user = self::user();
        return self::get('active_student_id', $user['active_student_id'] ?? null);
    }

    public static function setActiveStudentId(string $studentId): void
    {
        self::set('active_student_id', $studentId);
    }

    public static function regenerate(): void
    {
        if (php_sapi_name() === 'cli') {
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public static function destroy(): void
    {
        if (php_sapi_name() === 'cli') {
            $GLOBALS['_CLI_SESSION'] = ['_flash' => []];
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            if (ini_get("session.use_cookies") && !headers_sent()) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
        }
    }
}
