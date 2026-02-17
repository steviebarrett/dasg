<?php

require_once '../includes/include.php';

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

$item = isset($_GET["item"]) ? Functions::e($_GET["item"]) : null;

$fieldworkDoc = FieldworkItems::getHtml($item);

echo $fieldworkDoc;

