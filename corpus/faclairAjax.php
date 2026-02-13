<?php

require_once '../includes/include.php';


if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * File setup
 */
$file = fopen('../../faclair/draft.php', "a");

/**
 * Database setup
 */
$dbh = new PDO(
    "mysql:host=localhost;dbname=" . FACLAIR_DB_NAME . ";charset=utf8;",
    FACLAIR_DB_USER, FACLAIR_DB_PASSWORD);

//$databaseHandle = new PDO(
//    "mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=utf8;", DB_USER, DB_PASSWORD
//);

/**
 * AJAX calls
 */

switch($_POST['action'])
{
    case 'addCitation':
        $stmt = $dbh->prepare("SELECT short_title, reference_editor as ed, reference_author as au, reference_volume AS vol FROM corpus_text WHERE reference_number = :id");
        $stmt->execute(array(':id' => $_POST["text"]));
        $text = $stmt->fetch(PDO::FETCH_ASSOC);
        $title = $text["short_title"];
        $vol = $text["vol"] ? ' ' . $text["vol"] : '';
        $issue = $_POST["issue"] ? " {$_POST["issue"]}" : '';

        $break = $_POST['type'] == 'sense' ? '<br>' : '';
        $citation = trim($_POST["citation"]);
        $mark = trim($_POST["mark"]);
        $citation = $mark !== '' ? str_replace($mark, "<mark>{$mark}</mark>", $citation) : $citation;
        $preText = "";
        if ($text['ed'] || $text['au']) {
            $preText = ($text['au'])
                ? '<span class="small-caps">' . ucfirst(strtolower($text["au"])) . '</span>'
                : $text["ed"];
                ;
                $preText .= " ";
        }
        $html = <<<HTML
            \n
            <li>
                <div class="date">{$_POST['year']}</div>
                <div class="citation">
                    <span class="reference"><a href="https://dasg.ac.uk/corpus/textmeta.php?text={$_POST["text"]}" target="_blank" title="{$title}">{$preText}<em>{$title}</em>{$vol}{$issue}</a> <span class="pageref">{$_POST["page"]}</span></span>{$break}
                    <span class="quote">{$citation}</span>
                </div>
            </li>
HTML;
        fwrite($file, $html);
        break;

}