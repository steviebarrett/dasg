<?php
namespace controllers;

require_once "includes/htmlHeader.php";

$m = (string)($_POST['m'] ?? $_GET['m'] ?? 'records');
$a = (string)($_POST['a'] ?? $_GET['a'] ?? 'list');

if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,40}$/', $m)) $m = 'records';
if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,40}$/', $a)) $a = 'list';

$routesNoAction = ['faq', 'about'];
$routesWithAction = [
    'records' => ['list','search'],
    'record'  => ['view','edit','save'],
    'admin'   => ['login','logout'],
];

$routes = [
    'records' => ['list', 'search'],
    'record'  => ['view', 'edit', 'save'],
    'admin'   => ['login', 'logout'],
    'faq'     => ['show'],    // or whatever you use
];

$controller = null;

switch ($m) {
	case "record":
		$controller = new record($_GET["id"]);
		break;
	case "records":
		$controller = new records();
		break;
	case "admin":
		$controller = new admin($a);
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

$controller->run($a);

require_once "includes/htmlFooter.php";