<?php

require_once 'includes/include.php';

$pageSlug = "maintenance";
$pageTitle = "Blog Maintenance";

$cqpPage = true;

require_once 'includes/htmlHeader.php';

echo <<<HTML

	<br/>
	<h2>The DASG Blog is undergoing maintenance.</h2>

	<br/><br/>
	<h3>Please check back later.</h3>
	
	<br/>
	<h3>Apologies for any inconvenience.</h3>
	
	<br/><br/>
HTML;

require_once 'includes/htmlFooter.php';
