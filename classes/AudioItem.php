<?php

class AudioItem {
	
	private $isVideo, $title, $original_tape_name, $completed, $physical_location, $digital_location, $digital_reference,
	    $transcribed, $transcription, $no_of_audio_files_supplied, $no_of_images, $fieldworker, $contributors, $transcription_by, $date, $year,
		$location, $tape_brand, $reel_size, $speed, $duration, $indexed, $contents_information, $detailed_contents,
		$notes, $capture_station, $deck, $digitiser_initials, $sadie_project_number,
		$keywords, $languages, $additionalInfo, $is_new, $last_updated;
	
	private $langs = array("en"=>"English", "gd"=>"Gaelic", "ga"=>"Irish", "gv"=>"Manx");
	
	public function __construct($reference) {
		$this->setDigitalReference($reference);	
	}
	
	public function setIsVideo($isVideo) {
	    $this->isVideo = $isVideo;
	}
	
	public function getIsVideo() {
	    return $this->isVideo;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getOriginalTapeName() {
		return $this->original_tape_name;
	}
	
	public function setOriginalTapeName($tapeName) {
		$this->original_tape_name = $tapeName;
	}
	
	public function getCompleted() {
		return $this->completed;
	}
	
	//expects an integer of 1 or 0
	public function setCompleted($completed) {
		$this->completed = $completed;
	}
	
	public function getPhysicalLocation() {
		return $this->physical_location;
	}
	
	public function setPhysicalLocation($location) {
		$this->physical_location = $location;
	}
	
	public function getDigitalLocation() {
		return $this->digital_location;
	}
	
	public function setDigitalLocation($location) {
		$this->digital_location = $location;
	}
	
	public function getDigitalReference() {
		return $this->digital_reference;
	}
	
	public function setDigitalReference($reference) {
		$this->digital_reference = $reference;
	}
	
	public function getTranscribed() {
		return $this->transcribed;
	}
	
	public function setTranscribed($transcribed) {
		$this->transcribed = $transcribed;
	}
	
	public function getTranscription() {
		return $this->transcription;
	}
	
	public function setTranscription($transcription) {
		$this->transcription = $transcription;
	}
	
	private function _getAllSearchIndices($searchterm) {
		$offset = 0;
		$indices = array();
		//check for hit at first position
/*		if (mb_stripos($this->getTranscription(), $searchterm, $offset) == 0) {
		    $indices[] = 0;
		    $offset = 1;
		}
*/		
/*		while ($index = mb_stripos($this->getTranscription(), $searchterm, $offset))  {
			$indices[] = $index;
			$offset = $index+1;
		}
*/
	    $searchterm = Functions::getAccentInsensitive($searchterm, false);

	    while(preg_match("/{$searchterm}/i", $this->getTranscription(), $matches, PREG_OFFSET_CAPTURE, $offset)) {
		    $indices[] = $matches[0][1];
		    $offset = $matches[0][1] + 2; //offet increased by 2 (instead of 1) to fix multibyte characters doubling search results 
	    }
		
	    return $indices;
	}
	
	public function getSearchContextIndices($searchterm) {
		$indices = $this->_getAllSearchIndices($searchterm);
		return $indices;
	}
	
	public function getSearchContext($index, $scope, $searchterm) {
		//find the first space 
		$start = mb_strpos($this->getTranscription(), " ", $index-$scope);
		//find the last space
		$end = mb_strpos($this->getTranscription(), " ", $index+($scope*1.5));
		if (!$end) {  //too close to the end of the transcription
		    $end = mb_strlen($this->getTranscription());
		}
		$substr = mb_substr($this->getTranscription(), $start, $end-$start);
	    $searchterm = Functions::getAccentInsensitive($searchterm. false);
		return preg_replace("/{$searchterm}/iu", '<span class="highlight">$0</span>', $substr);
	}
	
	public function getNoOfAudioFilesSupplied() {
		return $this->no_of_audio_files_supplied;
	}
	
	public function setNoOfAudioFilesSupplied($number) {
		$this->no_of_audio_files_supplied = $number;
	}
	
	public function getNoOfImages() {
		return $this->no_of_images;
	}
	
	public function setNoOfImages($number) {
		$this->no_of_images = $number;
	}
	
	public function getFieldworker() {
		return $this->fieldworker;
	}
	
	public function setFieldworker($fieldworker) {
		$this->fieldworker = $fieldworker;
	}
	
	public function getContributors() {
		return $this->contributors;
	}
	
	public function setContributors($contributors) {
		$this->contributors = $contributors;
	}
	
	public function getTranscriptionBy() {
	    return $this->transcription_by;
	}
	
	public function setTranscriptionBy($name) {
	    $this->transcription_by = $name;
	}
	
	public function getDate() {
		return $this->date;
	}
	
	public function setDate($date) {
		$this->date = $date;
	}
	
	public function getYear() {
		return $this->year;
	}
	
	public function setYear($year) {
		$this->year = $year ;
	}
	
	public function getLocation() {
		return $this->location;
	}
	
	public function setLocation($location) {
		$this->location = $location;
	}
	
	public function getTapeBrand() {
		return $this->tape_brand;
	}
	
	public function setTapeBrand($brand) {
		$this->tape_brand = $brand;
	}
	
	public function getReelSize() {
		return $this->reel_size;
	}
	
	public function setReelSize($size) {
		$this->reel_size = $size;
	}
	
	public function getSpeed() {
		return $this->speed;
	}
	
	public function setSpeed($speed) {
		$this->speed = $speed;
	}
	
	public function getDuration() {
		return $this->duration;
	}
	
	public function setDuration($duration) {
		$this->duration = $duration;
	}
	
	public function getIndexed() {
		return $this->indexed;
	}
	
	public function setIndexed($indexed) {
		$this->indexed = $indexed;
	}
	
	public function getContentsInformation() {
		return $this->contents_information;
	}
	
	public function setContentsInformation($contents) {
		$this->contents_information = $contents;
	}
	
	public function getDetailedContents() {
		return $this->detailed_contents;
	}
	
	public function setDetailedContents($contents) {
		$this->detailed_contents = $contents;
	}
	
	public function getNotes() {
		return $this->notes;
	}
	
	public function setNotes($notes) {
		$this->notes = $notes;
	}
	
	public function getCaptureStation() {
		return $this->capture_station;
	}
	
	public function setCaptureStation($station) {
		$this->capture_station = $station;
	}
	
	public function getDeck() {
		return $this->deck;
	}
	
	public function setDeck($deck) {
		$this->deck = $deck;
	}

	public function getDigitiserInitials() {
		return $this->digitiser_initials;
	}
	
	public function setDigitiserInitials($initials) {
		$this->digitiser_initials = $initials;
	}
	
	public function getSadieProjectNumber() {
		return $this->sadie_project_number;
	}
	
	public function setSadieProjectNumber($number) {
		$this->sadie_project_number = $number;
	}
	
	/*
	 * Keywords are stored as a single string in form: "key1|key2|key3"
	 */
	public function getKeywords() {
		return $this->keywords;
	}
	
	public function getKeywordsArray() {
		return explode('|', $this->getKeywords());
	}
	
	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	
	/*
	 * Languages are stored as a single string in form: "lang1|lang2|lang3"
	 */
	public function getLanguages() {
		return $this->languages;
	}
	
	/*
	 * Returns languages in form ("code"=>"language name")
	 */
	public function getLanguagesArray() {
		$languagesArray = array();
		foreach (explode('|', $this->getLanguages()) as $langCode) {
			$languagesArray[$langCode] = $this->langs[$langCode];
		}
		return $languagesArray;
	}
	
	public function setLanguages($languages) {
		$this->languages = $languages;
	}
	
	public function getLastUpdated() {
		return $this->last_updated;
	}
	
	public function setAdditionalInfo($info) {
	    $this->additionalInfo = $info;
	}
	
	public function getAdditionalInfo() {
	    return $this->additionalInfo;
	}

	public function setIsNew($flag) {
	    $this->is_new = $flag;
	}
	
	public function getIsNew() {
	    return $this->is_new;
	}
	
	public function setLastUpdated($time) {
		$this->last_updated = $time;
	}
}