<?php


namespace controllers;
use views;

class index
{
	public function run($action = null): void {
		$view = new views\index();
		$view->show();
	}
}
