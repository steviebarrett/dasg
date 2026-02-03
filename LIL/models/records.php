<?php


namespace models;


class records
{
	private $_db; //an instance of models\database
	private $_searchQueryFields, $_searchFields, $_browseFields;

	public function __construct() {
		$this->_db = isset($this->_db) ? $this->_db : new database();
		$this->_load();
	}

	private function _load() {
		$sql = <<<SQL
			SELECT * FROM searchSettings
SQL;
		$results = $this->_db->fetch($sql);
		$this->_setSearchQueryFields($results[0]["searchQueryFields"]);
		$this->_setSearchFields($results[0]["searchFields"]);
		$this->_setBrowseFields($results[0]["browseFields"]);
	}

	public function save() {
		$sql = <<<SQL
			UPDATE searchSettings SET searchQueryFields = :searchQueryFields, 
				searchFields = :searchFields, browseFields = :browseFields
SQL;
		$searchQueryFieldsString = implode('|', $this->getSearchQueryFields());
		$searchFieldsString = implode('|', $this->getSearchFields());
		$browseFieldsString = implode('|', $this->getBrowseFields());
		$this->_db->exec($sql, array(
				":searchQueryFields"=>trim($searchQueryFieldsString, '|'),
				":searchFields"=>trim($searchFieldsString, '|'),
				":browseFields"=>trim($browseFieldsString, '|'))
		);
	}

	public function addSearchQueryField($field) {
		array_push($this->_searchQueryFields, $field);
	}

	public function removeSearchQueryField($field) {
		$key = array_search($field, $this->_searchQueryFields);
		unset($this->_searchQueryFields[$key]);
	}

	public function addSearchField($field) {
		array_push($this->_searchFields, $field);
	}

	public function removeSearchField($field) {
		$key = array_search($field, $this->_searchFields);
		unset($this->_searchFields[$key]);
	}

	public function addBrowseField($field) {
		array_push($this->_browseFields, $field);
	}

	public function removeBrowseField($field) {
		$key = array_search($field, $this->_browseFields);
		unset($this->_browseFields[$key]);
	}

	//Getters

	public function getSearchQueryFields() {
		return $this->_searchQueryFields;
	}

	public function getFriendlySearchQueryFields() {
		foreach ($this->_searchQueryFields as $field) {
			$friendlyFields[] = functions::getFriendlyName($field);
		}
		return $friendlyFields;
	}

	public function getSearchFields() {
		return $this->_searchFields;
	}

	public function getBrowseFields() {
		return $this->_browseFields;
	}

	//Setters

	private function _setSearchQueryFields($fields) {
		$this->_searchQueryFields = explode('|', $fields);
	}

	private function _setSearchFields($fields) {
		$this->_searchFields = explode('|', $fields);
		if (!in_array("ai", $this->_searchFields)) {  //ensure that the "ai: field is always displayed
			array_push($this->_searchFields, "ai");
		}
	}

	private function _setBrowseFields($fields) {
		$this->_browseFields = explode('|', $fields);
		if (!in_array("ai", $this->_browseFields)) {  //ensure that the "ai: field is always displayed
			array_push($this->_browseFields, "ai");
		}
	}

	public function getAllRecords() {
		$sql = <<<SQL
			SELECT * FROM record ORDER BY ai ASC
SQL;
		$results = $this->_db->fetch($sql);
		return $results;
	}

	/**
	 * Queries the records for info on specific fields
	 * @return array of results
	 */
	public function getBrowseResults($offset=0, $limit=10, $sort, $order, $searchString = "", $getText = true) {
		$whereClause = "";
		$limit = (int)$limit;
		$offset = (int)$offset;
		$sort = $sort ? $sort: "ai";
		if (!in_array($sort, $this->getAllFieldNames())) {
			return false;   //possibly an attack
		};
		$order = $order ? $order : "asc";
		if ($order != "asc" AND $order != "desc") {
			return false;     //error - could be an injection attack
		}
		$fieldList = ($getText)
			? implode(", ", $this->getBrowseFields()) . ', text, original_format, online_access'
			: implode(", ", $this->getAllFieldNames()) ;   	//add in other fields for "API" use

		//handle the 'filter' search in the bootstrap-table
		if (mb_strlen($searchString) > 1) {     //there is a search to run
			$whereClause = "WHERE ";
			$this->_db->exec("SET @search = :search", array(":search" => "%{$searchString}%"));  //set a MySQL variable for the searchterm
			foreach ($this->getBrowseFields() as $field) {
				$whereClause .= " {$field} LIKE @search OR";
			}
			$whereClause = trim($whereClause, ' OR');
		} //end handle 'filter' search
		$sql = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS {$fieldList}  FROM record 
			    LEFT JOIN transcription ON ai = record_ai
			{$whereClause}
			ORDER BY {$sort} {$order} 
			LIMIT {$limit} OFFSET {$offset} 
SQL;


		$results = $this->_db->fetch($sql);
		$hits = $this->_db->fetch("SELECT FOUND_ROWS() as hits;");
		return array("total"=>(int)$hits[0]["hits"], "totalNotFiltered"=>count($results), "rows"=>$results);

	}

	/**
	 * Simple search function - searches across all search fields stored in admin database
	 * @param string $searchString
	 * @return array of results
	 */
/*	public function getSearchResults($searchString) {
		$whereList = array();
		foreach ($this->getSearchQueryFields() as $field) {
			$whereList[] = "{$field} LIKE :searchString";
		}
		$whereClause = "WHERE " . implode(' OR ', $whereList);
		$selectFields = implode(',', $this->getSearchFields());
		$sql = <<<SQL
			SELECT {$selectFields} FROM record
				{$whereClause}
SQL;
		$results = $this->_db->fetch($sql, array(":searchString" => "%$searchString%"));
		return $results;
	}
*/

	/**
	 * Advanced search function - searches chosen search fields using boolean operators
	 * @param array $searchStrings
	 * @param array $searchFields
	 * @param array $booleans
	 * @params array $params - optional search parameters
	 * @return array of results
	 */
	public function getAdvancedSearchResults(
        $searchStrings, $searchFields, $booleans, $params = array(), $search, $offset=0, $limit=10, $sort="ai", $order="ASC") {
		$limit = (int)$limit;
		$offset = (int)$offset;
		$sort = $sort ? $sort: "ai";
		if (!in_array($sort, $this->getAllFieldNames())) {
			return false;   //possibly an attack
		}
		$order = $order ? $order : "asc";
		if ($order != "asc" AND $order != "desc") {
			return false;     //error - could be an injection attack
		}
		$orCount = 0; //tracks the number of OR clauses in the query
		$whereClause = "(";
		foreach ($searchStrings as $index => $searchString) {
			//check for hyphen in searchString and make optional
			$searchString = str_replace("-", "-?", $searchString);
			$whereList = [];
			if (!empty($params)) {    //params selected so process the search string
				$searchString = $this->_processSearchStringOptions($searchString, $params);
			}
			$placeholders[":searchString". $index] = $searchString; //for the PDO query
			if ($searchFields[$index] == "all") {   //all fields to be searched
				foreach ($this->getSearchQueryFields() as $field) {
					$whereList[] = "{$field} REGEXP :searchString{$index}";
				}
				$whereClause .= " {$booleans[$index]} (" . implode(' OR ', $whereList). ')';
			} else {    //user selected field to be searched
				$parenthesis = ($booleans[$index+1] == 'OR') ? "(" : "";   //nest the ORs for correct precedence
				$whereClause .= <<<SQL
					{$booleans[$index]} {$parenthesis} {$searchFields[$index]} REGEXP :searchString{$index}
SQL;
				if ($booleans[$index] == "OR") {
					$whereClause .= ")";      //nest the ORs for correct precedence
				}
			}
		}
		$whereClause .= ") ";
        if ($params[4]) {   // transcriptions ONLY
            $whereClause .= " AND (transcription.text IS NOT NULL) ";
        }
        if ($params[5]) {   // sound recordings ONLY
            $whereClause .= " AND (original_format = 'Sound Recording' AND SUBSTRING(online_access, 1, 4) = 'http') ";
        }
		//handle the 'filter' search in the bootstrap-table
		if (mb_strlen($search) > 1) {     //there is a search to run
			$this->_db->exec("SET @search = :search", array(":search" => "%{$search}%"));  //set a MySQL variable for the searchterm
			$whereClause .= " AND (";
			foreach ($this->_searchFields as $field) {
				$whereClause .= " {$field} LIKE @search OR";
			}
			$whereClause = trim($whereClause, ' OR');
			$whereClause .= ") ";
		} //end handle 'filter' search
		$selectFields = implode(',', $this->getSearchFields()); //the fields to be shown in the results table
		$sql = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS {$selectFields}, text, original_format, online_access FROM record
				LEFT JOIN transcription ON ai = record_ai
				WHERE {$whereClause}
				ORDER BY {$sort} {$order}
				LIMIT {$limit} OFFSET {$offset}
SQL;
		$results = $this->_db->fetch($sql, $placeholders);
		$hits = $this->_db->fetch("SELECT FOUND_ROWS() as hits;");
		return array("params"=>$params, "total"=>(int)$hits[0]["hits"], "totalNotFiltered"=>count($results), "rows"=>$results, "ss"=>$searchStrings);
	}

	private function _processSearchStringOptions($searchString, $params) {
		if ($params[2]) {
			$searchString = "[[:<:]]" . $searchString . "[[:>:]]";  //add word boundaries for exact string
		}
		return $searchString;
	}

	/**
	 * Queries the database for all the field names in the record table
	 * @return array of results
	 */
	public function getAllFieldNames() {
		$sql = <<<SQL
			SHOW columns FROM record
SQL;
		$results = $this->_db->fetch($sql);
		$fields = array();
		foreach ($results as $result) {
			$fields[] = $result["Field"];
		}
		return $fields;
	}

	public static function getRecordExists($ai) {
		$db = new database();
		$sql = <<<SQL
			SELECT ai FROM record WHERE ai = :ai
SQL;
		$result = $db->fetch($sql, array(":ai"=>$ai));
		return !empty($result);
	}

	public static function getControlledVocabularies($field) {
		$fields = array(
			"classifications" => array("Ballad", "Bawdy", "Clapping","Complaint","Dialogue","Drinking","Elegy","Exile",
				"Flyting","Historical","Homeland","Humorous","Instructive","Lament","Local events and characters","Love","Lullaby",
				"Macaronic","Milling","Nature","Pibroch","Political","Port-a-beul","Praise","Rann / Duan","Religious","Sailing",
				"Satire","Spiritual","Supernatural","Work"),
			"composer_gender" => array("Male", "Female", "Other"),
			"structure" => array("One line verse", "One line verse / three line chorus", "One line verse / split chorus",
				"Two line verse", "Two line verse / two line chorus",
				"Two line verse / three line chorus", "Two line verse / four line chorus", "Two line verse / woven",
				"Three line verse", "Three line verse / two line chorus", "Three line verse / three line chorus",
				"Three line verse / four line chorus", "Three line verse / woven", "Four line verse", "Four line verse / two line chorus",
				"Four line verse / three line chorus", "Four line verse / four line chorus", "Four line verse / five line chorus",
				"Five line verse", "Six line verse", "Six line verse / two line chorus", "Six line verse / three line chorus",
				"Six line verse / four line chorus", "Seven line verse", "Eight line verse", "Eight line verse / four line chorus",
				"Eight line verse / eight line chorus", "Nine line verse", "Ten line verse", "Twelve line verse",
				"Sixteen line verse", "Woven", "Split chorus", "Woven / split chorus", "Irregular"),
			"place_of_origin" => array("Scotland", "Nova Scotia", "Prince Edward Island", "Ontario", "United States", "Other"),
			"original_format" => array("Sound recording", "Manuscript", "Publication", "Newspaper Clipping")
		);
		return $fields[$field];
	}

	public static function delete($ai) {
		$sql = <<<SQL
			DELETE FROM record WHERE ai = ?
SQL;
		$db = new database();
		$db->exec($sql, array($ai));
	}
}