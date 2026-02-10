<?php
declare(strict_types=1);

final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    public static function ensureToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            // Session should already be started in include.php; this is a safety net.
            session_start();
        }

        $t = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_string($t) || $t === '') {
            $t = bin2hex(random_bytes(32)); // 64 hex chars
            $_SESSION[self::SESSION_KEY] = $t;
        }
        return $t;
    }

    public static function token(): string
    {
        return self::ensureToken();
    }

    public static function field(): string
    {
        $t = self::token();
        // Escape defensively
        $tEsc = htmlspecialchars($t, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return '<input type="hidden" name="_csrf" value="'.$tEsc.'">';
    }

    public static function validateRequest(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? '';
        if (!is_string($sessionToken) || $sessionToken === '') {
            self::reject();
        }

        // Accept either POST field or header (AJAX)
        $post = $_POST['_csrf'] ?? '';
        $hdr  = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        $candidate = '';
        if (is_string($post) && $post !== '') {
            $candidate = $post;
        } elseif (is_string($hdr) && $hdr !== '') {
            $candidate = $hdr;
        }

        if ($candidate === '' || !hash_equals($sessionToken, $candidate)) {
            self::reject();
        }
    }

    public static function reject(): void
    {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "CSRF failed";
        exit;
    }
}