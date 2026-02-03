<?php


namespace controllers;
use models, views;

class records
{
	public function run($action) {
		switch ($action) {
			case "list":
				$model = new models\records();
				$view = new views\records($model);
				$view->show();
				break;
			case "search":
				$model = new models\records();
				$view = new views\records($model);
				if (empty($_GET["s"])) {    //no search string
					$view->showSearchForm();
					break;
				}
				$view->show();
				break;
		}
	}
}