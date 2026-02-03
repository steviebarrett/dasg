<?php


namespace models;


class record
{
	private $_ai;
	private $_db; //an instance of models\database
	private $_props = array();  //an array of properties pulled from the database
	private $_transcription;
	private static $_gaelicFieldMap = array(
		"ai" => "Àireamh-aithneachaidh",
		"title" => "Tiotal",
		"alternative_title" => "Tiotal eile",
		"air" => "Fonn",
		"first_line_chorus" => "A’ chiad sreath (séist)",
		"first_line_verse" => "A’ chiad sreath (rann)",
		"classifications" => "Seòrsachan",
		"subjects" => "Cuspairean",
		"structure" => "Structar",
		"place_of_origin" => "Tùs-àite",
		"composer_first_name" => "Ainm a’ bhàird",
		"composer_last_name" => "Cinneadh a’ bhàird",
		"composer_patronymic" => "Sloinneadh / ainmean eile a' bhàird",
		"composer_dates" => "Bliadhnachan a’ bhàird",
		"composer_gender" => "Gné a' bhàird",
		"community" => "Coimhearsnachd",
		"county" => "Siorramachd",
		"era_of_poetry" => "Linn na bàrdachd",
		"original_format" => "Cruth tùsail",
		"singer" => "Seinneadair",
		"singer_location" => "Àite an t-seinneadair",
		"date_recorded" => "Ceann-latha clàraidh",
		"collector" => "Neach-cruinneachaidh",
		"collection_title" => "Tiotal a’ chruinneachaidh",
		"collection_location" => "Àite a' chruinneachaidh",
		"collection_number" => "Àireamh-bhratha",
		"publication_title" => "Tiotal an fhoillseachain",
		"editor" => "Neach-deasachaidh",
		"publisher" => "Foillsichear",
		"publication_date" => "Ceann-là foillseachaidh",
		"page_number" => "Àireamh na duilleige",
		"online_access" => "Air loidhne",
		"notes_1" => "Nòtaichean 1",
		"notes_2" => "Nòtaichean 2",
		"notes_3" => "Nòtaichean 3",
		"notes_4" => "Nòtaichean 4"
	);

	public function __construct($ai) {
		$this->_ai = $ai;
		if (!$this->_db) {
			$this->_db = new database();
		}
	}

	/**
	 * Queries the database for record properties and sets them appropriately
	 * @return $this
	 */
		public function load() {
			if ($this->_ai == -1) {    //ai flag for creating a new record
				$this->_loadTemplate();
				return $this;
			}
			$sql = "SELECT * FROM record WHERE ai = :ai";
			$props = $this->_db->fetch($sql, array(":ai"=>$this->getAI()));
			foreach ($props[0] as $propName => $value) {
				$this->setPropValue($propName, $value);
			}
			$this->getTranscriptionLink();    //refactor - this should not be here SB
			return $this;
		}

		public function getTranscriptionLink() {
			//check if there is a transcription for this record
			$sql = <<<SQL
				SELECT text FROM transcription WHERE record_ai = :ai
SQL;
			$result = $this->_db->fetch($sql, array(":ai" => $this->getAI()));
			if ($result) {
				$this->_transcription = $result[0]["text"];
				return <<<HTML
					<a target="_blank" href="transcription.php?ai={$this->getAI()}">link</a>
HTML;
			}
		}

		public function getTranscription() {
			return $this->_transcription;
		}

	/**
	 * Initialises the required properties for a new blank record
	 * @return $this
	 */
		private function _loadTemplate() {
			$model = new records();
			$fields = $model->getAllFieldNames();
			foreach ($fields as $field) {
				$this->setPropValue($field, "");  //initialise all the properties
			}
			return $this;
		}

		public function save($data) {
			$fields = array_keys($data);
			$fieldList = implode(', ', $fields);
			$values = $placeholders = array();
			foreach ($data as $fieldname => $value) {
				//test for multiples (array): e.g. classifications
				if (is_array($value)) {
					$value = implode(" , ", $value);
				}
				$placeholders[] = '?';  //set a PDO placeholder for each value
				$values[] = $value;
			}
			$placeholderList = implode(', ', $placeholders);
			$sql = "REPLACE INTO record ({$fieldList}) VALUES({$placeholderList})";
			$this->_db->exec($sql, $values);
			$this->_updateTracking($data["ai"]);
		}

	private function _updateTracking($ai) {
		$sql = <<<SQL
			REPLACE INTO recordTracking VALUES(?, now())	
SQL;
		$this->_db->exec($sql, array($ai));
	}

		public function getAI() {
			return $this->_ai;
		}

		public function getPropValue($propName) {
			return $this->_props[$propName];
		}

		public function getAllProps() {
			return $this->_props;
		}

		public function setPropValue($propName, $value) {
			$this->_props[$propName] = $value;
		}

		public static function getGaelicFieldMap() {
			return self::$_gaelicFieldMap;
		}
}