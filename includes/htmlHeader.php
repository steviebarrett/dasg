<?php

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: "
    . "default-src 'self'; "
    . "base-uri 'self'; "
    . "frame-ancestors 'none'; "

    . "img-src 'self' data: https://www.google-analytics.com; "
    . "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; "

    . "script-src 'self' 'unsafe-inline' https://code.jquery.com https://cdnjs.cloudflare.com https://www.googletagmanager.com https://www.google-analytics.com https://ssl.p.jwpcdn.com; "
    . "script-src-elem 'self' 'unsafe-inline' https://code.jquery.com https://cdnjs.cloudflare.com https://www.googletagmanager.com https://www.google-analytics.com https://ssl.p.jwpcdn.com; "

    . "connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com; "

    . "media-src 'self'; "
);

$metaTitleEsc = htmlspecialchars($metaTitle ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$pageTitleEsc = htmlspecialchars($pageTitle ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$pageSlugSafe = isset($pageSlug) ? preg_replace('/[^a-z0-9_-]/i', '', (string)$pageSlug) : "";
$pageSlugAttr = htmlspecialchars($pageSlugSafe, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

$javascriptBlock = isset($javascriptBlock) ? $javascriptBlock : "";

if (empty($pageSlugSafe) && stristr($_SERVER["REQUEST_URI"], "corpus")) {
    $pageSlugSafe = "corpus";
}

$languageChoiceBlock = "";

$languages = array("gd"=>"Gàidhlig", "en"=>"English");	//perhaps better to make this a CONSTANT and move to includes?

if($includeIrish)
	$languages["ga"] = "Gaeilge";
	
$cqpStyleSheetBlock = "";
//$pageUrl = $_SERVER['REQUEST_URI'];

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if (!is_string($path) || $path === '') $path = '/';
$path = '/' . ltrim($path, '/'); // normalize

if (!isset($cqpPage)) {	//do not include language block for corpus section as it breaks CQPWeb at the moment
	$urlParts = explode('/', $path);
	//if the URL has been rewritten set the href accordingly
	if (array_key_exists(end($urlParts), $languages)) {
		array_pop($urlParts);	//remove the language portion
		foreach ($languages as $code => $name) {

			$linkUrl = implode('/', $urlParts);

            // when outputting:
            $linkUrlEsc = htmlspecialchars($linkUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $codeEsc    = rawurlencode($code); // code is allowlisted anyway
            $nameEsc    = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			$languageLinks[$code] = <<<HTML
				<a href="{$linkUrlEsc}/{$codeEsc}" title="{$nameEsc}">{$nameEsc}</a>
HTML;
		}
	} else {
		foreach ($languages as $code => $name)
            $codeQ = rawurlencode($code);
			$languageLinks[$code] = <<<HTML
				<a href="?lang={$codeQ}" title="{$name}">{$name}</a>
HTML;
	}

	$languageLinksHtml = implode(" / ", $languageLinks);
}

$languageChoiceBlock = <<<HTML
		<div id="language">
			{$languageLinksHtml}
		</div>
HTML;

$menuItems = array(
	"home"		=>array("en"=>"Home", "gd"=>"Dachaigh", "url"=>"/index.php?lang={$lang}"),
	"corpus"	=>array("en"=>"Corpus", "gd"=>"Corpas na Gàidhlig", "url"=>"/corpus/"),
	"fieldwork"	=>array("en"=>"Fieldwork", "gd"=>"Faclan bhon t-Sluagh", "url"=>"/fieldwork"),
	"audio"	    =>array("en"=>"Audio Archive", "gd"=>"Cluas ri Claisneachd", "url"=>"/audio")
);

$menuText = "";
foreach ($menuItems as $key => $item) {
	$menuSelected = $pageSlugSafe == $key ? " menuSelected" : "";
	$menuText .= <<<HTML
		\n<div id="{$key}_menu" class="menuItem{$menuSelected}">
			<a href="{$item["url"]}" title="{$item[$lang]}">
				{$item["gd"]}<br/><span class="menuTranslated">{$item["en"]}</span>
			</a>
		</div>
HTML;
}

/*
 * Accented Character Shortcut Code
 */
$accentedCharHtml = "";
$accentedChars = array('à', 'è', 'ì', 'ò', 'ù', 'á', 'é', 'í', 'ó', 'ú');
foreach ($accentedChars as $char) {
	$accentedCharHtml .= <<<HTML
			&nbsp;<a href="#" onclick="addCharacterToSearch('{$char}', 'query');return false;">{$char}</a>
HTML;
}

/*
 * Accented Character Shortcut Code
 */
$accentedCharHtml = "";
$accentedChars = ['à','è','ì','ò','ù','á','é','í','ó','ú'];

foreach ($accentedChars as $char) {
    $charEsc = htmlspecialchars($char, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $accentedCharHtml .= <<<HTML
        &nbsp;<a href="#" class="accent-char" data-char="{$charEsc}">{$charEsc}</a>
HTML;
}

/**
 * Temp style code
 */
		$styleFile = "style.css";
/*
 * 
 */

if ($_SERVER["SERVER_NAME"] == "dev.dasg.ac.uk") 
	$devHighlightCss = 'style="border:2px solid red;"';

// CSRF token exposure
$csrf = Csrf::token();
$csrfJs = htmlspecialchars($csrf, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$metaHtml =  "<meta name=\"csrf-token\" content=\"{$csrfJs}\">";

// Google tag
$gaId = GOOGLE_TAG_ID;
$gaIdAttr = htmlspecialchars($gaId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

echo <<<HTML

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="{$lang}">

	<head profile="http://www.w3.org/2005/10/profile">
	
		<link rel="shortcut icon" href="favicon.ico?23189126" type="image/x-icon">
	
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		
		{$metaHtml}
		
		<meta name="description" content="DASG - Digital Archive of Scottish Gaelic. DASG is an online repository of digitised texts and lexical resources for Scottish Gaelic.">
		
		<title>{$metaTitleEsc}</title>
		  		
  		{$cqpStyleSheetBlock}
  		<link id="main_css" rel="stylesheet" type="text/css" href="/css/{$styleFile}"/>
  		
  		<script async src="https://www.googletagmanager.com/gtag/js?id={$gaIdAttr}"></script>
  		<script defer src="/js/google.js"></script>
  			
  	    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
  		<script type="text/javascript" src="/js/jquery.caret.js"></script> 
  		<script type="text/javascript" src="/js/jquery.validate.min.js"></script> 
  		<script type="text/javascript" src="/js/bpopup.min.js"></script>
  		<script type="text/javascript" src="/js/functions.js"></script>
  		
  		{$javascriptBlock}
  		
	</head>
	
	<body>
		<div id="wrapper" {$devHighlightCss}>
			<div id="header" class="head {$pageSlugAttr}_head">
				
				<a id="adminLink" href="/blogAdmin.php" title="Admin login">Admin</a>
				<div id="menu">
					{$menuText}
				</div>
				
				<div id="logo">
					<a href="/index.php" title="DASG Home">
						<img src="/images/logo.png" width="186px" height="182px" alt="DASG Logo"/>
					</a>
				</div>
								
				<h1 id="title">{$pageTitleEsc}</h1>
				
			</div> <!-- end header -->
			
			{$languageChoiceBlock}
			
			<div id="main">		
HTML;
