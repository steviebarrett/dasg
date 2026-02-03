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
        $searchFields = $_GET["f"];
        $ignoreTags = $_GET["ignoreTags"];
        //ignore tags
        if ($ignoreTags === 'true') {
            foreach ($searchFields as $k => $f) {
                $searchFields[$k] = "REGEXP_REPLACE({$f}, '<[^>]+>', '') ";
            }
        }

        $q = $_GET["q"];
        //replace the 'any character' dot with an alpha to handle diacritics etc
        $q = str_replace(".", "[[:alpha:]]", $q);

        //accent insensitive search
        if ($_GET["as"] != 'true') {
            $q = getAccentInsens($_GET["q"]);
        }

        //check for single word search
        $prebound = $_GET["sw"] == 'true' ? "(^|[^[:alpha:]])" : "";
        $postbound = $_GET["sw"] == 'true' ? "([^[:alpha:]]|$)" : "";

        $fieldQuery = implode(" REGEXP :q OR ", $searchFields);
        $whereClause = $fieldQuery . " REGEXP :q ";

        $sql = "SELECT l.id AS id, page_id, l.number AS number, p.number AS pageNum, text_id, 
                    diplomatic,
                    vernacular, 
                    classical, 
                    translation, 
                    notes
                FROM bdl_line l JOIN bdl_page p ON l.page_id = p.id 
                WHERE {$whereClause}";
        $sth = $dbh->prepare($sql);

        $sth->execute(array(":q" => "{$prebound}{$q}{$postbound}"));
        $text = $sth->fetchAll(PDO::FETCH_ASSOC);
        $output = [];

        foreach ($text as $line) {
            $output[] = removeTags($line, array("strike"));
        }
        echo json_encode($output);
        break;
    case "searchText":
        $sql = "SELECT id, 
                    author,
                    firstline_c 
                FROM bdl_text l 
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