<?php

namespace models;

require_once 'includes/include.php';

$model = new records();
$results = $model->getAllRecords();

$fieldnames = $model->getAllFieldNames();

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="LanguageInLyrics.xls"');

echo <<<XML
<?xml version="1.0"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Worksheet ss:Name="Sheet1">
  <!--Table ss:ExpandedColumnCount="2" ss:ExpandedRowCount="2" x:FullColumns="1" x:FullRows="1"-->
  <Table>
   <Row>
XML;

foreach ($fieldnames as $fieldname) {
	$fieldname = functions::getFriendlyName($fieldname);
	echo <<<XML
		<Cell><Data ss:Type="String">{$fieldname}</Data></Cell>
XML;
}
echo "</Row>";

foreach ($results as $record) {
	echo "<Row>";
	foreach ($record as $column) {
		$column = trim(htmlspecialchars($column));
		echo <<<XML
			<Cell><Data ss:Type="String">{$column}</Data></Cell>
XML;
	}
	echo "</Row>";
}


echo <<<XML
	  </Table>
 </Worksheet>
</Workbook>
XML;

die();
