<?php

require_once '../includes/include.php';

$item = $_GET["item"];

$fieldworkDoc = FieldworkItems::getHtml($item);

echo $fieldworkDoc;

