<?php

require_once '../includes/include.php';

// require a logged in user with admin privileges for all access
Functions::requireAdmin();

echo <<<HTML
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Transcription Parser</title>
    </head>
    <body>
HTML;

/**
 * Transcription Parser
 */

$dbh = DB::getDatabaseHandle();

$dir = __DIR__ . "/transcriptions";
$files = scandir($dir);

foreach ($files as $file) {
    if (substr($file, 0, 1) == '.') {continue; }    //skip directories

    $fp = @fopen($dir . "/" . $file, "r");  //open the transcription file
    if ($fp) {
        $line = [];   //used to store the line info
        $pageId = null;
        while (($text = fgets($fp, 4096)) !== false) {    //read a line from the file
            //skip unneeded lines
            if (substr($text, 0, 3) == "[p "|| substr($text, 0, 3) == "[t "
                || $text == "\n") {
                continue;
            }

            //parse a line identifier
            preg_match('/\[(\w+)\.(\w+)\.(\w+)\]/', $text, $matches);

            if (!empty($matches)) {         //is an identifier
                $line = array(
                    "id"        => $matches[0],
                    "pageNum"   => $matches[1],
                    "textNum"   => $matches[2],
                    "lineNum"   => $matches[3]
                );
                echo $line["id"] . "<br>";

                echo <<<HTML
        Page : {$line["pageNum"]} <br>
        Text : {$line["textNum"]} <br>
        Line : {$line["lineNum"]} <br>

HTML;


                //add the text
                $sth = $dbh->prepare("REPLACE INTO bdl_text (id) VALUES(:textNum)");
                $sth->execute(array(":textNum" => $line["textNum"]));

                //get the page ID
                $sth = $dbh->prepare("SELECT id FROM bdl_page WHERE text_id = :textId AND number = :pageNum");
                $sth->execute(array(":textId" => $line["textNum"], ":pageNum" => $line["pageNum"]));
                if (!$result = $sth->fetch(PDO::FETCH_ASSOC)) {     //page not saved yet, so add to DB
                    $sth = $dbh->prepare("INSERT INTO bdl_page (number, text_id) VALUES(:pageNum, :textNum)");
                    $sth->execute(array(":pageNum"=>$line["pageNum"], ":textNum"=>$line["textNum"]));
                    $pageId = DB::getLastId(DB_NAME, 'bdl_page') - 1;
                }
                    // else {                                            //page exists
        //            $pageId = $result['id'];
        //        }
        //        $line["pageId"] = $pageId;

            } else {                    //not an identifier so treat as line text
                echo $text . "<br>";

                //ensure there is a line number
                if ($line["lineNum"]) {
                    $sth = $dbh->prepare("INSERT INTO bdl_line (number, page_id, diplomatic) VALUES(:lineNum, :pageId, :text);");
                    $sth->execute(array(":lineNum" => $line["lineNum"], ":pageId" => $pageId, ":text" => $text));
                }
            }


        }
        if (!feof($fp)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($fp);
    }
}

echo "\n\n</body></html>";