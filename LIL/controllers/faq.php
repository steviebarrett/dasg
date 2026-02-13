<?php


namespace controllers;
use views;

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}
class faq
{
	public function run() {
		$view = new views\faq();
		$view->show();
	}
}