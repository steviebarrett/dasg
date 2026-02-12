<?php
require_once 'includes/include.php';

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateRequest();
}

$titleText = ["en" => "Login", "gd" => "Cuir a-staigh"];

$pageTitle = $titleText[$lang] ?? "Login";
$pageSlug  = "login";

require_once 'includes/htmlHeader.php';

// Build a safe internal redirect target
$referer = (string)($_POST['referer'] ?? $_SERVER['HTTP_REFERER'] ?? '/');
$section = isset($_GET['section']) ? (string)$_GET['section'] : '';

// Parse as URL
$parts = @parse_url($referer);
$path  = is_array($parts) && isset($parts['path']) ? (string)$parts['path'] : '/';

// Reject anything that could become an external redirect or header injection
if ($path === '' || $path[0] !== '/' || strncmp($path, '//', 2) === 0) {
    $path = '/';
}
if (strpos($path, "\\") !== false || strpbrk($path, "\r\n\0") !== false) {
    $path = '/';
}

// Map any allowed incoming path to a safe, server-chosen return location.
$redirectTo = '/';

if (strncmp($path, '/LIL/', 5) === 0) {
    $redirectTo = '/LIL/index.php';
} elseif (strncmp($path, '/audio', 6) === 0) {
    $redirectTo = '/audio';
} elseif (strncmp($path, '/blog', 5) === 0) {
    $redirectTo = '/blog.php';
} elseif (strncmp($path, '/fieldwork', 10) === 0) {
    $redirectTo = '/fieldwork';
} elseif (strncmp($path, '/corpus', 7) === 0) {
    $redirectTo = '/corpus/';
}

// Allow only a safe fragment (no quotes/spaces etc.). Note: URL fragments are client-side.
if ($section !== '' && preg_match('/\A[A-Za-z0-9_-]{1,80}\z/', $section)) {
    $redirectTo .= '#' . $section;
}

$refererSafe = $redirectTo;

// If login form returns true, redirect safely server-side
if (Functions::showLoginForm($refererSafe, $lang) === true) {
    header('Location: ' . $refererSafe, true, 303);
    exit;
}

require_once 'includes/htmlFooter.php';