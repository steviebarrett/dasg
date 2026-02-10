<?php
declare(strict_types=1);

/**
 * include.php
 * - sets session config
 * - starts session ONCE
 * - manages inactivity timeout
 * - contains requireAdmin() function for authentication
 * - ensures CSRF token exists
 * - loads secrets/config
 * - autoload
 */

// ========= DEV/PROD error handling =========
$appEnv = defined('APP_ENV') ? (string)APP_ENV : (string)(getenv('APP_ENV') ?: 'prod');
if ($appEnv !== 'prod') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// ========= Session hardening (MUST be before session_start) =========
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');

// Give the session a non-default name
session_name('DASGSESSID');

// Cookie params (must be set before session_start)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => !empty($_SERVER['HTTPS']), // becomes effective once HTTPS is real
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Server-side lifetime (GC is probabilistic; still fine to set)
$timeout = 1800; // 30 min
ini_set('session.gc_maxlifetime', (string)$timeout);

// ========= Start session (ONCE) =========
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ========= Inactivity timeout (NOW it is safe to use $_SESSION) =========
$now = time();
$last = isset($_SESSION['last_activity']) ? (int)$_SESSION['last_activity'] : 0;

if ($last > 0 && ($now - $last) > $timeout) {
    // expire session data
    $_SESSION = [];

    // expire cookie
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }

    session_destroy();

    // start a fresh session
    session_start();
}

$_SESSION['last_activity'] = $now;

// ========= CSRF token =========
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ========= Security headers =========
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// ========= Secrets / config =========
require_once '.env';

// ========= Autoload =========
spl_autoload_extensions(".php");
spl_autoload_register();
