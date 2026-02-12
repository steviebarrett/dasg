<?php

require_once '../includes/include.php';

//error_reporting(E_ALL); ini_set('display_errors', '1');

$pageSlug = "gairmRecord";
$pageTitle = "Gairm Index Record";

$cqpPage = true;

$javascriptBlock = <<<HTML

HTML;

require_once '../includes/htmlHeader.php';

$id = isset($_GET["id"]) ? Functions::e($_GET["id"]) : "";
$query = isset($_GET["query"]) ? Functions::e($_GET["query"]) : "";

$gairmRecord = GairmRecords::getGairmRecord($id);

if (empty($gairmRecord)) {
	echo "<h3 class='error'>No record found</h2>";
} else {
	
	$queryString = urldecode($query);
	$backHtml = <<<HTML
		<a href="index.php?{$queryString}" title="Back to search">< Back to search results</a>
HTML;
	
	$commentsHtml =	$gairmRecord->getComments() === "" ? "" : "<dt>Comments:</dt><dl>{$gairmRecord->getComments()}</dl>";
	
	$recordHtml = <<<HTML
	<dl id="gairmRecordFull">
		<dt>Title:</dt>
		<dd>{$gairmRecord->getTitle()}</dd>
		<dt>Last Name:</dt>
		<dd>{$gairmRecord->getLastName()}</dd>
		<dt>First Name:</dt>
		<dd>{$gairmRecord->getFirstName()}</dd>
		<dt>Origin:</dt>
		<dd>{$gairmRecord->getOrigin()}</dd>
		<dt>Year:</dt>
		<dd>{$gairmRecord->getYear()}</dd>
		<dt>Volume:</dt>
		<dd>{$gairmRecord->getVolume()}</dd>
		<dt>First Page:</dt>
		<dd>{$gairmRecord->getFirstPage()}</dd>
		<dt>Last Page:</dt>
		<dd>{$gairmRecord->getLastPage()}</dd>
		<dt>Language:</dt>
		<dd>{$gairmRecord->getLanguage()}</dd>
		<dt>Type:</dt>
		<dd>{$gairmRecord->getType()}</dd>
		<dt>Genre:</dt>
		<dd>{$gairmRecord->getGenre()}</dd>
		<dt>Register:</dt>
		<dd>{$gairmRecord->getRegister()}</dd>
		{$commentsHtml}
	</dl>
HTML;
	
	echo $backHtml;
	echo $recordHtml;
}


require_once '../includes/htmlFooter.php';