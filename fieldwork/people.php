<?php


require_once '../includes/include.php';

$cqpPage = true;	//hide the language switch

$id = Functions::e($_GET["id"]);
//get the person object from DB data
$person = FieldworkPersons::getPerson($id);

$pageTitle = $person->getFirstName() . " " . $person->getLastName();

require_once '../includes/htmlHeader.php';

if ($imageUrl = Functions::urlEncode($person->getImageUrl())) {
	echo <<<HTML
		<div id="fieldworkPersonImage">
			<img src="/fieldwork/images/people/{$imageUrl}" alt="{$pageTitle}"/>
		</div>
HTML;
}

echo Functions::e($person->getBiog());

require_once '../includes/htmlFooter.php';
