<?php

$pageTitle = "Cluas ri Claisneachd";
$pageSlug = "audio";
$audioSlug = "About";

$cqpPage = true;

$javascriptBlock = <<<HTML
HTML;

require_once '../includes/include.php';
require_once '../includes/htmlHeader.php';
require_once 'includes/audioVars.php';
require_once 'includes/audioHeader.php';

$archive    = $_GET["archive"];
$slug       = $audioSlug;
$language   = $lang;
$pageText   = "";

//get the page data from the database
$dbh = DB::getDatabaseHandle(DB_AUDIO);
try {
    $sth = $dbh->prepare("SELECT text
					FROM page
					WHERE archive = :archive AND slug = :slug AND language = :language;");
    $sth->execute(array(":archive"=>$archive, ":slug" => $slug, ":language" => $language));
    $row = $sth->fetch();
    $pageText = $row["text"];
} catch (PDOException $e) {
    echo $e->getMessage();
}

$languageChoiceHtml = <<<HTML

HTML;

echo <<<HTML
    <div>   <!-- container -->
        <div>
            <a href="en" title="English">English</a>
            <a href="gd" title="Gaelic">Gàidhlig</a>
        </div>

        {$pageText}
HTML;

echo <<<HTML
    </div>  <!-- container -->
HTML;

require_once '../includes/htmlFooter.php';