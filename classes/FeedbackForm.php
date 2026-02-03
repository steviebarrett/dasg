<?php

class FeedbackForm
{
	public static function saveUser($name, $email) 
	{
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("REPLACE INTO feedbackUser (username, useremail) VALUES(:name, :email);");
			$sth->execute(array(":name"=>$name, ":email"=>$email));
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}	
	}
	
	public static function loadUsers()
	{
		$users = array();
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT username, useremail, updated FROM feedbackUser ORDER BY updated DESC;");
			$sth->execute();
	
			while ($row = $sth->fetch()) {
				$users[] = $row;
			}

			return $users;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public static function loadUser($email)
	{
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT username FROM feedbackUser WHERE useremail = :email;");
			$sth->execute(array(":email"=>$email));
	
			$row = $sth->fetch();

			return $row;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}		
	}
	
	public static function saveAnswer($email, $id, $answer) 
	{
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("REPLACE INTO feedbackData (useremail, label_id, feedback) VALUES(:email, :id, :answer);");
			$sth->execute(array(":email"=>$email, ":id"=>$id, ":answer"=>$answer));
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}	
	}
	
	public function loadAnswers($email)
	{
		$answers = array();
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT label_id, feedback FROM feedbackData WHERE useremail = :email ORDER BY label_id;");
			$sth->execute(array(":email"=>$email));
	
			while ($row = $sth->fetch()) {
				$answers[$row["label_id"]] = $row["feedback"];
			}

			return $answers;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public static function getLabels($labelType) 
	{
		$labels = array();
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT number,label FROM feedbackLabels WHERE type = :labelType; ORDER BY number");
			$sth->execute(array(":labelType"=>$labelType));
	
			while ($row = $sth->fetch()) {
				$labels[$row["number"]] = $row["label"];
			}
	
			return $labels;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public static function getAllLabels()
	{
		$labels = array();
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT number,label,type FROM feedbackLabels ORDER BY type DESC, number ASC");
			$sth->execute();
	
			while ($row = $sth->fetch()) {
				$labels[$row["type"] . $row["number"]] = $row["label"];
			}
	
			return $labels;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
}