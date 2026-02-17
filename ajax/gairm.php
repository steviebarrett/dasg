<?php

require_once '../includes/include.php';

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

switch ($_GET["action"]) {

	case "getRecord":
		$id = $_GET["id"];
		$gairmRecord = GairmRecords::getGairmRecord($id);
		$results = array();
		$results["id"]				= $id;
		$results["Title"]			= $gairmRecord->getTitle();
		$results["LastName"]		= $gairmRecord->getLastName();
		$results["FirstName"] 		= $gairmRecord->getFirstName();
		$results["Origin"]			= $gairmRecord->getOrigin();
		$results["Year"]			= $gairmRecord->getYear();
		$results["Volume"]			= $gairmRecord->getVolume();
		$results["FirstPage"]		= $gairmRecord->getFirstPage();
		$results["LastPage"]		= $gairmRecord->getLastPage();
		$results["Language"]		= $gairmRecord->getLanguage();
		$results["Type"]			= $gairmRecord->getType();
		$results["Genre"]			= $gairmRecord->getGenre();
		$results["Register"]		= $gairmRecord->getRegister();
		$results["Comments"]		= $gairmRecord->getComments();
	//	$results["Transcription"] = nl2br($gairmRecord->getTranscription());
		echo json_encode($results);
		break;
	case "getTranscription":
		$elems = explode("/", $_GET["path"]);
		$records = GairmRecords::getGairmRecordByVolAndPage($elems[1], $elems[2]);
		foreach ($records as $record) {
			$transcription .= $record ? $record->getTranscriptionForFile($elems[2]) : "";
		}
		echo json_encode(array("transcription" => nl2br($transcription)));
		break;
	default:
		break;
}
