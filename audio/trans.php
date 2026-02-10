<?php

$pageTitle = "Cluas ri Claisneachd";
$pageSlug = "audio";
$audioSlug = "Search";

$cqpPage = true;

$javascriptBlock = <<<HTML
<script src="/js/jquery.tablesorter.min.js"></script>
<script>
	$(document).ready(function() {
		
		sortTable();

        $('#search_metadata').on('click', function() {
			window.location.href = "/audio/search/s/{$_GET["archive"]}/";
		});
		
		$('#searchReset').on('click', function() {
			$('#searchTerm').val('');
		});
	});
		
	function sortTable() {
		$("#audioTable").tablesorter({
			sortList: [[0,0]],
			headers: { 
	        	5: { 
	        		sorter: false 
	            }
			}
		}); 
	}
		
</script>
<script src="/js/jquery.caret.js"></script> 
<script src="/js/functions.js"></script>
<script src="/audio/includes/audioJavascript.js"></script>
HTML;
		
require_once '../includes/include.php';
require_once '../includes/htmlHeader.php';
require_once 'includes/audioVars.php';
foreach ($archives as $abbr => $name) {
    if(!AudioItems::archiveHasTranscriptions($abbr)) {
        $hideArchives[] = $abbr;            //hide archives with no transcriptions
    }
}
require_once 'includes/audioHeader.php';
$searchDomain = "transcriptions";
require_once 'includes/searchForm.php';

$tableHtml = "";

$searchTranscriptionsChecked = "checked";

$audioItems = AudioItems::getAudioItemsForTranscriptions($_GET["archive"], $_GET["searchTerm"]);

$contextScope = 30;

if ($_GET["searchTerm"] && empty($audioItems)) {
    $tableStructureHtml = 'There were no results for ' . $_GET["searchTerm"];
	$tableHtml .= 'There were no results for ' . $_GET["searchTerm"];
} else if($_GET["searchTerm"]) {
	foreach ($audioItems as $ref) {
		$item = AudioItems::getAudioItem($ref);
		foreach (explode(" ", $_GET["searchTerm"]) as $word)
		foreach ($item->getSearchContextIndices($word) as $index) {
			$contextHtml = $item->getSearchContext($index, $contextScope, $word);
			$tableHtml .= <<<HTML
				<tr {$rowClasses}>
					<td>{$item->getTitle()}</td>
					<td>{$item->getYear()}</td>
					<td>{$item->getLocation()}</td>
					<td>{$contextHtml}</td>
                    <td><input type="button" class="dasg_smlButton" onclick="window.location.href='/audio/view/t/{$_GET["archive"]}/{$_GET["searchTerm"]}/{$ref}';" value="Listen"/></td>
                    <td><input type="button" class="dasg_smlButton" onclick="window.open('/audio/trans/{$_GET["searchTerm"]}/{$ref}', '_blank');" value="Read"/></td>

<!--td><a href="/audio/trans/{$_GET["searchTerm"]}/{$ref}" target="_blank">Read</a></td-->
				</tr>
HTML;
		}
	}
	$tableStructureHtml = <<<HTML
		<table id="audioTable" class="tablesorter">
			<thead>
				<tr>
					<th>Title</th>
					<th class="audioSmallTableHeading">Year</th>
					<th>Location</th>
					<th>Context</th>
					<th>Entry</th>
                    <th class="audioMidTableHeading">Transcription</th>
				</tr>
			</thead>
			<tbody>
				{$tableHtml}
			</tbody>
		</table>
HTML;
}

echo $searchForm;

if (!AudioItems::archiveHasTranscriptions($_GET["archive"])) {
    echo "<h3>This archive currently has no transcriptions</h3>";
} else {
    echo <<<HTML
	{$tableStructureHtml}
HTML;
}

require_once '../includes/htmlFooter.php';