<?php

require_once '../includes/include.php';

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

switch ($_GET["action"]) {
	
	case "search":
	    $archive = $_GET["archive"];
		$query = $_GET["q"];
		$audioItems = AudioItems::getAudioItemReferences($archive, $query);
		$results = array();
		foreach ($audioItems as $ref) {
			$item = AudioItems::getAudioItem($ref);
			$results[$ref]["fieldworker"]	= $item->getFieldworker();
			$results[$ref]["year"]			= $item->getYear();
			$results[$ref]["location"] 		= $item->getLocation();
			$results[$ref]["keywords"]		= $item->getKeywordsArray();
			$keywords = $item->getKeywordsArray();
			$keywordsCol = array();
			foreach ($keywords as $keyword) {
				$keywordsCol[] = "<span class=\"audio_{$keyword}\"{$keyword}</span>";
			}
			$results[$ref]["keywordsList"]	= implode(" ", $keywordsCol);
		}
		echo json_encode($results);
		break;
	case "updatePlays":
	    $ref = $_GET["ref"];
	    AudioItems::updatePlayCount($ref);
	default:
		break;
}

