<?php

class MssSlips
{
    public static function getSlip($id) {
        $query = "SELECT
						id,
						headword,
                        headword_hdsg,
						slip_found,
						quotation,
                        quotation_hdsg,
						author,
						title,
                        title_2,
                        volume,
                        volume_2,
						page,
                        page_2,
						date,
						notes,
						translation,
                        translation_hdsg,
						sense,
						sense_hdsg,
						edition,
						folder
 					FROM mss_slip
					WHERE id= :id ";
        
        $dbh = DB::getDatabaseHandle(DB_NAME);
        
        $sth = $dbh->prepare($query);
        $sth->bindParam(":id", $id);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        
        $slips = array();
        //make the field names user friendly
        foreach ($result as $fieldname => $value) {
            //		$slips[$fieldname]["fieldname"] = Functions::getFriendlyFieldName($fieldname, '_');
            $slips[$fieldname] = ($value == "") ? "n/a" : $value;
        }
        return $slips;
    }
    
    public static function getSlipDateRanges($id) {
        $query = "SELECT date_range_id FROM mss_date_range mdr INNER JOIN mss_slip_date_range ON mdr.id = date_range_id WHERE slip_id= :id ";
        
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->bindParam(":id", $id);
        $sth->execute();
        $dates = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $dates[] = $row["date_range_id"];
        }
        return $dates;
    }
    
    public static function saveSlip($data) {
        
        //replace html break tags with newlines
        foreach ($data as $fieldname => $value) {
            $data[$fieldname] = str_replace("<br />","", $value);
        }
        $query = <<<SQL
			REPLACE INTO mss_slip (
				id,
				headword,
                headword_hdsg,
				slip_found,
				quotation,
                quotation_hdsg,
				author,
				title,
                title_2,
                volume,
                volume_2,
				page,
                page_2,
				date,
				notes,
				translation,
                translation_hdsg,
				sense,
				sense_hdsg,
				edition,
				folder,
                metadataCache)
			VALUES (
				:id,
				:headword,
                :headword_hdsg,
				:slip_found,
				:quotation,
                :quotation_hdsg,
				:author,
				:title,
                :title2,
                :volume,
                :volume2,
				:page,
                :page2,
				:date,
				:notes,
				:translation,
                :translation_hdsg,
				:sense,
				:sense_hdsg,
				:edition,
				:folder,
                :metadataCache)
SQL;
        $metadataFields = array($data["headword"], $data["title"], $data["author"], $data["page"]);
        $metadataCache = self::createMetadataCacheString("-", $metadataFields);
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute(array(
            ":id"=>$data["id"],
            ":headword"=>$data["headword"],
            ":headword_hdsg"=>$data["headword_hdsg"],
            ":slip_found"=>$data["slip_found"],
            ":quotation"=>$data["quotation"],
            ":quotation_hdsg"=>$data["quotation_hdsg"],
            ":author"=>$data["author"],
            ":title"=>$data["title"],
            ":title2"=>$data["title_2"],
            ":volume"=>$data["volume"],
            ":volume2"=>$data["volume_2"],
            ":page"=>$data["page"],
            ":page2"=>$data["page_2"],
            ":date"=>$data["date"],
            ":notes"=>$data["notes"],
            ":translation"=>$data["translation"],
            ":translation_hdsg"=>$data["translation_hdsg"],
            ":sense"=>$data["sense"],
            ":sense_hdsg"=>$data["sense_hdsg"],
            ":edition"=>$data["edition"],
            ":folder"=>$data["folder"],
            ":metadataCache"=>$metadataCache
        ));
        
        //clear old data range(s)
        $query = <<<SQL
			DELETE FROM mss_slip_date_range WHERE slip_id = :slip_id;
SQL;
        $sth = $dbh->prepare($query);
        $sth->bindParam(":slip_id", $data["id"]);
        $sth->execute();
        
        //save the new date range(s)
        $query = <<<SQL
			INSERT INTO mss_slip_date_range (
				slip_id,
				date_range_id)
			VALUES (
				:id,
				:date_range_id)
SQL;
        $sth = $dbh->prepare($query);
        foreach ($data["dateRange"] as $key => $date) {
            $sth->execute(array(
                ":id"=>$data["id"],
                ":date_range_id"=>$date
            ));
        }
        return empty($data["id"]) ? $dbh->lastInsertId() : $data["id"];
    }
    
    /*
     * Searches the slips database using searchString
     */
    public static function searchSlips($searchString, $preciseMatchOnly = false) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $query = ($preciseMatchOnly)
        ? "SELECT id FROM mss_slip WHERE author REGEXP ? OR title REGEXP ? OR title_2 REGEXP ?
				OR headword REGEXP ? OR headword_hdsg REGEXP ? OR quotation REGEXP ? OR quotation_hdsg REGEXP ? OR translation REGEXP ?
                OR translation_hdsg REGEXP ?"
            : "SELECT id FROM mss_slip WHERE author LIKE ? OR title LIKE ? OR title_2 LIKE ? OR headword LIKE ? OR headword_hdsg LIKE ?
                OR quotation LIKE ? OR quotation_hdsg LIKE ? OR translation LIKE ? OR translation_hdsg LIKE ?";
            
            $sth = $dbh->prepare($query);
            for ($i=0; $i<9; $i++) {
                $searchFields[] = ($preciseMatchOnly) ? "[[:<:]]{$searchString}[[:>:]]": "%{$searchString}%";
            }
            $sth->execute($searchFields);
            $slips = array();
            while ($row = $sth->fetch()) {
                $slips[$row["id"]] = self::getSlip($row["id"]);
            }
            return $slips;
    }
    
    /*
     * Searches the slips database using a date range ID
     */
    public static function searchByDateRange($dateRangeId) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $query = <<<SQL
			SELECT id FROM mss_slip ms INNER JOIN mss_slip_date_range msdr ON msdr.slip_id COLLATE utf8_general_ci = ms.id WHERE msdr.date_range_id = ?
SQL;
        $sth = $dbh->prepare($query);
        $sth->execute(array($dateRangeId));
        $slips = array();
        while ($row = $sth->fetch()) {
            $slips[$row["id"]] = self::getSlip($row["id"]);
        }
        return $slips;
    }
    
    public static function searchByAuthor($author) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $query = <<<SQL
			SELECT id FROM mss_slip WHERE author = ?
SQL;
        $sth = $dbh->prepare($query);
        $sth->execute(array($author));
        $slips = array();
        while ($row = $sth->fetch()) {
            $slips[$row["id"]] = self::getSlip($row["id"]);
        }
        return $slips;
    }
    
    public static function searchByTitle($title) {
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $query = <<<SQL
			SELECT id FROM mss_slip WHERE title = ?
SQL;
        $sth = $dbh->prepare($query);
        $sth->execute(array($title));
        $slips = array();
        while ($row = $sth->fetch()) {
            $slips[$row["id"]] = self::getSlip($row["id"]);
        }
        return $slips;
    }
    
    public static function getAllHeadwords($folder = "", $found = "") {
        $whereClause = "";
        $params = array();
        if ($folder !== "") {
            $whereClause = "WHERE folder = :folder";
            $params[":folder"] = $folder;
        }
        if ($found != "") {
            if ($whereClause != "") {
                $whereClause .= " AND ";
            } else {
                $whereClause = "WHERE ";
            }
            $whereClause .= ($found == "1") ? "slip_found = 1 " : "(slip_found != 1 OR slip_found IS NULL) ";
        }
        $query = <<<SQL
			SELECT id, headword FROM mss_slip {$whereClause} ORDER BY id ASC
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute($params);
        $slips = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $slips[$row["id"]] = $row["headword"];
        }
        return $slips;
    }
    
    public static function getDropdownMetadata($folder = "", $found = "") {
        $whereClause = "";
        $params = array();
        if ($folder !== "") {
            $whereClause = "WHERE folder = :folder";
            $params[":folder"] = $folder;
        }
        if ($found != "") {
            if ($whereClause != "") {
                $whereClause .= " AND ";
            } else {
                $whereClause = "WHERE ";
            }
            $whereClause .= ($found == "1") ? "slip_found = 1 " : "(slip_found != 1 OR slip_found IS NULL) ";
        }
        $query = <<<SQL
			SELECT id, headword, metadataCache FROM mss_slip {$whereClause} ORDER BY id ASC
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute($params);
        $slips = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $slips[$row["id"]] = ($folder == "") ? $row["headword"] : $row["metadataCache"];
        }
        return $slips;
    }
    
    public static function getAllIds($folder = "", $found = "") {
        $whereClause = "";
        $params = array();
        if ($folder !== "") {
            $whereClause = "WHERE folder = :folder";
            $params[":folder"] = $folder;
        }
        if ($found != "") {
            if ($whereClause != "") {
                $whereClause .= " AND ";
            } else {
                $whereClause = "WHERE ";
            }
            $whereClause .= ($found == "1") ? "slip_found = 1 " : "(slip_found != 1 OR slip_found IS NULL) ";
        }
        $query = <<<SQL
			SELECT id FROM mss_slip {$whereClause} ORDER BY id ASC
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute($params);
        $ids = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $ids[] = $row["id"];
        }
        return $ids;
    }
    
    public static function getAllDateRanges($showUnused = true) {
        if ($showUnused) {
            $query = <<<SQL
				SELECT DISTINCT id, value, sortOrder FROM mss_date_range ORDER BY sortOrder ASC
SQL;
        } else {				//return only the date ranges that are in use (e.g. for browsing)
            $query = <<<SQL
				SELECT DISTINCT id, value, sortOrder FROM mss_date_range mdr INNER JOIN mss_slip_date_range msdr ON mdr.id = msdr.date_range_id ORDER BY sortOrder ASC
SQL;
            }
            $dbh = DB::getDatabaseHandle(DB_NAME);
            
            $sth = $dbh->prepare($query);
            $sth->execute();
            $dateRanges = array();
            while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
                $dateRanges[$row["id"]] = $row["value"];
            }
            return $dateRanges;
    }
    
    public static function getAllAuthors() {
        $query = <<<SQL
			SELECT DISTINCT author FROM mss_slip ORDER BY author ASC
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        
        $sth = $dbh->prepare($query);
        $sth->execute();
        $authors = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $authors[$row["author"]] = $row["author"];
        }
        
        return $authors;
    }
    
    public static function getAllTitles() {
        $query = <<<SQL
			SELECT DISTINCT title FROM mss_slip ORDER BY title ASC
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        
        $sth = $dbh->prepare($query);
        $sth->execute();
        $titles = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $titles[$row["title"]] = $row["title"];
        }
        
        return $titles;
    }
    
    public static function getAllFolders() {
        $query = <<<SQL
			SELECT name, sortOrder FROM mss_folder ORDER BY sortOrder ASC
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        
        $sth = $dbh->prepare($query);
        $sth->execute();
        $folders = array();
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $folders[$row["sortOrder"]] = $row["name"];
        }
        
        return $folders;
    }
    
    public static function getDateRangeName($dateRangeId) {
        $query = <<<SQL
			SELECT value FROM mss_date_range WHERE id = :id
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        
        $sth = $dbh->prepare($query);
        $sth->bindParam(":id", $dateRangeId);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result["value"];
    }
    
    public static function getSlipsDateRangeNameList($slipId) {
        $slipDateRanges = MssSlips::getSlipDateRanges($slipId);
        foreach ($slipDateRanges as $dateRangeId) {
            $dateRanges[] = self::getDateRangeName($dateRangeId);
        }
        $dateRangeList = implode(",", $dateRanges);
        return $dateRangeList;
    }
    
    private static function createMetadataCacheString($delim, $params) {
        return implode(" {$delim} ", $params);
    }
}