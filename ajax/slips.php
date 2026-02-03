<?php

require_once '../includes/include.php';

switch ($_GET["action"]) {
    
    case "getSlips":
        $slips = MssSlips::getDropdownMetadata($_GET["folder"], $_GET["showFound"]);
        echo json_encode($slips);
        break;
    case "getCat":
        switch ($_GET["cat"]) {
            case "dateRange":
                $dateRanges = MssSlips::getAllDateRanges(false);	//get only the date ranges in use
                echo json_encode($dateRanges);
                break;
            case "author":
                $authors = MssSlips::getAllAuthors();
                echo json_encode($authors);
                break;
            case "title":
                $titles = MssSlips::getAllTitles();
                echo json_encode($titles);
                break;
        }
    default:
        break;
}
