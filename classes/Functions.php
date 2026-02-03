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
    
    public static function showLoginForm($referer=null, $lang="en")
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
    	$emailText["en"]	= "Email";
    	$emailText["gd"]	= "Seòladh puist-d.";
    	$passwordText["en"]	= "Password";
    	$passwordText["gd"]	= "Facal-faire";
    	$forgotText["en"]	= "Forgot my password";
    	$forgotText["gd"]	= "Dhìochuimhnich mi am facal-faire";
    	$loginText["en"]	= "login";
    	$loginText["gd"]	= "cuir a-staigh";
    	echo <<<HTML
    	
    		<div id="login">
    		
    			<form id="loginForm" method="POST">
    				
    				<div>
	    				<label for="email">{$emailText[$lang]}:</label>
	    				<input type="text" id="email" name="email"/>
	    			</div>
	    			
	    			<div>
	    				<label for="password">{$passwordText[$lang]}:</label>
	    				<input type="password" id="password" name="password"/>
	    			</div>
	    			
	    			<div id="forgotPassLink">
	    				<a href="https://dasg.ac.uk/forgotPassword.php" title="Forgotten password link">{$forgotText[$lang]}</a>
	    			</div>
	    			
	    			<input type="hidden" name="referer" value="{$referer}"/>
	    				
	    			<input type="submit" value="{$loginText[$lang]}" class="loginSecondCol dasg_bigButton"/>
    			
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
    
    public static function getTextLink($textId) {
    	$dbh = DB::getDatabaseHandle(DB2_NAME);
    	
    	$query = "SELECT link FROM corpus_text WHERE reference_number = :textId";
    	$sth = $dbh->prepare($query);
		$sth->bindParam(":textId", $textId);
		$sth->execute();
		$result = $sth->fetch();
		$row = $result[0];
		
		return $result["link"];
    }
    
    public static function writeError($message) {
    	echo "<h3 class=\"error\">{$message}</h3>";
		require_once '../includes/htmlFooter.php';
		die();
    }
    
    public static function canBeLenited($word) { 
		if (strlen($word) < 2) {
			return false;
		}
		
		if (substr($word, 1, 1) == 'h') {
			return false;
		}
		
		$excludeChars = array('h', 'l', 'n', 'r', '?', '*', '~', '[', ']');
		if (in_array(substr($word, 0, 1), $excludeChars)) {
			return false;
		}		
		return true;
	}
	
	public static function getLenited($word) {
		if (self::canBeLenited($word) == false) {
			return $word;
		}
		
		$word = substr_replace($word, "h=", 1, 0);
		return $word;
	}

	public static function getAccentInsensitive($text, $caseSens = true) {
	    $regExp = "";
	    $accentMappedChars = null;
	    if ($caseSens) {
	        $accentMappedChars = array(
	            "aàá", "eèé", "iìí", "oòó", "uùú"
	        );
	    } else {
	        $accentMappedChars = array(
	            "aàáAÀÁ", "eèéEÈÉ", "iìíIÌÍ", "oòóOÒÓ", "uùúUÙÚ"
	        );
	    }
	    
	    foreach (Functions::str_split_unicode($text) as $char) {
	        $replaced = false;
	        foreach ($accentMappedChars as $accentMap) {
	            if (stristr($accentMap, $char)) {
	                $regExp .= "[{$accentMap}]+";
	                $replaced = true;
	            }
	        }
	        if ($replaced == false)
	            $regExp .= $char;
	    }
	    return $regExp;
	}
	
	public static function str_split_unicode($str, $l = 0) {
		if ($l > 0) {
			$ret = array();
			$len = mb_strlen($str, "UTF-8");
			for ($i = 0; $i < $len; $i += $l) {
				$ret[] = mb_substr($str, $i, $l, "UTF-8");
			}
			return $ret;
		}
		return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
	}	
}