<?php


if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

$archive = isset($_GET["archive"]) ? Functions::e($_GET["archive"]) : "crc";
$domain = isset($_GET["domain"]) ? Functions::e($_GET["domain"]) : "s";

//Assemble the archive navigation HTML
$navElems = "";
foreach ($archives as $abbr => $name) {
    if (in_array($abbr, $hideArchives)) {   //hide links to any non-public archive(s)
        continue;
    }
    if ($abbr == $archive) {
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
    "About" => '<a href="/audio/about/' . $archive . '/' . $lang . '">About</a>',
    "Browse" => '<a href="/audio/browse/' . $archive . '">Browse</a>',
    "Search" => '<a href="/audio/search/' . $domain . '/' . $archive . '/">Search</a>'
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
                $tabEsc = Functions::e($tab);
                echo <<<HTML
            <div class="archiveMenuSelected">
                {$tab}
            </div>
HTML;
            } else {
                //$link = Functions::urlEncode($link);
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
        
        