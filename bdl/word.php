<?php

/*
 * Generate a Word file of output
 */
require_once '../includes/include.php';

// require a logged in user with admin privileges for all access
Functions::requireAdmin();

//require Composer
require_once '../vendor/autoload.php';
$phpWord = new \PhpOffice\PhpWord\PhpWord();
\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
$documentProtection = $phpWord->getSettings()->getDocumentProtection();
$documentProtection->setEditing("none");
//print_r($documentProtection); die();


$dbh = DB::getDatabaseHandle();

$source = $_GET["source"];
$id = $_GET["id"];

$metadata = [];
$output = [];

switch ($source) {
    case "text":
        $sth = $dbh->prepare("SELECT * FROM bdl_text WHERE id = :textId");
        $sth->execute(array(":textId" => $id));
        $metadata = $sth->fetch(PDO::FETCH_ASSOC);
        $sql = <<<SQL
            SELECT t.id AS textid, p.number AS pagenum, l.number AS linenum,
                   diplomatic AS d, vernacular AS v, classical AS c, translation AS t,
                   l.notes AS notes
            FROM bdl_line l JOIN bdl_page p ON page_id = p.id 
                JOIN bdl_text t ON text_id = t.id
                    WHERE t.id = :textId
            ORDER BY CAST(pagenum AS UNSIGNED), CAST(linenum AS UNSIGNED)
SQL;

        $sth = $dbh->prepare($sql);
        $sth->execute(array(":textId" => $id));
        $output = $sth->fetchAll(PDO::FETCH_ASSOC);
        break;
    default:
        die("Invalid action");

}


/* Note: any element you append to a document must reside inside of a Section. */

// Adding an empty Section to the document...


$fontStyle = new \PhpOffice\PhpWord\Style\Font();
$fontStyle->setBold(false);
$fontStyle->setName('Calibri');
$section = $phpWord->addSection();

$text = <<<TEXT
    Metadata : {$metadata["id"]}
TEXT;
$section->addText($text);

$lines = "";

foreach ($output as $line) {
    $ref = "[{$line["pagenum"]}.{$line["textid"]}.{$line["linenum"]}]";

    $section->addText($ref);
    $section->addText("D: " . $line["d"]);
    $section->addText("V: " . $line["v"]);
    $section->addText("C: " . $line["c"]);
    $section->addText("T: " . $line["t"]);
    $section->addText("N: " . $line["notes"]);

    $section->addTextBreak(1);
}


/*
 * Note: it's possible to customize font style of the Text element you add in three ways:
 * - inline;
 * - using named font style (new font style object will be implicitly created);
 * - using explicitly created font style object.
 */

// Adding Text element with font customized inline...
$section->addText(
    '"Great achievement is usually born of great sacrifice, '
    . 'and is never the result of selfishness." '
    . '(Napoleon Hill)',
    array('name' => 'Tahoma', 'size' => 10)
);

// Adding Text element with font customized using named font style...
$fontStyleName = 'oneUserDefinedStyle';
$phpWord->addFontStyle(
    $fontStyleName,
    array('name' => 'Tahoma', 'size' => 10, 'color' => '1B2232', 'bold' => true)
);
$section->addText(
    '"The greatest accomplishment is not in never falling, '
    . 'but in rising again after you fall." '
    . '(Vince Lombardi)',
    $fontStyleName
);

// Adding Text element with font customized using explicitly created font style object...
$fontStyle = new \PhpOffice\PhpWord\Style\Font();
$fontStyle->setBold(true);
$fontStyle->setName('Calibri');
$fontStyle->setSize(13);
$myTextElement = $section->addText('"Believe you can and you\'re halfway there." (Theodor Roosevelt)');
$myTextElement->setFontStyle($fontStyle);

$file = 'BDL_' . $_GET["source"] . '_' . $_GET["id"] . '.docx';
header("Content-Description: File Transfer");
header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');
$xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
$xmlWriter->save("php://output");

