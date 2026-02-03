<?php

class Manuscripts {
    
    public static function saveComment($manuscript, $section, $section_id, $comment, $user) { 
        $query = <<<SQL
			INSERT INTO manuscript_comment (
			    manuscript,
				section,
                section_id,
				comment,
				user)
			VALUES (
				:manuscript,
				:section,
                :section_id,
				:comment,
				:user)
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $saved = $sth->execute(array(
            ":manuscript" => $manuscript,
            ":section" => $section,
            ":section_id" => $section_id,
            ":comment" => $comment,
            ":user" => $user
        ));
        $id = $dbh->lastInsertId();
        return array("saved" => $saved, "id" => $id);
    }
    
    public static function deleteComment($comment_id) {
        $query = <<<SQL
			UPDATE manuscript_comment 
            SET deleted = 1 
            WHERE id = :comment_id
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $deleted = $sth->execute(array(
            ":comment_id" => $comment_id
        ));
        
        $commentsInSection = self::_getNumCommentsInSection($comment_id);
        $commentsDeleted = self::_getNumCommentsDeleted($comment_id);
        return array("deleted"=>"deleted", "empty" => $commentsDeleted == $commentsInSection);
    }
    
    /*
     * Get the ID info (id, manuscript, section, section_id) for a comment
     * Returns an array
     */
     public static function getCommentIdInfo($comment_id) {
        $query = <<<SQL
			SELECT id, manuscript, section, section_id
            FROM manuscript_comment 
            WHERE id = :comment_id
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute(array(
            ":comment_id" => $comment_id
        ));
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }
    
    /*
     * Checks to get count of comments in a given section that are deleted 
     */
    private static function _getNumCommentsDeleted($comment_id) {
        $commentInfo = self::getCommentIdInfo($comment_id);
        $query = <<<SQL
			SELECT COUNT(deleted) as numDeleted 
            FROM `manuscript_comment` 
            WHERE `manuscript` = :manuscript AND `section` = :section AND `section_id` = :section_id AND deleted = 1;
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute(array(
            ":manuscript" => $commentInfo["manuscript"],
            ":section" => $commentInfo["section"],
            ":section_id" => $commentInfo["section_id"]
        ));
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result["numDeleted"];
    }
    
    /*
     * Checks to get count of comments in a given section
     */
    private static function _getNumCommentsInSection($comment_id) {
        $commentInfo = self::getCommentIdInfo($comment_id);
        $query = <<<SQL
			SELECT COUNT(id) as num
            FROM `manuscript_comment`
            WHERE `manuscript` = :manuscript AND `section` = :section AND `section_id` = :section_id;
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute(array(
            ":manuscript" => $commentInfo["manuscript"],
            ":section" => $commentInfo["section"],
            ":section_id" => $commentInfo["section_id"]
        ));
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result["num"];
    }
    
    
    public static function getCommentsById($manuscript, $section, $section_id) {
        $query = <<<SQL
            SELECT 
                id,
                comment,
                user,
                deleted,
                last_updated
            FROM
                manuscript_comment
            WHERE
                manuscript = :manuscript AND
                section = :section AND
                section_id = :section_id
            ORDER BY 
                last_updated DESC
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute(array(
            ":manuscript" => $manuscript,
            ":section" => $section,
            ":section_id" => $section_id
        ));
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $commentInfo = array();
        foreach ($result as $fieldname => $value) {
            $commentInfo[][$fieldname] = $value;
        }
        return $commentInfo;
    }
    
    /*
     * Function used to find populated comment sections 
     */
    public static function getCommentSectionsByManuscriptId($msId) {
        $query = <<<SQL
            SELECT
                section,
                section_id,
                deleted
            FROM
                manuscript_comment
            WHERE
                manuscript = :manuscript 
            ORDER BY
                last_updated DESC
SQL;
        $dbh = DB::getDatabaseHandle(DB_NAME);
        $sth = $dbh->prepare($query);
        $sth->execute(array(":manuscript" => $msId));
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $sectionInfo = array();
        foreach ($result as $fieldname => $value) {
            $sectionInfo[][$fieldname] = $value;
        }
        return $sectionInfo;
    }
    
    public static function getGlyph($id) {
        $xmlId = $id;
        $filepath = ROOT . "/mss/Transcribing/corpus.xml";
        $xml = simplexml_load_file($filepath);
        $xml->registerXPathNamespace('tei', 'http://www.tei-c.org/ns/1.0');
        $nodes = $xml->xpath("/tei:teiCorpus/tei:teiHeader/tei:encodingDesc/tei:charDecl/tei:glyph[@xml:id='{$xmlId}']");
        $node = $nodes[0];
        //$xmlId = (string)$node->attributes($ns="xml", true)[0];     //the xml:id
        $corresp = (string)$node["corresp"];                  //the corresp
        $glyphName = (string)$node->glyphName;
        $note = $node->note->asXML();
        $glyph = array("id" => $xmlId, "corresp" => $corresp, "name" => $glyphName, "note" => $note);
        return $glyph;   
    }

    public static function getDwelly($edil) {
        $filepath = ROOT . "/mss/Transcribing/hwData.xml";
        $xml = simplexml_load_file($filepath);
        $xml->registerXPathNamespace('tei', 'http://www.tei-c.org/ns/1.0');
        $nodes = $xml->xpath("/tei:TEI/tei:text/tei:body/tei:entryFree[@corresp='{$edil}']/tei:w");
        $node = $nodes[0];
        $lemmaDW = (string)$node["lemmaDW"];
        $lemmaRefDW = (string)$node["lemmaRefDW"];
        $dwelly = array("hw" => $lemmaDW, "url" => $lemmaRefDW);
        return $dwelly;
    }

}
