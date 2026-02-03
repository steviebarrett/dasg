<?php

class GairmRecord
{
	private $id, $lastName, $firstName, $origin, $year, $yearOfPublication, $volume, $firstPage, $lastPage, $title,
	$language, $type, $genre, $register, $comments, $transcription;


	public function __construct($id) {
		$this->id = $id;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getLastName() {
		return $this->lastName;
	}
	
	public function setLastName($name) {
		$this->lastName = $name;
	}
	
	public function getFirstName() {
		return $this->firstName;
	}
	
	public function setFirstName($name) {
		$this->firstName = $name;
	}
	
	public function getOrigin() {
		return $this->origin;
	}
	
	public function setOrigin($origin) {
		$this->origin = $origin;
	}
	
	public function getYear() {
		return $this->year;
	}
	
	public function setYear($year) {
		$this->year = $year;
	}
	
	public function getYearOfPublication() {
		return $this->yearOfPublication;
	}
	
	public function setYearOfPublication($year) {
		$this->yearOfPublication = $year;
	}
	
	public function getVolume() {
		return $this->volume;
	}
	
	public function setVolume($volume) {
		$this->volume = $volume;
	}
	
	public function getFirstPage() {
		return $this->firstPage;
	}
	
	public function setFirstPage($page) {
		$this->firstPage = $page;
	}
	
	public function getLastPage() {
		return $this->lastPage;
	}
	
	public function setLastPage($page) {
		$this->lastPage = $page;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getLanguage() {
		return $this->language;
	}
	
	public function setLanguage($lang) {
		$this->language = $lang;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setType($type) {
		$this->type = $type;
	}
	
	public function getGenre() {
		return $this->genre;
	}
	
	public function setGenre($genre) {
		$this->genre = $genre;
	}
	
	public function getRegister() {
		return $this->register;
	}
	
	public function setRegister($register) {
		$this->register = $register;
	}
	
	public function getComments() {
		return $this->comments;
	}
	
	public function setComments($comments) {
		$this->comments = $comments;
	}

	public function getTranscription() {
		return $this->transcription;
	}

	public function setTranscription($transcription) {
		$this->transcription = $transcription;
	}

	public function getTranscriptionForFile($file) {
		$trans = nl2br($this->getTranscription());
		$page = trim($file, ".pdf");
		$elems = explode('_', $page);
		$pageType = $elems[0];
		$page = $elems[1];
		$nextPage = $page+1;

		$pageStart = mb_strpos($trans, "[TD {$page}");
		$pageEnd = mb_strpos($trans, "[TD {$nextPage}]");

		$length = $pageEnd ? $pageEnd - $pageStart : null;
		$trans = mb_substr($trans, $pageStart, $length);

		return $trans;
	}
}

