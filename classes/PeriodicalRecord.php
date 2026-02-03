<?php

class PeriodicalRecord
{
    private $id, $lastName, $firstName, $origin, $originGD, $monthOfPublication, $yearOfPublication, $firstPage, $lastPage, $title,
    $language, $type, $genre, $register, $comments, $periodicalId, $part;
    
    
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
    
    public function getOriginGD() {
        return $this->originGD;
    }
    
    public function setOriginGD($origin) {
        $this->originGD = $origin;
    }

    public function getMonthOfPublication() {
        return $this->monthOfPublication;
    }
    
    public function setMonthOfPublication($month) {
        $this->monthOfPublication = $month;
    }
    
    public function getYearOfPublication() {
        return $this->yearOfPublication;
    }
    
    public function setYearOfPublication($year) {
        $this->yearOfPublication = $year;
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
    
    public function getPeriodicalId() {
        return $this->periodicalId;
    }
    
    public function setPeriodicalId($id) {
        $this->periodicalId = $id;
    }
    
    public function getPart() {
        return $this->part;
    }
    
    public function setPart($part) {
        $this->part = $part;
    }
}

