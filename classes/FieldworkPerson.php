<?php

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

class FieldworkPerson {
	
	private $id, $firstName, $lastName, $biog, $imageUrl;
	
	public function _construct($id) {
		$this->id = $id;
		return $this;
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
	
	public function getBiog() {
		return $this->biog;
	}
	
	public function setBiog($biog) {
		$this->biog = $biog;
	}
	
	public function getImageUrl() {
		return $this->imageUrl;
	}
	
	public function setImageUrl($url) {
		$this->imageUrl = $url;
	}
}