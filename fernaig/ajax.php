<?php


require_once '../includes/include.php';

$dbh = DB::getDatabaseHandle();


switch ($_REQUEST["action"]) {
    case "getTextsByVolume":
        //get all texts
        $sth = $dbh->prepare("SELECT * FROM fernaig_text WHERE volume = ? ORDER BY CAST(number AS UNSIGNED) ASC");
        $sth->execute(array($_REQUEST["volume"]));
        $texts = $sth->fetchAll();
        echo json_encode($texts);
        break;
    case "createText":
        $sth = $dbh->prepare("INSERT INTO fernaig_text (volume, number) VALUES (?, ?)");
        $sth->execute(array($_REQUEST["volume"], $_REQUEST["number"]));
        $id = $dbh->lastInsertId();
        echo json_encode(array("id" => $id, "number" => $_REQUEST["number"]));
        break;
    case "createPage":
        $sth = $dbh->prepare("INSERT INTO fernaig_page (number, text_id) VALUES (?, ?)");
        $sth->execute(array($_REQUEST["number"], $_REQUEST["textid"]));
        $id = $dbh->lastInsertId();
        echo json_encode(array("id" => $id, "number" => $_REQUEST["number"]));
        break;
    case "createLine":
        $sth = $dbh->prepare("INSERT INTO fernaig_line (number, page_id) VALUES (?, ?)");
        $sth->execute(array($_REQUEST["number"], $_REQUEST["pageid"]));
        $id = $dbh->lastInsertId();
        echo json_encode(array("id" => $id, "number" => $_REQUEST["number"]));
        break;
    case "search":
        // 1) allowlist searchable fields (keys are what the UI sends; values are safe SQL identifiers)
        $allowedFields = [
            'diplomatic'    => 'l.diplomatic',
            'vernacular'    => 'l.vernacular',
            'classical'     => 'l.classical',
            'translation'   => 'l.translation',
            'notes'         => 'l.notes',
            'team_comments' => 'l.team_comments',
        ];

        // 2) normalise/validate inputs
        $searchFieldsIn = $_GET['f'] ?? [];
        if (!is_array($searchFieldsIn) || !$searchFieldsIn) {
            http_response_code(400);
            echo json_encode(['error' => 'No fields selected']);
            exit;
        }

        $ignoreTags       = (($_GET['ignoreTags'] ?? '') === 'true');
        $singleWord       = (($_GET['sw'] ?? '') === 'true');
        $accentSensitive  = (($_GET['as'] ?? '') === 'true');

        // 3) build safe expressions
        $searchExprs = [];
        foreach ($searchFieldsIn as $f) {
            if (!is_string($f) || !isset($allowedFields[$f])) {
                // hard-fail is usually better than silently ignoring:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid field']);
                exit;
            }

            $col = $allowedFields[$f]; // safe
            $searchExprs[] = $ignoreTags
                ? "REGEXP_REPLACE($col, '<[^>]+>', '')"
                : $col;
        }

        // 4) query value
        $q = $_GET['q'] ?? '';
        if (!is_string($q)) $q = '';
        if (mb_strlen($q, 'UTF-8') > 200) {
            http_response_code(400);
            echo json_encode(['error' => 'Query too long']);
            exit;
        }

        if (!$accentSensitive) {
            $q = getAccentInsens($q);
        }

        $prebound  = $singleWord ? "(^|[^[:alpha:]])" : "";
        $postbound = $singleWord ? "([^[:alpha:]]|$)" : "";

        // 5) build WHERE with safe field expressions
        $whereClause = '(' . implode(' REGEXP :q OR ', $searchExprs) . ' REGEXP :q)';

        $sql = "SELECT l.id AS id, page_id, l.number AS number, p.number AS pageNum, text_id,
                   diplomatic, vernacular, classical, translation, notes
            FROM fernaig_line l
            JOIN fernaig_page p ON l.page_id = p.id
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
    case "searchText":
        $sql = "SELECT id, 
                    number,
                    author,
                    firstline_c 
                FROM fernaig_text  
                WHERE author LIKE :qt OR firstline_c LIKE :qt OR notes LIKE :qt OR edition LIKE :qt OR notes LIKE :qt 
                    OR firstline_t LIKE :qt OR firstline_d LIKE :qt OR firstline_v LIKE :qt";
        $sth = $dbh->prepare($sql);

        $sth->execute(array(":qt" => "%{$_GET["qt"]}%"));
        $texts = $sth->fetchAll(PDO::FETCH_ASSOC);
        $output = [];

        foreach ($texts as $text) {
            $output[] = removeTags($text, array("strike"));
        }
        echo json_encode($output);
        break;
    case "getText":
        $sth = $dbh->prepare("SELECT * FROM fernaig_text WHERE id = :textId");
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

            SELECT p.id AS id, p.number AS number, text_id, isos, t.id as tid, t.number AS textnumber, volume
                   FROM fernaig_page p INNER JOIN fernaig_text t ON t.id = text_id
                        {$whereClause} 
                    ORDER BY 
                      volume,
                      LENGTH(REGEXP_SUBSTR(p.number, '^[0-9]+')),  -- Length of the numeric part
                      CAST(REGEXP_SUBSTR(p.number, '^[0-9]+') AS UNSIGNED),  -- Order by numeric part
                      SUBSTRING(p.number FROM LENGTH(REGEXP_SUBSTR(p.number, '^[0-9]+')) + 1), 
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
            SELECT l.id AS id, l.number AS number, p.number AS pageNum, t.number AS textNumber, text_id, diplomatic,
                   vernacular, classical, translation, l.notes AS notes, team_comments
                FROM fernaig_line l JOIN fernaig_page p ON l.page_id = p.id JOIN fernaig_text t ON p.text_id = t.id
                WHERE page_id = :pageId ORDER BY CAST(l.number AS UNSIGNED) ASC
SQL;

        $sth = $dbh->prepare($sql);
        $sth->execute(array(":pageId" => $_GET["pid"]));
        $lines = $sth->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($lines);
        break;
    case "getLine":
        $sth = $dbh->prepare("SELECT * FROM fernaig_line WHERE id = :lineId");
        $sth->execute(array(":lineId" => $_GET["lid"]));
        $line = $sth->fetch(PDO::FETCH_ASSOC);
        echo json_encode($line);
        break;
    case "saveText":
        $flu = removeTags($_POST["flc"], array("p", "span", "i", "sup"));
        $sql = <<<SQL
            UPDATE fernaig_text SET  
                number = :number, firstline_d = :fld, firstline_v = :flv, firstline_c = :flc, firstline_t = :trans, 
                firstline_untagged = :flu,
                author = :author, pagerange = :pagerange, edition = :edition, notes = :notes
            WHERE id = :id
SQL;
        $sth = $dbh->prepare($sql);
        try {
            $sth->execute(array(
                ":number" => $_POST["number"], "fld" => $_POST["fld"], ":flv" => $_POST["flv"], ":flc" => $_POST["flc"], ":flu" => $flu,
                ":trans" => $_POST["trans"], ":author" => $_POST["author"], ":pagerange" => $_POST["pagerange"],
                ":edition" => $_POST["edition"], ":notes" => $_POST["notes"],
                ":id" => $_POST["id"]));
        } catch (Exception $e) {
            echo json_encode(array("error" => "The text could not be saved"()));
            return;
        }
        echo json_encode(array("msg" => "Text saved"));
        break;
    case "saveLine":
        $fields = ['diplomatic', 'vernacular', 'classical', 'translation', 'notes'];
        foreach ($fields as $field) {
            $_POST[$field] = removeTags($_POST[$field], array("span"));
        }
        print_r($_POST);
        $sql = <<<SQL
            UPDATE fernaig_line SET  
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
            echo json_encode(array("error" => "The line could not be saved"));
            return;
        }
        echo json_encode(array("msg" => "Line saved"));
        break;
    case "saveIsos":
        $sql = <<<SQL
            UPDATE fernaig_page SET isos = :isos WHERE id = :id 
SQL;
        $sth = $dbh->prepare($sql);
        try {
            $sth->execute(array(
                ":isos" => $_POST["isos"], ":id" => $_POST["id"]));
        } catch (Exception $e) {
            echo json_encode(array("error" => "Isos could not be saved"()));
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