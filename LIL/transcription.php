<?php

require_once 'includes/include.php';

require_once 'includes/htmlHeader.php';

$ai = $_GET["ai"];

$record = new \models\record($_GET["ai"]);
$record->load();

echo <<<HTML
	<div class="row">
		<div class="col-lg-10 offset-lg-1">
      {$record->getTranscription()}
    </div>
  </div>
HTML;

require_once 'includes/htmlFooter.php';
