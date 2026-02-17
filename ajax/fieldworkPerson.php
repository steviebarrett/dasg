<?php

require_once '../includes/include.php';

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

switch ($_GET["action"]) {
	
	case "getBiog":
		$id = (int)htmlentities($_GET["id"]);
		$person = FieldworkPersons::getPerson($id);
		echo $person->getBiog();
		break;
	default:
		echo "AJAX error";
}
