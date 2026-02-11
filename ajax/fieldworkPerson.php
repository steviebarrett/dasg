<?php

require_once '../includes/include.php';

switch ($_GET["action"]) {
	
	case "getBiog":
		$id = (int)htmlentities($_GET["id"]);
		$person = FieldworkPersons::getPerson($id);
		echo $person->getBiog();
		break;
	default:
		echo "AJAX error";
}
