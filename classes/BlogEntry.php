<?php

class BlogEntry
{
	private $id, $title, $author, $lexicopiaEntry, $publishDate, $updated;
	private $content = array();
	private $date = array();
	
	public function __construct($id)
	{
		$this->id = $id;	
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	
	public function setTitle($title) 
	{
		$this->title = $title;
	}
	
	public function setAuthor($author) 
	{
		$this->author = $author;
	}
	
	public function getAuthor()
	{
		return $this->author;
	}
	
	public function getDate($lang)
	{
		return $this->date[$lang];
	}
	
	public function setDate($lang, $timestamp) 
	{
		$this->date[$lang] = $timestamp;
	}
	
	public function getLexicopiaEntry()
	{
		return $this->lexicopiaEntry;
	}
	
	public function setLexicopiaEntry($term)
	{
		$this->lexicopiaEntry = $term;
	}
	
	public function getContent($lang)
	{
		return $this->content[$lang];
	}
	
	public function setContent($lang, $content) 
	{
		$this->content[$lang] = $content;
	}
	
	public function getFirstLine($lang) {
		preg_match('/^([^.!?]*[\.!?]+){0,1}/', strip_tags($this->content[$lang]), $firstLine);
		return $firstLine[0];
	}
	
	public function getPublishTimestamp()
	{
		return strtotime($this->getPublishDate());
	}
	
	public function getPublishDate()
	{
		return $this->publishDate;
	}
	
	public function getPublishMonth() {
		return substr($this->getPublishDate(),5,2);
	}
	
	public function setPublishDate($timestamp)
	{
		//parse the input and reformat into MySQL format 
		$timestamp = str_replace('/', '-', $timestamp);
		if (strlen($timestamp < 19))
			$timestamp .= ":00";
		
		$this->publishDate = $timestamp;	
	}
	
	public function getIsPublished() {
		return $this->getPublishTimestamp() <= time();
	}
	
	public function getUpdated()
	{
		return $this->updated;
	}
	
	public function setUpdated($timestamp)
	{
		$this->updated = $timestamp;
	}
}