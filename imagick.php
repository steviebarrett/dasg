<?php
ini_set("display_errors", 1);

define("ROOT", "/var/www/html/dasg.arts.gla.ac.uk/www");

$blogImageDir = ROOT . "/images/blog/";
$filepath = $blogImageDir . "44.jpg";
$image = new Imagick($filepath);

echo "<h2>Class Imagick found</h2>";
