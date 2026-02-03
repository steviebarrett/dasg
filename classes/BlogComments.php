<?php

class BlogComments
{	
	public static function getBlogComment($id) 
	{		
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT id, blogId, userEmail, approved, content, date, updated FROM blogComment WHERE id = :id;");
			$sth->execute(array(":id"=>$id));
	
			while ($row = $sth->fetch()) {
				$blogComment = new BlogComment($id);
				$blogComment->setBlogId($row["blogId"]);
				$blogComment->setUserEmail($row["userEmail"]);
				$blogComment->setApproved($row["approved"]);
				$blogComment->setContent($row["content"]);
				$blogComment->setDate($row["date"]);
				$blogComment->setUpdated($row["updated"]);
			}

			return $blogComment;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public static function saveBlogComment($blogComment) 
	{		
		$dbh = DB::getDatabaseHandle();
		
		try {
			$id = ($blogComment->getId() == -1) ? "" : $blogComment->getId();
			$sth = $dbh->prepare("REPLACE INTO blogComment (id, blogId, userEmail, approved, content, date, updated) VALUES 
				(:id, :blogId, :userEmail, :approved, :content, :date, now())");
			$sth->execute(array(":id"=>$id, 
								":blogId"=>$blogComment->getBlogId(), 
								":userEmail"=>$blogComment->getUserEmail(), 
								":approved"=>$blogComment->getApproved(), 
								":content"=>$blogComment->getContent(), 
								":date"=>$blogComment->getDate()
			));
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public static function getBlogCommentIds($blogId, $approved=true) {
		$blogCommentIds = array();
		$dbh = DB::getDatabaseHandle();

		try {
			$approvedSql = $approved ? "AND approved = 1 " : "";
			$sth = $dbh->prepare("SELECT id FROM blogComment WHERE blogId = :blogId {$approvedSql} ORDER by date DESC");
			$sth->execute(array(":blogId"=>$blogId));
	
			while ($row = $sth->fetch()) {
				$blogCommentIds[] = $row["id"];
			}

			return $blogCommentIds;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}	
	}
	
	public static function getCommentIds($approved = 1) {
		$blogCommentIds = array();
		$dbh = DB::getDatabaseHandle();
		
		try {
			$sth = $dbh->prepare("SELECT id FROM blogComment WHERE approved = {$approved} ORDER by date DESC");
			$sth->execute(array(":blogId"=>$blogId));
		
			while ($row = $sth->fetch()) {
				$blogCommentIds[] = $row["id"];
			}
		
			return $blogCommentIds;
		
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
}