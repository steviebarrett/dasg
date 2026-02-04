<?php


namespace models;


class functions
{
    /**
     * Escapes for HTML output
     * @param mixed $value
     * @return string
     */
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Builds application-style URLs
     * @param mixed $value
     * @return string
     */
    public static function urlEncode(mixed $value): string
    {
        $text = str_ireplace(' ', '.', $value);
        return rawurlencode((string)$text);
    }

	/**
	 * Takes a database column name and returns a human-friendly version
	 * @param $text string input string (database column name)
	 * @return string the processed text
	 */
	public static function getFriendlyName($text) {
		$friendlyName = $text == "ai" ? "Identifier number" : str_replace('_', ' ', $text);
		$friendlyName = ucwords($friendlyName);
		return $friendlyName;
	}

	public static function encodeOrigin($url) {
		$encodedOrigin = str_replace(array('=', '&'), array('-', '|'), $url);
		return $encodedOrigin;
	}

	public static function decodeOrigin($string) {
		$url = str_replace(array('-', '|'), array('=', '&'), $string);
		return $url;
	}

	/**
	 * Generates and returns an HTML anchor
	 * @param string $url
	 * @param string $text : the display name
	 * @param string $title
	 * @param string $target
	 * @return string : the HTML code
	 */
	public static function addAnchorTags($url, $text, $title, $target = "_self") {
		$taggedUrl = <<<HTML
			<a href="{$url}" title="{$title}" target="{$target}">{$text}</a>
HTML;
		return $taggedUrl;
	}

	public static function addMutations($word) {
		$mutations = array('h-', 'n-', 't-');
		foreach ($mutations as $mutation) {
			if (mb_substr($word, 0, 2) == $mutation) {
				$word = str_replace($mutation, "", $word);
			}
		}
		$regexp = "[h|n||t-]?" . $word;
		return $regexp;
	}

	public static function canBeLenited($word) {
		if (mb_strlen($word) < 2 || mb_substr($word, 1, 1) == '-') {
			return false;
		}

		$excludeChars = array('h', 'l', 'n', 'r', '?', '*', '~', '[', ']');
		if (in_array(mb_substr($word, 0, 1), $excludeChars)) {
			return false;
		}
		return true;
	}

	public static function getLenited($word) {
		if (self::canBeLenited($word) == false) {
			return $word;
		}
		if (mb_substr($word, 1, 1) == 'h') { //already lenited
			$word = self::_mb_substr_replace($word, "?", 2, 0);
		} else {                                       //add lenition test
			$word = self::_mb_substr_replace($word, "h?", 1, 0);
		}
		return $word;
	}

	private static function _mb_substr_replace($output, $replace, $posOpen, $posClose) {
		return mb_substr($output, 0, $posOpen).$replace.mb_substr($output, $posClose+1);
	}

	public static function getAccentInsensitive($text, $caseSens = false) {
		$regExp = "";
		$accentMappedChars = null;
		if ($caseSens) {
			$accentMappedChars = array(
				"aàá", "eèé", "iìí", "oòó", "uùú"
			);
		} else {
			$accentMappedChars = array(
				"aàáAÀÁ", "eèéEÈÉ", "iìíIÌÍ", "oòóOÒÓ", "uùúUÙÚ"
			);
		}

		foreach (functions::str_split_unicode($text) as $char) {
			$replaced = false;
			foreach ($accentMappedChars as $accentMap) {
				if (stristr($accentMap, $char)) {
					$regExp .= "[{$accentMap}]+";
					$replaced = true;
				}
			}
			if ($replaced == false)
				$regExp .= $char;
		}
		return $regExp;
	}

	public static function str_split_unicode($str, $l = 0) {
		if ($l > 0) {
			$ret = array();
			$len = mb_strlen($str, "UTF-8");
			for ($i = 0; $i < $len; $i += $l) {
				$ret[] = mb_substr($str, $i, $l, "UTF-8");
			}
			return $ret;
		}
		return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
	}

	public static function gdSort($s, $t) {
		$accentedvowels = array('à', 'è', 'ì', 'ò', 'ù', 'À', 'È', 'Ì', 'Ò', 'Ù', 'ê', 'ŷ', 'ŵ', 'â', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú');
		$unaccentedvowels = array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'e', 'y', 'w', 'a', 'a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U');
		$str3 = str_replace($accentedvowels, $unaccentedvowels, $s);
		$str4 = str_replace($accentedvowels, $unaccentedvowels, $t);
		return strcasecmp($str3, $str4);
	}
}