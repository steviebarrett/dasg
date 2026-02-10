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

if (empty($_POST["referer"])) {
	$referer = $_SERVER["HTTP_REFERER"];
	if (isset($_GET["section"])) {
		$referer .= "#{$_GET["section"]}";
	}
} else {
	$referer = $_POST["referer"];
}

if (Functions::showLoginForm($referer, $lang) == true) {

	echo <<<HTML
		<script>
			window.location.replace('{$referer}');
		</script>
		<a href="{$referer}">Return to page</a>
HTML;
}



require_once 'includes/htmlFooter.php';