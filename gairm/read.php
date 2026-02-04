<?php
declare(strict_types=1);

require_once '../includes/include.php'; // so Functions is available (adjust if needed)

// Safe GET read
$subRaw = (string)($_GET['sub'] ?? '');

// Expecting "vol/NN" (based on your use elsewhere)
$subParts = array_values(array_filter(explode('/', trim($subRaw, '/')), 'strlen'));
$vol = $subParts[1] ?? '';  // for "vol/{vol}" => index 1

// Validate volume: digits only (prevents traversal like ../../)
if ($vol === '' || !preg_match('/^\d+$/', $vol)) {
	http_response_code(400);
	echo 'Bad request';
	exit;
}

$sub = "vol/{$vol}";
$dir = "vol/{$vol}/";

// Directory must exist
if (!is_dir($dir)) {
	http_response_code(404);
	echo 'Not found';
	exit;
}

$files = scandir($dir);
if ($files === false) {
	http_response_code(500);
	echo 'Server error';
	exit;
}

sort($files, SORT_NATURAL);

// Helper: safely embed a PHP string into JS as a quoted string
$jsStr = static function (string $s): string {
	return json_encode($s, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
};

// Build special pages if present
$coverJS = in_array("C_1.jpg", $files, true)
	? "[{ width: 800, height: 1200, uri: " . $jsStr($dir . "C_1.jpg") . " }],"
	: "";

$rearCoverJS = in_array("C_2.jpg", $files, true)
	? "[{ width: 800, height: 1200, uri: " . $jsStr($dir . "C_2.jpg") . " }]"
	: "";

$insideCoverJS = in_array("DA_1.jpg", $files, true)
	? "[{ width: 800, height: 1200, uri: " . $jsStr($dir . "DA_1.jpg") . " },"
	: "[{},"; // placeholder if missing

$pageListJS = "";
$open = false;

foreach ($files as $file) {
	// Only include TD_*.jpg pages
	if (strpos($file, 'TD_') !== 0) continue;
	if (!str_ends_with($file, '.jpg')) continue;

	// Extract page number safely
	// TD_12.jpg => 12
	if (!preg_match('/^TD_(\d+)\.jpg$/', $file, $m)) continue;
	$page = (int)$m[1];

	$uri = $jsStr($dir . $file);

	if ($page % 2) {
		// odd page closes a spread
		$pageListJS .= <<<JS
            { width: 800, height: 1200, uri: {$uri} },
          ],
JS;
		$open = false;
	} else {
		// even page opens a spread
		$pageListJS .= <<<JS
          [
            { width: 800, height: 1200, uri: {$uri} },
JS;
		$open = true;
	}
}

if ($open) {
	$pageListJS .= "],";
}

$titleSubEsc = Functions::e($sub);

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
  <title>Gairm Volume {$titleSubEsc}</title>

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="apple-mobile-web-app-capable" content="yes">

  <!-- JS dependencies -->
  <script src="bookreader/BookReader/webcomponents-bundle.js"></script>
  <script src="bookreader/BookReader/jquery-3.js"></script>

  <!-- BookReader and plugins -->
  <link rel="stylesheet" href="bookreader/BookReader/BookReader.css"/>
  <script src="bookreader/BookReader/BookReader.js"></script>

  <!-- Mobile nav plugin -->
  <script src="bookreader/BookReader/plugins/plugin.mobile_nav.js"></script>

  <!-- URL-changing plugin -->
  <script src="bookreader/BookReader/plugins/plugin.url.js"></script>

  <style>
    html, body { width: 100%; height: 100%; margin: 0; padding: 0; }
    #BookReader { width: 100%; height: 100%; }
  </style>
</head>
<body>
  <div id="BookReader"></div>

  <script>
    instantiateBookReader('#BookReader')

    function instantiateBookReader(selector, extraOptions) {
      selector = selector || '#BookReader';
      extraOptions = extraOptions || {};
      var options = {
        data: [
          {$coverJS}
          {$insideCoverJS}
          {$pageListJS}
          {$rearCoverJS}
        ],

        bookTitle: 'Gairm',
        thumbnail: '//archive.org/download/BookReader/img/page014.jpg',
        metadata: [
          {label: 'Title', value: 'Open Library BookReader Presentation'},
          {label: 'Author', value: 'Internet Archive'},
          {label: 'Demo Info', value: 'This demo shows how one could use BookReader with their own content.'},
        ],

        imagesBaseURL: 'bookreader/BookReader/images/',
        ui: 'full',
        el: selector,
      };
      $.extend(options, extraOptions);
      var br = new BookReader(options);
      br.init();
    }
  </script>
</body>
</html>
HTML;