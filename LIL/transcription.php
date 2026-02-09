<?php

require_once 'includes/include.php';
require_once 'includes/htmlHeader.php';

$ai = (string)($_GET["ai"] ?? '');
if ($ai === '' || !preg_match('/^[A-Za-z0-9_-]{1,64}$/', $ai)) {
    http_response_code(400);
    echo '<p>Invalid record identifier.</p>';
    require_once 'includes/htmlFooter.php';
    exit;
}

$record = new \models\record($ai);
$record->load();

$transcription = (string)$record->getTranscription();

function sanitizeTranscription(string $html): string
{
    if ($html === '') {
        return '';
    }

    $allowed = ['p', 'em', 'strong', 'div', 'br'];
    $dropTags = ['script', 'style'];

    $dom = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML('<?xml encoding="UTF-8"?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    if (!$loaded) {
        return \models\functions::e($html);
    }

    $nodes = $dom->getElementsByTagName('*');
    for ($i = $nodes->length - 1; $i >= 0; $i--) {
        $node = $nodes->item($i);
        if (!$node) {
            continue;
        }

        $tag = strtolower($node->nodeName);
        if (in_array($tag, $dropTags, true)) {
            $node->parentNode?->removeChild($node);
            continue;
        }

        if (!in_array($tag, $allowed, true)) {
            $parent = $node->parentNode;
            if ($parent) {
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);
            }
            continue;
        }

        // Strip all attributes from allowed tags.
        if ($node->hasAttributes()) {
            while ($node->attributes->length) {
                $node->removeAttributeNode($node->attributes->item(0));
            }
        }
    }

    return $dom->saveHTML() ?: '';
}

$transcriptionHtml = sanitizeTranscription($transcription);

echo <<<HTML
	<div class="row">
		<div class="col-lg-10 offset-lg-1">
      {$transcriptionHtml}
    </div>
  </div>
HTML;

require_once 'includes/htmlFooter.php';
