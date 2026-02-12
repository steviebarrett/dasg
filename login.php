<?php

require_once 'includes/include.php';

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateRequest();
}

$titleText = array("en"=>"Login", "gd"=>"Cuir a-staigh");

$pageTitle = $titleText[$lang];
$pageSlug = "login";


require_once 'includes/htmlHeader.php';

$referer = (string)($_POST["referer"] ?? $_SERVER["HTTP_REFERER"] ?? '/');
if (isset($_GET["section"])) {
	$referer .= "#" . (string)$_GET["section"];
}

$refererUrl = filter_var($referer, FILTER_SANITIZE_URL);
$path = parse_url($refererUrl, PHP_URL_PATH);
if ($path === false || $path === null) {
	$path = '/';
}
$refererSafe = $path;

if (Functions::showLoginForm($refererSafe, $lang) == true) {

	echo <<<HTML
		<script>
			window.location.replace('{$refererSafe}');
		</script>
		<a href="{$refererSafe}">Return to page</a>
HTML;
}



require_once 'includes/htmlFooter.php';
