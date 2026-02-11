<?php

require_once 'includes/include.php';

// CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::validateRequest();
}

$titleText = array("en"=>"Register", "gd"=>"Cunntas a chruthachadh");

$lang = isset($lang) ? Functions::e($lang) : "en";

$pageTitle = $titleText[$lang];
$pageSlug = "register";

$javascriptBlock = <<<HTML
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
HTML;

require_once 'includes/htmlHeader.php';

$referer = (string)($_POST['referer'] ?? $_SERVER['HTTP_REFERER'] ?? '');
$path = parse_url($referer, PHP_URL_PATH) ?: '/';
if ($path === '' || $path[0] !== '/') $path = '/';
$pathEsc = Functions::e($path);


if ($_POST["submit"]) {
	if (!isValid()) {
		$errorMsg["en"] = "<h3>There was a problem. Please try again</h3><p><em><strong>Please make sure you use the Captcha to prove you are human</strong></em></p>";
		$errorMsg["gd"] = "<h3>Bha trioblaid ann. Nach feuch thu a-rithist</h3><p><em><strong>Cleachd an Captcha airson dearbhadh gu bheil thu beò</strong></em></p>";
		echo "<h3 class=\"error\">{$errorMsg[$lang]}</h3>";
		unset ($_POST["submit"]);
	}
}

if (!empty($_POST["submit"])) {
	
	//create and save the user
	$user = new User($_POST["email"]);
	$user->setUsername($_POST["username"]);
	$user->setFirstName($_POST["firstname"]);
	$user->setLastName($_POST["lastname"]);
	$user->setIsBlogAdmin(false);
	
	//set temp password and password change token
	$pass = bin2hex(random_bytes(32));
	$user->setPasswordFromPlaintext($pass);
    $token = bin2hex(random_bytes(32)); // raw token for email
    $tokenHash = hash('sha256', $token);
    $user->setPasswordAuth($tokenHash);
    $user->setPasswordAuthExpires(date('Y-m-d H:i:s', time() + 3600)); // 1 hour
	
	Users::saveUser($user);

    $url = "https://" . $_SERVER["HTTP_HOST"] . "/forgotPassword.php?action=reset&email=" . rawurlencode($user->getEmail()) . "&token=" . rawurlencode($token);
	
	//email the user
    $firstNameEsc = Functions::e($user->getFirstName());
	$emailText = <<<HTML
		<p>Dear {$firstNameEsc},</p>

		<p>A DASG user account has been set up for you using this email address.</p>

		<p>Please reset your password by clicking <a href="{$url}">here</a>.</p>
				
		<p>If you have received this email in error or have any other queries please contact <a title="Email DASG" href="mailto:mail@dasg.ac.uk">mail@dasg.ac.uk</a>.</p>
	
		<p>Kind regards</p>
	
		<p>The DASG team</p>
HTML;
	$email = new Email($user->getEmail(), "DASG Admin Password Reset", $emailText, "mail@dasg.ac.uk");
	$email->send();
    $emailEsc = Functions::e($user->getEmail());
	
	echo <<<HTML
		<h3>Thank you.</h3>
		
		<h3>Your user account has been created and an email has been sent to {$emailEsc}.</h3> 
				
		<h3>Please use the link in this email to set your password.</h3>
	
		<p><a href="{$pathEsc}">Return to page</a></p>
HTML;
	
	require_once 'includes/htmlFooter.php';
	die();
}


if ($_SESSION["user"]) {
	$user = Users::getUser($_SESSION["user"]);
    $firstNameEsc = Functions::e($user->getFirstName());
    $lastNameEsc = Functions::e($user->getLastName());
	echo "<p>You are already registered and logged-in as {$firstNameEsc} {$lastNameEsc}";
	echo <<<HTML
		<br/><br/><a href="{$pathEsc}">Return to page</a>
HTML;
	require_once 'includes/htmlFooter.php';
	die();
}

$errorEmailText["en"]	= "Please enter a valid email";
$errorEmailText["gd"]	= "Cuir a-steach seòladh puist-d. dligheach";
$errorConfirmText["en"]	= "Enter the same value again";
$errorConfirmText["gd"]	= "Cuir a-steach an aon rud a-rithist";
$errorFirstText["en"]	= "You must enter a first name";
$errorFirstText["gd"]	= "Feumaidh rudeigin a bhith anns an raon seo";
$errorLastText["en"]	= "You must enter a last name";
$errorLastText["gd"]	= "Feumaidh rudeigin a bhith anns an raon seo";
$errorUsername["en"]	= "You must enter a username";
$errorUsername["gd"]	= "Feumaidh rudeigin a bhith anns an raon seo";

$emailText["en"]		= "Email address";
$emailText["gd"]		= "Seòladh puist-d.";		
$confirmText["en"]		= "Confirm email address";
$confirmText["gd"]		= "Dearbh an seòladh puist-d.";
$firstNameText["en"]	= "First name";
$firstNameText["gd"]	= "Ainm";
$lastNameText["en"]		= "Last name";
$lastNameText["gd"]		= "Sloinneadh";
$usernameText["en"]		= "Please choose a username";
$usernameText["gd"]		= "Tagh ainm mar neach-cleachdaidh";
$submitText["en"]		= "submit";
$submitText["gd"]		= "cuir a-steach";

$csrfField = Csrf::field();

echo <<<HTML

	<form method="POST" id="register">
	    
	    {$csrfField}
	    
		<div>
			<label for="email">{$emailText[$lang]}:</label>
			<input title="{$errorEmailText[$lang]}" type="text" name="email" id="email"/>
		</div>
		
		<div>
			<label for="confirmEmail">{$confirmText[$lang]}:</label>
			<input title="{$errorConfirmText[$lang]}" type="text" name="confirmEmail" id="confirmEmail"/>
		</div>
		
		<br/>
		
		<div>
			<label for="firstname">{$firstNameText[$lang]}:</label>
			<input title="{$errorFirstText[$lang]}" type="text" name="firstname" id="firstname" required/>
		</div>
		
		<div>
			<label for="lastname">{$lastNameText[$lang]}:</label>
			<input title="{$errorLastText[$lang]}" type="text" name="lastname" id="lastname" required/>
		</div>
		
		<br/>
		
		<div>
			<label for="username">{$usernameText[$lang]}:</label>
			<input title="{$errorUsername[$lang]}" type="text" name="username" id="username"/>
		</div>
		
		<div id="captcha-container">
			<div class="g-recaptcha" data-sitekey="6Lf4axcTAAAAAD7eBWCYEcpxvdU77D86YKhA3VCY"></div>
		</div>
		
		<br/>
		<input type="hidden" name="referer" value="{$pathEsc}"/>
		
		<input class="dasg_medButton" id="submit" name="submit" type="submit" value="{$submitText[$lang]}"/>
		
	</form>
				
	<script>
        $('#register').validate({
          rules: {
            email: {
              required: true,
              email: true,
              remote: {
                url: "/ajax/user.php",
                type: "get",
                data: {
                  lang: function() { return "{$lang}"; },
                  action: function() { return "checkregistered"; },
                  email: function() { return $('#email').val(); }
                }
              }
            },
            confirmEmail: {
              equalTo: "#email"
            },
            username: {
              required: true,
              remote: {
                url: "/ajax/user.php",
                type: "get",
                data: {
                  lang: function() { return "{$lang}"; },
                  action: function() { return "checkusername"; },
                  username: function() { return $('#username').val(); }
                }
              }
            }
          }
        });
</script>
HTML;

function isValid()
{
	try {

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array('secret'   => '6Lf4axcTAAAAAPlgauJJsCpDLJpc-ykRTrlL3CEG',
				'response' => $_POST['g-recaptcha-response'],
				'remoteip' => $_SERVER['REMOTE_ADDR']);

		$options = array(
				'http' => array(
						'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($data)
				)
		);

		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		return json_decode($result)->success;
	}
	catch (Exception $e) {
		return null;
	}
}

require_once 'includes/htmlFooter.php';