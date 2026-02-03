<?php


namespace controllers;
use views;

class faq
{
	public function run() {
		$view = new views\faq();
		$view->show();
	}
}