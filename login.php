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
$referer = (string)($_POST["referer"] ?? $_SERVER["HTTP_REFERER"] ?? '/');
$section = isset($_GET["section"]) ? (string)$_GET["section"] : '';

// Parse as URL; accept only same-origin relative paths
$parts = parse_url($referer);
$path  = $parts['path'] ?? '/';

// Normalise path
if (!is_string($path) || $path === '' || $path[0] !== '/') {
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