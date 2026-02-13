<?php
declare(strict_types=1);

namespace models;

header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/includes/include.php';


if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

// -------------------------
// JSON responder (consistent)
// -------------------------
$sendJson = static function ($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');

    $json = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_INVALID_UTF8_SUBSTITUTE
        | JSON_PARTIAL_OUTPUT_ON_ERROR
    );
    if ($json === false) {
        // fall back to a safe error if encoding fails
        http_response_code(500);
        echo '{"error":"json_encode failed"}';
        exit;
    }

    echo $json;
    exit;
};

// -------------------------
// Guards
// -------------------------
$requirePost = static function () use ($sendJson): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        $sendJson(['error' => 'POST required'], 405);
    }
};

$requireAdmin = static function () use ($sendJson): void {
    if (empty($_SESSION['loggedIn'])) {
        $sendJson(['error' => 'admin required'], 403);
    }
};

$requireCsrf = static function () use ($sendJson): void {
    $token = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (empty($_SESSION['csrf_token']) || $token === '' || !hash_equals((string)$_SESSION['csrf_token'], $token)) {
        $sendJson(['error' => 'CSRF failed'], 403);
    }
};

// -------------------------
// Input helpers
// -------------------------
$clampInt = static function ($v, int $min, int $max, int $default): int {
    if ($v === null || $v === '') return $default;
    $n = (int)$v;
    if ($n < $min) return $min;
    if ($n > $max) return $max;
    return $n;
};

$validAi = static function (string $ai): bool {
    return (bool)preg_match('/^[A-Za-z0-9_-]{1,64}$/', $ai);
};

$validField = static function (records $records, string $field): bool {
    $fields = $records->getAllFieldNames();
    return in_array($field, $fields, true);
};

// -------------------------
// Request data
// -------------------------
$get  = $_GET ?? [];
$post = $_POST ?? [];
$req  = $_REQUEST ?? [];

$action = (string)($req['action'] ?? '');
if ($action === '') {
    $sendJson(['error' => 'missing action'], 400);
}

// -------------------------
// Router
// -------------------------
switch ($action) {

    // =========================
    // READ-ONLY JSON endpoints
    // =========================

    case "getRecord": {
        $ai = (string)($get["ai"] ?? '');
        if ($ai === '' || !$validAi($ai)) {
            $sendJson(["error" => "invalid ai"], 400);
        }

        $record = new record($ai);
        $record->load();

        // Return structured data (labels + value) so the client can render safely
        $gaelicFieldMap = record::getGaelicFieldMap();

        $out = [];
        foreach ($record->getAllProps() as $key => $value) {
            $k = (string)$key;

            $labelEn = ($k === "ai")
                ? "Identifier Number"
                : functions::getFriendlyName($k);

            $labelGd = (string)($gaelicFieldMap[$k] ?? '');

            $out[$k] = [
                'label_en' => $labelEn,
                'label_gd' => $labelGd,
                'value'    => $value,
            ];
        }

        // transcription link is handled specially in your UI
        $out["Transcription"] = [
            'label_en' => 'Transcription',
            'label_gd' => '',
            'value'    => $record->getTranscriptionLink(), // may be null
        ];

        $sendJson($out);
        break;
    }

    case "searchRecords": {
        // NOTE: bootstrap-table uses GET. Keep as GET/POST compatible.
        $records = new records();

        $searchStringsRaw = (string)($req["searchStrings"] ?? '');
        if ($searchStringsRaw === '') {
            $sendJson(["total" => 0, "totalNotFiltered" => 0, "rows" => []]);
        }

        if (strlen($searchStringsRaw) > 2000) {
            $sendJson(['error' => 'search too long'], 413);
        }

        $searchFieldsRaw = (string)($req["searchFields"] ?? '');
        $booleansRaw     = (string)($req["booleans"] ?? '');
        $paramsRaw       = (string)($req["params"] ?? '');

        // Split and normalise
        $searchStrings = array_values(array_filter(explode('|', $searchStringsRaw), 'strlen'));
        $searchFields  = array_values(array_filter(explode('|', $searchFieldsRaw), 'strlen'));

        // Ensure searchFields aligns with searchStrings
        if (count($searchFields) === 1 && count($searchStrings) > 1) {
            $searchFields = array_fill(0, count($searchStrings), $searchFields[0]);
        }

        // Booleans: preserve alignment with searchStrings (index 0 has no operator)
        $tmpBooleans = explode('|', $booleansRaw); // keep empties
        $allowedOps  = ['AND', 'OR', 'NOT', 'AND NOT'];

        $booleans = array_fill(0, count($searchStrings), 'AND');
        $booleans[0] = ''; // first term has no operator

        for ($i = 1; $i < count($searchStrings); $i++) {
            $op = strtoupper(trim($tmpBooleans[$i] ?? 'AND'));
            $booleans[$i] = in_array($op, $allowedOps, true) ? $op : 'AND';
        }

        // Params: parse "0|1|0|..." into array; pad to at least 6 items
        $params = array_map('intval', explode('|', $paramsRaw));
        for ($i = count($params); $i < 6; $i++) {
            $params[$i] = 0;
        }

        // Paging/sort
        $offset = $clampInt($req["offset"] ?? null, 0, 1000000, 0);
        $limit  = $clampInt($req["limit"] ?? null, 1, 100, 10);

        $allowedSort = [
            'ai','title','alternative_title','first_line_chorus','first_line_verse','classifications',
            'subjects','place_of_origin','composer_first_name','composer_last_name','community','singer'
        ];
        $sort = (string)($req["sort"] ?? '');
        $sort = in_array($sort, $allowedSort, true) ? $sort : 'ai';

        $order = strtolower((string)($req["order"] ?? 'asc'));
        $order = in_array($order, ['asc','desc'], true) ? $order : 'asc';

        $search = (string)($req["search"] ?? '');

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
        break;
    }

    case "browseRecords": {
        $records = new records();

        $offset = $clampInt($get["offset"] ?? null, 0, 1000000, 0);
        $limit  = $clampInt($get["limit"] ?? null, 1, 100, 50);

        $allowedSort = [
            'ai','title','alternative_title','first_line_chorus','first_line_verse','classifications',
            'subjects','place_of_origin','composer_first_name','composer_last_name','community','singer'
        ];
        $sort = (string)($req["sort"] ?? '');
        $sort = in_array($sort, $allowedSort, true) ? $sort : 'ai';

        $order = strtolower((string)($req["order"] ?? 'asc'));
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

    case "getRecordExists": {
        $ai = (string)($get["ai"] ?? '');
        if ($ai === '' || !$validAi($ai)) {
            $sendJson(["error" => "invalid ai"], 400);
        }

        $sendJson(["exists" => records::getRecordExists($ai)]);
        break;
    }

    case "getDropdownOptions": {
        $field = (string)($get["field"] ?? '');
        if ($field === '') {
            $sendJson(["error" => "missing field"], 400);
        }
        if ($field === 'all') {
            $sendJson(null);
        }

        $model = new records();
        if (!$validField($model, $field)) {
            $sendJson(["error" => "invalid field"], 400);
        }

        $fields = records::getControlledVocabularies($field);
        $sendJson($fields);
        break;
    }

    case "getSearchFieldDropdown": {
        // read-only (returns HTML snippet); no CSRF required
        $count = isset($post["searchFieldCount"]) ? (int)$post["searchFieldCount"] : 0;
        if ($count < 0 || $count > 25) $count = 0;

        $countEsc = functions::e($count);

        $html = <<<HTML
            <select name="searchField[{$countEsc}]" data-index="{$countEsc}" class="form-control searchFieldSelect">
                <option value="all">Search All Fields ▿</option>
HTML;

        $model = new records();
        $searchFields = $model->getSearchQueryFields();

        foreach ($searchFields as $searchField) {
            $sf = (string)$searchField;
            $friendlyName = functions::getFriendlyName($sf);

            $sfEsc = functions::e($sf);
            $fnEsc = functions::e($friendlyName);

            $html .= <<<HTML
                <option value="{$sfEsc}">{$fnEsc}</option>
HTML;
        }

        $html .= "</select>";

        header('Content-Type: text/html; charset=UTF-8');
        echo $html;
        exit;
    }

    // =========================
    // STATE-CHANGING endpoints
    // =========================

    case "saveSearchForm":
        $requirePost();
        $requireCsrf();

        // store the submitted form so search page can reload it.
        // Hardening: do NOT trust searchFieldCount.
        $clean = [];

        $clean['m'] = isset($post['m']) ? (string)$post['m'] : 'records';
        $clean['a'] = isset($post['a']) ? (string)$post['a'] : 'search';

        // Pull arrays if present
        $searchField = (isset($post['searchField']) && is_array($post['searchField'])) ? $post['searchField'] : [];
        $s           = (isset($post['s']) && is_array($post['s'])) ? $post['s'] : [];
        $b           = (isset($post['b']) && is_array($post['b'])) ? $post['b'] : [];
        $params      = (isset($post['params']) && is_array($post['params'])) ? $post['params'] : [];

        // Derive count from the actual submitted rows (max index + 1), cap to prevent abuse
        $maxIndex = -1;
        foreach ([$searchField, $s] as $arr) {
            foreach ($arr as $k => $_) {
                if (is_int($k) || ctype_digit((string)$k)) {
                    $maxIndex = max($maxIndex, (int)$k);
                }
            }
        }
        $count = max(1, $maxIndex + 1);
        $count = min($count, 10); // cap (matches your previous intent)

        $clean['searchFieldCount'] = $count;

        // Normalise and slice/preserve keys
        $clean['searchField'] = array_slice($searchField, 0, $count, true);
        $clean['s']           = array_slice($s, 0, $count, true);

        // Booleans are indexed starting at 1 in your UI (row 0 has no boolean)
        // Keep only keys 1..count-1
        $clean['b'] = [];
        for ($i = 1; $i < $count; $i++) {
            if (isset($b[$i])) {
                $clean['b'][$i] = (string)$b[$i];
            }
        }

        // Params: keep expected indexes 0..5 (checkboxes)
        $clean['params'] = [];
        for ($i = 0; $i <= 5; $i++) {
            if (isset($params[$i])) {
                $clean['params'][$i] = (string)$params[$i];
            }
        }

        $_SESSION["searchForm"] = $clean;

        header('Content-Type: text/plain; charset=UTF-8');
        echo "1";
        exit;

    case "resetSearchForm": {
        $requirePost();
        $requireCsrf();

        unset($_SESSION["searchForm"]);

        header('Content-Type: text/plain; charset=UTF-8');
        echo "1";
        exit;
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
        if (!$validField($records, $field)) {
            $sendJson(["error" => "invalid field"], 400);
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
        if (!$validField($records, $field)) {
            $sendJson(["error" => "invalid field"], 400);
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
        if (!$validField($records, $field)) {
            $sendJson(["error" => "invalid field"], 400);
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

    case "deleteRecord": {
        $requirePost();
        $requireCsrf();
        $requireAdmin();

        $ai = (string)($post["ai"] ?? '');
        if ($ai === '' || !$validAi($ai)) {
            $sendJson(["error" => "invalid ai"], 400);
        }

        records::delete($ai);
        $sendJson(["ok" => true]);
        break;
    }

    default:
        $sendJson(["error" => "unknown action"], 400);
}
