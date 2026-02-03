<?php

class User
{
	private $email, $username, $password, $salt, $firstName, $lastName, $isBlogAdmin, $passwordAuth, $lastLoggedIn, $updated;
	
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
	
	public function getSalt() {
		if (empty($this->salt)) {
			$this->salt = uniqid(mt_rand(), true);
		}
		return $this->salt;
	}
	
	public function setSalt($salt) {
		$this->salt = $salt;
	}
	
	public function checkPassword($password) {
		return md5($this->getSalt() . $password) == $this->getPassword() ? 1 : 0;	
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	public function setPassword($password) {
		$this->password = $password;	
	}
	
	public function encryptPassword() {
		$this->password = md5($this->getSalt() . $this->getPassword());
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