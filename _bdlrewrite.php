<?php

echo "hello world";

function replaceInHtmlFiles($directory) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

    foreach ($iterator as $file) {
        if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'html') {
            $filePath = $file->getRealPath();

            // Read file contents
            $content = file_get_contents($filePath);

            // Replace <form></form> with <div></div>
            $from = <<<HTML
<form id="sites-searchbox-form" action="https://sites.google.com/site/deansbook/system/app/pages/search">
<input type="text" onpropertychange="JOT_setTextDir(this)" oninput="JOT_setTextDir(this)" dir="" id="jot-ui-searchInput" name="q" autocomplete="off" size="20" />
<input type="hidden" id="sites-searchbox-scope" name="scope" value="search-site" />
<div class="goog-inline-block goog-button goog-button-base   "><div class="goog-inline-block goog-button-base-outer-box"><div class="goog-inline-block goog-button-base-inner-box"><div class="goog-button-base-pos"><div class="goog-button-base-top-shadow"> </div><div id="sites-searchbox-select-button-wrapper" class="goog-button-base-content " style=""><div class="goog-inline-block" id="sites-searchbox-select-button">Search this site</div><div id="sites-searchbox-select-button-menu" class="goog-inline-block"><div id="sites-searchbox-select-button-menu-inner"><div class="goog-toolbar-menu-button-dropdown"> </div></div></div></div></div></div></div></div>
</form>
HTML;
            $to = <<<HTML
<div id="logo">
    <a href="/index.php" title="DASG Home">
        <img src="/images/logo.png" width="186px" height="182px" alt="DASG Logo">
    </a>
</div>
    
 
    <!--form id="sites-searchbox-form" action="https://sites.google.com/site/deansbook/system/app/pages/search">
    <input type="text" onpropertychange="JOT_setTextDir(this)" oninput="JOT_setTextDir(this)" dir="" id="jot-ui-searchInput" name="q" autocomplete="off" size="20" />
    <input type="hidden" id="sites-searchbox-scope" name="scope" value="search-site" />
    <div class="goog-inline-block goog-button goog-button-base   "><div class="goog-inline-block goog-button-base-outer-box"><div class="goog-inline-block goog-button-base-inner-box"><div class="goog-button-base-pos"><div class="goog-button-base-top-shadow"> </div><div id="sites-searchbox-select-button-wrapper" class="goog-button-base-content " style=""><div class="goog-inline-block" id="sites-searchbox-select-button">Search this site</div><div id="sites-searchbox-select-button-menu" class="goog-inline-block"><div id="sites-searchbox-select-button-menu-inner"><div class="goog-toolbar-menu-button-dropdown"> </div></div></div></div></div></div></div></div>
    </form-->
    
    
HTML;

            $updatedContent = str_replace($from, $to, $content);

            // Save the updated content back to the file
            file_put_contents($filePath, $updatedContent);

            echo "Updated: $filePath\n";
        }
    }
}

$directory = __DIR__ . '/BDL_DonaldMeek'; // Adjust path if necessary
replaceInHtmlFiles($directory);

echo "Processing complete.\n";
