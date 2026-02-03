<?php


namespace controllers;
use views;

class about
{
	public function run() {
		$view = new views\about();
		$view->show();
	}
}