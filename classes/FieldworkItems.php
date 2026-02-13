<?php

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

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

    public static function getHtml(string $filename): string
    {
        // Allowlist filename
        if (!preg_match('/\A[a-zA-Z0-9_-]+\z/', $filename)) {
            throw new RuntimeException('Invalid filename');
        }

        $xmlPath = FIELDWORK_XML . '/' . $filename . '.xml';
        $xslPath = ROOT . '/xml/fieldworkHTML.xsl';

        if (!is_file($xmlPath) || !is_readable($xmlPath)) {
            throw new RuntimeException('XML not found');
        }
        if (!is_file($xslPath) || !is_readable($xslPath)) {
            throw new RuntimeException('XSL not found');
        }

        $xml = new DOMDocument();

        $ok = $xml->load($xmlPath, LIBXML_NONET);
        if (!$ok) {
            throw new RuntimeException('Invalid XML');
        }

        // Load XSL (also block network)
        $xsl = new DOMDocument();
        $ok = $xsl->load($xslPath, LIBXML_NONET);
        if (!$ok) {
            throw new RuntimeException('Invalid XSL');
        }

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xsl);

        // prevent reading/writing files from XSLT
        if (defined('XSL_SECPREF_NONE')) {
            $proc->setSecurityPrefs(
                XSL_SECPREF_READ_FILE
                | XSL_SECPREF_WRITE_FILE
                | XSL_SECPREF_CREATE_DIRECTORY
                | XSL_SECPREF_READ_NETWORK
                | XSL_SECPREF_WRITE_NETWORK
            );
        }

        $out = $proc->transformToXml($xml);
        if ($out === false) {
            throw new RuntimeException('Transform failed');
        }

        return $out;
    }
}