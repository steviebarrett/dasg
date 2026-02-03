<?php

class FieldworkItems
{
	public static function getAll()
	{
		$fieldworkItems = array();
		
		foreach (glob(FIELDWORK_XML . "/*.xml") as $filepath) {		
			$xml = simplexml_load_file($filepath);
			$fileparts = explode('/', $filepath);
			$filename = array_pop($fileparts);
			$filename = str_replace(".xml", "", $filename);
			
			$fieldworkItems[$filename] = $xml;
		}
		
		return $fieldworkItems;
	}
	
	public static function getXml($filename)
	{
		$filepath = FIELDWORK_XML . '/' . $filename . ".xml";
		$xml = simplexml_load_file($filepath);
		
		return $xml;
	}
	
	public static function getHtml($filename)
	{	
		$xml = new DOMDocument();
		$xml->load(FIELDWORK_XML . '/' . $filename . ".xml");
		
		$xsl = new DOMDocument();
		$xsl->load(ROOT . "/xml/fieldworkHTML.xsl");	//put this somewhere else
		
		$proc = new XSLTProcessor();
		$proc->importStylesheet($xsl);
		
		$fieldworkDoc = $proc->transformToXml($xml);
		
		return $fieldworkDoc;
	}
}