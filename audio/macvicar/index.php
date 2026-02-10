<?php

$pageTitle = "Donald MacVicar";
$pageSlug = "macvicar";
$audioSlug = "MacVicar";

$cqpPage = true;

$javascriptBlock = <<<HTML
    <style>
        ul {
            list-style-type: none;
        }

        .playerBox {
            background-color: #ddd; 
            width: 30em; 
            margin-left: 1em; 
            padding: 1em; 
            border: 1px solid black;
        }
    </style>
HTML;
		
require_once '../../includes/include.php';
require_once '../../includes/htmlHeader.php';

$filenames = array("GU_MacVicar_I", "GU_MacVicar_II", "GU_MacVicar_III", "GU_MacVicar_IV", "GU_MacVicar_V", "GU_MacVicar_VI",
    "GU_MacVicar_VII", "GU_MacVicar_VIII", "GU_MacVicar_IX", "GU_MacVicar_X"
);
$fileListHtml = "";
foreach ($filenames as $filename) {
    $fileListHtml .= <<<HTML
        <li><a href="?ref={$filename}" title="{$filename}">{$filename}</a></li>
HTML;
}

$ref = empty($_GET["ref"]) ? $filenames[0] : $_GET["ref"];

echo <<<HTML
    <div>   <!-- container -->
        <div class="playerBox">
            <h3>{$ref}</h3>
            <audio controls="controls" id="audio_player">
                <source src="/audio/mp3/{$ref}.mp3" type="audio/mpeg" />
                Your browser does not support the audio element.
            </audio>
        </div>
        
        <ul>
            {$fileListHtml}
        </ul>

    </div>  <!-- container -->
HTML;

require_once '../../includes/htmlFooter.php';