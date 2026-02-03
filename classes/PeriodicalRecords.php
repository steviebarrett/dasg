<?php

class PeriodicalRecords
{
    public static function getPeriodicalRecord($id) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        
        try {
            $sth = $dbh->prepare("SELECT lastName, firstName, origin, origin_gd, monthOfPublication, yearOfPublication,
						firstPage, lastPage, title, language, type, genre, register, comments, periodicalId, part
					FROM periodical_record
					WHERE id = :id;");
            $sth->execute(array(":id" => $id));
            
            while ($row = $sth->fetch()) {
                $periodicalRecord = new PeriodicalRecord($id);
                $periodicalRecord->setLastName($row["lastName"]);
                $periodicalRecord->setFirstName($row["firstName"]);
                $periodicalRecord->setOrigin($row["origin"]);
                $periodicalRecord->setOriginGD($row["origin_gd"]);
                $periodicalRecord->setMonthOfPublication($row["monthOfPublication"]);
                $periodicalRecord->setYearOfPublication($row["yearOfPublication"]);
                $periodicalRecord->setFirstPage($row["firstPage"]);
                $periodicalRecord->setLastPage($row["lastPage"]);
                $periodicalRecord->setTitle($row["title"]);
                $periodicalRecord->setLanguage($row["language"]);
                $periodicalRecord->setType($row["type"]);
                $periodicalRecord->setGenre($row["genre"]);
                $periodicalRecord->setRegister($row["register"]);
                $periodicalRecord->setComments($row["comments"]);
                $periodicalRecord->setPeriodicalId($row["periodicalId"]);
                $periodicalRecord->setPart($row["part"]);
            }
            return $periodicalRecord;
            
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
    /*
     * Updates the data for a Periodical record in the database
     * Includes a private call to update the foreign keyed data (genre, language, type)
     */
    public static function setPeriodicalRecord($data) {
        //get the previous Periodical object data to check for orphans later on
        if ($data["id"] != -1) {	//existing record
            $prevRecord = self::getPeriodicalRecord($data["id"]);
            $id = $data["id"];
        } else {
            $id = null;
        }
        
        $dbh = DB::getDatabaseHandle(DB_NAME);
        
        //update the record table
        $query = <<<SQL
			REPLACE INTO periodical_record VALUES (
				:id,
				:lastName,
				:firstName,
				:origin,
				:origin_gd,
                :monthOfPub,
				:yearOfPub,
				:firstPage,
				:lastPage,
				:title,
				:language,
				:type,
				:genre,
				:register,
				:comments,
				:periodicalId,
                :part
			)
SQL;
        try {
            $sth = $dbh->prepare($query);
            $sth->execute(array(
                ":id"		=>$id,
                ":lastName"	=>$data["lastName"],
                ":firstName"=>$data["firstName"],
                ":origin"	=>$data["origin"],
                ":origin_gd"=>$data["origin_gd"],
                ":monthOfPub"=>$data["monthOfPublication"],
                ":yearOfPub"=>$data["yearOfPublication"],
                ":firstPage"=>$data["firstPage"],
                ":lastPage"	=>$data["lastPage"],
                ":title"	=>$data["title"],
                ":language"	=>$data["language"],
                ":type"		=>$data["type"],
                ":genre"	=>$data["genre"],
                ":register"	=>$data["register"],
                ":comments"	=>$data["comments"],
                ":periodicalId"=>$data["periodicalId"],
                ":part"     =>$data["part"]
            ));
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        
        if ($id === null) {	//new record so get the last insert ID
            $data["id"] = $dbh->lastInsertId();
            $prevRecord = new PeriodicalRecord($data["id"]);
        }
        
        return true;
    }
    
    /*
     * Searches the Periodical Record data and returns an array of PeriodicalRecord objects
     */
    public static function searchRecords($queryString, $searchFields) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        
        $placeholders = array();
        $sql = "SELECT id FROM periodical_record ";
        
        $sql .= "WHERE (";
        foreach ($searchFields as $field) {
            $queryString = trim($queryString);
            $queryTerms = explode(' ', $queryString);
            foreach ($queryTerms as $term) {
                $sql .= "{$field} LIKE ? OR ";
                $placeholders[] = "%{$term}%";
            }
        }
        //trim the trailing OR
        $sql = substr($sql, 0, strlen($sql)-3) . ") ";
        
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute($placeholders);
            
            $PeriodicalRecords = array();
            while ($row = $sth->fetch()) {
                $id = $row[0];
                $PeriodicalRecords[$id] = self::getPeriodicalRecord($id);
            }
            return $PeriodicalRecords;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
    /*
     * Browses the Periodical Record data and returns an array of PeriodicalRecord objects
     */
    public static function browseRecords($query, $searchFields) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $placeholders = array();
        $sql = "SELECT id FROM periodical_record WHERE ";
        
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
            
            $PeriodicalRecords = array();
            while ($row = $sth->fetch()) {
                $id = $row[0];
                $PeriodicalRecords[$id] = self::getPeriodicalRecord($id);
            }
            return $PeriodicalRecords;
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
    public static function getYears() {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sql = "SELECT DISTINCT yearOfPublication FROM periodical_record ORDER BY year ASC";
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $years = array();
            while ($row = $sth->fetch()) {
                $years[] = $row[0];
            }
            return $years;
        }  catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
    public static function getAuthors() {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sql = "SELECT DISTINCT lastName, firstName FROM periodical_record WHERE lastName != '' ORDER BY lastName, firstName ASC";
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
            echo $e->getMessage();
        }
    }
    
    public static function getVolumes() {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sql = "SELECT DISTINCT volume FROM periodical_record ORDER BY CAST(volume AS UNSIGNED) ASC";
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $volumes = array();
            while ($row = $sth->fetch()) {
                $volumes[] = $row[0];
            }
            return $volumes;
        }  catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
    public static function getFieldData($field) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sql = "SELECT DISTINCT {$field} FROM periodical_record ORDER BY {$field} ASC";
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $fieldData = array();
            while ($row = $sth->fetch()) {
                $fieldData[] = $row[0];
            }
            return $fieldData;
        }  catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    /*
     public static function getLanguages() {
     $dbh = DB::getDatabaseHandle(DB_NAME);
     $sql = "SELECT DISTINCT language FROM periodical_record ORDER BY language ASC";
     try {
     $sth = $dbh->prepare($sql);
     $sth->execute();
     $languages = array();
     while ($row = $sth->fetch()) {
     $languages[] = $row[0];
     }
     return $languages;
     }  catch (PDOException $e) {
     echo $e->getMessage();
     }
     }
     
     public static function getTypes() {
     $dbh = DB::getDatabaseHandle(DB_NAME);
     $sql = "SELECT DISTINCT type FROM periodical_record ORDER BY type ASC";
     try {
     $sth = $dbh->prepare($sql);
     $sth->execute();
     $types = array();
     while ($row = $sth->fetch()) {
     $types[] = $row[0];
     }
     return $types;
     }  catch (PDOException $e) {
     echo $e->getMessage();
     }
     }
     
     public static function getGenres() {
     $dbh = DB::getDatabaseHandle(DB_NAME);
     $sql = "SELECT DISTINCT genre FROM periodical_record ORDER BY genre ASC";
     try {
     $sth = $dbh->prepare($sql);
     $sth->execute();
     $genres = array();
     while ($row = $sth->fetch()) {
     $genres[] = $row[0];
     }
     return $genres;
     }  catch (PDOException $e) {
     echo $e->getMessage();
     }
     }
     
     public static function getRegisters() {
     $dbh = DB::getDatabaseHandle(DB_NAME);
     $sql = "SELECT DISTINCT register FROM periodical_record ORDER BY register ASC";
     try {
     $sth = $dbh->prepare($sql);
     $sth->execute();
     $registers = array();
     while ($row = $sth->fetch()) {
     $registers[] = $row[0];
     }
     return $registers;
     }  catch (PDOException $e) {
     echo $e->getMessage();
     }
     }
     */
    public static function getPeriodicals() {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sql = "SELECT id, title, volume FROM periodical ORDER BY title, volume ASC";
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $periodicals = array();
            while ($row = $sth->fetch()) {
                $periodicals[$row["id"]]["title"] = $row["title"];
                $periodicals[$row["id"]]["volume"] = $row["volume"];
            }
            return $periodicals;
        }  catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
    
    public static function getPeriodicalById($id) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sql = "SELECT title, volume FROM periodical WHERE id = :id ORDER BY title, volume ASC";
        try {
            $sth = $dbh->prepare($sql);
            $sth->execute(array(":id" => $id));
            $row = $sth->fetch();
            return $row;
        }  catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}