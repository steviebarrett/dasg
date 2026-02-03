<?php


namespace controllers;
use views;

class team
{
	public function run() {
		$view = new views\team();
		$view->show();
	}
}