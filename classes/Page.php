<?php

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

class Page
{
	private $slug, $updated;
	private $title = array();
	private $content = array();
	
	public function __construct($slug)
	{
		$this->slug = $slug;	
	}
	
	public function getSlug()
	{
		return $this->slug;
	}
	
	public function getTitle($lang)
	{
		return $this->title[$lang];
	}
	
	public function setTitle($lang, $title) 
	{
		$this->title[$lang] = $title;
	}
	
	public function getContent($lang)
	{
		return $this->content[$lang];
	}
	
	public function setContent($lang, $content) 
	{
		$this->content[$lang] = $content;
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