<?php

if (!isset($_GET["archive"])) {
    $_GET["archive"] = "crc";       //default archive
}

if (isset($_POST["searchTerm"])) {
    $_GET["searchTerm"] = $_POST["searchTerm"];
}

$domain = isset($_GET["domain"]) ? $_GET["domain"] : "s";

//Assemble the archive navigation HTML
$navElems = "";
foreach ($archives as $abbr => $name) {
    if (in_array($abbr, $hideArchives)) {   //hide links to any non-public archive(s)
        continue;
    }
    if ($abbr == $_GET["archive"]) {
        $navElems .= <<<HTML
            <div class="audioArchiveLink archiveMenuSelected">{$name}</div>
HTML;
    } else {
        $navElems .= <<<HTML
            <div class="audioArchiveLink">
                <a href="/audio/about/{$abbr}/{$lang}" title="{$name}">{$name}</a>
            </div>
HTML;
    }
}

$subTabs = array(
    "About" => '<a href="/audio/about/' . $_GET["archive"] . '/' . $lang . '">About</a>',
    "Browse" => '<a href="/audio/browse/' . $_GET["archive"] . '">Browse</a>',
    "Search" => '<a href="/audio/search/' . $domain . '/' . $_GET["archive"] . '/">Search</a>'
);

echo <<<HTML
    <div id="archiveNav">
        {$navElems}
    </div>
    
    <br class="clear"/>
    
    <div id="audioBrowseSearchLinks">
HTML;
        
        foreach ($subTabs as $tab => $link) {
            if ($tab == $audioSlug) {
                echo <<<HTML
            <div class="archiveMenuSelected">
                {$tab}
            </div>
HTML;
            } else {
                echo <<<HTML
            <div>
                {$link}
            </div>
HTML;
            }
        }
        
        echo <<<HTML
        <br class="clear"/>
        <br/>
    </div>
HTML;
        
        