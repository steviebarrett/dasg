<?php

require_once '../includes/include.php';

// require a logged in user with admin privileges for all access
Functions::requireAdmin();

$dbh = DB::getDatabaseHandle();

/*
$sql = <<<SQL
    SELECT p.number as pagenum, text_id, l.number as linenum, l.id AS id, diplomatic FROM bdl_line l JOIN bdl_page p ON p.id = page_id WHERE diplomatic LIKE '%*%';

SQL;
*/
$sql = <<<SQL
    SELECT p.number as pagenum, text_id, l.number as linenum, l.id AS id, diplomatic FROM bdl_line l JOIN bdl_page p ON p.id = page_id
SQL;
$sth = $dbh->prepare($sql);
$sth->execute();
$results = $sth->fetchAll(PDO::FETCH_ASSOC);

echo <<<HTML
<html>

    <head>
        <title>BDL</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    </head>
    <body>
    
        <div class="container-fluid">
HTML;

/*
$unicodeCodePoint = 0x0305; // Unicode code point for combining macron

// Output the character corresponding to the Unicode code point
$outputCharacter = mb_chr($unicodeCodePoint, 'UTF-8');

// Display the output character
echo $outputCharacter;

die();
*/
$count = 0;

foreach ($results as $result) {
    $diplomatic = $result['diplomatic'];
    $id = $result["id"];
/*if ($id !== 100) {
    continue;
}
    $char = '';
*/

    $originalLine = '';
    $replace = null;

    for ($i = 0; $i < mb_strlen($diplomatic); $i++) {
//echo "<br>{$i} : <h3>$replace</h3>";

            if (mb_substr($diplomatic, $i, 1) == '̅') {

                if (($replace !== null &&$replace+8 == $i) || mb_substr($diplomatic, $i+2, 1) == '̅') {       //replace

                    $originalLine = $diplomatic;
                    $diplomatic = mb_substr_replace($diplomatic, '&#x303;', $i, 1);

                    $replace = $i;

    //                echo "<strong>{$i}</strong>";

                }

          //      echo $char;


                //echo " : ". dechex(mb_ord($char));
            }

    }
    if ($replace) {
        echo "<br><br>{$id}   :<strong>[{$result['pagenum']}.{$result['text_id']}.{$result['linenum']}]</strong><br>";


//suppress tags
$result["diplomatic"] = str_replace(array("<strike>", "</strike>", "<sup>", "</sup>"), "", $result["diplomatic"]);
$diplomatic = str_replace(array("<strike>", "</strike>", "<sup>", "</sup>"), "", $diplomatic);
        echo $result["diplomatic"] . "<br>" . $diplomatic ;
        $count++;
    }
}

echo "<h1>count : {$count}</h1>";



    function mb_substr_replace($string, $replacement, $start, $length = null, $encoding = null)
    {
        if (extension_loaded('mbstring') === true)
        {
            $string_length = (is_null($encoding) === true) ? mb_strlen($string) : mb_strlen($string, $encoding);

            if ($start < 0)
            {
                $start = max(0, $string_length + $start);
            }

            else if ($start > $string_length)
            {
                $start = $string_length;
            }

            if ($length < 0)
            {
                $length = max(0, $string_length - $start + $length);
            }

            else if ((is_null($length) === true) || ($length > $string_length))
            {
                $length = $string_length;
            }

            if (($start + $length) > $string_length)
            {
                $length = $string_length - $start;
            }

            if (is_null($encoding) === true)
            {
                return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length);
            }

            return mb_substr($string, 0, $start, $encoding) . $replacement . mb_substr($string, $start + $length, $string_length - $start - $length, $encoding);
        }

        return (is_null($length) === true) ? substr_replace($string, $replacement, $start) : substr_replace($string, $replacement, $start, $length);
    }

?>
<!--
/*
 * Italics processing
 *
foreach ($results as $result) {
    $diplomatic = $result['diplomatic'];
    //remove strike tags
   // $diplomatic = str_replace(array("<strike>", "</strike>", "<sup>", "</sup>"), "", $diplomatic);
    $id = $result["id"];
    echo "<br><br><strong>[{$result['pagenum']}.{$result['text_id']}.{$result['linenum']}]</strong><br>";
    preg_match_all('/\*\w+\*/', $diplomatic, $matches);
    foreach ($matches as $match) {
        foreach($match as $key => $val) {
            $diplomatic = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $diplomatic);
            //update the database
            $sql = "UPDATE bdl_line SET diplomatic = :text WHERE id = :id";
            $sth = $dbh->prepare($sql);
            $sth->execute(array(":text" => $diplomatic, ":id" => $id));
            echo $sql . ": {$id}";
        }
    }
}
*/
--?

</div>
</body>
</html>
