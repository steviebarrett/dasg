<?php


namespace controllers;
use views;

class gratitude
{
	public function run() {
		$view = new views\gratitude();
		$view->show();
	}
}