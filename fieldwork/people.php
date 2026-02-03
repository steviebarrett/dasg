<?php


require_once '../includes/include.php';

$cqpPage = true;	//hide the language switch

//get the person object from DB data
$person = FieldworkPersons::getPerson($_GET["id"]);

$pageTitle = $person->getFirstName() . " " . $person->getLastName();

require_once '../includes/htmlHeader.php';

if ($imageUrl = $person->getImageUrl()) {
	echo <<<HTML
		<div id="fieldworkPersonImage">
			<img src="/fieldwork/images/people/{$imageUrl}" alt="{$pageTitle}"/>
		</div>
HTML;
}

echo $person->getBiog();

require_once '../includes/htmlFooter.php';
