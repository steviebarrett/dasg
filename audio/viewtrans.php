<?php

$pageTitle = "Cluas ri Claisneachd";
$pageSlug = "audio";

$cqpPage = true;

$javascriptBlock = <<<HTML
<script>
	var elem;

	$(function() {

		var elems = $('span.highlight').toArray();
		var id = -1;
		$('#findPrev').addClass('disabled');

		$('.findTerm').on('click', function() {
			$('.highlight').removeClass('highlightStrong');
			if ($(this).attr('id') == 'findPrev') {
				id--;
			} else {
				id++;
				$('#findPrev').removeClass('disabled');
			}
			if (id >= elems.length-1) {
				id = elems.length-1;
				$('#findNext').addClass('disabled');
			} else if (id < 1) {
				id = 0;	
				$('#findPrev').addClass('disabled');
				$('#findNext').removeClass('disabled');
			} else {
				$('#findNext').removeClass('disabled');
			}
			elem = elems[id];
			$(elem).addClass('highlightStrong');
			var idPos = $(elem).offset().top;
            $('html,body').animate({scrollTop: idPos-150},'fast');
		});

		$('#transTop').on('click', function() {
			$('html,body').animate({scrollTop: 0},'fast');
			id = -1;
			$('#findPrev').addClass('disabled');
			$('#findNext').removeClass('disabled');
		});
	});
</script>
HTML;

require_once '../includes/include.php';
require_once '../includes/htmlHeader.php';

$q = Functions::e(["q"]);
echo <<<HTML
	<div class="fixedNav">
		<h4 class="transEx">{$q}</h4>
		<div class="controlBar">
			<input id="findPrev" class="findTerm" value="prev" type="button"/>
			<input id="findNext" class="findTerm" value="next" type="button"/>
		</div>
		<input type="button" id="transTop" value="back to top" type="button"/>
	</div>
HTML;


$ref = $_GET['ref'] ?? '';
if (!is_string($ref)) {
    http_response_code(400);
    exit('Bad request');
}

/**
 * STRICT allowlist:
 * - No slashes, no dots, no colons => blocks traversal and URL schemes.
 * - Adjust max length to your IDs.
 */
if (!preg_match('/\A[A-Za-z0-9_-]{1,80}\z/', $ref)) {
    http_response_code(404);
    exit('Not found');
}

$baseDir = realpath(__DIR__ . '/transcriptions');
if ($baseDir === false) {
    http_response_code(500);
    exit('Server misconfig');
}

// Build path and resolve it (realpath requires existence)
$requested = $baseDir . DIRECTORY_SEPARATOR . $ref . '.txt';
$realFile = realpath($requested);
if ($realFile === false) {
    http_response_code(404);
    exit('Not found');
}

// Enforce: must stay inside transcriptions/
$prefix = $baseDir . DIRECTORY_SEPARATOR;
if (strncmp($realFile, $prefix, strlen($prefix)) !== 0) {
    http_response_code(404);
    exit('Not found');
}

// Local-file read (not URL-capable sink)
$fh = @fopen($realFile, 'rb');
if ($fh === false) {
    http_response_code(404);
    exit('Not found');
}

$transcription = stream_get_contents($fh);
fclose($fh);

if ($transcription === false) {
    http_response_code(404);
    exit('Not found');
}

//colour code the search term
$q = Functions::getAccentInsensitive($q, false);
$q = preg_quote($q, '/');
$transcription = preg_replace("/({$q})/iu", '<span class="highlight">$0</span>', $transcription);

header('Content-Type: text/plain; charset=UTF-8');
echo nl2br($transcription);

require_once '../includes/htmlFooter.php';