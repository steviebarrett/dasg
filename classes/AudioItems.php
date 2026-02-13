<?php


if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

class AudioItems
{	
	public static function getAudioItem($digital_reference) 
	{	
		$dbh = DB::getDatabaseHandle(DB_AUDIO);
		
		try {
			$sth = $dbh->prepare("SELECT isVideo, title, original_tape_name, completed, physical_location, digital_location,
				transcribed, transcription, no_of_audio_files_supplied, no_of_images, fieldworker, contributors, transcription_by, date, year,
				location, tape_brand, reel_size, speed, duration, indexed, contents_information, detailed_contents,
				notes, capture_station, deck, digitiser_initials, sadie_project_number,
				keywords, languages, additionalInfo, is_new, last_updated 
					FROM item
					WHERE digital_reference = :digital_reference;");
			$sth->execute(array(":digital_reference"=>$digital_reference));
	
			while ($row = $sth->fetch()) {
				$audioItem = new AudioItem($digital_reference);
				$audioItem->setIsVideo($row["isVideo"]);
				$audioItem->setTitle($row["title"]);
				$audioItem->setOriginalTapeName($row["original_tape_name"]);
				$audioItem->setCompleted($row["completed"]);
				$audioItem->setPhysicalLocation($row["physical_location"]);
				$audioItem->setDigitalLocation($row["digital_location"]);
				$audioItem->setTranscribed($row["transcribed"]);
				$audioItem->setTranscription($row["transcription"]);
				$audioItem->setNoOfAudioFilesSupplied($row["no_of_audio_files_supplied"]);
				$audioItem->setNoOfImages($row["no_of_images"]);
				$audioItem->setFieldworker($row["fieldworker"]);
				$audioItem->setContributors($row["contributors"]);
				$audioItem->setTranscriptionBy($row["transcription_by"]);
				$audioItem->setDate($row["date"]);
				$audioItem->setYear($row["year"]);
				$audioItem->setLocation($row["location"]);
				$audioItem->setTapeBrand($row["tape_brand"]);
				$audioItem->setReelSize($row["reel_size"]);
				$audioItem->setSpeed($row["speed"]);
				$audioItem->setDuration($row["duration"]);
				$audioItem->setIndexed($row["indexed"]);
				$audioItem->setContentsInformation($row["contents_information"]);
				$audioItem->setDetailedContents($row["detailed_contents"]);
				$audioItem->setNotes($row["notes"]);
				$audioItem->setCaptureStation($row["capture_station"]);
				$audioItem->setDeck($row["deck"]);
				$audioItem->setDigitiserInitials($row["digitiser_initials"]);
				$audioItem->setSadieProjectNumber($row["sadie_project_number"]);
				$audioItem->setKeywords($row["keywords"]);
				$audioItem->setLanguages($row["languages"]);
				$audioItem->setAdditionalInfo($row["additionalInfo"]);
				$audioItem->setIsNew($row["is_new"]);
				$audioItem->setLastUpdated($row["last_updated"]);
			}
			return $audioItem;
	
		} catch (PDOException $e) {
			echo "The item could not be loaded";
		}
	}
	
	public static function getAudioItemReferences($archive = "crc", $searchTerm = null, $completedOnly = 1) {
		$audioItemRefs = array();
		$dbh = DB::getDatabaseHandle(DB_AUDIO);
		$searchFields = array(":archive" => $archive);
		try {
			$query = "SELECT digital_reference FROM item ";
			$whereClause = "";
			if ($completedOnly == 1) {
				$whereClause = "WHERE archiveName = :archive AND completed = 1 ";
			}
			if (!empty($searchTerm)) {
				$searchFields[":searchTerm"] = Functions::getAccentInsensitive($searchTerm, false);
				$whereClause .= ($whereClause == "") ? "WHERE " : "AND "; 
				$whereClause .= "(detailed_contents REGEXP :searchTerm OR title REGEXP :searchTerm OR fieldworker REGEXP :searchTerm
                     OR contributors REGEXP :searchTerm OR year REGEXP :searchTerm OR location REGEXP :searchTerm OR contents_information REGEXP :searchTerm)";
			}
			$query .= $whereClause;
			$sth = $dbh->prepare($query);
			$sth->execute($searchFields);
	
			while ($row = $sth->fetch()) {
				$audioItemRefs[] = $row["digital_reference"];
			}

			return $audioItemRefs;
	
		} catch (PDOException $e) {
			echo "The items could not be loaded";
		}	
	}
	
	public static function getAudioItemsForTranscriptions($archive = "crc", $searchTerm = null) {
		$audioItemRefs = array();
		$dbh = DB::getDatabaseHandle(DB_AUDIO);
		$searchFields = array();
		
		try {
			$query = <<<SQL
					SELECT digital_reference,
						MATCH (transcription) AGAINST (:searchTerm IN BOOLEAN MODE) AS relevance
					FROM item 
					WHERE MATCH (transcription) AGAINST (:searchTerm IN BOOLEAN MODE) AND completed = 1 AND archiveName = :archive
					ORDER BY relevance DESC
SQL;
			$searchFields = array(":searchTerm" => $searchTerm);
			$searchFields[":archive"] = $archive;
			$sth = $dbh->prepare($query);
			$sth->execute($searchFields);
			
			while ($row = $sth->fetch()) {
				$audioItemRefs[] = $row["digital_reference"];
			}
	
			return $audioItemRefs;
			
		} catch (PDOException $e) {
			echo "The items could not be loaded";
		}
	}
	
	/*
	 * Checks if a given archive has any transcriptions
	 * Returns a boolean
	 */
	public static function archiveHasTranscriptions($archive) {
	    $audioItemRefs = array();
	    $dbh = DB::getDatabaseHandle(DB_AUDIO);	    
	    try {
	        $query = <<<SQL
					SELECT COUNT(*) AS cnt FROM `item` WHERE `transcribed` = "Yes" AND archiveName = :archive
SQL;
	        $searchFields = array(":archive" => $archive);
	        $sth = $dbh->prepare($query);
	        $sth->execute($searchFields);
	        $row = $sth->fetch();   
	        return $row["cnt"] > 0;
	    } catch (PDOException $e) {
	        echo "The information could not be loaded";
	    }
	}
	
	public static function updatePlayCount($ref, $numPlays = 1) {
	    $dbh = DB::getDatabaseHandle(DB_AUDIO);
	    $prevNumPlays = self::_getNumPlays($ref);
	    $numPlays += $prevNumPlays;
	    try {
	        $query = <<<SQL
					REPLACE INTO `audioPlays` (`digital_reference`, `play_count`) VALUES(:ref, :numPlays)
SQL;
	        $searchFields = array(":ref" => $ref, "numPlays" => $numPlays);
	        $sth = $dbh->prepare($query);
	        $sth->execute($searchFields);
	    } catch (PDOException $e) {
	        echo "The play count could not be updated";
	    }
	}
	
	private static function _getNumPlays($ref) {
	    $dbh = DB::getDatabaseHandle(DB_AUDIO);
	    try {
	        $query = <<<SQL
					SELECT `play_count` FROM `audioPlays` WHERE `digital_reference` = :ref
SQL;
	        $searchFields = array(":ref" => $ref);
	        $sth = $dbh->prepare($query);
	        $sth->execute($searchFields);
	        $row = $sth->fetch();
	        return $row["play_count"];
	    } catch (PDOException $e) {
	        echo "The play count could not be loaded";
	    }
	}
}