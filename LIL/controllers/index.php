<?php


namespace controllers;
use views;

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}
class index
{
	public function run($action = null): void {
		$view = new views\index();
		$view->show();
	}
}
