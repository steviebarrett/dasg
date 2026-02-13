<?php

require_once '../includes/include.php';

$item = isset($_GET["item"]) ? Functions::e($_GET["item"]) : null;

$fieldworkDoc = FieldworkItems::getHtml($item);

echo $fieldworkDoc;

