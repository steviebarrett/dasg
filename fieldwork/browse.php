<?php

require_once '../includes/include.php';

$pageTitle = "Browse the Fieldwork Archive";
$pageSlug = "fieldwork";

$cqpPage = true;

require_once '../includes/htmlHeader.php';

echo <<<HTML

    <div id="dasg_cqp_main_menu">
        <div class="menuItem">
            <a href="/fieldwork/search">Search</a>
        </div>
        <div class="menuItem menuSelected">
            <a href="/fieldwork/browse">Browse</a>
        </div>
		<br class="clear"/>
    </div>
		
	<div>
        <h2>Browse</h2>
        <div>
        	<div id="browse"/>
        </div>
    </div>
    <br class="clear"/>
	
    <div id="browseFieldwork">
		
		<table id="fieldworkTable" class="tablesorter">
			<thead>
	            <th>Title</th>
				<th>Origin</th>
		    	<th>Fieldworker</th>
	            <th>Location</th>
		    	<th>Informant</th>
	            <th>Link</th>
	        </thead>
	        <tbody>
HTML;

$fieldworkItems = FieldworkItems::getAll();

$i = 0;
foreach ($fieldworkItems as $filename => $xml) {

	$i++;
	//informants
	$informants = $origins = array();
	$informantHtml = $originHtml = "";
	foreach ($xml->informant as $informant) {
		$informantName = $informant->nameEnglish;
		if (!empty($informant->nameGaelic)) 
			$informantName .= " / " . $informant->nameGaelic;
		$informants[] = $informantName;
		if (!empty($informant->origin)) {
			$origins[] = $informant->origin;
		}		
	}
	$informantHtml = implode("; ", $informants);
	$originHtml = implode(" or ", $origins);
//
	$encodedItem = base64_encode($filename . "|||item{$i}|");

	echo <<<HTML
				<tr>
					<td><a id="item{$i}"></a>{$xml->title}</td>
					<td>{$originHtml}</td>
					<td>{$xml->fieldworker}</td>
					<td>{$xml->location}</td>
					<td>{$informantHtml}</td>
					<td><input type="button" class="dasg_smlButton" onclick="window.location.href='view/{$encodedItem}';" value="View"/></td>
				</tr>		
HTML;

}

echo <<<HTML
			</tbody>
		</table>
		
		<a href="#" class="scrollToTop">^ Return To Top ^</a>

	</div> <!-- end browseFieldwork -->

HTML;

echo <<<HTML

  		<script type="text/javascript" src="/js/jquery.tablesorter.min.js"></script>

		<script type="text/javascript">
		
		function getTextExtractor()
		{
		  return (function() {
		    var patternLetters = /[öäüÖÄÜáàâéèêúùûóòôÁÀÂÉÈÊÚÙÛÓÒÔß]/g;
		    var patternDateDmy = /^(?:\D+)?(\d{1,2})\.(\d{1,2})\.(\d{2,4})$/;
		    var lookupLetters = {
		      "ä": "a", "ö": "o", "ü": "u",
		      "Ä": "A", "Ö": "O", "Ü": "U",
		      "á": "a", "à": "a", "â": "a",
		      "é": "e", "è": "e", "ê": "e",
		      "ú": "u", "ù": "u", "û": "u",
		      "ó": "o", "ò": "o", "ô": "o",
		      "Á": "A", "À": "A", "Â": "A",
		      "É": "E", "È": "E", "Ê": "E",
		      "Ú": "U", "Ù": "U", "Û": "U",
		      "Ó": "O", "Ò": "O", "Ô": "O",
		      "ß": "s"
		    };
		    var letterTranslator = function(match) { 
		      return lookupLetters[match] || match;
		    }

			    return function(node) {
			      var text = $.trim($(node).text());
			      var date = text.match(patternDateDmy);
			      if (date)
			        return [date[3], date[2], date[1]].join("-");
			      else
			        return text.replace(patternLetters, letterTranslator);
			    }
			  })();
			}
					
	  		$(document).ready(function() {

	           $("#fieldworkTable").tablesorter({
						textExtraction:getTextExtractor(),	
						sortList: [[0,0],[1,0]]
                }); 
                
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
	        }); 

  		</script>

HTML;
require_once '../includes/htmlFooter.php';

