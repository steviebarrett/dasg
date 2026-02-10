<?php

$pageTitle = "Cluas ri Claisneachd";
$pageSlug = "audio";

$cqpPage = true;

$javascriptBlock = <<<HTML
	<script src="/jwplayer-7.12.8/jwplayer.js"></script>
	<script>jwplayer.key="lteoxtWdKAG/o2x8u8IxYTsucgnBzYdbiM/3lQ==";</script>
    <script>
        function fbshareCurrentPage() {
            window.open("https://www.facebook.com/sharer/sharer.php?u="+escape(window.location.href)+"&t="+document.title, '',
                'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');
            return false;
        }
</script>
HTML;

require_once '../includes/include.php';
require_once '../includes/htmlHeader.php';

$ref 	= $_GET["ref"];
$archive= $_GET["archive"];
$item 	= AudioItems::getAudioItem($ref);
if (empty($item)) {
    Functions::writeError("Sorry, the requested resource could not be found");
}
$mediaFormat = ($item->getIsVideo()) ? "mp4" : "mp3";
$domain = $_GET["domain"];

/* Metadata and colour coding of results */
$metadata = array("location" => $item->getLocation(), "fieldworker" => $item->getFieldworker(), "contributors" => $item->getContributors(),
    "transcription_by" => $item->getTranscriptionBy(), "info" => $item->getContentsInformation()
);

//make search term accent insensitive
$searchterm = Functions::getAccentInsensitive($_GET["searchTerm"], false);

foreach ($metadata as $key => $value) {
    if ($searchterm && preg_match("/{$searchterm}/i", $value, $matches)) {
        $metadata[$key] = preg_replace("/{$matches[0]}/", '<span class="hi">$0</span>', $value);
    }
}

/* Format the language HTML */
$langHtml = implode(", ", $item->getLanguagesArray());
$keywords = $item->getKeywordsArray();
$keywordsCol = array();
foreach ($keywords as $keyword) {
    $keywordsCol[] = "<span class=\"audio_{$keyword}\">{$keyword}</span>";
}
$keywordHtml = implode(" ", $keywordsCol);

//check for transcription
$transcriptionLink = "";
$transcriptionPath = "transcriptions/{$ref}.txt";
if (file_exists($transcriptionPath)) {
    $transcriptionLink = <<<HTML
		<div id="transcriptionLink">Transcription: <a href="/audio/{$transcriptionPath}" title="Download Transcription" download target="_blank">Download</a></div>
HTML;
}

//assemble detailed contents if not empty
if ($detailedContents = $item->getDetailedContents()) {
    $detailHTML = "";
    $contentItems = explode(';', $detailedContents);
    $selectedItem = "";
    foreach ($contentItems as $contentItem) {
        $info = explode('|', $contentItem);
        $timecode = trim($info[1]);
        $timecode = str_replace(".000", "", $timecode);
        $timeElems = explode(':', $timecode);
        $seconds = (int)array_pop($timeElems);			//get the seconds value
        $seconds += (int)array_pop($timeElems) * 60;		//add the minutes
        if (!empty($timeElems)) {
            $seconds += (int)array_pop($timeElems) * 3600;	//add the hours
        }
        if ($searchterm != "" && preg_match("/{$searchterm}/i", $info[0], $matches)) {
            $info[0] = preg_replace("/{$matches[0]}/", '<span class="hi">$0</span>', $info[0]);
            if ($selectedItem === "") {
                $selectedItem = "seek_{$seconds}";
            }
        }
        $detailHTML .= <<<HTML
			<li id="seek_{$seconds}" class="seekContents">{$info[0]}</li>
HTML;
    }
    
    $detailedContents = <<<HTML
		<ul id="audioDetailedContents">
			{$detailHTML}
		</ul>
HTML;
}

$searchString = urlencode($_GET["searchTerm"]);
$backLink = "/audio/browse/{$archive}"; //default to browse (index.php)
$uri = $_SERVER["REQUEST_URI"];
if (!empty($_GET["searchTerm"]) || substr($uri, (strlen($uri)-1), 1) == "/") {   //if there's a trailing slash then the referrer was search.php
    $backLink = "/audio/search/{$domain}/{$archive}/{$searchString}";
}

//check if there is a transcription_by value and populate the HTML
if (!empty($item->getTranscriptionBy())) {
    $transByHtml = <<<HTML
	<dt>Transcription By:</dt>
	<dd>{$metadata["transcription_by"]}</dd>
HTML;
} else {
    $transByHtml = "";
}

//check if there should be a VTT
$vttLink = ($item->getTranscribed()) == "Yes" ? '"file": "/audio/transcriptions/' . $ref . '.vtt",' : '';
//Get a background image for the audio player if there is no transcription
$jsImageCode = ($item->getTranscribed() == "Yes") ? "" : '"image": "/audio/images/' . $item->getDigitalReference() . '.jpg",';
//Assemble the additonal infor HTML]
$additionalInfoHtml = ($item->getAdditionalInfo()) ? $item->getAdditionalInfo() : "<em>If you are a relative or have any additonal information about this informant please use the above 'Contact us about this item' link to let us know.</em>";
echo <<<HTML
	<style>
		.jw-icon-cc {
			display:none;
		}
	</style>
    <div id="audioContainer">
       <a href="{$backLink}">< Back</a>
       <h2>{$item->getTitle()}</h2>
	   <div id="audioTitle">
	   </div>
	   
       <div id="playerJumpLinks">
            <div id="jumpForward" class="jumpLink">
               <a id="forwardTen" class="playerControl">Forward 10 Seconds >></a>
            </div>
            <div class="jumpLink">
	           <a id="backTen" class="playerControl"><< Back 10 Seconds</a>
            </div>
            
	   </div>
	   
       {$detailedContents}
       
       {$transcriptionLink}
       
       <a href="mailto:mail@dasg.ac.uk?subject={$ref}">Contact us about this item</a>
       
	   <div id="audioMetadata">
		  <dl>
			<!--dt>Date:</dt>
			<dd>{$item->getDate()}</dd-->
			<dt>Location:</dt>
			<dd>{$metadata["location"]}</dd>
            <dt>Year:</dt>
			<dd>{$item->getYear()}</dd>
			<dt>Language(s):</dt>
			<dd>{$langHtml}</dd>
			<dt>Fieldworker:</dt>
			<dd>{$metadata["fieldworker"]}</dd>
			<dt>Contributors:</dt>
			<dd>{$metadata["contributors"]}</dd>
            {$transByHtml}
			<dt>Info:</dt>
			<dd>{$metadata["info"]}</dd>
			<dt>Keywords:</dt>
			<dd>{$keywordHtml}</dd>
            <dt>Additional Info:</dt>
			<dd>{$additionalInfoHtml}</dd>
		  </dl>
	   </div> <!-- end container -->
	   
	   <a href="{$backLink}">< Back</a>
    </div>
    
	<div class="socialMedia audioSocial">
	
		<a href="https://twitter.com/share?ref_src=twsrc%5Etfw" class="twitter-share-button" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
        
		<br/>
        <a id="fbshare" href="javascript:fbshareCurrentPage()" target="_blank" alt="Share on Facebook">Facebook</a>
        
	</div>
	
<script type="text/javascript">

    $(function() {
        //facebook share button instead of link
        $('#fbshare').html('<img src="/images/fbshare.png">');
        
        var seekTo = '{$selectedItem}';
        //scroll to detailed contents item
        if (seekTo) {
            scrollDetailedContents(seekTo);
        }
        
        jwplayer('audioTitle').setup({
            		"playlist": [{
                		"file": "/audio/mp3/{$ref}.{$mediaFormat}",
                        {$jsImageCode}
        				"height": 500,
                		"tracks": [{
                    		{$vttLink}
                    		"label": "English",
                    		"kind": "captions",
                    		"default": true
                }]
            }]
        });
        
        if (localStorage['{$ref}'] == '' || localStorage['{$ref}'] == 'undefined') {
        		var currentPosition = 0;
        } else {
        	if (localStorage['{$ref}'] == "null") {
        			localStorage['{$ref}'] = 0;
        	} else {
        		var currentPosition = localStorage['{$ref}'];
        	}
        }
        
        var fontSize = ("{$mediaFormat}" == "mp4") ? 20 : 30;   //smaller font for video
    	jwplayer().setCaptions({"color": "#ffffff", "backgroundColor": "#000000", "fontSize": fontSize});
    	
    	jwplayer().once('play',function(){
            //update the database
            $.ajax('/ajax/audio.php?action=updatePlays&ref={$ref}');
    		if (currentPosition > 0 && Math.abs(jwplayer().getDuration() - currentPosition) > 5) {
    			jwplayer().seek(currentPosition);
    		}
    	});
    	
    	window.onunload = function() {
       		localStorage['{$ref}'] = jwplayer().getPosition();
    	}
    	
    	$('.seekContents').on('click', function() {
    		var seconds = $(this).attr('id').substr(5);
    		jwplayer().seek(seconds);
            $('.seekContents').removeClass('audioPlaying');
            $('#seek_' + seconds).addClass('audioPlaying');
    	});
    	
    	$('#backTen').on('click', function() {
    		var pos = jwplayer().getPosition();
    		pos = Math.round(pos);
            pos = pos < 10 ? 0 : pos - 10;
            jwplayer().seek(pos);
    	});
    	
        $('#forwardTen').on('click', function() {
    		var pos = jwplayer().getPosition();
            var dur = jwplayer().getDuration();
    		pos = Math.round(pos);
            pos = pos + 10 > dur ? dur : pos + 10;
            jwplayer().seek(pos);
    	});
    	
        //show the currently playing section in detailed contents
        jwplayer().on('time', function(event) {
            var pos = Math.round(event.position);
            var s = 'seek_' + pos;
            var id = '#' + s;
            if ($(id).length && !$(id).hasClass('audioPlaying')) {
                $('.seekContents').removeClass('audioPlaying');
                $(id).addClass('audioPlaying');
                scrollDetailedContents(s);
            }
        });
    });
    
    function scrollDetailedContents(s) {
        $('#audioDetailedContents').scrollTop(
                $('#' + s).offset().top - $('#audioDetailedContents').offset().top + $('#audioDetailedContents').scrollTop());
    }
    
    
</script>
HTML;
                    		
                    		require_once '../includes/htmlFooter.php';