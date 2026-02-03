<?php
namespace controllers;

require_once "includes/htmlHeader.php";

$action = isset($_GET["a"]) ? $_GET["a"] : "";
$module = isset($_GET["m"]) ? $_GET["m"] : "";
$controller = null;

switch ($module) {
	case "record":
		$controller = new record($_GET["id"]);
		break;
	case "records":
		$controller = new records();
		break;
	case "admin":
		$controller = new admin($action);
		break;
	case "faq":
		$controller = new faq();
		break;
	case "gratitude":
		$controller = new gratitude();
		break;
	case "team":
		$controller = new team();
		break;
	case "contact":
		$controller = new contact();
		break;
	case "about":
		$controller = new about();
		break;
	default:
		$controller = new index();
}

$controller->run($action);

require_once "includes/htmlFooter.php";