<?php

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}


class DB
{
	private static $databaseHandle;
	const ERROR_REPORTING = true;
	
	private static function connect($dbName, $user = DB_USER, $pass= DB_PASSWORD)
	{
		try { 
			self::$databaseHandle = new PDO(
				"mysql:host=" . DB_HOST . ";dbname=" . $dbName . ";charset=utf8;", $user, $pass
			);
		} catch (PDOException $e){
			echo "A connection could not be established.";
		}
	}
	
	public static function getDatabaseHandle($dbName = DB_NAME, $user = DB_USER, $pass = DB_PASSWORD)
	{
		self::connect($dbName, $user, $pass);
			
		if (self::ERROR_REPORTING)
			self::$databaseHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			
		self::$databaseHandle->query("SET NAMES utf8");
		
		return self::$databaseHandle;
	}
	
	public static function getLastId($dbName, $tableName) 
	{
        // allowlist
        $tableNames = ['blogComment', 'bdl_page', 'meek_transcription'];
        if ($dbName != DB_NAME || !in_array($tableName, $tableNames)) {
            echo json_encode(["error" => "DB/table allowlist error"]);
            return null;
        }

		$dbh = self::getDatabaseHandle($dbName);
		$stmt = $dbh->prepare("SELECT `AUTO_INCREMENT` FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table");
		$stmt->execute([":db" => $dbName, ":table" => $tableName]);
		$lastId = $stmt->fetch(PDO::FETCH_NUM);
		$lastId = $lastId[0];
		return $lastId;
	}
}