<?php

require_once '../includes/include.php';

$cqpPage = true;

$textId = $_GET["text"] ?? 1;

$dbh = new PDO(
	"mysql:host=localhost;dbname=" . FACLAIR_DB_NAME . ";charset=utf8;",
	FACLAIR_DB_USER, FACLAIR_DB_PASSWORD);

$databaseHandle = new PDO(
	"mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=utf8;", DB_USER, DB_PASSWORD
);

$stmt = $dbh->prepare("SELECT * FROM corpus_text WHERE reference_number = :id");
$stmt->execute(array(':id' => $textId));
$text = $stmt->fetch(PDO::FETCH_ASSOC);

$pageTitle = $text['short_title'];

require_once '../includes/htmlHeader.php';

$html .= <<<HTML

	<style>
		table,
        th,
        td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 5px;
        }
 
        tr:nth-child(odd) {
            background-color: #aFbAf9;
        }
	</style>
	
	<table>
		<tbody>
HTML;

$skipFields = ['filename', 'imported', 'text_contents', 'reference_author', 'reference_editor', 'reference_volume', 'reference_style', 'add_to_corpus'];

if (empty($text)) {
	$html = "<h2>Sorry, this text currently has no entry</h2>";
} else {
	foreach ($text as $field => $data) {
        $data = $data ?? "";
		if (in_array($field, $skipFields)) {
			continue;
		}
		$friendlyField = Functions::getFriendlyFieldName($field, '_');

		if ($field == 'link') {
			$data = '<a href="' . $data . '" target="_blank">' . $data . '</a>';
		} else if ($field == 'download_file') {
			$data = '<a href="/text/' . $data . '" target="_blank">' . $data . '</a>';
		}
		$html .= '<tr><td>' . $friendlyField . '</td><td>' . nl2br($data) . '</td></tr>';

	}

	$html .= '</tbody></table>';
}

echo $html;


require_once '../includes/htmlFooter.php';