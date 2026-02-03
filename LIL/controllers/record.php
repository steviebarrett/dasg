<?php


namespace controllers;
use views, models;

class record
{
	private $_model;

	public function __construct($ai) {
        if (!$ai) {$ai = $_GET["ai"];}      //backup code to ensure crawling of all records
		$this->_model = new models\record($ai);
	}

	public function run($action) {
		switch ($action) {
			case "view":
				$view = new views\record($this->_model, $_GET["o"]);
				$view->show();
				break;
			case "edit":
				$view = new views\record($this->_model);
				$view->edit();
				break;
			case "save":
				$this->_model->save($_POST);
				if ($_GET["id"] == -1) {  //a new record has been created ...
					$this->_model = new models\record($_POST["ai"]);  // .. so load it
				}
				$view = new views\record($this->_model);
				$view->show();
				break;
			default:
				echo "default: " .  $action;
		}
	}
}