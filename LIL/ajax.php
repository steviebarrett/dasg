<?php
declare(strict_types=1);

namespace models;

header('X-Content-Type-Options: nosniff');

// output clean JSON consistently
$sendJson = static function ($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
};

// helper to ensure POST is used
$requirePost = static function () use ($sendJson): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        $sendJson(['error' => 'POST required'], 405);
    }
};

// helper to ensure admin is logged in
$requireAdmin = static function () use ($sendJson): void {
    if (empty($_SESSION['loggedIn'])) {
        $sendJson(['error' => 'admin required'], 403);
    }
};

// helper to ensure CSRF token is valid
$requireCsrf = static function () use ($sendJson): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $sendJson(['error' => 'CSRF failed'], 403);
    }
};

$clampInt = static function (mixed $v, int $min, int $max, int $default) : int {
    if ($v === null || $v === '') return $default;
    $n = (int)$v;
    if ($n < $min) return $min;
    if ($n > $max) return $max;
    return $n;
};

$validAi = static function (string $ai) : bool {
    return (bool)preg_match('/^[A-Za-z0-9_-]{1,64}$/', $ai);
};

require_once 'includes/include.php';

// Helpers for safe reads
$get = $_GET ?? [];
$post = $_POST ?? [];
$req = $_REQUEST ?? [];

$action = (string)($req['action'] ?? '');

switch ($action) {

    case "getRecord": {
        $ai = (string)($get["ai"] ?? '');

        if ($ai === '' || !$validAi($ai)) {
            $sendJson(["error" => "invalid ai"], 400);
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

            $result[$key]["label_en"] = $friendlyName;
            $result[$key]["label_gd"] = $gaelicLabel;
            $result[$key]["value"] = $value;
        }

        $result["Transcription"] = $record->getTranscriptionLink();

        $sendJson($result);
        break;
    }

    case "searchRecords": {

        $records = new records();

        $searchStringsRaw = (string)($_REQUEST["searchStrings"] ?? '');
        if ($searchStringsRaw === '') {
            $sendJson(["error" => "no search string"], 400);
        }

        $searchFieldsRaw = (string)($_REQUEST["searchFields"] ?? '');
        $booleansRaw     = (string)($_REQUEST["booleans"] ?? '');
        $paramsRaw       = (string)($_REQUEST["params"] ?? '');

        if (strlen($searchStringsRaw) > 2000) {
            $sendJson(['error' => 'search too long'], 413);
        }

        // Split and normalise
        $searchStrings = array_values(array_filter(explode('|', $searchStringsRaw), 'strlen'));
        $searchFields  = array_values(array_filter(explode('|', $searchFieldsRaw), 'strlen'));

        // --- Booleans: MUST preserve index alignment with searchStrings ---
        // Expect booleans like: "" (index 0), "OR" (index 1), "AND" (index 2) ...
        $tmpBooleans = explode('|', $booleansRaw); // KEEP empties

        $allowedOps = ['AND', 'OR', 'NOT'];
        $booleans = array_fill(0, count($searchStrings), 'AND');
        $booleans[0] = ''; // first term has no operator

        for ($i = 1; $i < count($searchStrings); $i++) {
            $op = strtoupper(trim($tmpBooleans[$i] ?? 'AND'));
            $booleans[$i] = in_array($op, $allowedOps, true) ? $op : 'AND';
        }

        // Params: trim trailing empties
        $params = explode('|', $paramsRaw);
        while (!empty($params) && end($params) === '') {
            array_pop($params);
        }

        // Ensure searchFields aligns with searchStrings
        if (count($searchFields) === 1 && count($searchStrings) > 1) {
            $searchFields = array_fill(0, count($searchStrings), $searchFields[0]);
        }

        // Default paging/sort
        $offset = $clampInt($_REQUEST["offset"] ?? null, 0, 1000000, 0);
        $limit  = $clampInt($_REQUEST["limit"] ?? null, 1, 100, 10);

        $allowedSort = ['ai','title','alternative_title','first_line_chorus','first_line_verse','classifications',
            'subjects','place_of_origin','composer_first_name','composer_last_name','community','singer'];

        $sort = (string)($_REQUEST["sort"] ?? '');
        $sort = in_array($sort, $allowedSort, true) ? $sort : '';

        $order = strtolower((string)($_REQUEST["order"] ?? ''));
        $order = in_array($order, ['asc','desc'], true) ? $order : 'asc';

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

        $sendJson($results);
    }


    case "browseRecords": {
        $records = new records();

        $offset = $clampInt($get["offset"] ?? null, 0, 1000000, 0);
        $limit  = $clampInt($get["limit"] ?? null, 1, 100, 50);

        $allowedSort = ['ai','title', 'alternative_title','first_line_chorus','first_line_verse','classifications',
            'subjects', 'place_of_origin', 'composer_first_name', 'composer_last_name', 'community', 'singer'];
        $sort = (string)($get["sort"] ?? '');
        $sort = in_array($sort, $allowedSort, true) ? $sort : '';

        $order = strtolower((string)($get["order"] ?? ''));
        $order = in_array($order, ['asc','desc'], true) ? $order : 'asc';
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
        $requirePost();
        $requireCsrf();
        $requireAdmin();

        $ai = (string)($post["ai"] ?? $get["ai"] ?? '');
        if ($ai === '' || !$validAi($ai)) {
            $sendJson(["error" => "invalid ai"], 400);
        }

        $controller = new \controllers\record($ai);
        $controller->run("edit");
        exit;
    }

    case "saveSearchForm":
        $requirePost();
        $requireCsrf();

        $clean = [];

        $clean['m'] = isset($post['m']) ? (string)$post['m'] : 'records';
        $clean['a'] = isset($post['a']) ? (string)$post['a'] : 'search';

        // IMPORTANT: derive row count from posted arrays (this is what restores “worked before hardening” behaviour)
        $sCount  = (isset($post['s']) && is_array($post['s'])) ? count($post['s']) : 0;
        $fCount  = (isset($post['searchField']) && is_array($post['searchField'])) ? count($post['searchField']) : 0;
        $count   = max(1, $sCount, $fCount);
        $count   = min(10, $count); // cap to prevent abuse
        $clean['searchFieldCount'] = $count;

        // Copy row arrays up to $count
        if (isset($post['searchField']) && is_array($post['searchField'])) {
            $clean['searchField'] = array_slice($post['searchField'], 0, $count, true);
        }
        if (isset($post['s']) && is_array($post['s'])) {
            $clean['s'] = array_slice($post['s'], 0, $count, true);
        }

        // b[] only exists from index 1..($count-1) typically — keep whatever was posted, but cap size
        if (isset($post['b']) && is_array($post['b'])) {
            // keep keys, but cap to at most $count entries
            $clean['b'] = array_slice($post['b'], 0, $count, true);
        }

        // params[] is independent of row count — DO NOT slice it by $count
        if (isset($post['params']) && is_array($post['params'])) {
            $clean['params'] = $post['params'];
        }

        $_SESSION["searchForm"] = $clean;

        header('Content-Type: text/plain; charset=UTF-8');
        echo "1";
        exit;

    case "resetSearchForm":
        $requirePost();
        $requireCsrf();

        unset($_SESSION["searchForm"]);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "1";
        exit;

    case "getRecordExists": {
        $ai = (string)($get["ai"] ?? '');
        if ($ai === '' || !$validAi($ai)) {
            $sendJson(["error" => "invalid ai"], 400);
        }

        $result = ["exists" => records::getRecordExists($ai)];
        $sendJson($result);
        break;
    }

    case "updateSearchQueryOptions": {
        $requirePost();
        $requireCsrf();
        $requireAdmin();

        $records = new records();

        $checked = (string)($post["checked"] ?? 'false');
        $field   = (string)($post["field"] ?? '');

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
        $requirePost();
        $requireCsrf();
        $requireAdmin();

        $records = new records();

        $checked = (string)($post["checked"] ?? 'false');
        $field   = (string)($post["field"] ?? '');

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
        $requirePost();
        $requireCsrf();
        $requireAdmin();

        $records = new records();

        $checked = (string)($post["checked"] ?? 'false');
        $field   = (string)($post["field"] ?? '');

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