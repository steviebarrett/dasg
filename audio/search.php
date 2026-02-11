<?php

$pageTitle = "Cluas ri Claisneachd";
$pageSlug = "audio";
$audioSlug = "Search";

$cqpPage = true;

if (!isset($_GET["archive"])) {
    $_GET["archive"] = "crc";
}


$javascriptBlock = <<<HTML
<script src="/js/jquery.tablesorter.min.js"></script>
<script>
	$(function() {
	
		sortTable();
		resetAudioKeyword();
		
        $('#search_transcriptions').on('click', function() {
			window.location.href = "/audio/search/t/{$_GET["archive"]}/";
		});
		
		$('#audioKeyword').change(function() {
			if (this.selectedIndex == 0) {
				$('.keyword').show();
				return;
			}
			var rowClass = '.keyword_' + $(this).val();
			$('.keyword').hide();
			$(rowClass).show();
		});
		
		$('#searchAudio').on('submit', function() {
			searchAudio();
			return false;
		});
		
		$('#searchReset').on('click', function() {
			$('#searchTerm').val('');
			searchAudio();
			resetAudioKeyword();
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
	
	function resetAudioKeyword() {
		$('#audioKeyword').find('option:eq(0)').prop('selected', true);
	}
	
	function searchAudio() {
		var searchTerm = $('#searchTerm').val();
		resetAudioKeyword();
		$('#audioTable tbody tr').remove();
		$.getJSON("/ajax/audio.php?archive={$_GET["archive"]}&action=search&q="+searchTerm, function(data) {
			if ($.isEmptyObject(data)) {
				$('#audioTable').append('<tr><td colspan="6">There were no results for ' + searchTerm + '</td></tr>');
				return false;
			}
			$.each(data, function(key, arr) {
				var rowClasses = ' class="keyword';
				$.each(arr.keywords, function(k, v) {
					rowClasses += ' keyword_'+v;
				});
				rowClasses += '"';
				html = '<tr' + rowClasses + '><td>' + arr.fieldworker + '</td>';
				html += '<td>' + arr.year + ' </td>';
				html += '<td>' + arr.location + '</td>';
				html += '<td>' + arr.keywordsList + '</td>';
				html += '<td><input type="button" class="dasg_smlButton" onclick="window.location.href=\'view.php?ref='+ key + '\';" value="View"/></td></tr>';
				$('#audioTable').append(html);
			});
		});
		sortTable();
	}
	
</script>
<script src="/js/jquery.caret.js"></script> 
<script src="/js/functions.js"></script>
<script src="/audio/includes/audioJavascript.js"></script>
HTML;

require_once '../includes/include.php';
require_once '../includes/htmlHeader.php';
require_once 'includes/audioVars.php';
$showFilters = true;
$showSearchFilters = true;
require_once 'includes/audioHeader.php';
$searchDomain = "metadata";
require_once 'includes/searchForm.php';

$searchTerm = isset($_GET["searchTerm"]) ? htmlentities($_GET["searchTerm"]) : "";
$archive = htmlentities($_GET["archive"]);

$tableHtml = "";

$_GET["completed"] = 1;     //force completed for the moment, to only show these items

$audioItems = AudioItems::getAudioItemReferences($_GET["archive"], $searchTerm, $_GET["completed"]);

if (empty($audioItems)) {
    $tableHtml .= '<tr><td colspan="6">There were no results for ' . $searchTerm. '</td></tr>';
} else {
    foreach ($audioItems as $ref) {
        $item = AudioItems::getAudioItem($ref);
        $keywords = $item->getKeywordsArray();
        $rowClasses = " class=\"keyword";
        $keywordsCol = array();
        foreach ($keywords as $keyword) {
            $rowClasses .= " keyword_" . $keyword;
            $keywordsCol[] = "<span class=\"audio_{$keyword}\">{$keyword}</span>";
        }
        $rowClasses .= "\"";
        $keywordHtml = implode(" ", $keywordsCol);
        $tableHtml .= <<<HTML
				<tr {$rowClasses}>
					<td><a href="/audio/view/s/{$archive}/{$searchTerm}/{$ref}" title="{$item->getTitle()}">{$item->getTitle()}</td>
					<td><a href="/audio/search/s/{$archive}/{$item->getFieldworker()}">{$item->getFieldworker()}</a></td>
                    <td>{$item->getContributors()}</td>
					<td>{$item->getYear()}</td>
					<td>{$item->getLocation()}</td>
					<td>{$keywordHtml}</td>
				</tr>
HTML;
    }
}

echo <<<HTML
	{$searchForm}
	<table id="audioTable" class="tablesorter">
		<thead>
			<tr>
				<th>Title</th>
				<th class="audioMidTableHeading">Fieldworker</th>
                <th>Contributors</th>
				<th class="audioSmallTableHeading">Year</th>
				<th>Location</th>
				<th>Keywords</th>
			</tr>
		</thead>
		<tbody>
			{$tableHtml}
		</tbody>
	</table>
	
HTML;
			
require_once '../includes/htmlFooter.php';