<?php

class Functions
{
    public static function getFriendlyFieldName($fieldname, $delimiter)
    {
    	$elements = explode($delimiter, $fieldname);
    	$friendlyFieldName = ucwords(implode(' ', $elements));
    
    	return $friendlyFieldName;
    }
    
    public static function urlEncode($text)
    {
    	$text = str_ireplace(' ', '.', $text);
    	$text = rawurlencode($text);
    
    	return $text;
    }
    
    public static function urlDecode($text)
    {
    	$text = str_ireplace('.', ' ', $text);
    	$text = stripslashes(rawurldecode($text));
    	
    	return $text;
    }
    
    public static function showLoginForm()
    {  	
    	if ($_POST["logout"]) {
    		unset($_SESSION["user"]);
    	} else if (!empty($_SESSION["user"])) {
    		self::showLogoutForm();
    		return true;
    	} else if ($_POST["email"]) {
 
    		$user = Users::getUser($_POST["email"]);
    		if (empty($user)) {
    			echo "<h3 class=\"error\">Email/Password combination not recognised</h3>";
    		} else if ($user->checkPassword($_POST["password"])) {
    			$_SESSION["user"] = $user->getEmail();
    			Users::saveUser($user);		//updates last logged-in
				self::showLogoutForm();
    			return true;
    		} else {  		
    			echo "<h3 class=\"error\">Email/Password combination not recognised</h3>";
    		}
    	}
    	echo <<<HTML
    	
    		<div id="login">
    		
    			<form id="loginForm" method="POST">
    			
	    			<label for="email">Email:</label>
	    			<input type="text" id="email" name="email"/>
	    			
	    			<br/><br/>
	    			
	    			<label for="password">Password:</label>
	    			<input type="password" id="password" name="password"/>
	    			
	    			<br/><br/>
	    			<a href="/forgotPassword.php" title="Forgotten password link">Forgot my password</a>
	    			
	    			<br/><br/>
	    			<input type="submit" value="login" class="dasg_bigButton"/>
    			
    			</form>
    			
    		</div>
HTML;
    }
    
    public static function showLogoutForm()
    {
    	$user = Users::getUser($_SESSION["user"]);
		echo <<<HTML
   					<div id="logout">
   						<p><strong>Logged-in as {$user->getFirstName()} {$user->getLastName()}</strong></p>
   						<form method="POST">
   							<input type="hidden" name="logout" value="true"/>
   							<input type="submit" value="logout" class="dasg_smlButton">
   						</form>
   					</div>
HTML;
    }
}