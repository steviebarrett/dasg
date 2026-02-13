<?php

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

class FieldworkPersons 
{
	
	public static function getPerson($id) {
		$person = new FieldworkPerson($id);
		
		$dbh = DB::getDatabaseHandle();
		
		try {
		
			$sth = $dbh->prepare("SELECT firstName, lastName, biog, imageUrl FROM fieldworkPerson WHERE id = :id;");
			$sth->execute(array(":id"=>$id));
		
			while ($row = $sth->fetch()) {
				$person->setFirstName($row["firstName"]);
				$person->setLastName($row["lastName"]);
				$person->setBiog($row["biog"]);
				$person->setImageUrl($row["imageUrl"]);
			}
		
			return $person;
		
		} catch (PDOException $e) {
			echo "The person could not be retrieved";
		}
	}	
}