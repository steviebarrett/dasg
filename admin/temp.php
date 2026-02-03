<?php

ini_set("display_errors", 1);

session_start();

require_once '../includes/include.php';

$titleText = array("en"=>"Admin", "gd"=>"Admin");

$pageTitle = $titleText[$lang];
$pageSlug = "temp";

$cqpPage = true;
$javascriptBlock = <<<HTML
	<script src="../ckeditor/ckeditor.js"></script>
	<script>
		$('#textMetadata').validate();
	</script>
HTML;

require_once '../includes/htmlHeader.php';

if (Functions::showLoginForm() == true) {
    
    $user = Users::getUser($_SESSION["user"]);
    
    //check for blog admin status
    if ($user->getIsBlogAdmin() != 1) {
        Functions::writeError("You are not authorised to view this page");
    }
    
    $query = <<<SQL
        SELECT DISTINCT geoMacro FROM corpus_text
SQL;
    
    $dbh = DB::getDatabaseHandle(DB2_NAME);
    
    $sth = $dbh->prepare($query);
    $sth->execute();
    $resultG = $sth->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($resultG as $result) {
    
        $query = <<<SQL
        SELECT DISTINCT dateMacro FROM corpus_text WHERE dateMacro NOT IN (SELECT DISTINCT dateMacro from corpus_text WHERE geoMacro = :geoMacro)
SQL;
        
        $dbh = DB::getDatabaseHandle(DB2_NAME);
        
        $sth = $dbh->prepare($query);
        $sth->bindParam(":geoMacro", $result["geoMacro"]);
        $sth->execute();
        $result2 = $sth->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($result2 as $r) {
            echo "{$result["geoMacro"]} - {$r["dateMacro"]}<br/>";
        }
    }
    

    
			
}

require_once '../includes/htmlFooter.php';