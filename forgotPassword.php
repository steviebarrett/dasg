<?php

//classes
/*
require_once 'classes/User.php';
require_once 'classes/Users.php';
require_once 'classes/Email.php';
*/
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

$action = (string)($_POST['action'] ?? $_GET['action'] ?? '');

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateRequest();
}

switch ($action)
{
	case "save":
        $email = (string)($_POST['email'] ?? '');
        $token = (string)($_POST['token'] ?? '');
        $pass1 = (string)($_POST['pass1'] ?? '');
        $pass2 = (string)($_POST['pass2'] ?? '');

        if ($pass1 === '' || $pass1 !== $pass2) { echo "<h3>Passwords do not match</h3>"; break; }

        $user = Users::getUser($email);
        if (!$user) { echo "<h3>Email not recognised</h3>"; break; }

        $tokenHash = hash('sha256', $token);
        if (!$user->getPasswordAuth() || !hash_equals($user->getPasswordAuth(), $tokenHash)) {
            echo "<h3>Password reset link has expired</h3>";
            break;
        }
        if (!$user->getPasswordAuthExpires() || strtotime($user->getPasswordAuthExpires()) < time()) {
            echo "<h3>Password reset link has expired</h3>";
            break;
        }

        // Set new password hash
        $user->setPasswordFromPlaintext($pass1);

        // Clear token
        $user->setPasswordAuth(null);
        $user->setPasswordAuthExpires(null);

        Users::saveUser($user);

        echo "<h3>Your password has been updated.</h3>";
		break;		
	case "reset":
        $email = (string)($_GET['email'] ?? '');
        $token = (string)($_GET['token'] ?? '');

        $expiredMsg = "<h3>Your password reset link has expired</h3>";

        $user = Users::getUser($email);
        if (!$user) { echo $expiredMsg; break; }

        $tokenHash = hash('sha256', $token);
        $expires = $user->getPasswordAuthExpires();

        if (!$user->getPasswordAuth() || !hash_equals($user->getPasswordAuth(), $tokenHash)) {
            echo $expiredMsg; break;
        }
        if (!$expires || strtotime($expires) < time()) {
            echo $expiredMsg; break;
        }

        $emailEsc = Functions::e($email);
        $tokenEsc = Functions::e($token);

        // show form with hidden email+token
        $csrfField = Csrf::field();
		echo <<<HTML
		
		<h3>Please enter a new password</h3>
	
		<form id="savePassword" action="forgotPassword.php" method="POST">
	        {$csrfField}
			<label for="pass1">Password:</label>
			<input type="password" id="pass1" name="pass1"/>
			
			<label for="pass2">Re-enter password:</label>
			<input type="password" id="pass2" name="pass2"/>
			
			<input type="hidden" name="email" value="{$emailEsc}"/>
			<input type="hidden" name="token" value="{$tokenEsc}"/>
			<input type="hidden" name="action" value="save"/>
		
			<input type="submit" value="submit"/>
		
		</form>
		
HTML;
		break;
	case "email":
        // Always show same message, even if user not found
        echo "<h3>If the email exists in our system, a reset link has been sent.</h3>";

        // user submitted email
        $emailIn = trim((string)($_POST['email'] ?? ''));
        if ($emailIn === '') {
            break;
        }

        $user = Users::getUser($emailIn);
        if (!$user) {
            break; // user not recognised (do not reveal)
        }

        // Create token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $user->setPasswordAuth($tokenHash);
        $user->setPasswordAuthExpires(date('Y-m-d H:i:s', time() + 3600)); // 1 hour
        Users::saveUser($user);

        // Build reset URL using a trusted base (do NOT trust HTTP_HOST / Host header)
        $baseUrl = defined('APP_BASE_URL') ? (string)APP_BASE_URL : 'https://dasg.ac.uk';
        $baseUrl = rtrim($baseUrl, '/');

        $url = $baseUrl . '/forgotPassword.php?' . http_build_query([
            'action' => 'reset',
            'email'  => $user->getEmail(),
            'token'  => $token,
        ], '', '&', PHP_QUERY_RFC3986);

        // Escape for HTML attribute context in the email body
        $urlEsc = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $firstNameEsc = Functions::e((string)$user->getFirstName());
        if ($firstNameEsc === '') {
            $firstNameEsc = 'there';
        }

        // Send email
        $emailText = <<<HTML
			<p>Dear {$firstNameEsc},</p>
			
			<p>Please reset your password by clicking <a title="DASG password reset" href="{$urlEsc}">here</a>.</p>
					
			<p>If you have received this email in error or have any other queries please contact <a title="Email DASG" href="mailto:mail@dasg.ac.uk">mail@dasg.ac.uk</a>.</p>
	
			<p>Kind regards</p>
	
			<p>The DASG team</p>

HTML;

        $emailObj = new Email($user->getEmail(), "DASG Admin Password Reset", $emailText, "mail@dasg.ac.uk");
        $emailObj->send();
        break;
	default:
        $csrfField = Csrf::field();
		echo <<<HTML

            <h3>Please enter your email:</h3>
            
            <form id="forgotPassword" action="forgotPassword.php" method="POST">
                {$csrfField}
                <label id="email">Email:</label>
                <input type="text" id="email" name="email"/>
                
                <input type="hidden" name="action" value="email"/>
                <input type="submit" class="dasg_medButton" value="submit"/>
                
            </form>
	
HTML;

}

require_once 'includes/htmlFooter.php';
