<?php

$metaTitle = empty($seoTitle) ? $pageTitle : $seoTitle;

if (empty($pageSlug) && stristr($_SERVER["REQUEST_URI"], "corpus"))
	$pageSlug = "corpus";
	
$languageChoiceBlock = "";
$javascriptBlock = isset($javascriptBlock) ? $javascriptBlock : "";

$languages = array("gd"=>"Gàidhlig", "en"=>"English");	//perhaps better to make this a CONSTANT and move to includes?

if($includeIrish)
	$languages["ga"] = "Gaeilge";
	
$cqpStyleSheetBlock = "";
$pageUrl = $_SERVER['REQUEST_URI'];

if (!isset($cqpPage)) {	//do not include language block for corpus section as it breaks CQPWeb at the moment
	$urlParts = explode('/', $pageUrl);
	//if the URL has been rewritten set the href accordingly
	if (array_key_exists(end($urlParts), $languages)) {
		array_pop($urlParts);	//remove the language portion
		foreach ($languages as $code => $name) {		
			$linkUrl = implode('/', $urlParts);	
			$languageLinks[$code] = <<<HTML
				<a href="{$linkUrl}/{$code}" title="{$name}">{$name}</a>
HTML;
		}
	} else {
		foreach ($languages as $code => $name)
			$languageLinks[$code] = <<<HTML
				<a href="?lang={$code}" title="{$name}">{$name}</a>
HTML;
	}

	$languageLinksHtml = implode(" / ", $languageLinks);
} else	//if corpus section add in the DASG CQP stylesheet
	$cqpStyleSheetBlock = "<link rel=\"stylesheet\" type=\"text/css\" href=\"/corpus_live/css/CQPweb-dasg.css\"/>";

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
	$menuSelected = $pageSlug == $key ? " menuSelected" : "";
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

echo <<<HTML

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="{$lang}">

	<head profile="http://www.w3.org/2005/10/profile">
	
		<link rel="shortcut icon" href="favicon.ico?23189126" type="image/x-icon">
	
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		
		{$metaHtml}
		
		<meta name="description" content="DASG - Digital Archive of Scottish Gaelic. DASG is an online repository of digitised texts and lexical resources for Scottish Gaelic.">
		
		<title>{$metaTitle}</title>
		  		
  		{$cqpStyleSheetBlock}
  		<link id="main_css" rel="stylesheet" type="text/css" href="/css/{$styleFile}"/>
  		
  		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		  ga('create', 'UA-56518363-1', 'auto');
		  ga('send', 'pageview');
		</script>
  		
  		<!--script type="text/javascript" src="/js/jquery-1.11.1.min.js"></script--> 
  		<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
  		<!--script-- src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script-->
  		<script type="text/javascript" src="/js/jquery.caret.js"></script> 
  		<script type="text/javascript" src="/js/jquery.validate.min.js"></script> 
  		<script type="text/javascript" src="/js/bpopup.min.js"></script>
  		<script type="text/javascript" src="/js/functions.js"></script>
  		
  		{$javascriptBlock}
  		
	</head>
	
	<body>
		<div id="wrapper" {$devHighlightCss}>
			<div id="header" class="head {$pageSlug}_head">
				
				<a id="adminLink" href="/blogAdmin.php" title="Admin login">Admin</a>
				<div id="menu">
					{$menuText}
				</div>
				
				<div id="logo">
					<a href="/index.php" title="DASG Home">
						<img src="/images/logo.png" width="186px" height="182px" alt="DASG Logo"/>
					</a>
				</div>
								
				<h1 id="title">{$pageTitle}</h1>
				
			</div> <!-- end header -->
			
			{$languageChoiceBlock}
			
			<div id="main">		
HTML;
