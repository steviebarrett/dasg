<?php


namespace controllers;
use views;

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}
class team
{
	public function run() {
		$view = new views\team();
		$view->show();
	}
}