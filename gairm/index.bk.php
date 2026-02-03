<?php

require_once '../includes/include.php';

//error_reporting(E_ALL); ini_set('display_errors', '1');

$pageSlug = "gairm";
$pageTitleElems = array("en" => "Gairm Online", "gd" => "Gairm Air-loidhne");
$pageTitle = $pageTitleElems[$lang];

$cqpPage = true;

$javascriptBlock = <<<HTML
	<script type="text/javascript" src="{$webPath}/js/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="{$webPath}/js/dasg_gairm.js"></script>
HTML;

require_once '../includes/htmlHeader.php';
require_once '../includes/sideMenu.php';



//TEMP : disable Gaelic for now
$lang = "en";



$searchSelected = $browseSelected = "";
if ($_GET["action"] === "browse") {
	$browseSelected = "menuSelected";
} else {
	$searchSelected = "menuSelected";
}
$menuElems = array(
	"search" => array("en"=>"Search", "gd"=>"[gd]Search"),
	"browse" => array("en"=>"Browse", "gd"=>"[gd]Browse"),
	"about" => array("en"=>"About Gairm", "gd"=>"[gd]About Gairm"),
	"acknowledge" => array("en"=>"Acknowledgements", "gd"=>"[gd]Acknowledgements")
);
$aboutText = array("en"=>"<p><em>Gairm</em> was a Gaelic periodical that ran from 1952 to 2002, edited by Professor Derick S. Thomson. In total,
			200 issues were printed, with more than 6,000 articles. This index allows you to search for article references.
			In due course we expect to have full-text digitised versions of each article.
			<br/><br/>DASG gratefully acknowledges the help of Jamie Gaukroger at Am Baile in assessing the scale of this project.
			</p>
			<h3>Gairm Online</h3>
			<p>DASG aims to put all issues of <em>Gairm</em> (1952–2002) online, including both images and transcribed texts enabling search by word, contributor, subject etc.</p>
			<p>Free and accessible, this will allow readers and researchers to fully access the most important periodical in Gaelic literature.</p>
			<p>In order to complete this, <em>Gairm</em>’s issues will be added online gradually over time. A considerable amount of work is still required to complete the full text transcription and we are continually working on our systems.</p>
",
"gd"=>"<p>’S e ràitheachan Gàidhlig a bha ann an Gairm a bha air fhoillseachadh eadar 1952 agus 2002, agus a bha deasaichte airson na cuid a bu mhotha leis an Ollamh Ruaraidh MacThòmais. Bha 200 àireamh ann uile gu lèir, a’ gabhail a-steach còrr is 6,000 alt, sgeulachd-ghoirid, dàn is eile. Tha an clàr-innse seo a’ toirt cothrom dhut altan fa leth no pìosan fiosrachaidh a lorg. An ceann greis, bidh dreach didseatach dhen chruinneachadh air fad ri fhaighinn air loidhne.</p>
			<h3>Gairm Air-loidhne</h3>
			<p>’S e an t-amas a th’ aig DASG <em>Gairm</em> gu lèir (1952–2002) a chur air-loidhne, a’ gabhail a-steach ìomhaighean agus teacsa tar-sgrìobhte a ghabhas rannsachadh a rèir facal, com-pàirtiche, cuspair is eile.</p> 
			<p>Bidh seo saor an-asgaidh agus so-ruigsinn – a’ leigeil le leughadairean agus rannsaichean cothrom fhaighinn air an ràitheachan as cudromaiche ann an litreachas na Gàidhlig.</p>
			<p>Gus seo a ghabhail os làimh, cuirear na h-àireamhan air-loidhne mean air mhean. Tha tòrr obrach fhathast ri dèanamh air tar-sgrìobhadh agus tha sinn a’ sìor leasachadh nan siostaman againn.</p>
");
$acknowledgeText = array("en"=>"
	<h3>Acknowledgements</h3>
	<p>We would like to sincerely thank the Gaelic Books Council for their help and support; and the family of Derick S. Thomson (Ruaraidh MacThòmais), <em>Gairm</em>’s editor, for giving their blessing to this project.</p>
	<p>We gratefully ackowledge the following inviduals/organisations: Carcanet Press for permission to reproduce selected poems by Sorley MacLean – <em>Sorley MacLean: Collected Poems</em> (2017), edited by Christopher Whyte and Emma Dymock, is available to purchase <a target='_blank' href='https://www.carcanet.co.uk/cgi-bin/indexer?product=345' title='Buy Sorley MacLean Collected Poems'>here</a>; Fiona MacKenzie and the National Trust for Scotland for permission to reproduce the work of John Lorne Campbell; Donald E. Meek for permission to reproduce his own writings as well as the writings of the Rev. Dr Thomas Murchison (An Dr Urr. Tòmas MacCalmain) – Os Cionn Gleadhraich nan Sràidean: Sgrìobhaidhean Thòmais MhicCalmain (2010), deas. le Dòmhnall E. Meek, is availablity to purchase <a target='_blank' href=\"https://www.gaelicbooks.org/explore-the-shop/non-fiction/spirituality-beliefs/os-cionn-gleadhraich-nan-sraidean?lang=gd\" title='Gairm Book'>here</a>.</p>
	<h3>Copyright</h3>
	<p>We aim to contact as many of the contributors to <em>Gairm</em> as we can in order to obtain permission to keep their work online here.</p>
	<p>Where this hasn’t been possible, we will follow a ‘take down’ policy – i.e. the periodical’s content will remain online but we will take down any article or other contribution if requested.</p>
	<p>We strongly wish, however, that the periodical will be as complete as possible so that it can be accessed by anyone. <em>Gairm Online</em> is a non-profit, educational project.</p>
	<p>If you contributed to <em>Gairm</em> in any way – or if you know a member of your family contributed – please let us know that you are happy for this work to be accessed online <a title='Email DASG' href='mailto:mail@dasg.ac.uk'>here</a>.</p>
	<p>If you can give us any other information about contributors or the content of the periodical, we would be interested to include this in our metadata so that others can read it.</p>
</h3>
",
	"gd"=>"
	<h3>Buidheachas</h3>
	<p>Bu mhath leinn taing mhòr a thoirt do Chomhairle nan Leabhraichean airson na taic a tha iad air toirt dhuinn; agus do theaghlach Ruaraidh MhicThòmais, deasaiche Ghairm, airson beannachd a thoirt don phròiseact seo.</p>
	<p>Tha sinn a’ toirt buidheachas do na daoine/buidhnean a leanas: Carcanet Press airson cead dàin Shomhairle MhicGill-Eain ath-riochdachadh – faodar <em>Sorley MacLean: Collected Poems</em> (2017), deas. le Christopher Whyte agus Emma Dymock, a cheannach an <a target='_blank' title='Buy Sorley MacLean Poems' href='https://www.carcanet.co.uk/cgi-bin/indexer?product=345'>seo</a>; Fiona NicCoinnich agus Urras Nàiseanta na h-Alba airson cead sgrìobhaidhean Iain Latharna Chaimbeil ath-riochdachadh; Dòmhnall E. Meek airson cead a chuid sgrìobhaidhean fhèin agus sgrìobhaidhean an Dr Urr. Tòmas MacCalmain ath-riochdachadh – faodar Os Cionn Gleadhraich nan Sràidean: Sgrìobhaidhean Thòmais MhicCalmain (2010), deas. le Dòmhnall E. Meek, a cheannach an <a target='_blank' href='https://www.gaelicbooks.org/explore-the-shop/non-fiction/spirituality-beliefs/os-cionn-gleadhraich-nan-sraidean?lang=gd' title='Gairm Book'>seo</a>.</p>
	<h3>Còraichean</h3>
	<p>Tha sinn a’ dèanamh oidhirp fios a chur gu neach sam bith a sgrìobh no a chur ri <em>Gairm</em> tro na bliadhnaichean airson cead fhaighinn an cuid obrach a chumail air-loidhne an seo.</p>
	<p>Mura h-eil sinn comasach air sin a dhèanamh, tha sinn a’ leantainn poileasaidh ‘thoir a-nuas’ – .i. bidh an t-susbaint air-loidhne ach faodar fios a chur thugainn airson a thoirt a-nuas.</p> 
	<p>Tha sinn ag iarraidh, ge-tà, gum bi an ràitheachan cho slàn coileanta ’s a ghabhas airson ’s gun dèan duine sam bith feum dheth.  ’S e goireas foghlaim neo-phrothaide a th’ ann an <em>Gairm Air-loidhne</em>.</p>
	<p>Ma chuir thusa ri <em>Gairm</em> ann an dòigh sam bith – no, ma chuir ball dhe do theaglach ris – nach innis thu dhuinn gu bheil thu riaraichte gum bi an obair ri faotainn an <a title='Email DASG' href='mail@dasg.ac.uk'>seo</a>.</p>
	<p>Cuideachd, mas urrainn dhut fiosrachadh eile a thoirt dhuinn mu chom-pàirtiche no susbaint an ràitheachain, bhiodh ùidh againn sin a chur ris a’ mheata-dàta againn gus am faodadh daoine eile a leughadh.</p>
");
$closeText = array("en"=>"close", "gd"=>"[gd]close");
echo <<<HTML
<div class="rightContentWrapper">
	<div id="dasg_cqp_main_menu">
        <div class="menuItem {$searchSelected}">
            <a href="{$webPath}/gairm/">{$menuElems["search"][$lang]}</a>
        </div>
        <div class="menuItem {$browseSelected}">
            <a href="{$webPath}/gairm/browse">{$menuElems["browse"][$lang]}</a>
        </div>
        <div class="menuItem">
						<a href="#" id="aboutGairmLink">{$menuElems["about"][$lang]}</a>
				</div>
				<div class="menuItem">
						<a href="#" id="acknowledgeGairmLink">{$menuElems["acknowledge"][$lang]}</a>
				</div>
				<div id="gairmAbout">
					<h2>Gairm</h2>
						{$aboutText[$lang]}
					<a href="#" id="gairmAboutClose">{$closeText[$lang]}</a>
				</div>
				<div id="gairmAcknowledge">
					<h2>Gairm</h2>
						{$acknowledgeText[$lang]}
					<a href="#" id="gairmAcknowledgeClose">{$closeText[$lang]}</a>
				</div>
				
				<br class="clear"/>
				
    </div>
    <!-- div id="gairmPDFLinks">
		<div>
        	<button class="dasg_medButton" id="pdfAuthor">Author PDF</button>
        </div>
        <div>
			<button class="dasg_medButton" id="pdfVolume">Volume PDF</button>
        </div>
    </div -->
HTML;

if ($_GET["action"] !== "browse") {		//default to search
	/*
	 * Generate the search form HTML and populate the search field array
	 */
	$selectedSearchFields = $selectedFilterFields = array();
	$searchFields = array("en" => array(
		"Title"=>"title", "Last Name"=>"lastName", "First Name"=>"firstName", "Origin (en)"=>"origin", "Origin (gd)"=>"origin_gd", "Year"=>"yearOfPublication",
		"Comments"=>"comments", "Transcription"=>"transcription"),
			"gd" => array(
		"Title[gd]"=>"title", "Last Name[gd]"=>"lastName", "First Name[gd]"=>"firstName", "Origin[gd] (en)"=>"origin", "Origin[gd] (gd)"=>"origin_gd", "Year[gd]"=>"yearOfPublication",
				"Comments[gd]"=>"comments", "Transcription[gd]"=>"transcription"
			)
	);
	$searchFieldHtml = "<ul>";
	//Default to title search if no search entered yet
	if (empty($_GET["search"])) {
		$_GET["title"] = "on";
	}
	//clear the form
	if (isset($_GET["clear"])) {
		unset($_GET);
	}
	foreach ($searchFields[$lang] as $label => $searchField) {
		if (empty($_GET[$searchField])) {
			$checked = "";
		} else {
			$selectedSearchFields[] = $searchField;
			$checked = "checked='checked'";
		}
		$searchFieldHtml .= <<<HTML
			<li>
				<label for="{$searchField}">{$label}</label>
				<input type="checkbox" name="{$searchField}" id="{$searchField}" $checked/>
			</li>
HTML;
	}
	$searchFieldHtml .= "</ul>";
	$filterFields = array("en"=>array("Language"=>"language", "Genre"=>"genre", "Type"=>"type"),
		"gd"=>array("Language[gd]"=>"language", "Genre[gd]"=>"genre", "Type[gd]"=>"type"));
	$filterFieldHtml = "<ul>";
	foreach ($filterFields[$lang] as $label => $filterField) {
		$filterFieldHtml .= <<<HTML
			<li><label for="{$filterField}">{$label}</label>
			<select name="{$filterField}" id="{$filterField}"/>
				<option value="">--All--</option>
HTML;
		$filterValues = GairmRecords::getFilterValues($filterField);
		foreach ($filterValues as $filterValue) {
			if ($_GET[$filterField] != $filterValue) {
				$selected = "";
			} else {
				$selected = "selected='selected'";
				$selectedFilterFields[$filterField] = $filterValue;
			}
			$filterFieldHtml .= <<<HTML
				<option value="{$filterValue}" {$selected}>{$filterValue}</option>
HTML;
		}
		$filterFieldHtml .= "</select></li>";
	}
	$filterFieldHtml .= "</ul>";
	
	$queryString = "";
	$results = array();
	/*
	 * If there has been a search, get the results
	 */

	$errorMsg = array(
		"en" => array(
			"selectFields" => "<h3 class=\"error\">Please select one or more fields to search</h3>",
			"enterQuery" => "<h3 class=\"error\">Please enter a search query</h3>"),
		"gd" => array(
			"selectFields" => "<h3 class=\"error\">[gd]Please select one or more fields to search</h3>",
			"enterQuery" => "<h3 class=\"error\">[gd]Please enter a search query</h3>"));
	if (!empty($_GET["search"])) {
		$queryString = $_GET["queryString"];
		if (empty($selectedSearchFields)) {
			echo "<h3 class=\"error\">{$errorMsg[$lang]["selectFields"]}</h3>";
		} else if (empty($_GET["queryString"])) {
			echo "<h3 class=\"error\">{$errorMsg[$lang]["enterQuery"]}</h3>";
		} else {
			$results = GairmRecords::searchRecords($queryString, $selectedSearchFields, $selectedFilterFields);
		}
	}

	$searchForText = array("en"=>"Search for", "gd"=>"[gd]Search for");
	$searchLabel = array("en"=>"Search", "gd"=>"[gd]Search");
	$filterLabel = array("en"=>"Filter by", "gd"=>"[gd]Filter by");
	$searchButton = array("en"=>"search", "gd"=>"[gd]search");
	$placeholder = array("en"=>"search term", "gd"=>"[gd]search term");
	$clear = array("en"=>"clear", "gd"=>"[gd]clear");
	$searchFormHtml = <<<HTML
		<div id="gairmSearch">
			<form method="GET">
			
				<div id="gairmSearchFields">
					<h3>{$searchLabel[$lang]}:</h3>
					{$searchFieldHtml}
					<br/>
				</div>	<!-- end gairmSearchFields -->
				
				<div id="gairmSearchFilters">
					<h3>{$filterLabel[$lang]}:</h3>
					{$filterFieldHtml}
					<br/>
				</div>
				
				<div id="gairmSearchMain">
					<h3>{$searchForText[$lang]}:</h3>
					<input type="text" name="queryString" id="queryString" value="{$queryString}" placeholder="{$placeholder[$lang]}"/>
					<input type="submit" name="search" id="search" value="{$searchButton[$lang]}" class="dasg_medButton"/>
					<input type="submit" name="clear" id="clear" value="{$clear[$lang]}" class="dasg_smlButton"/>
				</div>	<!-- end gairmSearchMain -->
							
			</form>
		</div>
							
HTML;
	echo $searchFormHtml;		//end search form code
} else {					//begin browse form code
	$results = array();
	$queryString = "";
	$category = "";
	$dbCategories = array();
	if (!empty($_GET["yearOfPublication"])) {
		$queryString = $_GET["yearOfPublication"];
		$category = "year";
		$dbCategories = array("yearOfPublication");
	} else if (!empty($_GET["author"])) {
		$queryString = $_GET["author"];
		$category = "author";
		$dbCategories = array("lastName", "firstName");
	} else if (!empty($_GET["volume"])) {
		$queryString = $_GET["volume"];
		$category = "volume";
		$dbCategories = array("volume");
	}
	if ($category !== "" && $category != "read") {
		$results = GairmRecords::browseRecords($queryString, $dbCategories);
	}
	
	/*
	 * Assemble the browse options HTML
	 */
	$browseOptions = array("year" => array("en" => "Browse By Year", "gd" => "[gd]Browse by Year"),
		"volume" => array("en" => "Browse By Volume", "gd" => "[gd]Browse by Volume"),
		"read" => array("en" => "Read Volume", "gd" => "[gd]Read Volume"));
	/*
	 * years
	 */
	$years = GairmRecords::getYears();
	$yearSelectHtml = '<select id="yearOfPublication" name="yearOfPublication"><option value="">-- ' .$browseOptions["year"][$lang] .' --</option>';
	foreach ($years as $year) {
		$yearSelectHtml .= '<option value="' . $year . '">' . $year . '</option>';
	}
	$yearSelectHtml .= '</select>';
	
	/*
	 * authors
	 */
	/*
	$authors = GairmRecords::getAuthors();
	$authorSelectHtml = '<select id="author" name="author"><option value="">-- Browse By Author --</option>';
	foreach ($authors as $author) {
		$lastName = (mb_strlen($author["lastName"]) > 30) ? mb_substr($author["lastName"], 0, 30) . " ... " : $author["lastName"];
		$firstName = (mb_strlen($author["firstName"]) > 18) ? mb_substr($author["firstName"], 0, 18) . " ... " : $author["firstName"];
		$formattedName = (empty($firstName)) ? $lastName : $lastName . ", " . $firstName;
		$authorSelectHtml .= '<option value="' . str_replace('"', '||', $author["lastName"] . '|' . $author["firstName"]) . '">' . $formattedName . '</option>';
	}
	$authorSelectHtml .= '</select>';
	*/

	/*
	 * volumes
	 */
	$volumes = GairmRecords::getVolumes();
	$volumeSelectHtml = '<select id="volume" name="volume"><option value="">-- ' .$browseOptions["volume"][$lang] .' --</option>';
	$readVolumeSelectHtml = '<select id="read" name="read"><option value="">-- ' .$browseOptions["read"][$lang] .' --</option>';
	foreach ($volumes as $volume) {
		$volumeSelectHtml .= '<option value="' . $volume . '">' . $volume . '</option>';
		//restrict the volumes available
		if ((int)$volume < 11) {
			$readVolumeSelectHtml .= '<option value="' . $volume . '">' . $volume . '</option>';
		}
	}
	$volumeSelectHtml .= '</select>';
	$readVolumeSelectHtml .= '</select>';
	
	//write browse options
	echo <<<HTML
		<div class="gairmCategory">{$readVolumeSelectHtml}</div>
		<div class="gairmCategory">{$yearSelectHtml}</div>
		<!--div class="gairmCategory">{$authorSelectHtml}</div-->
		<div class="gairmCategory">{$volumeSelectHtml}</div>
HTML;
}	//end the browse form code


/*
 * Print the results
 */
$resultsHtml = '<img id="gairmImage" src="' . $webPath . '/images/gairm/gairmCartoon.jpg" alt="Gairm Cartoon Image" width="730"/>';

if (!empty($queryString)) {
	$encodedQuery = urlencode($_SERVER['QUERY_STRING']);
	//test for zero results
	if (empty($results)) {
		$resultsHtml = "<h2>There were no results for " . $queryString . "</h2>";
	} else {
		$numResults = count($results);
		$resultsTagline = ($numResults === 1) ? "There was one result for " : "There were {$numResults} results for ";
		//hack for year of publication
		if ($category === "yearOfPublication") {
			$category = "year";
		}
		$queryString = str_replace('||', '"', $queryString);
		$queryString = str_replace('|', ', ', $queryString);
		$queryString = trim($queryString, ', ');
		$resultsTagline .= ucfirst($category) . ": " . $queryString;
		$headings = array(
			"title" => array("en"=>"Title", "gd"=>"[gd]Title"),
			"surname" => array("en"=>"Last Name", "gd"=>"[gd]Last Name"),
			"firstname" => array("en"=>"First Name", "gd"=>"[gd]First Name"),
			"origin" => array("en"=>"Origin", "gd"=>"[gd]Origin"),
			"year" => array("en"=>"Year", "gd"=>"[gd]Year"),
			"info" => array("en"=>"Info", "gd"=>"Fios"),
			"read" => array("en"=>"Read", "gd"=>"[gd]Read")
		);
		$resultsHtml = <<<HTML
			<h2>{$resultsTagline}</h2>
				<table id="gairmResultsTable" class="tablesorter">
					<thead>
						<tr>
							<th class="header">{$headings["title"][$lang]}</th>
							<th class="header">{$headings["surname"][$lang]}</th>
							<th class="header" id="firstNameCol">{$headings["firstname"][$lang]}</th>
							<th class="header">{$headings["origin"][$lang]}</th>
							<th class="header headerSortUp">{$headings["year"][$lang]}</th>
							<th>{$headings["info"][$lang]}</th>
							<th>{$headings["read"][$lang]}</th>
						</tr>
					</thead>
					<tbody>
HTML;
		foreach ($results as $id => $gairmRecord) {
			$origin = ($gairmRecord->getOrigin() === "/") ? "" : $gairmRecord->getOrigin();
			//if the page number is NOT an integer then it must be a roman numeral in sqaure brackets => remove brackets
			$volume = $gairmRecord->getVolume();
			$page = $gairmRecord->getFirstPage();
/*			$page = is_int((int)$gairmRecord->getFirstPage())
				? "TD_" . $gairmRecord->getFirstPage()
				: str_ireplace(array('[', ']'), '', $gairmRecord->getFirstPage());
*/
			$volFirstPage = GairmRecords::getFirstPageNoInVolume($volume);
			if ($volFirstPage > 1) {  //handle vols that start with higher page numbers
				$page = ($page - $volFirstPage) +3; //take account of covers at start of vol
			}
	//		$page .= ".pdf";
			$view = array("en"=>"view", "gd"=>"[gd]view");
			$resultsHtml .= <<<HTML
						<tr>
							<td>{$gairmRecord->getTitle()}</td>
							<td>{$gairmRecord->getLastName()}</td>
							<td>{$gairmRecord->getFirstName()}</td>
							<td>{$origin}</td>
							<td>&nbsp;&nbsp;{$gairmRecord->getYearOfPublication()}&nbsp;&nbsp;</td>
							<td><a href="view.php?id={$id}&query={$encodedQuery}" id="gairmRecord_{$id}" class="gairmViewLink" title="View record">{$view[$lang]}</a></td>
							<td><a target="_blank" alt="View text" href="read.php?sub=vol/{$volume}#page/n{$page}/mode/2up">{$view[$lang]}</a></td>								
HTML;
		}
		$resultsHtml .= <<<HTML
					</tbody>
				</table>
				
				<div id="gairmRecord"></div>
HTML;
	}
}

echo <<<HTML
	<div id="gairmSearchResults">
		{$resultsHtml}
	</div> <!-- end gairmSearchResults -->
</div> <!-- end right content wrapper -->
HTML;
		
		echo <<<JS
	<script>
		$(function() {
	  	$('#read').change(function() {
	  	  let url = '{$webPath}/gairm/read.php?sub=vol/' + $(this).val();
				window.open(url, '_blank');
			});
			$('#yearOfPublication').change(function() {
				window.location.href = '{$webPath}/gairm/browse/year/' + $(this).val();
			});
			$('#author').change(function() {
				window.location.href = '{$webPath}/gairm/browse/author/' + $(this).val();
			});
			$('#volume').change(function() {
				window.location.href = '{$webPath}/gairm/browse/volume/' + $(this).val();
			});
			$('#pdfVolume').on('click', function() {
				window.open('{$webPath}/gairm/pdf.php?format=volume');
			});
			$('#pdfAuthor').on('click', function() {
				window.open('{$webPath}/gairm/pdf.php?format=author');
			});
		});
	</script>
JS;
		
		require_once '../includes/htmlFooter.php';
