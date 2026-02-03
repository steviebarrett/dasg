<?php

require_once '../includes/include.php';

switch ($_GET["action"]) {
    
    case "saveComment":
        $manuscript = $_GET["docid"];
        $section = $_GET["s"];
        $sectionId = $_GET["sid"];
        $comment = $_GET["comment"];
        $user = $_GET["user"];
        $saved = Manuscripts::saveComment($manuscript, $section, $sectionId, $comment, $user);
        echo json_encode($saved);
        break;
    case "deleteComment":
        $commentId = $_GET["cid"];
        $deleted = Manuscripts::deleteComment($commentId);
        echo json_encode($deleted);
        break;
    case "getComment":
        $manuscript = $_GET["docid"];
        $section = $_GET["s"];
        $sectionId = $_GET["sid"];
        $comments = Manuscripts::getCommentsById($manuscript, $section, $sectionId);
        echo json_encode($comments);
        break;
    case "getCommentInfo":
    	$commentId = $_GET["cid"];
    	$commentInfo = Manuscripts::getCommentIdInfo($commentId);
    	echo json_encode($commentInfo);
    	break;
    case "getPopulatedSections":
        $manuscript = $_GET["docid"];
        $sections = Manuscripts::getCommentSectionsByManuscriptId($manuscript);
        echo json_encode($sections);
        break;
    case "getGlyph":
        $xmlId = $_GET["xmlId"];
        $glyph = Manuscripts::getGlyph($xmlId);
        echo json_encode($glyph);
        break;
    case "getDwelly":
        $edil = $_GET["edil"];
        $dwelly = Manuscripts::getDwelly($edil);
        echo json_encode($dwelly);
        break; 
   default:
        break;
}
