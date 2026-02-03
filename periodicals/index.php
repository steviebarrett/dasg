<?php

//error_reporting(E_ALL); ini_set('display_errors', '1');

require_once '../includes/include.php';

require_once ROOT . '/classes/PeriodicalRecord.php';
require_once ROOT . '/classes/PeriodicalRecords.php';

$titleText = array("en"=>"Periodicals", "gd"=>"Periodicals");

$pageTitle = $titleText[$lang];
$pageSlug = "periodicals";

$cqpPage = true;
$results = array();
$queryString = $encodedQuery = $resultsHtml = "";
$javascriptBlock = <<<HTML
	<script type="text/javascript" src="/js/jquery.tablesorter.min.js"></script>
HTML;

require_once '../includes/htmlHeader.php';


$queryString = $_GET["periodical"];
$encodedQuery = urlencode($_SERVER['QUERY_STRING']);

/*
 * periodicals
 */
$periodicals = PeriodicalRecords::getPeriodicals();
$periodicalSelectHtml = '<select id="periodical" name="periodical"><option value="">-- Browse By Periodical --</option>';
foreach ($periodicals as $id => $periodical) {
    $periodicalSelected =  ($queryString == $id) ? " selected " : "";
    $periodicalSelectHtml .= '<option value="' . $id . '"' . $periodicalSelected . '>' . $periodical["title"] . ": " . $periodical["volume"] . '</option>';
}
$periodicalSelectHtml .= '</select>';

//write periodical select dropdown
echo <<<HTML
	<div class="periodicalCategory">{$periodicalSelectHtml}</div>
HTML;

if (!empty($queryString)) {
    
    $results = PeriodicalRecords::browseRecords($queryString, array("periodicalId"));
    
    if (count($results) == 0) {
        $resultsHtml = "<h2>There were no results for your query</h2>";
    } else {
        $resultsHtml = <<<HTML
		<table id="periodicalResultsTable" class="tablesorter">
			<thead>
				<tr>
					<th>ID</th>
					<th>Title</th>
					<th>Last Name</th>
					<th id="firstNameCol">First Name</th>
					<th>Type</th>
                    <th>Link</th>
				</tr>
			</thead>
			<tbody>
HTML;
        foreach ($results as $id => $periodicalRecord) {
            $resultsHtml .= <<<HTML
				<tr>
					<td>{$periodicalRecord->getId()}</td>
					<td>{$periodicalRecord->getTitle()}</td>
					<td>{$periodicalRecord->getLastName()}</td>
					<td>{$periodicalRecord->getFirstName()}</td>
					<td>{$periodicalRecord->getType()}</td>
                    <td><a href="view.php?id={$id}&query={$encodedQuery}" title="View record">view</a></td>
HTML;
        }
        $resultsHtml .= <<<HTML
			</tbody>
		</table>
		
		<div id="periodicalRecord"></div>
HTML;
    }
}

echo <<<HTML
        <div id="periodicalSearchResults">
            {$resultsHtml}
        </div> <!-- end periodicalSearchResults -->
HTML;
            
/*
 * Javascript
 */
echo <<<JS
	<script>
		$(function() {
		
			$("#periodicalResultsTable").tablesorter({
				sortList: [[0,0]],
				headers: {
		        	5: {
		        		sorter: false
		            },
					6: {
		        		sorter: false
		            }
				}
			});
			
			$('#periodical').change(function() {
				window.location.href = 'index.php?periodical=' + $(this).val();
			});
		});
	</script>
JS;
            
require_once '../includes/htmlFooter.php';
            