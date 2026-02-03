<?php

namespace models;

session_start();

header("Access-Control-Allow-Origin: *");

require_once 'includes/include.php';

switch ($_REQUEST["action"]) {
	case "getRecord":
		$result = array();
		$gaelicFieldMap = record::getGaelicFieldMap();
		$record = new record($_GET["ai"]);
		$record->load();
		foreach ($record->getAllProps() as $key => $value) {
			$friendlyName = ($key == "ai")
				? "Identifier Number"
				: functions::getFriendlyName($key);
			$friendlyName = '<span class="text-muted"><em>' . $gaelicFieldMap[$key] . '</span></em><br>' . $friendlyName;
			$result[$friendlyName] = $value;
		}
		$result["Transcription"] = $record->getTranscriptionLink();
		echo json_encode($result);
		break;
	case "searchRecords":
		$records = new records();
		if (empty($_GET["searchStrings"])) {
			echo json_encode(array("error" => "no search string"));
		}
		$searchStrings = explode('|', $_GET["searchStrings"]);
		$searchFields = explode('|', $_GET["searchFields"]);
		$booleans = explode('|', $_GET["booleans"]);
		$params = explode('|', $_GET["params"]);
		$results = $records->getAdvancedSearchResults(
			$searchStrings, $searchFields, $booleans, $params,
			$_GET["search"], $_GET["offset"], $_GET["limit"], $_GET["sort"], $_GET["order"]);
		echo json_encode($results);
		break;
	case "browseRecords":
		$records = new records();
		$getText = $_GET["getText"] == 'n' ? false : true;
		$results = $records->getBrowseResults($_GET["offset"], $_GET["limit"], $_GET["sort"], $_GET["order"], $_GET["search"],
			$getText);
		echo json_encode($results);
		break;
	case "editRecord":
		$controller = new \controllers\record($_GET["ai"]);
		$controller->run("edit");
		break;
	case "deleteRecord":
		records::delete($_GET["ai"]);
		break;
	case "saveSearchForm":
		$_SESSION["searchForm"] = $_POST;
		echo true;
		break;
	case "resetSearchForm":
		unset($_SESSION["searchForm"]);
		break;
	case "getRecordExists":
		$result = array("exists"=>records::getRecordExists($_GET["ai"]));
		echo json_encode($result);
		break;
	case "updateSearchQueryOptions":
		$records = new records();
		if ($_GET["checked"] == "true") {
			$records->addSearchQueryField($_GET["field"]);
		} else {
			$records->removeSearchQueryField($_GET["field"]);
		}
		$records->save();
		break;
	case "updateSearchOptions":
		$records = new records();
		if ($_GET["checked"] == "true") {
			$records->addSearchField($_GET["field"]);
		} else {
			$records->removeSearchField($_GET["field"]);
		}
		$records->save();
		break;
	case "updateBrowseOptions":
		$records = new records();
		if ($_GET["checked"] == "true") {
			$records->addBrowseField($_GET["field"]);
		} else {
			$records->removeBrowseField($_GET["field"]);
		}
		$records->save();
		break;
	case "getSearchFieldDropdown":
		$count = $_POST["searchFieldCount"];
		$html = <<<HTML
			<select name="searchField[{$count}]" data-index="{$count}" class="form-control searchFieldSelect">
				<option value="all">Search All Fields  ▿</option>
HTML;
		$model = new records();
		$searchFields = $model->getSearchQueryFields();
		foreach ($searchFields as $searchField) {
			$friendlyName = functions::getFriendlyName($searchField);
			$html .= <<<HTML
				<option value="{$searchField}">{$friendlyName}</option>
HTML;
		}
		$html .= "</select>";
		echo $html;
		break;
	case "getDropdownOptions":  //checks for "controlled vocabulary" and returns options if required
		$fields = records::getControlledVocabularies($_GET["field"]);
		echo json_encode($fields);
		break;
	default:
		echo "unknown action";
}