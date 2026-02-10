<?php

class User
{
	private $email, $username, $password, $firstName, $lastName, $isBlogAdmin, $passwordAuth, $passwordAuthExpires,
        $lastLoggedIn, $updated;
	
	public function __construct($email) {
		$this->email = $email;	
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getPassword() {
		return $this->password;	
	}

    public function checkPassword(string $password): bool
    {
        $hash = (string)$this->getPassword();
        if ($hash === '' || $hash === '!') return false;
        return password_verify($password, $hash);
    }

    public function setPasswordFromPlaintext(string $password): void
    {
        // PASSWORD_DEFAULT is fine; PHP will pick a strong algorithm and params.
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function setPassword($password) {
		$this->password = $password;	
	}
	
	public function getFirstName() {
		return $this->firstName;
	}
	
	public function setFirstName($name) {
		$this->firstName = $name;
	}

	public function getLastName() {
		return $this->lastName;
	}
	
	public function setLastName($name) {
		$this->lastName = $name;
	}
	
	public function getIsBlogAdmin() {
		return $this->isBlogAdmin;
	}
	
	public function setIsBlogAdmin($flag) {
		$this->isBlogAdmin = $flag;	
	}
	
	public function getLastLoggedIn() {
		return $this->lastLoggedIn;
	}
	
	public function getPasswordAuth() {
		return $this->passwordAuth;
	}
	
	public function setPasswordAuth($auth) {
		$this->passwordAuth = $auth;
	}

    public function getPasswordAuthExpires() {
        return $this->passwordAuthExpires;
    }

    public function setPasswordAuthExpires($time) {
        $this->passwordAuthExpires = $time;
    }
	
	public function setLastLoggedIn($timestamp) {
		$this->lastLoggedIn = $timestamp;
	}
	
	public function getUpdated() {
		return $this->updated;
	}

	public function setUpdated($timestamp) {
		$this->updated = $timestamp;
	}
}