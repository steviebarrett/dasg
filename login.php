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

// Build a safe internal redirect target (path + optional fragment only)
$referer = (string)($_POST['referer'] ?? $_SERVER['HTTP_REFERER'] ?? '/');
$section = isset($_GET['section']) ? (string)$_GET['section'] : '';

// Parse as URL; only use the PATH component, never scheme/host
$parts = @parse_url($referer);
$path  = is_array($parts) && isset($parts['path']) ? (string)$parts['path'] : '/';

// Reject anything that could become an external redirect or header injection
if ($path === '' || $path[0] !== '/' || strncmp($path, '//', 2) === 0) {
    $path = '/';
}
if (strpos($path, "\\") !== false || strpbrk($path, "\r\n\0") !== false) {
    $path = '/';
}

// Optionally, restrict redirects to known on-site areas (tighten if desired)
// Add/remove prefixes to match your deployment paths.
$allowedPrefixes = ['/', '/LIL/', '/audio', '/blog', '/fieldwork', '/corpus'];
$ok = false;
foreach ($allowedPrefixes as $pfx) {
    if ($pfx === '/' || strncmp($path, $pfx, strlen($pfx)) === 0) {
        $ok = true;
        break;
    }
}
if (!$ok) {
    $path = '/';
}

// Allow only a safe fragment (no quotes/spaces etc.)
if ($section !== '' && preg_match('/\A[A-Za-z0-9_-]{1,80}\z/', $section)) {
    $path .= '#' . $section;
}

$refererSafe = $path;

// If login form returns true, redirect safely server-side
if (Functions::showLoginForm($refererSafe, $lang) === true) {
    header('Location: ' . $refererSafe, true, 303);
    exit;
}

require_once 'includes/htmlFooter.php';