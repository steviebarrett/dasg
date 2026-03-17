<?php

//ini_set("display_errors", 1);

require_once '../includes/include.php';

// CSRF protection
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
    Csrf::validateRequest();
}

// initalise page variables
$lenitedHtml = $accentSelectedHtml = $searchAllHtml = $searchHeadHtml = $searchDefHtml = $resultsHtml = $query = "";

$pageTitle = "Search the Fieldwork Archive";
$pageSlug = "fieldwork";

$id = isset($_GET["id"]) ? Functions::e(["id"]) : "";
$q = isset($_GET["q"]) ? Functions::e($_GET["q"]) : "";
$l = isset($_GET["l"]) ? Functions::e($_GET["l"]) : "";
$as = isset($_GET["as"]) ? Functions::e($_GET["as"]) : "";
$searchScope = isset($_GET["searchScope"]) ? Functions::e($_GET["searchScope"]) : "";

$javascriptBlock = <<<HTML

    <script type="text/javascript" src="/js/jquery.slides.min.js"></script>
	<script type="text/javascript">
	
		var id = "{$id}";
		
		$(document).ready(function() {
		
            $('#slideshowSlides').slidesjs({
                width: 450,
                height: 450,
                navigation: {
                  active: false
                },
                pagination: {
                  active: false
                },
                play: {
                  active: false,
                  auto: true,
                  interval: 4000,
                  swap: true,
                  effect: "fade"
                },
                effect: {
                    fade: {
                        speed: 1500,
                        crossfade: true
                    }
                }
            });
            
            $('#reset').on('click', function() {
               $('#q').val("");
               $('#q').focus();
               $('#lenited').prop('checked', false);
               $('#accInsens').prop('checked', false);
               $('#searchScopeAll').prop('checked', true);
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
        	
        	if (id != '')
				goToByScroll(id);
				
        });
        
        function updateFieldworkDoc(url,id,headword,query,resultId)
        {
            $.ajax({url:url, dataType:"html"})
            .done(function(data) {
                //colour code the headword
                data = data.replace('<a id="'+id+'"/>'+headword, '<a id="'+id+'"/><span class="hi">'+headword+'</span>');
                //colour code the queried text
                data = highlightWords(data, query);
				$('#contentContainer').hide();
                $('#documentContainer').html(data);
				$('#documentContainer').show();
                goToByScroll(id);
            });
			$('.backLink').prop("href", "#");
            $('.backLink').prop("onclick", "$('#documentContainer').hide();$('#contentContainer').show();goToByScroll('"+resultId+"');");
            $('.backToResults').show();
        }
        
        function goToByScroll(id)
        {
			var idPos = $("#"+id).offset().top;
            $('html,body').animate({scrollTop: idPos-30},'slow');
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

$enteredQuery = $q;

$slidesHtml = "";

$slides = array(
    "7" => array("Edinburgh C Ferguson moine","",""),
    "1" => array("Ardhasaig D J MacLeod wool","", ""),
    "3" => array("Campbeltown M MacLeod moine","",""),
    "4" => array("Clachan N MacDonald taigh","",""),
    "5" => array("Clarkston R MacNeil aiteach","",""),
    "6" => array("Edinburgh C Ferguson moine","",""),
    "8" => array("Glasgow M MacLeod drawings","",""),
    "9" => array("Glasgow M MacLeod drawings","",""),
    "10" => array("Glasgow M MacLeod drawings","",""),
    "11" => array("Glasgow M MacLeod sheep","",""),
    "12" => array("Harris Gobhaig MacLennan eathar","",""),
    "13" => array("Lewis John Smith et al iasgach","",""),
    "14" => array("Lewis John Smith et al iasgach","",""),
    "15" => array("Lewis John Smith et al iasgach","",""),
    "16" => array("Lewis John Smith et al iasgach","",""),
    "17" => array("Lewis John Smith et al iasgach","",""),
    "18" => array("Lionel Junior Secondary personal misc","",""),
    "19" => array("Tiree Barrapol H MacLean agriculture","",""),
    "20" => array("Tiree Barrapol H MacLean agriculture","",""),
    "21" => array("Tiree Barrapol H MacLean agriculture","","")
);

foreach ($slides as $filename => $item) {
    
    $pageName = str_replace(' ', '', $item[0]);
    $encodedItem = base64_encode($pageName . "|{$item[1]}" . "|{$item[2]}");
    
    $slidesHtml .= <<<HTML
		\n<a href="/fieldwork/view/{$encodedItem}" title="DASG Fieldwork Item">
        \n    <img src="/images/slides/{$filename}.jpg" alt="DASG Fieldwork Image"/>
        \n</a>
        
HTML;
}

if ($l) {
    $lenitedHtml = "checked=\"checked\"";
}
if ($as) {
    $accentSelectedHtml = "checked=\"checked\"";
}
$searchAllHtml = $searchHeadHtml = $searchDefHtml = "";
if (empty($searchScope)) {
    $searchAllHtml = 'checked="checked"';
}
if ($searchScope) {
    switch ($searchScope) {
        case 'all':
            $searchAllHtml = 'checked="checked"';
            break;
        case 'headOnly':
            $searchHeadHtml = 'checked="checked"';
            break;
        case 'defOnly':
            $searchDefHtml = 'checked="checked"';
            break;
    }
}

echo <<<HTML

    <div id="dasg_cqp_main_menu">
        <div class="menuItem menuSelected">
            <a href="/fieldwork/search">Search</a>
        </div>
        <div class="menuItem">
            <a href="/fieldwork/browse">Browse</a>
        </div>
		<br class="clear"/>
    </div>
    
	<div class="backToResults clear">
        <a class="backLink" href="/fieldwork/search?q={$q}" title="Back to results">
            <br/>&lt; Back to results</a>
    </div>
    <div id="documentContainer">
    </div>
    <div id="contentContainer">
        <div>
            <br/>
            <h2>Search</h2>
            <div id="fieldworkDoc">
                <div id="slideshowContainer">
                    <div id="slideshowSlides">
                 		{$slidesHtml}
                    </div>
                </div>
            </div>
            <div class="searchColumn">
                <div id="fieldworkSearchBox">
                    <form action="">
                        <input type="text" name="q" id="q" value="{$enteredQuery}"/>
                        <br/>
                        <div id="accentsBox">
                            <a href="#" onclick="addCharacterToSearch('à', 'q');return false;">à</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('è', 'q');return false;">è</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('ì', 'q');return false;">ì</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('ò', 'q');return false;">ò</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('ù', 'q');return false;">ù</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('á', 'q');return false;">á</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('é', 'q');return false;">é</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('í', 'q');return false;">í</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('ó', 'q');return false;">ó</a>&#160;&#160;
                            <a href="#" onclick="addCharacterToSearch('ú', 'q');return false;">ú</a>
                        </div>
                        <div id="wildcards">
                        	<ul id="wildcardList">
                        		<li>
                        			<a href="#" onclick="addCharacterToSearch('?', 'q');return false;">?</a> - Any single letter
                        		</li>
                        		<li>
                        			<a href="#" onclick="addCharacterToSearch('~', 'q');return false;">~</a> - Any sequence of vowels
                        		</li>
                        		<li>
                        			<a href="#" onclick="addCharacterToSearch('*', 'q');return false;">*</a> - Sequence of any letters
                        		</li>
                        	</ul>
                        </div>
                        <label for="lenited">Lenited forms:</label>
                        <input type="checkbox" id="lenited" name="l" {$lenitedHtml}/>&#160;&#160;&#160;&#160;
                        <label for="accInsens">Accent insensitive:</label>
                        <input type="checkbox" id="accInsens" name="as" {$accentSelectedHtml}/>
                        <fieldset>
                            <legend>Search Scope</legend>
                            <ul>
                                <li>
                                    <label for="searchScopeAll">All:</label>
                                    <input type="radio" id="searchScopeAll" name="searchScope" value="all" {$searchAllHtml}/>&nbsp;
                                </li>
                                <li>
                                    <label for="searchScopeHead">Headwords Only:</label>
                                    <input type="radio" id="searchScopeHead" name="searchScope" value="headOnly" {$searchHeadHtml}/>&nbsp;
                                </li>
                                <li>
                                    <label for="searchScopeDef">Definitions Only:</label>
                                    <input type="radio" id="searchScopeDef" name="searchScope" value="defOnly" {$searchDefHtml}/>
                                </li>
                            </ul>
                        </fieldset>
                        <br/>
                        <input id="search" class="dasg_medButton" type="submit" value="search"/>&#160;&#160;
                            <input id="reset" class="dasg_smlButton" type="button" value="reset"/>
                    </form>
                </div>
                <img id="loading" alt="Loading..." src="/images/loading.gif" height="100px"/>
HTML;
                 		
                 		
//compare result count for uasort
/*function compare_count($a, $b)
 {
 if ($a["count"] == $b["count"]) {
 return 0;
 }
 return ($a["count"] > $b["count"]) ? -1 : 1;
 }
 */

if (!empty($enteredQuery)) {
    
    $query = mb_strtolower($enteredQuery, "UTF-8");

    //replace non-smart apostrophes
    $query = str_replace("'", "’", $query);
    
    if ($l) {
        $query = Functions::getLenited($query);
    }
    
    if ($as) {
        $query = Functions::getAccentInsensitive($query);
    }
    
    $query = trim($query); 	//remove any trailing whitespace
    $query = str_replace("'", "â€™", $query);			//replace any single quote with smart quote (as used in XML source docs)
    $query = str_replace("~", "[" . ACCENT_VOWELS . "]+", $query);
    $query = str_replace("?", "[" . ACCENT_CHARSET . "]", $query);
    $query = str_replace("*", "[" . ACCENT_CHARSET . "]*", $query);
    $query = str_replace("h=", "h?", $query);
    
    //original query, pre Bauerbug
    //$queryRegExp = "(\W{$query}\W)|(^{$query}$)";
    
    // Test change to solve Bauer-apostrophe bug
    //	$queryRegExp = "({$query}â€™|[^" . ACCENT_CHARSET . "]{$query}[^" . ACCENT_CHARSET . "])|(^{$query}$)|(^{$query}[^" . ACCENT_CHARSET . "])|([^" . ACCENT_CHARSET . "]{$query}$)";
    $queryRegExp = "(^{$query}â€™|[^" . ACCENT_CHARSET . "]{$query}[^" . ACCENT_CHARSET . "])|(^{$query}$)|(^{$query}[^" . ACCENT_CHARSET . "])|([^" . ACCENT_CHARSET . "]{$query}$)";
    $queryRegExpJS = "{$query}[^a-z0-9<]";	//Specific RegExp for JS to avoid issues with text highlighting
    
    $itemQ = "*//item";
    
    $results = array();
    //Note some duplicated code in loading the XML files- see browse.php
    foreach (glob($_SERVER['DOCUMENT_ROOT'] . "/xml/archive/*.xml") as $filepath) {
        
        $xml = simplexml_load_file($filepath);
        
        $itemNodes = $xml->xpath($itemQ);
        $hitNodes = array();


        /*
        foreach ($itemNodes as $node) {
            $queryRegExp = preg_quote($queryRegExp, '/');

            if ($_GET["searchScope"] == "headOnly") {
                if (preg_match("/({$queryRegExp})/iu", (string)$node->headword)) {
                    $hitNodes[] = $node;
                }
            } else if ($_GET["searchScope"] == "defOnly") {
                if (preg_match("/({$queryRegExp})/iu", (string)$node->description)) {
                    $hitNodes[] = $node;
                }
            } else {
                if (preg_match("/({$queryRegExp})/iu", (string)$node->description) || preg_match("/({$queryRegExp})/iu", (string)$node->headword)) {
                    $hitNodes[] = $node;
                }
            }
        }
        */
        try {
            $queryRegExp = Functions::buildFieldworkWildcardRegex($query);
        } catch (RuntimeException $e) {
            Functions::writeError("Regex query too large");
            require_once '../includes/htmlFooter.php';
            exit;
        }

        $pattern = "/({$queryRegExp})/iu";

        foreach ($itemNodes as $node) {

            if ($_GET["searchScope"] === "headOnly") {
                if (preg_match($pattern, (string)$node->headword)) {
                    $hitNodes[] = $node;
                }
            } elseif ($_GET["searchScope"] === "defOnly") {
                if (preg_match($pattern, (string)$node->description)) {
                    $hitNodes[] = $node;
                }
            } else {
                if (
                    preg_match($pattern, (string)$node->description) ||
                    preg_match($pattern, (string)$node->headword)
                ) {
                    $hitNodes[] = $node;
                }
            }
        }
        
        $count = count($hitNodes);
        
        if ($count) {
            foreach ($hitNodes as $node) {
                $headword = (string) $node->headword;
                //get the informant info
                $origins = array();
                foreach ($xml->informant as $informant) {
                    if (!empty($informant->origin)) {
                        $origins[] = $informant->origin;
                    }
                }
                $originText = implode(" or ", $origins);
                $id = $node->headword["id"];
                $description = (string) $node->description;
                $filenameParts = explode('/', $filepath);
                $filename = array_pop($filenameParts);
                $item = str_replace(".xml", "", $filename);
                $url = "/ajax/fieldworkItem.php?item=" . $item;
                $results[] = array("headword"=>$headword, "origins"=>$originText, "location"=>$xml->location, "title"=>$xml->title, "url"=>$url, "id"=>$id, "description"=>$description,
                    "filename"=>$filename);
            }
        }
    }
    $numHits = count($results);
    
    if ($numHits == 0) {
        $resultsHtml = "<h3>There were no results for {$enteredQuery}</h3>";
        
    } else {
        //generate results HTML
        if ($numHits > 1) {
            $resultsHtml = "<h3>There were {$numHits} hits for {$enteredQuery}</h3>";
        } else {
            $resultsHtml = "<h3>There was 1 hit for {$enteredQuery}</h3>";
        }
        
        //	uasort($results, "compare_count");
        
        $resultsHtml .= "<dl id=\"fieldworkResults\">";
        
        $i=0;
        
        asort($results);	//sort the results alphaetically by headword
        
        foreach ($results as $result) {
            $i++;
            $headword = $result["headword"];
            
            if ($headword == "") {
                $headwordHtml = "--blank--";
            } else {
                $query = preg_quote($query, '/');
                $headwordHtml = preg_replace("/({$query})/iu", '<span class="hi">' . "$1" . '</span>', $headword);
            }

            $queryRegExp = preg_quote($queryRegExp, '/');
            $description = $_GET["searchScope"] == "headOnly" ? "" : $description = preg_replace("/({$queryRegExp})/iu", '<span class="hi">' . "$1" . '</span>', $result["description"]);
            $resultId = "result{$i}";
            $filename = str_replace(".xml", "", $result["filename"]);
            $encodedItem = base64_encode($filename . "|{$headword}|{$result["id"]}||{$enteredQuery}|r{$i}|{$l}|{$as}|{$searchScope}");
            $geogHtml = "";
            if (!empty($result["origins"])) {
                $geogHtml = "<strong>Origin:</strong> <em>{$result["origins"]}</em> <br/>";
            } else if (!empty($result["location"])){
                $geogHtml = "<strong>Location:</strong> <em>{$result["location"]}</em> <br/>";
            }
            $resultsHtml .= <<<HTML
				<dt>
					<a id="r{$i}" href="/fieldwork/view/{$encodedItem}" class="fieldwork_result">
						{$headwordHtml}
					</a>
				</dt>
				<dd>
					{$description}
					<div class="fieldworkMeta">
						{$geogHtml}
						<strong>Category:</strong> <em>{$result["title"]}</em>
					</div>
				</dd>
HTML;
						
                 		        }
                 		        $resultsHtml .= "</dl>";
                 		    }
                 		}
                 		
                 		
                 		echo <<<HTML
                <div id="searchResults">
                    <div>{$resultsHtml}</div>
                </div>
            </div>
        </div>
        <br class="clear"/>
    </div>
    <a href="#" class="scrollToTop">^ Return To Top ^</a>
    <div class="backToResults clear">
        <a class="backLink" href="/fieldwork/search?q={$q}" title="Back to results">
            <br/>&lt; Back to results</a>
    </div>
    
    <script>
    	$(window).on('load', function () {
     		$('#loading').hide();
  		});
    </script>
HTML;
                 		
                 		
require_once '../includes/htmlFooter.php';
                 		
                 		