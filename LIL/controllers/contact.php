<?php


namespace controllers;
use views;

class contact
{
	public function run() {
		$view = new views\contact();
		$view->show();
	}
}