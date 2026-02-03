<?php
// Path to your CSV file
$csvFile = 'record.csv';
$sitemapFile = 'sitemap.xml';
$baseUrl = 'https://dasg.ac.uk/LIL/index.php?m=record&a=view&ai=';

// Open the CSV file
if (!file_exists($csvFile)) {
    die("CSV file not found.");
}

$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Unable to open CSV file.");
}

// Begin XML structure
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
$xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

// Read rows
$line = 0;
while (($data = fgetcsv($handle)) !== FALSE) {
    if ($line++ === 0) continue; // Skip header
    $ai = trim($data[0]);
    if ($ai === '') continue;

    $url = $xml->addChild('url');
    $url->addChild('loc', htmlspecialchars($baseUrl . $ai, ENT_QUOTES, 'UTF-8'));
    $url->addChild('changefreq', 'monthly');
    $url->addChild('priority', '0.8');
}
fclose($handle);

// Save the XML to file
$xml->asXML($sitemapFile);

echo "Sitemap generated as $sitemapFile";
?>