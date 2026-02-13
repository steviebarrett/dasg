<?php


if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

class BlogComment
{
	private $id, $blogId, $userEmail, $approved, $content, $date, $updated;
	
	public function __construct($id)
	{
		$this->id = $id;	
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getBlogId()
	{
		return $this->blogId;
	}
	
	public function setBlogId($blogId) 
	{
		$this->blogId = $blogId;
	}
	
	public function setUserEmail($userEmail) 
	{
		$this->userEmail = $userEmail;
	}
	
	public function getUserEmail()
	{
		return $this->userEmail;
	}
	
	public function getApproved()
	{
		return $this->approved;
	}
	
	public function setApproved($approved) 
	{
		$this->approved = $approved;
	}
	
	public function getContent()
	{
		return $this->content;
	}
	
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	public function getDate()
	{
		return $this->date;
	}
	
	public function setDate($timestamp)
	{
		$this->date = $timestamp;
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