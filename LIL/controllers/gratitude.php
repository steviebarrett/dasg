<?php


namespace controllers;
use views;

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}
class gratitude
{
	public function run() {
		$view = new views\gratitude();
		$view->show();
	}
}