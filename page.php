<?php

require_once 'includes/include.php';

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateRequest();
}

$page = Pages::getPage($_GET["slug"]);

$pageSlug = $page->getSlug();
$pageTitle = $page->getTitle($lang);
$pageContent = $page->getContent($lang);


require_once 'includes/htmlHeader.php';

require_once 'includes/sideMenu.php';

echo <<<HTML
	<div class="pageContent">		
		{$pageContent}
	</div>
HTML;

require_once 'includes/htmlFooter.php';

