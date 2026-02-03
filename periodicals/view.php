 <?php

//error_reporting(E_ALL); ini_set('display_errors', '1');

require_once '../includes/include.php';

require_once ROOT . '/classes/PeriodicalRecord.php';
require_once ROOT . '/classes/PeriodicalRecords.php';

$titleText = array("en"=>"View Periodical Record", "gd"=>"View Periodical Record");

$pageTitle = $titleText[$lang];
$pageSlug = "periodicalView";

$cqpPage = true;
$results = array();
$queryString = $resultsTagline = $encodedQuery = "";
$javascriptBlock = <<<HTML
    <style>
        #periodicalRecordFull dt, #periodicalRecordFull dd {
        	display:block;
        	float:left;
        	padding:10px;
        }
        
        #periodicalRecordFull dt {
            font-weight: bold;
        	clear:left;
        	width:8em;
        	text-align:right;
        	font-size:12pt;
        }
        
        #periodicalRecordFull dd {
        	font-size:14pt;
        }

        #periodicalComments {
        	width: 40em;
            height: 7em;
            font-family:"Helvetica Neue", Helvetica, "segoe ui symbol", sans-serif;
        }
    </style>

HTML;

require_once '../includes/htmlHeader.php';
	
$queryString = urldecode($_REQUEST["query"]);

$id = $_REQUEST["id"];
	
//get the entry object
$periodicalRecord = PeriodicalRecords::getPeriodicalRecord($id);

$backHtml = <<<HTML
	<a href="index.php?{$queryString}" title="Back to browse">< Back to browse</a>
HTML;
$periodicalId = $periodicalRecord->getPeriodicalId();
$periodical = PeriodicalRecords::getPeriodicalById($periodicalId);
$periodicalsHtml = <<<HTML
    {$periodical["title"]}: {$periodical["volume"]}</option>
HTML;

$idHtml = '<dt>ID:</dt><dd>' . $id . '</dd>';

$escapedTitle = htmlentities($periodicalRecord->getTitle());
$recordHtml = <<<HTML
<div>
	<dl id="periodicalRecordFull">
        {$idHtml}
        <dt>Periodical:</dt>
        <dd>{$periodicalsHtml}</dd>
        <dt>Part:</dt>
		<dd>{$periodicalRecord->getPart()}</dd>
        
		<dt>Title:</dt>
		<dd>{$escapedTitle}</dd>
		<dt>Last Name:</dt>
		<dd>{$periodicalRecord->getLastName()}</dd>
		<dt>First Name:</dt>
		<dd>{$periodicalRecord->getFirstName()}</dd>
        <dt>Origin (Gaelic):</dt>
		<dd>{$periodicalRecord->getOriginGD()}</dd>
		<dt>Origin (English):</dt>
		<dd>{$periodicalRecord->getOrigin()}</dd>
        <dt>Month Of Publication:</dt>
		<dd>{$periodicalRecord->getMonthOfPublication()}</dd>
		<dt>Year Of Publication:</dt>
		<dd>{$periodicalRecord->getYearOfPublication()}</dd>
		<dt>First Page:</dt>
		<dd>{$periodicalRecord->getFirstPage()}</dd>
		<dt>Last Page:</dt>
		<dd>{$periodicalRecord->getLastPage()}</dd>
		<dt>Language:</dt>
		<dd>{$periodicalRecord->getLanguage()}</dd>
		<dt>Type:</dt>
        <dd>{$periodicalRecord->getType()}</dd>
		<dt>Genre:</dt>
		<dd>{$periodicalRecord->getGenre()}</dd>
		<dt>Register:</dt>
        <dd>{$periodicalRecord->getRegister()}</dd>
		<dt>Comments:</dt>
		<dd>{$periodicalRecord->getComments()}</dd>
	</dl>
</div>
HTML;
		
echo $backHtml;
echo $recordHtml;
	
require_once '../includes/htmlFooter.php';
