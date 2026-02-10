<?php

//ini_set("display_errors", 1);
ini_set( 'pcre.backtrack_limit', '2M' );

require_once '../includes/include.php';

$search = Functions::e($_GET["search"]);

$params = explode('|', base64_decode($search));

$fieldworkItem 	= Functions::e($params[0]);
$headword 		= Functions::e($params[1]);
$id 			= Functions::e($params[2]);
$itemId 		= empty($params[3]) ? "" : Functions::e($params[3]);
$enteredQuery 	= empty($params[4]) ? "" : Functions::e($params[4]);
$resultId 		= empty($params[5]) ? "" : Functions::e($params[5]);
$lenited 		= empty($params[6]) ? "" : Functions::e($params[6]);
$accInsens		= empty($params[7]) ? "" : Functions::e($params[7]);
$searchScope 	= empty($params[8]) ? "" : Functions::e($params[8]);

$query = $enteredQuery;
$html = FieldworkItems::getHtml($fieldworkItem);

$headword = str_replace("[", "\[", $headword);
$headword = str_replace("]", "\]", $headword);

if (!empty($headword)) {
    $html = preg_replace("/({$headword})/iu", '<span class="hi">' . "$1" . '</span>', $html);
}
if (!empty($enteredQuery)) {
    if (!empty($lenited)) {
        $query = Functions::getLenited($query);
    }
    
    if (!empty($accInsens)) {
        $query = Functions::getAccentInsensitive($query);
    }
    
    $query = trim($query); 	//remove any trailing whitespace
    $query = str_replace("'", "â€™", $query);			//replace any single quote with smart quote (as used in XML source docs)
    $query = str_replace("~", "[" . ACCENT_VOWELS . "]+", $query);
    $query = str_replace("?", "[" . ACCENT_CHARSET . "]", $query);
    $query = str_replace("*", "[" . ACCENT_CHARSET . "]+", $query);
    $query = str_replace("h=", "h?", $query);
    
    //only add highlighting to text not in a tag (fix for illustration paths)
    $html = preg_replace("/(<.+?>[^<>]*?)({$query})([^<>]*?<.+?>)/iu", "$1" . '<span class="hi">' . "$2" . '</span>' . "$3", $html);
    //	$html = preg_replace("/({$query})/iu", '<span class="hi">' . "$1" . '</span>', $html);
    
}

$pageTitle = "View Item";
$pageSlug = "fieldwork";

$javascriptBlock = <<<HTML
	<script type="text/javascript">
	
		var id = "{$id}";
		
		$(document).ready(function() {
		
            //Check to see if the window is top if not then display button
            $(window).scroll(function(){
            	if ($(this).scrollTop() > 500) {
        			$('.scrollToTop').fadeIn();
        		} else {
        			$('.scrollToTop').fadeOut(10);
        		}
        	});
        	
        	//Click event to scroll to top
        	$('.scrollToTop').click(function(){
        		$('html, body').animate({scrollTop : 0},800);
        		return false;
        	});
        	
			if (id != '')
				goToByScroll(id);
				
			var bpopup;
			$('.fieldworkPerson').mouseenter(
				function() {
					var personId = $(this).attr('id');
					var url = "/ajax/fieldworkPerson.php?action=getBiog&id="+personId;
					$('#fieldworkBiog').load(url);
					bpopup = $('#fieldworkBiog').bPopup({
						modal: false
        		});
			});
			
			$('.fieldworkPerson').mouseleave(function() {
				bpopup.close();
			});
			
        });
        
        function goToByScroll(id)
        {
			var idPos = $("#"+id).offset().top;
            $('html,body').animate({scrollTop: idPos-150},'slow');
        }
        
        function highlightWords(line, word)
        {
            var regex = new RegExp('('+word+')','gi');
            return line.replace(regex, '<span class="hi">$1</span>');
        }
        
</script>
HTML;

$cqpPage = true;

require_once '../includes/htmlHeader.php';

//assemble the back links
$backHtml = <<<HTML
	<div class="clear">
HTML;
if (!empty($enteredQuery)) {
    $backHtml .= <<<HTML
		<a class="backLink" href="/fieldwork/search?q={$enteredQuery}&id={$resultId}&l={$lenited}&as={$accInsens}&searchScope={$searchScope}" title="Back to results">
            <br/>&lt; Back to results</a>
HTML;
} else {
    $backHtml .= <<<HTML
		<a class="backLink" href="/fieldwork/browse#{$itemId}" title="Back to browse">
            <br/>&lt; Back to browse</a>
HTML;
}
$backHtml .= "</div>";

echo <<<HTML

    <div id="dasg_cqp_main_menu">
        <div class="menuItem">
            <a href="/fieldwork/search">Search</a>
        </div>
        <div class="menuItem">
            <a href="/fieldwork/browse">Browse</a>
        </div>
		<br class="clear"/>
    </div>
    
	{$backHtml}
	
	<div id="fieldworkBiog"></div>
	
    <div id="content">
    	{$html}
    	
    	<br/>
    	© DASG
    </div>
    
    <a href="#" class="scrollToTop">^ Return To Top ^</a>
    
    {$backHtml}
    
HTML;
    
    require_once '../includes/htmlFooter.php';
    
    