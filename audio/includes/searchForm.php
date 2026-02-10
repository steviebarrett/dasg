<?php

$hasTranscriptions = AudioItems::archiveHasTranscriptions($_GET["archive"]);

$searchTypes = array(
    "metadata" => "for a recording",
    "transcriptions" => "the transcriptions" 
);
$typesHtml = "";
foreach ($searchTypes as $stype => $label) {
    $checked = ($stype == $searchDomain) ? "checked" : "";
    $typesHtml .= <<<HTML
        <li>
            <input type="radio" name="searchType" id="search_{$stype}" value="{$stype}" {$checked}/>
            <label for="search_{$stype}">{$label}</label>
        </li>
HTML;
}
$searchTypesHtml =  <<<HTML
        <div id="searchTypes">
            <ul>
			    {$typesHtml}
            </ul>
		</div>
HTML;

$searchFilterHtml = (!$showFilters) ? "" : <<<HTML
        <div id="searchFilters" class="{$showSearchFilters}">
			Filter by Keyword:
			<select id="audioKeyword">
				<option selected="selected">--show all--</option>
				<option class="audio_conversation">conversation</option>
				<option class="audio_misc">misc</option>
				<option class="audio_music">music</option>
                <option class="audio_poem">poem</option>
				<option class="audio_song">song</option>
				<option class="audio_story">story</option>
			</select>
		</div>
HTML;

$accentedChars = array('à', 'è', 'ì', 'ò', 'ù', 'á', 'é', 'í', 'ó', 'ú');
$accentHtml = <<<HTML
HTML;

foreach ($accentedChars as $char) {
    $accentHtml .= '<a href="#" class="accentChar">' . $char . '</a>&nbsp;';
}

$csrfField = Csrf::field();
$searchForm = <<<HTML

    <div>
		<form method="POST" id="searchAudio">
		    {$csrfField}
			<input id="searchTerm" name="searchTerm" value="{$_GET["searchTerm"]}" type="text" autocomplete="off" placeholder="search"/>
            <input type="hidden" name="archive" value="{$_GET["archive"]}"/>
			<input type="submit" class="dasg_medButton" value="search"/>
			<button class="dasg_smlButton" id="searchReset">reset</button>
            <br/>{$accentHtml}
        {$searchTypesHtml}
		{$searchFilterHtml}
		</form>
	</div>
HTML;


        