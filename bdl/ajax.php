<?php


require_once '../includes/include.php';

$dbh = DB::getDatabaseHandle();

function getAccentInsens($string)
{
    $chars = ["aàáā", "eèéē", "iìíī", "oòóō", "uùúū"];
    $result = "";
    for ($i = 0; $i < mb_strlen($string); $i++) {
        $replaced = false;
        $thisChr = mb_substr($string, $i, 1);
        foreach ($chars as $group) {
            if (mb_stristr($group, $thisChr)) {
                $result .= "[{$group}]";
                $replaced = true;
                break;
            }
        }
        if ($replaced === false) {
            $result .= $thisChr;
        }
    }
    return $result;
}

switch ($_REQUEST["action"]) {
    case "search":
        // 1) allowlist the searchable columns
        $allowedFields = [
            'diplomatic'  => 'l.diplomatic',
            'vernacular'  => 'l.vernacular',
            'classical'   => 'l.classical',
            'translation' => 'l.translation',
            'notes'       => 'l.notes'
        ];

        // 2) normalise/validate inputs
        $searchFieldsIn = $_GET['f'] ?? [];
        if (!is_array($searchFieldsIn) || !$searchFieldsIn) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields selected']);
            exit;
        }

        $ignoreTags = (($_GET['ignoreTags'] ?? '') === 'true');
        $singleWord = (($_GET['sw'] ?? '') === 'true');
        $accentSensitive = (($_GET['as'] ?? '') === 'true');

        // 3) map requested fields to safe SQL identifiers
        $searchExprs = [];
        foreach ($searchFieldsIn as $f) {
            if (!is_string($f) || !isset($allowedFields[$f])) {
                continue; // ignore unknown fields (or hard-fail if you prefer)
            }
            $col = $allowedFields[$f]; // safe: comes from allowlist
            if ($ignoreTags) {
                // MySQL 8+: REGEXP_REPLACE. Column name is allowlisted, so safe.
                $searchExprs[] = "REGEXP_REPLACE($col, '<[^>]+>', '')";
            } else {
                $searchExprs[] = $col;
            }
        }

        if (!$searchExprs) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid fields']);
            exit;
        }

        // 4) build the REGEXP where clause safely
        $whereClause = '(' . implode(' REGEXP :q OR ', $searchExprs) . ' REGEXP :q)';

        $q = $_GET['q'] ?? '';
        if (!is_string($q)) $q = '';
        // optional: cap length to avoid abuse / heavy REGEXP costs
        if (mb_strlen($q, 'UTF-8') > 200) {
            http_response_code(400);
            echo json_encode(['error' => 'Query too long']);
            exit;
        }

        $q = str_replace(".", "[[:alpha:]]", $q);

        if (!$accentSensitive) {
            $q = getAccentInsens($q);
        }

        $prebound  = $singleWord ? "(^|[^[:alpha:]])" : "";
        $postbound = $singleWord ? "([^[:alpha:]]|$)" : "";

        $sql = "SELECT l.id AS id, page_id, l.number AS number, p.number AS pageNum, text_id,
                   diplomatic, vernacular, classical, translation, notes
            FROM bdl_line l
            JOIN bdl_page p ON l.page_id = p.id
            WHERE {$whereClause}";

        $sth = $dbh->prepare($sql);
        $sth->execute([":q" => "{$prebound}{$q}{$postbound}"]);

        $text = $sth->fetchAll(PDO::FETCH_ASSOC);
        $output = [];
        foreach ($text as $line) {
            $output[] = removeTags($line, ["strike"]);
        }
        echo json_encode($output);
        break;
    case "getText":
        $sth = $dbh->prepare("SELECT * FROM bdl_text WHERE id = :textId");
        $sth->execute(array(":textId" => $_GET["tid"]));
        $text = $sth->fetch(PDO::FETCH_ASSOC);
        echo json_encode($text);
        break;
    case "getPageList":         //fetch a list of pages for either a given text_id OR all pages
        $whereClause = "WHERE 1";
        $params = array();
        if (isset($_GET["tid"])) {
            $whereClause = "WHERE text_id = :textId ";
            $params = array(":textId" => $_GET["tid"]);
        }

$sql = <<<SQL

            SELECT * FROM bdl_page 
                {$whereClause} 
                ORDER BY 
                  LENGTH(REGEXP_SUBSTR(number, '^[0-9]+')),  -- Length of the numeric part
                  CAST(REGEXP_SUBSTR(number, '^[0-9]+') AS UNSIGNED),  -- Order by numeric part
                  SUBSTRING(number FROM LENGTH(REGEXP_SUBSTR(number, '^[0-9]+')) + 1), 
                  LENGTH(REGEXP_SUBSTR(text_id, '^[0-9]+')),  -- Length of the numeric part
                  CAST(REGEXP_SUBSTR(text_id, '^[0-9]+') AS UNSIGNED),  -- Order by numeric part
                  SUBSTRING(text_id FROM LENGTH(REGEXP_SUBSTR(text_id, '^[0-9]+')) + 1),
                   CAST(text_id AS unsigned);  -- Order by non-numeric part

SQL;
        $sth = $dbh->prepare($sql);
        $sth->execute($params);
        $pages = $sth->fetchAll();
        echo json_encode($pages);
        break;
    case "getLineList":
        $sql = <<<SQL
            SELECT l.id AS id, l.number AS number, p.number AS pageNum, text_id, diplomatic,
                   vernacular, classical, translation, notes, team_comments
                FROM bdl_line l JOIN bdl_page p ON l.page_id = p.id 
                WHERE page_id = :pageId ORDER BY CAST(l.number AS UNSIGNED) ASC
SQL;

        $sth = $dbh->prepare($sql);
        $sth->execute(array(":pageId" => $_GET["pid"]));
        $lines = $sth->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($lines);
        break;
    case "getLine":
        $sth = $dbh->prepare("SELECT * FROM bdl_line WHERE id = :lineId");
        $sth->execute(array(":lineId" => $_GET["lid"]));
        $line = $sth->fetch(PDO::FETCH_ASSOC);
        echo json_encode($line);
        break;
    case "saveText":
        $flu = removeTags($_POST["flc"], array("p", "span", "i", "sup"));
        $sql = <<<SQL
            UPDATE bdl_text SET  
                firstline_d = :fld, firstline_v = :flv, firstline_c = :flc, firstline_t = :trans,
                firstline_untagged = :flu,
                author = :author, pagerange = :pagerange, edition = :edition, notes = :notes
            WHERE id = :id
SQL;
        $sth = $dbh->prepare($sql);
        try {
            $sth->execute(array(
                ":fld" => $_POST["fld"], ":flv" => $_POST["flv"], ":flc" => $_POST["flc"], ":flu" => $flu,
                ":trans" => $_POST["trans"], ":author" => $_POST["author"], ":pagerange" => $_POST["pagerange"],
                ":edition" => $_POST["edition"], ":notes" => $_POST["notes"],
                ":id" => $_POST["id"]));
        } catch (Exception $e) {
            echo json_encode(array("error" => $e->getMessage()));
            return;
        }
        echo json_encode(array("msg" => "Text saved"));
        break;
    case "saveLine":
        $fields = ['diplomatic', 'vernacular', 'classical', 'translation', 'notes'];
        foreach ($fields as $field) {
            $_POST[$field] = removeTags($_POST[$field], array("p", "span"));
        }
        $sql = <<<SQL
            UPDATE bdl_line SET  
                diplomatic = :diplomatic, vernacular = :vernacular, classical = :classical, 
                translation = :translation, notes = :notes, team_comments = :team_comments
            WHERE id = :id
SQL;
        $sth = $dbh->prepare($sql);
        try {
            $sth->execute(array(
                ":diplomatic" => $_POST["diplomatic"], ":vernacular" => $_POST["vernacular"],
                ":classical" => $_POST["classical"], ":translation" => $_POST["translation"],
                 ":notes" => $_POST["notes"], ":team_comments" => $_POST["team_comments"],
                ":id" => $_POST["id"]));
        } catch (Exception $e) {
            echo json_encode(array("error" => $e->getMessage()));
            return;
        }
        echo json_encode(array("msg" => "Line saved"));
        break;
    case "saveIsos":
        $sql = <<<SQL
            UPDATE bdl_page SET isos = :isos WHERE id = :id 
SQL;
        $sth = $dbh->prepare($sql);
        try {
            $sth->execute(array(
                ":isos" => $_POST["isos"], ":id" => $_POST["id"]));
        } catch (Exception $e) {
            echo json_encode(array("error" => $e->getMessage()));
            return;
        }
        echo json_encode(array("msg" => "ISOS saved"));
        break;
    default:
        echo json_encode(array("error" => "Invalid Action"));
}


function removeTags($str, $tags) {

    $tagsString = "";
    foreach($tags as $key => $v) {
        $tagsString .= $key == count($tags)-1 ? $v : "{$v}|";
    }

    $patterns = array("/(<\s*\b({$tagsString})\b[^>]*>)/i", "/(<\/\s*\b({$tagsString})\b\s*>)/i");
    $output = preg_replace($patterns, "", $str);

    return $output;

}