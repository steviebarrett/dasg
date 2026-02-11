<?php

ini_set("display_errors", 1);

date_default_timezone_set('Europe/London');

define("ROOT", $_SERVER['DOCUMENT_ROOT']);
define("AUDIO_DIR", "/mnt/storage01/data/audio");
define("FIELDWORK_XML", ROOT . "/xml/archive");


define("ACCENT_VOWELS", "aàáăeèéĕiìíĭoòóŏuùúŭ");
define("ACCENT_CHARSET", "A-Za-zÀÈÌÒÙàèìòùÁÉÍÓÚáéíóúĂăĔĕĬĭŎŏŬŭ’");

$expire = time() + 60 * 60 * 24 * 31;

// initialize some variables
$includeIrish = $languageLinksHtml = $devHighlightCss = "";

$siteTitle = array("en"=>"Digital Archive of Scottish Gaelic", "gd"=>"Dachaigh airson Stòras na Gàidhlig");

//load the classes
$classes = ['AudioItem', 'AudioItems', 'BlogComment', 'BlogComments', 'BlogEntries', 'BlogEntry',
	'BlogView', 'DB', 'Email', 'FieldworkItems', 'FieldworkPerson', 'FieldworkPersons',
	'Functions', 'GairmRecord', 'GairmRecords', 'Metadata', 'Page',
	'Pages', 'User', 'Users',];
foreach ($classes as $class) {
	spl_autoload_register(function ($class) {
		include $_SERVER['DOCUMENT_ROOT'] . '/classes/' . $class . '.php';
	});
}

$lang = empty($_COOKIE["lang"]) ? "" : Functions::e(["lang"]);
$getLang = isset($_GET["lang"]) ? Functions::e($_GET["lang"]) : "";
if (!empty($_GET["lang"])) {
    if ($getLang == "en") {
        setcookie("lang", "en", $expire, "/");
        $lang = "en";
    }
    else {
        setcookie("lang", "gd", $expire, "/");
        $lang = "gd";
    }
} else {
    if (empty($lang)) {
        setcookie("lang", "gd", $expire, "/");
        $lang = "gd";
    }
}

//CONSTANTS
require_once 'passwords.php';

define("DB_AUDIO", "dasgaudio");			//audio database - move into main DASG DB??

// load Csrf class
require_once __DIR__ . '/../classes/Csrf.php';
Csrf::ensureToken();

