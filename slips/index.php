<?php

//ini_set("display_errors", 1);

require_once '../includes/include.php';

$titleText = array("en"=>"MSS Slips", "gd"=>"MSS Slips");

$pageTitle = $titleText[$lang];
$pageSlug = "slips";

$cqpPage = true;
$javascriptBlock = <<<HTML
	<script type="text/javascript" src="../ckeditor/ckeditor.js"></script>
	<script type="text/javascript" src="../js/jquery.tablesorter.min.js"></script>
	<script>
		function getTextExtractor()
		{
		  return (function() {
			var patternLetters = /[öäüÖÄÜáàâéèêíìúùûóòôÁÀÂÉÈÊÌÍÚÙÛÓÒÔß]/g;
			    var patternDateDmy = /^(?:\D+)?(\d{1,2})\.(\d{1,2})\.(\d{2,4})$/;
			    var lookupLetters = {
			      "ä": "a", "ö": "o", "ü": "u",
			      "Ä": "A", "Ö": "O", "Ü": "U",
			      "á": "a", "à": "a", "â": "a",
			      "é": "e", "è": "e", "ê": "e",
				  "í": "i", "ì": "i", "Ì": "I", "Í": "I",
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
		
		$(function() {
			$(".tablesorter").tablesorter({
				textExtraction: getTextExtractor(),
		        ignoreCase : true,
				widthFixed : false,
		        sortList: [[0,0]],
				headers: {
        			6: {
        				sorter: false
            		}
				}
		    });
		    
			$('#folder').change(function() {
				$('#selectSlip').empty();
				var folder = $(this).val();
				if (folder === 'showAll') {
					folder = '';
				}
				$('#selectSlip').append('<option value="">-- Select a slip --</option>');
				$.getJSON('../ajax/slips.php?action=getSlips&folder=' + folder, function(data) {
					$.each(data, function(key, val) {
						$('#selectSlip').append('<option value="' + key + '">' + val + '</option>');
					});
				});
			});
			
			$('#cat').change(function() {
				var cat = $(this).val();
				$('#q').html('<option value="">------------</option>');	//reset the category field
				$.getJSON('../ajax/slips.php?action=getCat&cat=' + cat, function(data) {
					$.each(data, function(key, val) {
						if (val == '') {
							val = 'n/a';
						}
						$('#q').append('<option value="' + key + '">' + val + '</option>');
					});
				});
				$('#q').show();
			});
		});
	</script>
HTML;

require_once '../includes/htmlHeader.php';

if (empty($_GET["page"])) {
    $_GET["page"] = "browse";
}
$browseSelected = $searchSelected = "";
if ($_GET["page"] == "browse") {
    $browseSelected = "menuSelected";
} else {
    $searchSelected = "menuSelected";
}
echo <<<HTML
    <div id="dasg_cqp_main_menu">
        <div class="menuItem {$browseSelected}">
            <a href="?page=browse">Browse</a>
        </div>
        <div class="menuItem {$searchSelected}">
            <a href="?page=search">Search</a>
        </div>
    </div>
HTML;

$queryString = "";
$action = $_GET["action"];
if (isset($action)) {
    $queryString = ($action === "search") ? "action=search&q={$_GET["q"]}&page={$_GET["page"]}" : "action=browse&cat={$_GET["cat"]}&q=" . urlencode($_GET["q"]);
}

if (!empty($_REQUEST["id"])) {								//assemble a record's info
    $id = $_REQUEST["id"];
    $slip = MssSlips::getSlip($id);
    $slipFound = ($slip["slip_found"] == 1) ? "yes" : "no";
    $dateRangeHtml = MssSlips::getSlipsDateRangeNameList($slip["id"]);
    
    /*   $headword = str_replace("|", ", ", $slip["headword"]);
     $page = str_replace("|", ", ", $slip["page"]);
     $title = str_replace("|", ", ", $slip["title"]);
     */
    $quotation = html_entity_decode($slip["quotation"]);
    $translation = html_entity_decode($slip["translation"]);
    /*    $quotations = explode("|", $quotation);
     $translations = explode("|", $translation);
     */
    $quotationLabel = "Quotation";
    $secondQuotationHtml = "";
    if (!empty($quotations[1])) {
        $quotation = $quotations[0];
        $quotationLabel = "Quotation <em>Black</em>";
        $secondQuotationHtml = <<<HTML
			<div>
				<label for="quotation2">Quotation <em>HDSG</em></label>
				<span id="quotation2">{$quotations[1]}</span>
			</div>
HTML;
    }
    
    $translationLabel = "Translation";
    $secondTranslationHtml = "";
    if (!empty($translations[1])) {
        $translation = $translations[0];
        $translationLabel = "Translation <em>Black</em>";
        $secondTranslationHtml = <<<HTML
			<div>
				<label for="translation2">Translation <em>HDSG</em></label>
				<span id="translation2">{$translations[1]}</span>
			</div>
HTML;
    }
    
    $notes = html_entity_decode($slip["notes"]);
    
    $backlinkHtml = isset($_GET["q"])
    ? '<a href="index.php?page=' . $_GET["page"] . '&' . $queryString . '" title="Back to Results">< Back to Results</a>'
        : '<a href="index.php?page=' . $_GET["page"] .'" title="Back to Slips Home">< Back to Slips Home</a>';
        
        //write the record's HTML
        echo <<<HTML
		<div id="slipData">
		
			<div class="backLink">
				{$backlinkHtml}
			</div>
			
			<div>
				<label for="folder">Folder</label>
				<span id="folder">{$slip["folder"]}</span>
			</div>
			<div>
				<label for="id">Slip ID</label>
				<span id="id">{$slip["id"]}</span>
			</div>
			<div>
				<label for="headword">Headword (Black)</label>
				<span id="headword">{$slip["headword"]}</span>
			</div>
            <div>
				<label for="headword_hdsg">Headword (HDSG)</label>
				<span id="headword_hdsg">{$slip["headword_hdsg"]}</span>
			</div>
			<div>
				<label for="slip_found">Slip Found</label>
				<span id="slip_found">{$slipFound}</span>
			</div>
			<div>
				<label for="quotation">Quotation (Black)</label>
				<span id="quotation">{$slip["quotation"]}</span>
			</div>
			<div>
				<label for="quotation_hdsg">Quotation (HDSG)</label>
				<span id="quotation_hdsg">{$slip["quotation_hdsg"]}</span>
			</div>
			<div>
				<label for="author">Author</label>
				<span id="author">{$slip["author"]}</span>
			</div>
			<div>
				<label for="title">Title 1</label>
				<span id="title">{$slip["title"]}</span>
			</div>
            <div>
				<label for="title2">Title 2</label>
				<span id="title2">{$slip["title_2"]}</span>
			</div>
            <div>
				<label for="volume">Title 1 Volume</label>
				<span id="volume">{$slip["volume"]}</span>
			</div>
            <div>
				<label for="volume2">Title 2 Volume</label>
				<span id="volume2">{$slip["volume_2"]}</span>
			</div>
			<div>
				<label for="page">Title 1 Page</label>
				<span id="page">{$slip["page"]}</span>
			</div>
            <div>
				<label for="page2">Title 2 Page</label>
				<span id="page2">{$slip["page_2"]}</span>
			</div>
			<div>
				<label for="date">Date</label>
				<span id="date">{$slip["date"]}</span>
			</div>
			
			<div>
				<label for="dateRange">Date Range</label>
				<span id="dateRange">{$dateRangeHtml}</span>
			</div>
			
			<div>
				<label for="notes">Notes</label>
				<span id="notes">{$notes}</span>
			</div>
			<div>
				<label for="translation">Translation (Black)</label>
				<span id="translation">{$slip["translation"]}</span>
			</div>
			<div>
				<label for="translation_hdsg">Translation (HDSG)</label>
				<span id="translation_hdsg">{$slip["translation_hdsg"]}</span>
			</div>
			<div>
				<label for="sense">Sense (Black)</label>
				<span id="sense">{$slip["sense"]}</sense>
			</div>
            <div>
				<label for="sense_hdsg">Sense (HDSG)</label>
				<span id="sense_hdsg">{$slip["sense_hdsg"]}</sense>
			</div>
			<div>
				<label for="edition">Edition</label>
				<span id="edition">{$slip["edition"]}</span>
			</div>
			
            <div>
                <a href="mailto:mail@dasg.ac.uk?subject=Slip-{$slip["id"]}">Contact us about this item</a>
            </div>
            
			<div class="backLink">
				{$backlinkHtml}
			</div>
			
		</div>
HTML;
				
} else if (isset($_GET["action"])) {	//user has selected browse or search
    
    if ($action === "browse") {
        
        switch ($_GET["cat"]) {			//get the results based on the selected category
            case "dateRange":
                $slips = MssSlips::searchByDateRange($_GET["q"]);
                break;
            case "author":
                $slips = MssSlips::searchByAuthor(urldecode($_GET["q"]));
                break;
            case "title":
                $slips = MssSlips::searchByTitle(urldecode($_GET["q"]));
                break;
        }
        
    } else if ($action === "search") {
        $preciseMatchOnly = $_GET["page"] == "on" ? true : false;
        $slips = MssSlips::searchSlips($_GET["q"], $preciseMatchOnly);
    }
    
    //fix dateRange data for colour coding
    $query = ($_GET["cat"] == "dateRange") ? MssSlips::getDateRangeName($_GET["q"]) : urldecode($_GET["q"]);
    //fix blank author for colour coding
    if ($_GET["cat"] == "author" && $_GET["q"] == "") {
        $query = "n\/a";
    }
    
    if (!count($slips)) {					//there are no results
        echo "<h3 class=\"error\">There were no results for {$_GET["q"]}</h3>";
    }
				}
				
				if (count($slips)) {	//there are slips to display as results
				    
				    echo <<<HTML
		<div class="backLink">
			<a href="index.php?page={$_GET["page"]}" title="Back to Slips Home">< Back to Slips Home</a>
		</div>
HTML;
				    $resultsHtml = "";
				    foreach ($slips as $slip) {
				        $author =  html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["author"]));
				        $title =  html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["title"]));
				        if (!empty($slip["title_2"]) && $slip["title_2"] != "n/a") {
				            $title .= " // " . html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["title_2"]));
				        }
				        $headword =  html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["headword"]));
				        if (!empty($slip["headword_hdsg"])) {
				            $headword .= ", " . html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["headword_hdsg"]));
				        }
				        $quotation =  html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["quotation"]));
				        if (!empty($slip["quotation_hdsg"]) && $slip["quotation_hdsg"] != "n/a") {
				            $quotation .= " // " . html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["quotation_hdsg"]));
				        }
				        $translation =  html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["translation"]));
				        if (!empty($slip["translation_hdsg"]) && $slip["translation_hdsg"] != "n/a") {
				            $translation .= " // " . html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', $slip["translation_hdsg"]));
				        }
				        // $dateRange = html_entity_decode(preg_replace("/$query/ui", '<span class="error">$0</span>', MssSlips::getSlipsDateRangeNameList($slip["id"])));
				        $resultsHtml .= <<<HTML
				<tr>
					<td>{$author}</td>
					<td>{$title}</td>
					<td>{$headword}</td>
					<td>{$quotation}</td>
					<td>{$translation}</td>
                    <td>{$slip["page"]}</td>
					<td>{$slip["date"]}</td>
					<td><a href="index.php?id={$slip["id"]}&{$queryString}">view</a></td>
				</tr>
HTML;
				    }
				    echo <<<HTML
			<table class="tablesorter">
				<thead>
					<tr>
						<th>Author</th>
						<th>Title(s)</th>
						<th>Headword(s)</th>
						<th>Quotation(s)</th>
						<th>Translation(s)</th>
                        <th>Page</th>
						<th>Date</th>
						<th>Link</th>
					</tr>
				</thead>
				<tbody>
					{$resultsHtml}
				</tbody>
			</table>
HTML;
					
					echo <<<HTML
		<div class="backLink">
			<a href="index.php?page={$_GET["page"]}" title="Back to Slips Home">< Back to Slips Home</a>
		</div>
HTML;
				} else if (!$_REQUEST["id"]) {								//show the form if no ID requested
				    
				    //assemble folder select html
				    $folders = MssSlips::getAllFolders();
				    $folderOptionHtml = <<<HTML
				<option value="showAll">-- Show All Slips --</option>
HTML;
				    foreach ($folders as $sortOrder => $folderName) {
				        $folderOptionHtml .= '<option value="' . $folderName . '">' . $folderName . '</option>';
				    }
				    $folderSelectHtml = <<<HTML
				<label for="folder">Folder</label>
				<select name="folder" id="folder">
					{$folderOptionHtml}
				</select>
HTML;
					
					$slips = MssSlips::getAllHeadwords();
					$formHtml = "<option selected=\"selected\" value=\"\">-- Select a slip --</option>";
					foreach ($slips as $id=>$headword) {
					    $formHtml .= <<<HTML
				<option value="{$id}">{$headword}</option>
HTML;
					}
					
					if ($_GET["page"] == "browse") {
					    echo <<<HTML
            <div class="clear">
                <br/>
                <h3>Browse Slips:</h3>
				<h4>By Folder:</h4>
				<form method="GET" action="index.php">
					{$folderSelectHtml}
					<label for="selectSlip">Slip</label>
					<select name="id" id="selectSlip">
						{$formHtml}
					</select>
					<input type="submit" value="view"/>
				</form>
				<form method="GET" action="index.php">
				
					<h4>By Category:</h4>
					<select name="cat" id="cat">
						<option>------------</option>
						<option value="author">Author</option>
						<option value="title">Title</option>
						<option value="dateRange">Date Range</option>
					</select>
					
					<select id="q" name="q" class="hide">
						<option value="">------------</option>
					</select>
					
					<input type="submit" name="action" value="browse"/>
				</form>
            </div>
HTML;
					} else if ($_GET["page"] == "search") {
					    echo <<<HTML
            <div class="clear">
                <br/>
				<h3>Search Slips:</h3>
				<form method="GET" action="index.php">
					<input type="text" name="q"/>
					<label for="preciseMatchOnly">Precise matches only</label>
                    <input type="hidden" name="page" value="{$_GET["page"]}"/>
					<input type="checkbox" id="preciseMatchOnly" name="p"/>
					<input type="submit" name="action" value="search"/>
				</form>
            </div>
HTML;
					}
					}
					
					require_once '../includes/htmlFooter.php';
					