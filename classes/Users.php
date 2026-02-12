<?php

class Users
{
	public static function getUser($email) {
        $user = null;

		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT `username`, `password`, `firstname`, `lastname`, `blog_admin`, 
                `password_reset_token`, `password_reset_expires` , UNIX_TIMESTAMP(`last_logged_in`) AS last_logged_in, 
				UNIX_TIMESTAMP(`updated`) AS updated FROM user WHERE email = :email;");
			$sth->execute(array(":email"=>$email));
	
			if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$user = new User($email);
				$user->setUsername($row['username']);
				$user->setPassword($row['password']);
				$user->setFirstName($row['firstname']);
				$user->setLastName($row['lastname']);
				$user->setIsBlogAdmin($row['blog_admin']);
				$user->setPasswordAuth($row['password_reset_token']);
                $user->setPasswordAuthExpires($row['password_reset_expires']);
				$user->setUpdated($row["updated"]);
			}

			return $user;
	
		} catch (PDOException $e) {
			echo "The user could not be loaded";
		}
	}
	
	public static function saveUser($user) {
		$dbh = DB::getDatabaseHandle();
		
		try {
            $sth = $dbh->prepare("
                INSERT INTO user (
                    email,
                    username,
                    password,
                    firstname,
                    lastname,
                    blog_admin,
                    password_reset_token,
                    password_reset_expires
                ) VALUES (
                    :email,
                    :username,
                    :password,
                    :firstname,
                    :lastname,
                    :blog_admin,
                    :password_reset_token,
                    :password_reset_expires
                )
                ON DUPLICATE KEY UPDATE
                    username               = VALUES(username),
                    password               = VALUES(password),
                    firstname              = VALUES(firstname),
                    lastname               = VALUES(lastname),
                    blog_admin             = VALUES(blog_admin),
                    password_reset_token   = VALUES(password_reset_token),
                    password_reset_expires = VALUES(password_reset_expires)
            ");
            $sth->execute([
                ':email' => $user->getEmail(),
                ':username' => $user->getUsername(),
                ':password' => $user->getPassword(),
                ':firstname' => $user->getFirstName(),
                ':lastname' => $user->getLastName(),
                ':blog_admin' => $user->getIsBlogAdmin(),
                ':password_reset_token' => $user->getPasswordAuth(),
                ':password_reset_expires' => $user->getPasswordAuthExpires(),
            ]);
	
		} catch (PDOException $e) {
			echo "The user could not be saved";
		}
	}
	
	public static function getAllUsers() {
		$users = array();
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT email FROM user;");
			$sth->execute();
	
			while ($row = $sth->fetch()) {
				$users[] = self::getUser($row["email"]);
			}

			return $users;
	
		} catch (PDOException $e) {
			echo "The users could not be loaded";
		}
	}
	
	public static function checkUsernameExists($username) {
		$dbh = DB::getDatabaseHandle();
		
		try {
		
			$sth = $dbh->prepare("SELECT `email` FROM user WHERE username = :username;");
			$sth->execute(array(":username"=>$username));
		
			if ($row = $sth->fetch()) {
				return true;
			} else {
				return false;
			}
				
		} catch (PDOException $e) {
			echo "The username could not be checked";
		}		
	}

    public static function markLoggedIn(string $email): void {
        $dbh = DB::getDatabaseHandle();
        $sth = $dbh->prepare("UPDATE user SET last_logged_in = NOW() WHERE email = :email");
        $sth->execute([':email' => $email]);
    }
}