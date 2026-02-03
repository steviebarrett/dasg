<?php


chdir('../');

//manually loading classes just now due to conflict with __autoload in CQPWeb
require_once "./classes/DB.php";
require_once "./classes/FeedbackForm.php";

require_once 'includes/include.php';


switch ($_GET["action"]) {
	
	case "update":
		switch ($_GET["item"]) {
			
			case "user":
				FeedbackForm::saveUser($_GET["name"], $_GET["email"]);
				break;
			case "answer":
				FeedbackForm::saveAnswer($_GET["email"], $_GET["id"], $_GET["answer"]);
				break;
		}
		break;
	case "read":
		switch ($_GET["item"]) {
				
			case "answers":
				echo json_encode(FeedbackForm::loadAnswers($_GET["email"]));
				break;
			case "user":
				 echo json_encode(FeedbackForm::loadUser($_GET["email"]));
				 break;
			case "labels":
				 echo json_encode(FeedbackForm::getAllLabels());
				 break;
		}
		break;
}

