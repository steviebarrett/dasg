<?php

class BlogEntries
{	
	public static function getBlogEntry($id) 
	{		
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT title, author, date_en, date_gd, content_en, content_gd, publish_date, updated FROM blogEntry WHERE id = :id;");
			$sth->execute(array(":id"=>$id));
	
			while ($row = $sth->fetch()) {
				$blogEntry = new BlogEntry($id);
				$blogEntry->setTitle($row["title"]);
				$blogEntry->setDate("en", $row["date_en"]);
				$blogEntry->setDate("gd", $row["date_gd"]);
				$blogEntry->setAuthor($row["author"]);
				$blogEntry->setContent("en", $row["content_en"]);
				$blogEntry->setContent("gd", $row["content_gd"]);
				$blogEntry->setPublishDate($row["publish_date"]);
				$blogEntry->setUpdated($row["updated"]);
			}

			return $blogEntry;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public static function saveBlogEntry($blogEntry) 
	{		
		$dbh = DB::getDatabaseHandle();
		
		try {
			$id = ($blogEntry->getId() == -1) ? "" : $blogEntry->getId();
			$sth = $dbh->prepare("REPLACE INTO blogEntry (id, title, date_en, date_gd, author, content_en, content_gd, publish_date, updated) VALUES 
				(:id, :title, :date_en, :date_gd, :author, :content_en, :content_gd, :publish_date, now())");
			$sth->execute(array(":id"=>$id, 
								":title"=>$blogEntry->getTitle(), 
								":date_en"=>$blogEntry->getDate("en"), 
								":date_gd"=>$blogEntry->getDate("gd"), 
								":author"=>$blogEntry->getAuthor(), 
								":content_en"=>$blogEntry->getContent("en"), 
								":content_gd"=>$blogEntry->getContent("gd"), 
								":publish_date"=>$blogEntry->getPublishDate()	
			));
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public static function getAllBlogEntryIds()
	{		
		$blogEntryIds = array();
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT id FROM blogEntry ORDER by id DESC");
			$sth->execute();
	
			while ($row = $sth->fetch()) {
				$blogEntryIds[] = $row["id"];
			}

			return $blogEntryIds;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public static function getMostRecentBlogEntryIds($numEntries) {
		$blogEntryIds = array();
		$dbh = DB::getDatabaseHandle();
		try {
		
			$sth = $dbh->prepare("SELECT id FROM blogEntry WHERE publish_date < NOW() ORDER BY publish_date DESC");
			$sth->execute();

			for ($i=0;$i<$numEntries;$i++) {
				$row = $sth->fetch();
				$blogEntryIds[$i] = $row["id"];
			}

			return $blogEntryIds;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}	
	}
	
	public static function getMostRecentBlogEntryId() {
		$blogIds = self::getMostRecentBlogEntryIds(1);
		return $blogIds[0];
	}
	
	public static function getBlogEntryIdsByDate() {
		$blogEntryIds = array();
		$dbh = DB::getDatabaseHandle();
		try {
		
			$sth = $dbh->prepare("SELECT id, publish_date FROM blogEntry WHERE publish_date < NOW() ORDER BY publish_date DESC");
			$sth->execute();

			while ($row = $sth->fetch()) {
				$year = substr($row["publish_date"], 0, 4);
				$month = substr($row["publish_date"], 5, 2);
				$month = (int)$month;
				$blogEntryIds[$year][$month][] = $row["id"];
			}

			return $blogEntryIds;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}	
	}
	
	public static function getNextAndPrevIds($id) {
		$blogEntryIds = array();
		$dbh = DB::getDatabaseHandle();
		try {
		
			$sth = $dbh->prepare("SELECT id, publish_date FROM blogEntry WHERE publish_date < NOW() ORDER BY publish_date DESC");
			$sth->execute();
			$i = $hit = 0;
			while ($row = $sth->fetch()) {

				$blogEntryIds[$i] = $row["id"];
				if ($row["id"] == $id) {
					$hit = $i;
				}
 				$i++;
			}
			
			$next = (isset($blogEntryIds[$hit - 1])) ? $blogEntryIds[$hit - 1] : null;
			$prev = (isset($blogEntryIds[$hit + 1])) ? $blogEntryIds[$hit + 1] : null;
			
			return array("next"=>$next, "prev"=>$prev);
		
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
}