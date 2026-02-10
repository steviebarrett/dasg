<?php

$pageTitle = "Cluas ri Claisneachd";
$pageSlug = "audio";
$audioSlug = "Browse";

$cqpPage = true;

$javascriptBlock = <<<HTML
HTML;
		
require_once '../includes/include.php';
require_once '../includes/htmlHeader.php';

error_reporting(E_ALL);
require_once 'includes/audioVars.php';
require_once 'includes/audioHeader.php';


$audioItems = AudioItems::getAudioItemReferences($_GET["archive"]); //get all completed audio items

echo <<<HTML
    <div>   <!-- container -->
HTML;

foreach ($audioItems as $ref) {
	$item = AudioItems::getAudioItem($ref);
	$isNewHtml = $item->getIsNew() ? '<div class="newAudioItem"new">new</div>' : "";
	$subtitle = ($_GET["archive"] == "crc") ? $item->getFieldworker() : $item->getContributors();
	echo <<<HTML
        <a href="/audio/view/s/{$_GET["archive"]}/{$ref}" title="{$item->getTitle()}">
            <div class="audioCard">
                {$isNewHtml}
                <img src="/audio/images/{$ref}.jpg" width="100px" alt="{$item->getTitle()}"/>
                <h2>{$item->getTitle()}</h2>
                <h3>{$subtitle}</h3>
            </div>
        </a>
HTML;
}
echo <<<HTML
    </div>  <!-- container -->
HTML;

require_once '../includes/htmlFooter.php';