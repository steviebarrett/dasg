<?php

//classes
require_once 'classes/User.php';
require_once 'classes/Users.php';
require_once 'classes/Email.php';

$cqpPage = true;

require_once 'includes/include.php';

$pageSlug = "forgotPassword";
$pageTitle = "Password Reset";

$javascriptBlock = <<<HTML

	<script type="text/javascript">
	
		$(document).ready(function() {
			$('#savePassword').validate({
				rules: {
					pass1: "required",
					pass2: {
						equalTo: "#pass1"
					}
				}
			});
		});
		
	</script>
HTML;

require_once 'includes/htmlHeader.php';

$params 	= explode('|', base64_decode($_GET["p"]));
$action 	= $params[0];
$email 		= empty($params[1]) ? $_POST["email"] : $params[1];
$passAuth	= $params[2];

switch ($action)
{
	case "save":
		$user = Users::getUser($email);
		$user->setPassword($_POST["pass1"]);
		$user->encryptPassword();
		$user->setPasswordAuth(null);	//remove password auth 
		Users::saveUser($user);
		
		echo "<h3>Your password has been saved</h3>";
		break;		
	case "reset":	
		//run auth check
		$user = Users::getUser($email);
		if ($passAuth != $user->getPasswordAuth() || time() > $passAuth+300) {	//set a limit of five mins on auth
			echo <<<HTML
				<h2>Sorry, there is a problem</h2>
		
				<h3>The link you used has expired</h3>
		
				<p>You can request a password change by clicking <a href="forgotPassword.php">here</a></p>
HTML;
			require_once 'includes/htmlFooter.php';
			die();
		}
		
		$changeParams = array("save", $email);
		$changeParams = base64_encode(implode('|', $changeParams));
		
		echo <<<HTML
		
		<h3>Please enter a new password</h3>
	
		<form id="savePassword" action="forgotPassword.php?p={$changeParams}" method="POST">
	
			<label for="pass1">Password:</label>
			<input type="password" id="pass1" name="pass1"/>
			
			<label for="pass1">Re-enter password:</label>
			<input type="password" id="pass2" name="pass2"/>
		
			<input type="submit" value="submit"/>
		
		</form>
		
HTML;
		break;
	case "email":
		$user = Users::getUser($email);
		if (empty($user)) {
			echo "<h2 class=\"error\">Email not recognised</h2>";
			echo "<br/><a href=\"forgotPassword.php\">Enter another email address</a>";
			break;
		}
		//set password change authorisation in the DB
		$passwordAuth = time();
		$user->setPasswordAuth($passwordAuth);
		Users::saveUser($user);
		$changeParams = array("reset", $user->getEmail(), $passwordAuth);
		$changeParams = base64_encode(implode('|', $changeParams));
		$url = "https://" . $_SERVER["HTTP_HOST"] . "/forgotPassword.php?p=" . $changeParams;
		
		$emailText = <<<HTML
			<p>Dear {$user->getFirstName()},</p>
			
			<p>Please reset your password by clicking <a title="DASG password reset" href="{$url}">here</a>.</p>
					
			<p>If you have received this email in error or have any other queries please contact <a title="Email DASG" href="mailto:mail@dasg.ac.uk">mail@dasg.ac.uk</a>.</p>
	
			<p>Kind regards</p>
	
			<p>The DASG team</p>
HTML;
		$email = new Email($user->getEmail(), "DASG Admin Password Reset", $emailText, "mail@dasg.ac.uk");
		$email->send();
		echo "<h3>An email has been sent to your email address. Please click the link in the email to reset your password.</h3>";
		break;
	default:	
		$changeParams = array("email");
		$changeParams = base64_encode(implode('|', $changeParams));
		echo <<<HTML

	<h3>Please enter your email:</h3>
	
	<form id="forgotPassword" action="forgotPassword.php?p={$changeParams}" method="POST">
	
		<label id="email">Email:</label>
		<input type="text" id="email" name="email"/>
		
		<input type="hidden" name="action" value="email"/>
		<input type="submit" class="dasg_medButton" value="submit"/>
		
	</form>
	
HTML;

}

require_once 'includes/htmlFooter.php';
