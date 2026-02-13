<?php

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

$lenited = $_GET["lenited"] ?? '';
$accInsens = $_GET["accInsens"] ?? '';

$extrasHtml = isset($extrasHtml) ? $extrasHtml : "";

//prepare words for CQPSyntax query
if ($lenited == "on" || $accInsens == "on") {
    $words = explode(" ", $query);
	foreach ($words as $i => $word) {
        if ($lenited == "on") {
            $words[$i] = $word[0] . "h?" . mb_substr($word, 1);
        }
        $words[$i] = "\"" . $words[$i] . "\"";
        if ($accInsens == "on") {
            $words[$i] = $words[$i] . " %d";
        }
    }
	$changeMode = true;
	$query = implode(" ", $words);
}


$pageTitles = Pages::getPageTitles();

if ($pageSlug == "home" || $pageSlug == "blog") {

	$extrasHtml = <<<HTML

	<div class="socialMedia">

		<a target="_blank" href="https://twitter.com/DASG_Glaschu?ref_src=twsrc%5Etfw" title="Tweets by DASG_Glaschu"><img src="/images/x-logo-black.png" alt="X" width="40px"/></a>

		<br/><br/>
		<div style="margin:10px 0 10px 0;clear:both">
			<a href="http://www.facebook.com/DasgGlaschu" title="DASG Glaschu">
				<img src="/images/facebook.jpg" width="80px" alt="Facebook"/>
			</a>
		</div>

		<div class="fb-like" data-href="https://www.facebook.com/DasgGlaschu" data-layout="box_count" data-action="like" data-show-faces="false" data-share="true"></div>

	</div>


HTML;

}

echo <<<HTML
				<div id="sideMenu">
					<ul>
HTML;

foreach ($pageTitles as $slug=>$item) {
	$sideMenuSelected = $slug == $pageSlug ? " menuSelected" : "";
	echo <<<HTML
						<li class="sideMenuItem {$sideMenuSelected}">
							<a href="/about/{$slug}/{$lang}" title="{$item[$lang]}">{$item[$lang]}</a>
						</li>
HTML;
}

/*
 * Language in Lyrics
 */
$LILSlug = array("en"=>"Language in Lyrics", "gd"=>"Cainnt anns na Ceathramhan"); 
echo <<<HTML
						<li class="sideMenuItem">
							<a href="/LIL/" target="_blank" title="{$LILSlug[$lang]}">{$LILSlug[$lang]}</a>
						</li>
HTML;

/*
 * Briathradan
 */
echo <<<HTML
						<li class="sideMenuItem">
							<a href="https://briathradan.gla.ac.uk/briathradan/" target="_blank" title="Am Briathradan">Am Briathradan</a>
						</li>
HTML;

/*
 * Grammar
 */
$grammarSelected = ($pageSlug == "grammar") ? " menuSelected" : "";

$grammarSlug = array("en"=>"LEACAN", "gd"=>"LEACAN");
echo <<<HTML
						<li class="sideMenuItem {$grammarSelected}">
							<a href="https://leacan.gla.ac.uk/leacan/" target="_blank" title="LEACAN"">{$grammarSlug[$lang]}</a>
						</li>
HTML;

/*
 * Launch
 */
echo <<<HTML
						<li class="sideMenuItem">
							<a href="/launch/" title="DASG Launch">DASG Launch</a>
						</li>
HTML;

/*
 * Gairm Index
*/

$gairmSlug = array("en"=>"Gairm Online", "gd"=>"Gairm Air-loidhne");
$gairmSelected = ($pageSlug == "gairm") ? " menuSelected" : "";

echo <<<HTML
						<li class="sideMenuItem {$gairmSelected}">
							<a href="/gairm/" title="{$gairmSlug[$lang]}">{$gairmSlug[$lang]}</a>
						</li>
HTML;
	
/*
 * Seanchas
 */
$seanchasSelected = ($pageSlug == "seanchas") ? " menuSelected" : "";

echo <<<HTML
					<!--li class="sideMenuItem {$seanchasSelected}">
						<a href="/seanchas?lang={$lang}" title="Seanchas">Seanchas</a>
					</li-->
HTML;

	/*
	 * Blog
	*/
$blogSelected=  ($pageSlug == "blog") ? " menuSelected" : "";

echo <<<HTML
						<li class="sideMenuItem {$blogSelected}">
							<a href="/blog/{$lang}" title="Blog">Blog</a>
						</li>
					</ul>
			
					{$extrasHtml}
			
				</div>
HTML;
