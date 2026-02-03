<?php

$sub = "";  //used to track current subdirectory
$dirHtml = '<ul style="list-style-type: none;">';  //a hidden list to hold the page paths


$subParts = explode("/", $_GET["sub"]);
$vol = $subParts[1];
$dir = "vol/{$vol}/";

if ($_GET["sub"]) {
	$sub = trim($_GET["sub"], '/') . '/';
}
$files = scandir($dir);
sort($files, SORT_NATURAL);

$coverJS = in_array("C_1.jpg", $files) ? "[{ width: 800, height: 1200, uri: '{$dir}C_1.jpg' }]," : "";
$rearCoverJS = in_array("C_2.jpg", $files) ? "[{ width: 800, height: 1200, uri: '{$dir}C_2.jpg' }]" : "";
$insideCoverJS = in_array("DA_1.jpg", $files) ? "[{ width: 800, height: 1200, uri: '{$dir}DA_1.jpg' },"
	: "[{},"; //if there's no inside cover then add a blank placeholder for the 'first' page

$pageListJS = "";
$open = false;
foreach ($files as $file) {
	if (substr($file, 0, 2) == "TD") {
		$page = str_replace(array("TD_", ",jpg"), "", $file);
		//odd pages
		if ($page % 2) {
			$pageListJS .= <<<JS
            { 
              width: 800, height: 1200,
              uri: '{$dir}{$file}' },
            ],
JS;
			$open = false;
		} else {
			$pageListJS .= <<<JS
				[
				  { 
  						width: 800, height: 1200,
              uri: '{$dir}{$file}' },
JS;
			$open = true;
		}
	}
}

if ($open) { $pageListJS .= '],'; }
//$pageListJS = trim($pageListJS, ',');

echo <<<HTML

<!DOCTYPE html>
<html>
<head>
	<title>Gairm Volume {$sub}</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="apple-mobile-web-app-capable" content="yes">

	<!-- JS dependencies -->
	<script src="bookreader/BookReader/webcomponents-bundle.js"></script>
	<script src="bookreader/BookReader/jquery-3.js"></script>


	<!-- BookReader and plugins -->
	<link rel="stylesheet" href="bookreader/BookReader/BookReader.css"/>
	<script src="bookreader/BookReader/BookReader.js"></script>

	<!-- Mobile nav plugin -->
	<script src="bookreader/BookReader/plugins/plugin.mobile_nav.js"></script>

	<!-- URL-changing plugin -->
	<script src="bookreader/BookReader/plugins/plugin.url.js"></script>

	<style>
      html, body { width: 100%; height: 100%; margin: 0; padding: 0; }
      #BookReader { width: 100%; height: 100%; }
	</style>
</head>
<body>
<div id="BookReader"></div>
	<script>
		instantiateBookReader('#BookReader')

    // Create the BookReader object
    function instantiateBookReader(selector, extraOptions) {
      selector = selector || '#BookReader';
      extraOptions = extraOptions || {};
      var options = {
        data: [
					{$coverJS}
					{$insideCoverJS}
          {$pageListJS}
          {$rearCoverJS}
        ],

        // Book title and the URL used for the book title link
        bookTitle: 'Gairm',

        // thumbnail is optional, but it is used in the info dialog
        thumbnail: '//archive.org/download/BookReader/img/page014.jpg',
        // Metadata is optional, but it is used in the info dialog
        metadata: [
          {label: 'Title', value: 'Open Library BookReader Presentation'},
          {label: 'Author', value: 'Internet Archive'},
          {label: 'Demo Info', value: 'This demo shows how one could use BookReader with their own content.'},
        ],

        // Override the path used to find UI images
        imagesBaseURL: 'bookreader/BookReader/images/',

        ui: 'full', // embed, full (responsive)

        el: selector,
      };
      $.extend(options, extraOptions);
      var br = new BookReader(options);
      br.init();
    }
	</script>
</body>
</html>
HTML;

?>

<?php

/*
require_once '../includes/include.php';

//error_reporting(E_ALL); ini_set('display_errors', '1');

$pageSlug = "gairmPage";
$pageTitleElems = array("en" => "Read Gairm", "gd" => "Read Gairm");
$pageTitle = $pageTitleElems[$lang];

$cqpPage = true;

$javascriptBlock = <<<HTML
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
	<script src="https://kit.fontawesome.com/0b481d2098.js" crossorigin="anonymous"></script>
	<script src ="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" src="{$webPath}/js/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="{$webPath}/js/dasg_gairm.js"></script>
HTML;

require_once '../includes/htmlHeader.php';


//TEMP : disable Gaelic for now
$lang = "en";


$sub = "";  //used to track current subdirectory
$dirHtml = '<ul style="list-style-type: none;">';  //a hidden list to hold the page paths
$dir = $_GET["sub"] ? getcwd() . "/" . $_GET["sub"] : getcwd();
if ($_GET["sub"]) {
	$sub = trim($_GET["sub"], '/') . '/';
}
$files = scandir($dir);
sort($files, SORT_NATURAL);
$i = 0;
$firstFile = "";
$pageIndex = 1;
foreach ($files as $file) {
	$parts = pathinfo($file);
	if ($parts["extension"] == "php" || mb_substr($file, 0, 1) == ".") {continue;}
	if ($i == 0) {
		$firstPage = $file;
	}
	//write out HIDDEN file links
	$i++;
	if ($file == $_GET["p"]) {
		$pageIndex = $i;
	}
	$dirHtml .= <<<HTML
			<li><a href="#" id="file_{$i}" class="pdfLink" data-url="{$sub}{$file}" data-index={$i}>{$file}</a></li>
HTML;
}
//load the file indicated from the URL, else the first file in the directory
$pathToPage = $_GET["p"] ? $sub . $_GET["p"] : $sub . $firstPage;
$fileCount = $i;
$dirHtml .= "</ul>";

$hideLeftArrow = $pageIndex == 1 ? "display:none;" : "";
$hideRightArrow = $pageIndex == $fileCount ? "display:none;" : "";

//get the GairmRecord object(s) for this page
$volume = str_ireplace("vol/", "", $_GET["sub"]);
$records = GairmRecords::getGairmRecordByVolAndPage($volume, $_GET["p"]);

foreach ($records as $record) {
	$transcription .= $record->getTranscriptionForFile($_GET["p"]);
}
//mark any search terms in the transcription
if ($_GET["s"]) {
	$transcription = preg_replace('/('.$_GET["s"] . ')/iu', '<span class="hi">' . "$1" . '</span>', $transcription);
}

$reproduceText = array(
	"en" => "<span style='font-size:10pt;'>No part of <em>Gairm Online</em> – either the images and transcribed text – can be reproduced without first contacting <a title='Email DASG' href='mailto:mail@dasg.ac.uk'>DASG</a>.</span>",
	"gd" => "<span style='font-size:10pt;'>Chan fhaodar gin sam bith de Ghairm Air-loidhne – na h-ìomhaighean no an teacsa tar-sgrìobhte – ath-riochdachadh gun faighneachd ro làimh do <a title='Email DASG' href='mailto:mail@dasg.ac.uk'>DhASG</a>.</span>"
);
////main HTML body
$backText = array("en" => "back to Gairm Browse", "gd" => "[gd]back to Gairm Browse");
echo <<<HTML
	<div class="container-fluid">
		<div class="row">
			<p><a href="{$webPath}/gairm/browse" title="Back to Gairm"><-- {$backText[$lang]}</a></p>
		</div>
		<div class="row" style="margin-bottom: 10px;">
			{$reproduceText[$lang]}
		</div>
		<div class="row">
	
			<!-- the file list - hidden as it's used only for hooks to each page-->
			<div style="height: 100%; display:none;">
				{$dirHtml}
			</div>
	
			<div> <!-- RHS column -->
	
				<div style="100%;">
					<!-- back arrow -->
					<div style="width:35px; float:left;">
						<a href="#" class="prevPdf" style="{$hideLeftArrow}"><i class="fas fa-2x fa-arrow-left"></i></a>
					</div>
					<!-- forward arrow -->
					<div style="width:35px; float:right;">
						<a href="#" class="nextPdf" style="{$hideRightArrow}"><i class="fas fa-2x fa-arrow-right"></i></a>
					</div>
					
					<!-- the PDF viewer and the transcription -->
					<div style="width:970px;">
						<!-- the transcription -->
						<div id="transcription" style="float:right; width:300px;">
							{$transcription}
						</div>
						<!-- the viewer -->
						<div style="width:40vh; margin-left:60px; padding:0;">					
							<object style="width:38vh; height:65vh;" id="pdfViewer" data="{$pathToPage}#zoom=100" data-index={$pageIndex} 
								data-search={$_GET["s"]} type="application/pdf">
								cannot display file
							</object>
						</div>
					</div>
				
				</div>
	
			</div>  <!-- end RHS -->
	
		</div>  <!-- end of container row -->
	
	</div>  <!-- end of container -->

<script>
  $(function() {
    
    $('.nextPdf').on('click', function() {
      $('.prevPdf').show();
      var index = $('#pdfViewer').attr('data-index');
      var nextIndex = parseInt(index) + 1;
      var url = $('#file_'+nextIndex).attr("data-url");
      $('#pdfViewer').attr('data-index', nextIndex);
      $('#pdfViewer').attr('data', url);
      writeTranscription(url);
      checkForFinal(nextIndex);
    });

    $('.prevPdf').on('click', function() {
      $('.nextPdf').show();
      var index = $('#pdfViewer').attr('data-index');
      var prevIndex = parseInt(index) - 1;
      var url = $('#file_'+prevIndex).attr("data-url");
      $('#pdfViewer').attr('data-index', prevIndex);
      $('#pdfViewer').attr('data', url);
      writeTranscription(url);  
      checkForFirst(prevIndex);
    });

  });
  
  function checkForFirst(index) {
    if (index == 1) {
      $('.prevPdf').hide();
    }
  }

  function checkForFinal(index) {
    if (index == {$fileCount}) {
      $('.nextPdf').hide();
    }
  }


</script>

HTML;

include_once '../includes/htmlFooter.php';
*/