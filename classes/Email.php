<?php

class Email
{
	private $to, $subject, $message, $from;
	private $cc = array();
	
	public function __construct($to, $subject, $message, $from)	
	{
        $toClean = $this->cleanHeader($to);
        if (!filter_var($toClean, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }
        $this->to = $toClean;

        $fromClean = $this->cleanHeader($from);
        if (!filter_var($fromClean, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }
        $this->from = $fromClean;

		$this->subject = $subject;
		$this->message = $message;
	}
	
	public function getTo()
	{
		return $this->to;
	}
	
	public function getSubject()
	{
		return $this->subject;
	}
	
	public function getMessage()
	{
		return $this->message;
	}
	
	public function getFrom()
	{
		return $this->from;
	}
	
	public function setCc($cc)
	{
		$this->cc = $cc;	
	}
	
	public function getCc()
	{
		return $this->cc;	
	}
	
	public function getCcList()
	{
		return implode(",", $this->getCc());
	}
	
	public function getHeaders()
	{		
		$headers[] = "From: " . $this->getFrom();
		$headers[] = "Reply-To: " . $this->getFrom();
		if($this->getCc())
			$headers[] = "CC: " . $this->getCcList();
		$headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
		
		return implode("\r\n", $headers);
	}
	
	public function send()
	{
		mail($this->getTo(), $this->getSubject(), $this->getMessage(), $this->getHeaders());	
	}

    private function cleanHeader(string $value): string
    {
        return preg_replace('/[\x00-\x1F\x7F]+/u', '', $value);
    }
}