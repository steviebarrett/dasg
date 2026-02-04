<?php
declare(strict_types=1);

namespace models;

session_start();

require_once 'includes/include.php';

// Helpers for safe reads
$get = $_GET ?? [];
$post = $_POST ?? [];
$req = $_REQUEST ?? [];

$action = (string)($req['action'] ?? '');

// output clean JSON consistently
$sendJson = static function ($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
};

switch ($action) {

    case "getRecord": {
        $ai = (string)($get["ai"] ?? '');
        if ($ai === '') {
            $sendJson(["error" => "missing ai"], 400);
        }

        $result = [];
        $gaelicFieldMap = record::getGaelicFieldMap();

        $record = new record($ai);
        $record->load();

        foreach ($record->getAllProps() as $key => $value) {
            $key = (string)$key;

            $friendlyName = ($key === "ai")
                ? "Identifier Number"
                : functions::getFriendlyName($key);

            // Gaelic label: guard missing key
            $gaelicLabel = (string)($gaelicFieldMap[$key] ?? '');

            // NOTE: keys are used as labels in JSON; escaping is not required for JSON,
            // but you appear to intentionally embed HTML in the key for display client-side.
            // That’s okay if your UI expects it, but ensure $gaelicLabel is trusted (it’s server-defined).
            $friendlyName = '<span class="text-muted"><em>' . $gaelicLabel . '</em></span><br>' . $friendlyName;

            $result[$friendlyName] = $value;
        }

        $result["Transcription"] = $record->getTranscriptionLink();

        $sendJson($result);
        break;
    }

    case "searchRecords": {
        $records = new records();

        // Read from REQUEST so it works for GET or POST
        $searchStringsRaw = (string)($_REQUEST["searchStrings"] ?? '');
        if ($searchStringsRaw === '') {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(["error" => "no search string"]);
            exit;
        }

        $searchFieldsRaw = (string)($_REQUEST["searchFields"] ?? '');
        $booleansRaw     = (string)($_REQUEST["booleans"] ?? '');
        $paramsRaw       = (string)($_REQUEST["params"] ?? '');

        // Split and normalise
        $searchStrings = array_values(array_filter(explode('|', $searchStringsRaw), 'strlen'));
        $searchFields  = array_values(array_filter(explode('|', $searchFieldsRaw), 'strlen'));

        // Booleans: remove empties; if the UI sends just "|" treat as none
        $booleans = array_values(array_filter(explode('|', $booleansRaw), 'strlen'));

        // Params: KEEP empties sometimes matters, but your example has trailing empties.
        // We'll trim trailing empties and keep meaningful ones.
        $params = explode('|', $paramsRaw);
        while (!empty($params) && end($params) === '') {
            array_pop($params);
        }

        // Ensure searchFields aligns with searchStrings
        // If UI sends "all" once but there are N strings, repeat "all"
        if (count($searchFields) === 1 && count($searchStrings) > 1) {
            $searchFields = array_fill(0, count($searchStrings), $searchFields[0]);
        }

        // If booleans count doesn't match N-1, pad with AND (or truncate)
        $needBooleans = max(0, count($searchStrings) - 1);
        if (count($booleans) < $needBooleans) {
            $booleans = array_merge($booleans, array_fill(0, $needBooleans - count($booleans), 'AND'));
        } elseif (count($booleans) > $needBooleans) {
            $booleans = array_slice($booleans, 0, $needBooleans);
        }

        // Default paging/sort
        $offset = isset($_REQUEST["offset"]) ? (int)$_REQUEST["offset"] : 0;
        $limit  = isset($_REQUEST["limit"])  ? (int)$_REQUEST["limit"]  : 10;
        $sort   = (string)($_REQUEST["sort"] ?? '');
        $order  = (string)($_REQUEST["order"] ?? '');
        $search = (string)($_REQUEST["search"] ?? '');

        $results = $records->getAdvancedSearchResults(
            $searchStrings,
            $searchFields,
            $booleans,
            $params,
            $search,
            $offset,
            $limit,
            $sort,
            $order
        );

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($results);
        exit;
    }

    case "browseRecords": {
        $records = new records();

        $offset = isset($get["offset"]) ? (int)$get["offset"] : 0;
        $limit  = isset($get["limit"])  ? (int)$get["limit"]  : 50;

        $sort   = (string)($get["sort"] ?? '');
        $order  = (string)($get["order"] ?? '');
        $search = (string)($get["search"] ?? '');

        $getText = ((string)($get["getText"] ?? 'y')) !== 'n';

        $results = $records->getBrowseResults(
            $offset,
            $limit,
            $sort,
            $order,
            $search,
            $getText
        );

        $sendJson($results);
        break;
    }

    case "editRecord": {
        $ai = (string)($get["ai"] ?? '');
        if ($ai === '') {
            $sendJson(["error" => "missing ai"], 400);
        }

        $controller = new \controllers\record($ai);
        $controller->run("edit");
        // controller likely echoes its own response
        exit;
    }

    case "saveSearchForm":
        $_SESSION["searchForm"] = $_POST;
        echo "1"; // or echo true;
        exit;

    case "resetSearchForm":
        unset($_SESSION["searchForm"]);
        echo "1";
        exit;

    case "getRecordExists": {
        $ai = (string)($get["ai"] ?? '');
        if ($ai === '') {
            $sendJson(["error" => "missing ai"], 400);
        }

        $result = ["exists" => records::getRecordExists($ai)];
        $sendJson($result);
        break;
    }

    case "updateSearchQueryOptions": {
        $records = new records();

        $checked = (string)($get["checked"] ?? 'false');
        $field   = (string)($get["field"] ?? '');

        if ($field === '') {
            $sendJson(["error" => "missing field"], 400);
        }

        if ($checked === "true") {
            $records->addSearchQueryField($field);
        } else {
            $records->removeSearchQueryField($field);
        }
        $records->save();

        $sendJson(["ok" => true]);
        break;
    }

    case "updateSearchOptions": {
        $records = new records();

        $checked = (string)($get["checked"] ?? 'false');
        $field   = (string)($get["field"] ?? '');

        if ($field === '') {
            $sendJson(["error" => "missing field"], 400);
        }

        if ($checked === "true") {
            $records->addSearchField($field);
        } else {
            $records->removeSearchField($field);
        }
        $records->save();

        $sendJson(["ok" => true]);
        break;
    }

    case "updateBrowseOptions": {
        $records = new records();

        $checked = (string)($get["checked"] ?? 'false');
        $field   = (string)($get["field"] ?? '');

        if ($field === '') {
            $sendJson(["error" => "missing field"], 400);
        }

        if ($checked === "true") {
            $records->addBrowseField($field);
        } else {
            $records->removeBrowseField($field);
        }
        $records->save();

        $sendJson(["ok" => true]);
        break;
    }

    case "getSearchFieldDropdown": {
        $count = isset($post["searchFieldCount"]) ? (int)$post["searchFieldCount"] : 0;

        $countEsc = functions::e($count);

        $html = <<<HTML
			<select name="searchField[{$countEsc}]" data-index="{$countEsc}" class="form-control searchFieldSelect">
				<option value="all">Search All Fields ▿</option>
HTML;

        $model = new records();
        $searchFields = $model->getSearchQueryFields();

        foreach ($searchFields as $searchField) {
            $searchField = (string)$searchField;
            $friendlyName = functions::getFriendlyName($searchField);

            $sfEsc = functions::e($searchField);
            $fnEsc = functions::e($friendlyName);

            $html .= <<<HTML
				<option value="{$sfEsc}">{$fnEsc}</option>
HTML;
        }

        $html .= "</select>";

        // This endpoint returns HTML, not JSON
        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }

    case "getDropdownOptions": {
        $field = (string)($get["field"] ?? '');
        if ($field === '') {
            $sendJson(["error" => "missing field"], 400);
        }

        $fields = records::getControlledVocabularies($field);
        $sendJson($fields);
        break;
    }

    default:
        $sendJson(["error" => "unknown action"], 400);
}