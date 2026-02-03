<?php


namespace controllers;
use views;

class index
{
	public function run() {
		$view = new views\index();
		$view->show();
	}
}