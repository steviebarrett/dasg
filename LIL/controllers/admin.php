<?php


namespace controllers;

use views, models;

class admin
{
	public function run($action = null) {
		$model = new models\records();
		$view = new views\admin($model);
		switch ($action) {
			case "logout":
				$_SESSION["loggedIn"] = null;
			case "login":
				$username = $_POST["u"];
				$password = $_POST["p"];
				if ($username != ADMIN_USERNAME || $password != ADMIN_PASSWORD) {
					$view->writeLoginForm();
					break;
				}
				$_SESSION["loggedIn"] = true;
			default:
				if ($_SESSION["loggedIn"]) {
					$view->show();
				} else {
					$view->writeLoginForm();
				}
		}
	}
}