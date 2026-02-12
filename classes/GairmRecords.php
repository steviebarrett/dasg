<?php

class GairmRecords
{
	public static function getGairmRecord($id) {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		
		try {
			$sth = $dbh->prepare("SELECT lastName, firstName, CONCAT(origin, '/', origin_gd) as origin, year, yearOfPublication,
						volume, firstPage, lastPage, title, language, type, genre, register, comments, transcription
					FROM gairm_record
					WHERE id = :id;");
			$sth->execute(array(":id" => $id));
			
			while ($row = $sth->fetch()) {
				$gairmRecord = new GairmRecord($id);
				$gairmRecord->setLastName($row["lastName"]);
				$gairmRecord->setFirstName($row["firstName"]);
				$gairmRecord->setOrigin($row["origin"]);
				$gairmRecord->setYear($row["year"]);
				$gairmRecord->setYearOfPublication($row["yearOfPublication"]);
				$gairmRecord->setVolume($row["volume"]);
				$gairmRecord->setFirstPage($row["firstPage"]);
				$gairmRecord->setLastPage($row["lastPage"]);
				$gairmRecord->setTitle($row["title"]);
				$gairmRecord->setLanguage($row["language"]);
				$gairmRecord->setType($row["type"]);
				$gairmRecord->setGenre($row["genre"]);
				$gairmRecord->setRegister($row["register"]);
				$gairmRecord->setComments($row["comments"]);
				$gairmRecord->setTranscription($row["transcription"]);
			}
			return $gairmRecord;
			
		} catch (PDOException $e) {
			echo "The record could not be loaded";
		}
	}

	/*
	 * !!!! Only in use to fetch records WITH transcriptions for now
	 * @return array $gairmRecords - an array of GairmRecord objects
	 */
	public static function getGairmRecordByVolAndPage($volume, $filename) {
		$gairmRecords = array();
		$filename = trim($filename, ".pdf");
		$elems = explode('_', $filename);
		$pageType = $elems[0];
		$page = $elems[1];
		//if the page type is DA then we need to translate roman numerals
		if ($pageType == "DA") {
			$romans = array("", "i", "ii", "iii", "iv", "v", "vi", "vii", "viii", "ix", "x");
			$page = '[' . $romans[$page] . ']'; //roman numerals are stored in square brackets in the DB
		} else if ($pageType == "C") {  //and ignore cover pages
			return $gairmRecords;
		}
		$dbh = DB::getDatabaseHandle(DB_NAME);

		//check for multipage record
		$multipage = false;
		try {
			$sth = $dbh->prepare("SELECT lastPage FROM gairm_record WHERE volume = :volume 
				AND transcription IS NOT NULL AND firstPage = :page;");
			$sth->execute(array(":volume" => $volume, ":page" => $page));
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			if ($row["lastPage"] != $page) {
				$multipage = true;
			}
		} catch (PDOException $e) {
			echo "The record could not be loaded";
		}

		try {
			$pageQuery = $multipage ? "firstPage <= :page AND lastPage >= :page": "firstPage = :page;";
			$sth = $dbh->prepare("SELECT id, lastName, firstName, CONCAT(origin, '/', origin_gd) as origin, year, yearOfPublication,
						firstPage, lastPage, title, language, type, genre, register, comments, transcription
					FROM gairm_record
					WHERE volume = :volume AND transcription IS NOT NULL AND {$pageQuery};");
			$sth->execute(array(":volume" => $volume, ":page" => $page));

			while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$gairmRecord = new GairmRecord($row["id"]);
				$gairmRecord->setLastName($row["lastName"]);
				$gairmRecord->setFirstName($row["firstName"]);
				$gairmRecord->setOrigin($row["origin"]);
				$gairmRecord->setYear($row["year"]);
				$gairmRecord->setYearOfPublication($row["yearOfPublication"]);
				$gairmRecord->setVolume($volume);
				$gairmRecord->setFirstPage($row["firstPage"]);
				$gairmRecord->setLastPage($row["lastPage"]);
				$gairmRecord->setTitle($row["title"]);
				$gairmRecord->setLanguage($row["language"]);
				$gairmRecord->setType($row["type"]);
				$gairmRecord->setGenre($row["genre"]);
				$gairmRecord->setRegister($row["register"]);
				$gairmRecord->setComments($row["comments"]);
				$gairmRecord->setTranscription($row["transcription"]);
				$gairmRecords[] = $gairmRecord;
			}
			return $gairmRecords;

		} catch (PDOException $e) {
			echo "The record could not be loaded";
		}
	}
	
	/*
	 * Searches the Gairm Record data and returns an array of GairmRecord objects
	 */
	public static function searchRecords($queryString, $searchFields, $filterFields) {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		
		$placeholders = array();
		$sql = "SELECT id FROM gairm_record ";

        // Allowlist searchable columns (identifiers) to prevent SQL injection
        $allowedSearchFields = [
            'title'         => 'title',
            'lastName'      => 'lastName',
            'firstName'     => 'firstName',
            'origin'        => 'origin',
            'origin_gd'     => 'origin_gd',
            'year'          => 'year',
            'comments'      => 'comments',
            'transcription' => 'transcription',
        ];

        $allowedFilterFields = [
            'type'     => 'type',
            'genre'    => 'genre',
            'register' => 'register',
            'language' => 'language',
        ];

        if (!empty($filterFields)) {
            foreach ($filterFields as $field => $value) {
                if (!is_string($field) || !isset($allowedFilterFields[$field])) {
                    continue; // ignore unknown filters
                }
                $fieldSafe = $allowedFilterFields[$field];
                $sql .= "INNER JOIN gairm_record_{$fieldSafe} ON id = gairm_record_{$fieldSafe}.record_id ";
            }
        }

        $sql .= "WHERE (";
        $queryString = trim((string)$queryString);
        $queryTerms = $queryString === '' ? [] : preg_split('/\s+/', $queryString);

        foreach ((array)$searchFields as $field) {
            if (!is_string($field) || !isset($allowedSearchFields[$field])) {
                continue;
            }
            $fieldSafe = $allowedSearchFields[$field];

            foreach ($queryTerms as $term) {
                $term = (string)$term;
                if ($term === '') continue;
                $sql .= "{$fieldSafe} LIKE ? OR ";
                $placeholders[] = "%{$term}%";
            }
        }

        // If no valid fields or no terms, avoid returning everything accidentally
        if (substr($sql, -6) === 'WHERE (') {
            return [];
        }
        //trim the trailing OR
        $sql = substr($sql, 0, strlen($sql)-3) . ") ";

        if (!empty($filterFields)) {
            foreach ($filterFields as $field => $value) {
                if (!is_string($field) || !isset($allowedFilterFields[$field])) {
                    continue;
                }
                $fieldSafe = $allowedFilterFields[$field];
                $sql .= "AND {$fieldSafe}_name = ? ";
                $placeholders[] = (string)$value;
            }
        }

		try {
			$sth = $dbh->prepare($sql);
			$sth->execute($placeholders);
			
			$gairmRecords = array();
			while ($row = $sth->fetch()) {
				$id = $row[0];
				$gairmRecords[$id] = self::getGairmRecord($id);
			}
			return $gairmRecords;
		} catch (PDOException $e) {
			echo "The records could not be loaded";
		}
	}
	
	/*
	 * Browses the Gairm Record data and returns an array of GairmRecord objects
	 */
	public static function browseRecords($query, $searchFields) {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		$placeholders = array();
		$sql = "SELECT id FROM gairm_record WHERE ";
		
		$queryString = trim($query);
		$queryString = str_replace('||', '"', $queryString);	//double pipe used to replace double quotes
		$queryTerms = explode('|', $queryString); 	//muliple elements must be separated by a single pipe
		$x = 0;
		foreach ($searchFields as $field) {
			$sql .= "{$field} = ? AND ";
			$placeholders[] = $queryTerms[$x];
			$x++;
		}
		//trim the trailing AND
		$sql = substr($sql, 0, strlen($sql)-4);
		
		try {
			$sth = $dbh->prepare($sql);
			$sth->execute($placeholders);
			
			$gairmRecords = array();
			while ($row = $sth->fetch()) {
				$id = $row[0];
				$gairmRecords[$id] = self::getGairmRecord($id);
			}
			return $gairmRecords;
		} catch (PDOException $e) {
			echo "The records could not be loaded";
		}
	}
	
	public static function getRecordsByAuthor() {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		$sql = "SELECT id, title, yearOfPublication, volume, lastName, firstName FROM gairm_record ORDER BY lastName, firstName, volume ASC";
		$results = array();
		try {
			$sth = $dbh->prepare($sql);
			$sth->execute();
			while ($row = $sth->fetch()) {
				$results[] = $row;
			}
			return $results;
		} catch (PDOException $e) {
			echo "The records could not be loaded";
		}
	}
	
	public static function getRecordsByVolume() {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		$sql = "SELECT id, title, yearOfPublication, volume, lastName, firstName, firstPage FROM gairm_record ORDER BY CAST(volume AS UNSIGNED), CAST(firstPage AS UNSIGNED) ASC";
		$results = array();
		try {
			$sth = $dbh->prepare($sql);
			$sth->execute();
			while ($row = $sth->fetch()) {
				$results[] = $row;
			}
			return $results;
		} catch (PDOException $e) {
			echo "The records could not be loaded";
		}
	}
	
	public static function getFilterValues($tableSuffix) {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		$sql = "SELECT name FROM gairm_{$tableSuffix} ORDER BY name";
		try {
			$sth = $dbh->prepare($sql);
			$sth->execute(array());
			$filterValues = array();
			while ($row = $sth->fetch()) {
				$filterValues[] = $row[0];
			}
			return $filterValues;
		}  catch (PDOException $e) {
			echo "The filters could not be loaded";
		}
	}
	
	public static function getYears() {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		$sql = "SELECT DISTINCT yearOfPublication FROM gairm_record ORDER BY year ASC";
		try {
			$sth = $dbh->prepare($sql);
			$sth->execute();
			$years = array();
			while ($row = $sth->fetch()) {
				$years[] = $row[0];
			}
			return $years;
		}  catch (PDOException $e) {
			echo "The year values could not be loaded";
		}
	}
	
	public static function getAuthors() {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		$sql = "SELECT DISTINCT lastName, firstName FROM gairm_record WHERE lastName != '' ORDER BY lastName, firstName ASC";
		try {
			$sth = $dbh->prepare($sql);
			$sth->execute();
			$authors = array();
			$i=0;
			while ($row = $sth->fetch()) {
				$authors[$i]["lastName"] = $row[0];
				$authors[$i]["firstName"] = $row[1];
				$i++;
			}
			return $authors;
		}  catch (PDOException $e) {
			echo "The authors could not be loaded";
		}
	}
	
	public static function getVolumes() {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		$sql = "SELECT DISTINCT volume FROM gairm_record ORDER BY CAST(volume AS UNSIGNED) ASC";
		try {
			$sth = $dbh->prepare($sql);
			$sth->execute();
			$volumes = array();
			while ($row = $sth->fetch()) {
				$volumes[] = $row[0];
			}
			return $volumes;
		}  catch (PDOException $e) {
			echo "The volumes could not be loaded";
		}
	}

	public static function getFirstPageNoInVolume($vol) {
		$dbh = DB::getDatabaseHandle(DB_NAME);
		$sql = "SELECT MIN(CAST(firstPage AS unsigned)) AS first FROM gairm_record WHERE volume = :vol AND firstPage NOT LIKE '[%'";
		try {
			$sth = $dbh->prepare($sql);
			$sth->execute(array(":vol" => $vol));
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			return $row["first"];
		}  catch (PDOException $e) {
			echo "The first page number could not be loaded";
		}
	}
}