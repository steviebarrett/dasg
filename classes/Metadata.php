<?php

class Metadata
{
	public static function getMetadata($textId, $all=true) {
		if ($all)
			$query = "SELECT
							title,
							author,
							editor,
							reference_author,
							reference_editor,
							reference_volume,
							date_of_edition,
							dateMacro AS date_of_language,
							publisher,
							place_published,
							volume,
							location,
							link_label,
							link,
							download_file,
							geoMacro AS geographical_origins,
							medium,
							genre,
							alternative_author_name,
							manuscript_or_edition,
							size_and_condition,
							short_title,
							reference_details,
							number_of_pages,
							gaelic_text_by,
							illustrator,
							social_context,
							contents,
							sources,
							language,
							orthography,
							edition,
							other_sources,
							further_reading,
							credits,
							numWords
 					FROM corpus_text WHERE reference_number = :textId";
			else
				$query = "SELECT
							short_title,
							author,
							dateMacro AS date_of_language,
							geoMacro AS geographical_origins,
							medium,
							genre
					FROM corpus_text WHERE reference_number = :textId";
				
				$dbh = DB::getDatabaseHandle(FACLAIR_DB_NAME, FACLAIR_DB_USER, FACLAIR_DB_PASSWORD);
				
				$sth = $dbh->prepare($query);
				$sth->bindParam(":textId", $textId);
				$sth->execute();
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				//make the field names user friendly
				foreach ($result as $fieldname => $value) {
					$metadata[$fieldname]["fieldname"] = Functions::getFriendlyFieldName($fieldname, '_');
					$metadata[$fieldname]["value"] = $value;
				}
				
				return $metadata;
	}
	
	public static function getAllMetadata($textId) {
		$query = "SELECT * FROM corpus_text WHERE reference_number = :textId";
		
		$dbh = DB::getDatabaseHandle(FACLAIR_DB_NAME, FACLAIR_DB_USER, FACLAIR_DB_PASSWORD);
		$sth = $dbh->prepare($query);
		$sth->bindParam(":textId", $textId);
		$sth->execute();
		$result = $sth->fetch(PDO::FETCH_ASSOC);
		$metadata = array();
		foreach ($result as $fieldname => $value) {
			$metadata[$fieldname] = nl2br($value);
		}
		return $metadata;
	}
	
	public static function saveMetadata($data) {
		//replace html break tags with newlines
		foreach ($data as $fieldname => $value) {
			$data[$fieldname] = str_replace("<br />","", $value);
		}
		$query = <<<SQL
			REPLACE INTO corpus_text (
				reference_number,
				title,
				author,
				editor,
			    reference_author,
			    reference_editor,
			    reference_volume,
				date_of_edition,
				date_of_language,
				date_of_language_ed,
                dateMacro,
                date_of_language_notes,
				publisher,
				place_published,
				volume,
				location,
				geographical_origins,
				geographical_origins_ed,
                geographical_origins_notes,
				register,
				register_ed,
				medium,
				genre,
				reference_style,
				rating,
				alternative_author_name,
				manuscript_or_edition,
				size_and_condition,
				short_title,
				reference_details,
				number_of_pages,
				gaelic_text_by,
				illustrator,
				social_context,
				contents,
				sources,
				language,
				orthography,
				edition,
				other_sources,
				further_reading,
				credits,
				add_to_corpus,
				filename,
				link_label,
				link,
				download_file,
                geoMacro,
                geoX,
                geoY,
                numWords)
			VALUES (
				:reference_number,
				:title,
				:author,
				:editor,
                :reference_author,
                :reference_editor,
			    :reference_volume,
				:date_of_edition,
				:date_of_language,
				:date_of_language_ed,
                :dateMacro,
                :date_of_language_notes,
				:publisher,
				:place_published,
				:volume,
				:location,
				:geographical_origins,
				:geographical_origins_ed,
                :geographical_origins_notes,
				:register,
				:register_ed,
				:medium,
				:genre,
				:reference_style,
				:rating,
				:alternative_author_name,
				:manuscript_or_edition,
				:size_and_condition,
				:short_title,
				:reference_details,
				:number_of_pages,
				:gaelic_text_by,
				:illustrator,
				:social_context,
				:contents,
				:sources,
				:language,
				:orthography,
				:edition,
				:other_sources,
				:further_reading,
				:credits,
				:add_to_corpus,
				:filename,
				:link_label,
				:link,
				:download_file,
                :geoMacro,
                :geoX,
                :geoY,
                :numWords)
SQL;
		$dbh = DB::getDatabaseHandle(FACLAIR_DB_NAME, FACLAIR_DB_USER, FACLAIR_DB_PASSWORD);
		$sth = $dbh->prepare($query);
		$sth->execute(array(
				":reference_number"=>$data["reference_number"],
				":title"=>$data["title"],
				":author"=>$data["author"],
				":editor"=>$data["editor"],
                ":reference_author"=>$data["reference_author"],
                ":reference_editor"=>$data["reference_editor"],
                ":reference_volume"=>$data["reference_volume"],
				":date_of_edition"=>$data["date_of_edition"],
				":date_of_language"=>$data["date_of_language"],
				":date_of_language_ed"=>$data["date_of_language_ed"],
		        ":dateMacro"=>$data["dateMacro"],
		        ":date_of_language_notes"=>$data["date_of_language_notes"],
				":publisher"=>$data["publisher"],
				":place_published"=>$data["place_published"],
				":volume"=>$data["volume"],
				":location"=>$data["location"],
				":geographical_origins"=>$data["geographical_origins"],
				":geographical_origins_ed"=>$data["geographical_origins_ed"],
		        ":geographical_origins_notes"=>$data["geographical_origins_notes"],
				":register"=>$data["register"],
				":register_ed"=>$data["register_ed"],
				":medium"=>$data["medium"],
				":genre"=>$data["genre"],
				":reference_style"=>$data["reference_style"],
				":rating"=>$data["rating"],
				":alternative_author_name"=>$data["alternative_author_name"],
				":manuscript_or_edition"=>$data["manuscript_or_edition"],
				":size_and_condition"=>$data["size_and_condition"],
				":short_title"=>$data["short_title"],
				":reference_details"=>$data["reference_details"],
				":number_of_pages"=>$data["number_of_pages"],
				":gaelic_text_by"=>$data["gaelic_text_by"],
				":illustrator"=>$data["illustrator"],
				":social_context"=>$data["social_context"],
				":contents"=>$data["contents"],
				":sources"=>$data["sources"],
				":language"=>$data["language"],
				":orthography"=>$data["orthography"],
				":edition"=>$data["edition"],
				":other_sources"=>$data["other_sources"],
				":further_reading"=>$data["further_reading"],
				":credits"=>$data["credits"],
				":add_to_corpus"=>$data["add_to_corpus"],
				":filename"=>$data["filename"],
				":link_label"=>$data["link_label"],
				":link"=>$data["link"],
				":download_file"=>$data["download_file"],
				":geoMacro"=>$data["geoMacro"],
		        ":geoX"=>$data["geoX"],
		        ":geoY"=>$data["geoY"],
		        ":numWords"=>(int)$data["numWords"]
		));
		
		return;
	}
	
	public static function getAllTextShortTitles() {
		$query = <<<SQL
			SELECT reference_number, short_title FROM corpus_text ORDER BY reference_number ASC
SQL;
		$dbh = DB::getDatabaseHandle(FACLAIR_DB_NAME, FACLAIR_DB_USER, FACLAIR_DB_PASSWORD);
		
		$sth = $dbh->prepare($query);
		$sth->execute();
		$texts = array();
		while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$texts[$row["reference_number"]] = $row["short_title"];
		}
		
		return $texts;
	}
}
