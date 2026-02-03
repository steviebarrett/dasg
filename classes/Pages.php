<?php

class Pages
{
	public static function getPage($slug)
	{
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT slug, title_en, title_gd, content_en, content_gd, updated FROM page WHERE slug = :slug;");
			$sth->execute(array(":slug"=>$slug));
	
			while ($row = $sth->fetch()) {
				$page = new Page($row["slug"]);
				$page->setTitle("en", $row["title_en"]);
				$page->setTitle("gd", $row["title_gd"]);
				$page->setContent("en", $row["content_en"]);
				$page->setContent("gd", $row["content_gd"]);
				$page->setUpdated($row["updated"]);
			}

			return $page;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
	
	public static function getPageTitles()
	{
		$pageTitles = array();
		$dbh = DB::getDatabaseHandle();

		try {
		
			$sth = $dbh->prepare("SELECT slug, title_en, title_gd FROM page");
			$sth->execute();
	
			while ($row = $sth->fetch()) {
				$pageTitles[$row["slug"]] = array(
						"en"=>$row["title_en"],
						"gd"=>$row["title_gd"]);
			}

			return $pageTitles;
	
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}
}