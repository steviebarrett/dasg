<?php

namespace models;

require_once 'includes/include.php';
require_once 'models/database.php';
$db = new database();



die("<h2>Commented out for safety");


$htmlFile = file_get_contents('/Users/stephen.barrett/Downloads/TranscriptionsNS04900155.html');

$rows = explode("</p>", $htmlFile);

$transHtml = '';
$id = null;

foreach ($rows as $row) {

	//preg_match('/(NS[0-9]+)/', $row, $matches);   //the original match with only the ID on the line by itself
    preg_match('/(NS[0-9]+)([^<]+)/', $row, $matches); //ID + title on the same line

	if ($matches) {

		echo $id . '<br>';

		//insert into the DB
//        $sql = "UPDATE transcription SET text = :html WHERE record_ai = :id"; //original

        if ($id) {
            $sql = "INSERT INTO transcription VALUES(:id, :html)";
            $db->exec($sql, array(":id" => $id, ":html" => $transHtml));
        }
 //       echo $transHtml . "<br><br>";

		//get the next ID
//        $id = $matches[0];  //original
		$id = $matches[1];

//      $transHtml = '';  //original
        $transHtml = <<<HTML
            <style>.c5{height:11pt}.c13 {font-style: italic;}</style>
            <br>{$matches[2]}<br>
HTML;

		continue;
	}

    //replace empty paragraphs with break(s)
//    $row = str_replace('<p class="c3 c5"><span class="c1"></span></p>', '<br><br>', $row);

	//strip all tag attributes
//	$row = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/si",'<$1$2>', $row);

	//strip all non <p> and <i> tags
//	$row = strip_tags($row, '<p><i>');



	$transHtml .= $row;
}




