<?php

require_once '../includes/include.php';

switch ($_GET["action"]) {
	
	case "checkregistered":
		$email = $_GET["email"];
		$msg["en"] = "This email address is already registered";
		$msg["gd"] = "Tha an seòladh puist-d. seo clàraichte mar thà";
		if ($user = Users::getUser($email)) {
			echo json_encode($msg[$_GET["lang"]]);
		} else {
			echo json_encode(true);
		}
		break;
	case "checkusername":
		$username = $_GET["username"];
		$msg["en"] = "The username {$username} is already taken";
		$msg["gd"] = "Chaidh an t-ainm neach-cleachdaidh seo a chlàradh mar thà";
		if (Users::checkUsernameExists($username)) {
			echo json_encode($msg[$_GET["lang"]]);
		} else {
			echo json_encode(true);
		}
		break;
	default:
		break;
}


