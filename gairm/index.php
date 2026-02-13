<?php
declare(strict_types=1);

require_once '../includes/include.php';

$lang = 'en';

$pageSlug = "gairm";
$pageTitleElems = ["en" => "Gairm Online", "gd" => "Gairm Air-loidhne"];
$pageTitle = $pageTitleElems[$lang] ?? $pageTitleElems["en"];

$cqpPage = true;

$javascriptBlock = <<<HTML
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.32.0/js/jquery.tablesorter.min.js" integrity="sha512-O/JP2r8BG27p5NOtVhwqsSokAwEP5RwYgvEzU9G6AfNjLYqyt2QT8jqU1XrXCiezS50Qp1i3ZtCQWkHZIRulGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<script type="text/javascript" src="/js/dasg_gairm.js"></script>
HTML;

require_once '../includes/htmlHeader.php';
require_once '../includes/sideMenu.php';

// Normalise input
$get = $_GET ?? [];
$action = ($get['action'] ?? '') === 'browse' ? 'browse' : ''; // '' means search/default

// ---------- Menu ----------
$searchSelected = ($action === 'browse') ? "" : "menuSelected";
$browseSelected = ($action === 'browse') ? "menuSelected" : "";

$menuElems = [
    "search" => ["en"=>"Search", "gd"=>"[gd]Search"],
    "browse" => ["en"=>"Browse", "gd"=>"[gd]Browse"],
    "about" => ["en"=>"About Gairm", "gd"=>"[gd]About Gairm"],
    "acknowledge" => ["en"=>"Acknowledgements", "gd"=>"[gd]Acknowledgements"],
];

$aboutText = [
    "en" => "<p><em>Gairm</em> was a Gaelic periodical that ran from 1952 to 2002, edited by Professor Derick S. Thomson. In total,
			200 issues were printed, with more than 6,000 articles. This index allows you to search for article references.
			In due course we expect to have full-text digitised versions of each article.
			<br/><br/>DASG gratefully acknowledges the help of Jamie Gaukroger at Am Baile in assessing the scale of this project.
			</p>
			<h3>Gairm Online</h3>
			<p>DASG aims to put all issues of <em>Gairm</em> (1952–2002) online, including both images and transcribed texts enabling search by word, contributor, subject etc.</p>
			<p>Free and accessible, this will allow readers and researchers to fully access the most important periodical in Gaelic literature.</p>
			<p>In order to complete this, <em>Gairm</em>’s issues will be added online gradually over time. A considerable amount of work is still required to complete the full text transcription and we are continually working on our systems.</p>",
    "gd" => "<p>’S e ràitheachan Gàidhlig a bha ann an Gairm a bha air fhoillseachadh eadar 1952 agus 2002, agus a bha deasaichte airson na cuid a bu mhotha leis an Ollamh Ruaraidh MacThòmais. Bha 200 àireamh ann uile gu lèir, a’ gabhail a-steach còrr is 6,000 alt, sgeulachd-ghoirid, dàn is eile. Tha an clàr-innse seo a’ toirt cothrom dhut altan fa leth no pìosan fiosrachaidh a lorg. An ceann greis, bidh dreach didseatach dhen chruinneachadh air fad ri fhaighinn air loidhne.</p>
			<h3>Gairm Air-loidhne</h3>
			<p>’S e an t-amas a th’ aig DASG <em>Gairm</em> gu lèir (1952–2002) a chur air-loidhne, a’ gabhail a-steach ìomhaighean agus teacsa tar-sgrìobhte a ghabhas rannsachadh a rèir facal, com-pàirtiche, cuspair is eile.</p> 
			<p>Bidh seo saor an-asgaidh agus so-ruigsinn – a’ leigeil le leughadairean agus rannsaichean cothrom fhaighinn air an ràitheachan as cudromaiche ann an litreachas na Gàidhlig.</p>
			<p>Gus seo a ghabhail os làimh, cuirear na h-àireamhan air-loidhne mean air mhean. Tha tòrr obrach fhathast ri dèanamh air tar-sgrìobhadh agus tha sinn a’ sìor leasachadh nan siostaman againn.</p>",
];

$acknowledgeText = [
    "en" => "
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
    "gd" => "
	<h3>Buidheachas</h3>
	<p>Bu mhath leinn taing mhòr a thoirt do Chomhairle nan Leabhraichean airson na taic a tha iad air toirt dhuinn; agus do theaghlach Ruaraidh MhicThòmais, deasaiche Ghairm, airson beannachd a thoirt don phròiseact seo.</p>
	<p>Tha sinn a’ toirt buidheachas do na daoine/buidhnean a leanas: Carcanet Press airson cead dàin Shomhairle MhicGill-Eain ath-riochdachadh – faodar <em>Sorley MacLean: Collected Poems</em> (2017), deas. le Christopher Whyte agus Emma Dymock, a cheannach an <a target='_blank' title='Buy Sorley MacLean Poems' href='https://www.carcanet.co.uk/cgi-bin/indexer?product=345'>seo</a>; Fiona NicCoinnich agus Urras Nàiseanta na h-Alba airson cead sgrìobhaidhean Iain Latharna Chaimbeil ath-riochdachadh; Dòmhnall E. Meek airson cead a chuid sgrìobhaidhean fhèin agus sgrìobhaidhean an Dr Urr. Tòmas MacCalmain ath-riochdachadh – faodar Os Cionn Gleadhraich nan Sràidean: Sgrìobhaidhean Thòmais MhicCalmain (2010), deas. le Dòmhnall E. Meek, a cheannach an <a target='_blank' href='https://www.gaelicbooks.org/explore-the-shop/non-fiction/spirituality-beliefs/os-cionn-gleadhraich-nan-sraidean?lang=gd' title='Gairm Book'>seo</a>.</p>
	<h3>Còraichean</h3>
	<p>Tha sinn a’ dèanamh oidhirp fios a chur gu neach sam bith a sgrìobh no a chur ri <em>Gairm</em> tro na bliadhnaichean airson cead fhaighinn an cuid obrach a chumail air-loidhne an seo.</p>
	<p>Mura h-eil sinn comasach air sin a dhèanamh, tha sinn a’ leantainn poileasaidh ‘thoir a-nuas’ – .i. bidh an t-susbaint air-loidhne ach faodar fios a chur thugainn airson a thoirt a-nuas.</p> 
	<p>Tha sinn ag iarraidh, ge-tà, gum bi an ràitheachan cho slàn coileanta ’s a ghabhas airson ’s gun dèan duine sam bith feum dheth.  ’S e goireas foghlaim neo-phrothaide a th’ ann an <em>Gairm Air-loidhne</em>.</p>
	<p>Ma chuir thusa ri <em>Gairm</em> ann an dòigh sam bith – no, ma chuir ball dhe do theaglach ris – nach innis thu dhuinn gu bheil thu riaraichte gum bi an obair ri faotainn an <a title='Email DASG' href='mail@dasg.ac.uk'>seo</a>.</p>
	<p>Cuideachd, mas urrainn dhut fiosrachadh eile a thoirt dhuinn mu chom-pàirtiche no susbaint an ràitheachain, bhiodh ùidh againn sin a chur ris a’ mheata-dàta againn gus am faodadh daoine eile a leughadh.</p>
",
];

$closeText = ["en" => "close", "gd" => "[gd]close"];

// Precompute values that go into heredoc interpolation
$menuSearch = $menuElems["search"][$lang] ?? $menuElems["search"]["en"];
$menuBrowse = $menuElems["browse"][$lang] ?? $menuElems["browse"]["en"];
$menuAbout  = $menuElems["about"][$lang] ?? $menuElems["about"]["en"];
$menuAck    = $menuElems["acknowledge"][$lang] ?? $menuElems["acknowledge"]["en"];

$aboutHtml = $aboutText[$lang] ?? $aboutText["en"];
$ackHtml   = $acknowledgeText[$lang] ?? $acknowledgeText["en"];
$closeLbl  = $closeText[$lang] ?? $closeText["en"];



echo <<<HTML
<div class="rightContentWrapper">
	<div id="dasg_cqp_main_menu">
        <div class="menuItem {$searchSelected}">
            <a href="/gairm/">{$menuSearch}</a>
        </div>
        <div class="menuItem {$browseSelected}">
            <a href="/gairm/browse">{$menuBrowse}</a>
        </div>
        <div class="menuItem">
			<a href="#" id="aboutGairmLink">{$menuAbout}</a>
		</div>
		<div class="menuItem">
			<a href="#" id="acknowledgeGairmLink">{$menuAck}</a>
		</div>
		<div id="gairmAbout">
			<h2>Gairm</h2>
			{$aboutHtml}
			<a href="#" id="gairmAboutClose">{$closeLbl}</a>
		</div>
		<div id="gairmAcknowledge">
			<h2>Gairm</h2>
			{$ackHtml}
			<a href="#" id="gairmAcknowledgeClose">{$closeLbl}</a>
		</div>
		<br class="clear"/>
    </div>
HTML;

// ---------- Search vs Browse ----------
if ($action !== 'browse') {

    $selectedSearchFields = [];
    $selectedFilterFields = [];

    $searchFields = [
        "en" => [
            "Title" => "title", "Last Name" => "lastName", "First Name" => "firstName",
            "Origin (en)" => "origin", "Origin (gd)" => "origin_gd", "Year" => "yearOfPublication",
            "Comments" => "comments", "Transcription" => "transcription"
        ],
        "gd" => [
            "Title[gd]" => "title", "Last Name[gd]" => "lastName", "First Name[gd]" => "firstName",
            "Origin[gd] (en)" => "origin", "Origin[gd] (gd)" => "origin_gd", "Year[gd]" => "yearOfPublication",
            "Comments[gd]" => "comments", "Transcription[gd]" => "transcription"
        ]
    ];

    // Clear form: do NOT unset($_GET); just clear our local $get
    if (isset($get["clear"])) {
        $get = [];
    }

    // Default to title search if no search submitted yet
    if (empty($get["search"])) {
        $get["title"] = "on";
    }

    $searchFieldHtml = "<ul>";
    foreach ($searchFields[$lang] as $label => $searchField) {
        $isChecked = !empty($get[$searchField]);
        if ($isChecked) {
            $selectedSearchFields[] = $searchField;
        }
        $checked = $isChecked ? "checked='checked'" : "";

        $labelEsc = Functions::e($label);
        $sfEsc = Functions::e($searchField);

        $searchFieldHtml .= <<<HTML
			<li>
				<label for="{$sfEsc}">{$labelEsc}</label>
				<input type="checkbox" name="{$sfEsc}" id="{$sfEsc}" {$checked}/>
			</li>
HTML;
    }
    $searchFieldHtml .= "</ul>";

    $filterFields = [
        "en" => ["Language" => "language", "Genre" => "genre", "Type" => "type"],
        "gd" => ["Language[gd]" => "language", "Genre[gd]" => "genre", "Type[gd]" => "type"],
    ];

    $filterFieldHtml = "<ul>";
    foreach ($filterFields[$lang] as $label => $filterField) {

        $labelEsc = Functions::e($label);
        $ffEsc = Functions::e($filterField);
        $filterFieldHtml .= <<<HTML
			<li><label for="{$ffEsc}">{$labelEsc}</label>
			<select name="{$ffEsc}" id="{$ffEsc}">
				<option value="">--All--</option>
HTML;

        $filterValues = GairmRecords::getFilterValues($filterField);
        $current = (string)($get[$filterField] ?? '');

        foreach ($filterValues as $filterValue) {
            $filterValue = (string)$filterValue;

            $selected = ($current !== '' && $current === $filterValue) ? "selected='selected'" : "";
            if ($selected !== "") {
                $selectedFilterFields[$filterField] = $filterValue;
            }

            $fvEsc = Functions::e($filterValue);
            $filterFieldHtml .= <<<HTML
				<option value="{$fvEsc}" {$selected}>{$fvEsc}</option>
HTML;
        }
        $filterFieldHtml .= "</select></li>";
    }
    $filterFieldHtml .= "</ul>";

    $queryString = (string)($get["queryString"] ?? "");
    $results = [];

    $errorMsg = [
        "en" => [
            "selectFields" => "Please select one or more fields to search",
            "enterQuery" => "Please enter a search query",
        ],
        "gd" => [
            "selectFields" => "[gd]Please select one or more fields to search",
            "enterQuery" => "[gd]Please enter a search query",
        ]
    ];

    if (!empty($get["search"])) {
        if (empty($selectedSearchFields)) {
            echo '<h3 class="error">' . Functions::e($errorMsg[$lang]["selectFields"]) . '</h3>';
        } elseif (empty($get["queryString"])) {
            echo '<h3 class="error">' . Functions::e($errorMsg[$lang]["enterQuery"]) . '</h3>';
        } else {
            $results = GairmRecords::searchRecords($queryString, $selectedSearchFields, $selectedFilterFields);
        }
    }

    $searchForText = ["en" => "Search for", "gd" => "[gd]Search for"];
    $searchLabel = ["en" => "Search", "gd" => "[gd]Search"];
    $filterLabel = ["en" => "Filter by", "gd" => "[gd]Filter by"];
    $searchButton = ["en" => "search", "gd" => "[gd]search"];
    $placeholder = ["en" => "search term", "gd" => "[gd]search term"];
    $clear = ["en" => "clear", "gd" => "[gd]clear"];

    // Precompute for heredoc
    $searchLabelEsc = Functions::e($searchLabel[$lang]);
    $filterLabelEsc = Functions::e($filterLabel[$lang]);
    $searchForEsc = Functions::e($searchForText[$lang]);
    $queryStringEsc = Functions::e($queryString);
    $placeholderEsc = Functions::e($placeholder[$lang]);
    $searchBtnEsc = Functions::e($searchButton[$lang]);
    $clearEsc = Functions::e($clear[$lang]);

    echo <<<HTML
		<div id="gairmSearch">
			<form method="GET">
				<div id="gairmSearchFields">
					<h3>{$searchLabelEsc}:</h3>
					{$searchFieldHtml}
					<br/>
				</div>
				<div id="gairmSearchFilters">
					<h3>{$filterLabelEsc}:</h3>
					{$filterFieldHtml}
					<br/>
				</div>
				<div id="gairmSearchMain">
					<h3>{$searchForEsc}:</h3>
					<input type="text" name="queryString" id="queryString" value="{$queryStringEsc}" placeholder="{$placeholderEsc}"/>
					<input type="submit" name="search" id="search" value="{$searchBtnEsc}" class="dasg_medButton"/>
					<input type="submit" name="clear" id="clear" value="{$clearEsc}" class="dasg_smlButton"/>
				</div>
			</form>
		</div>
HTML;

} else {

    $results = [];
    $queryString = "";
    $category = "";
    $dbCategories = [];

    if (!empty($get["yearOfPublication"])) {
        $queryString = (string)$get["yearOfPublication"];
        $category = "year";
        $dbCategories = ["yearOfPublication"];
    } elseif (!empty($get["author"])) {
        $queryString = (string)$get["author"];
        $category = "author";
        $dbCategories = ["lastName", "firstName"];
    } elseif (!empty($get["volume"])) {
        $queryString = (string)$get["volume"];
        $category = "volume";
        $dbCategories = ["volume"];
    }

    if ($category !== "" && $category !== "read") {
        $results = GairmRecords::browseRecords($queryString, $dbCategories);
    }

    $browseOptions = [
        "year" => ["en" => "Browse By Year", "gd" => "[gd]Browse by Year"],
        "volume" => ["en" => "Browse By Volume", "gd" => "[gd]Browse by Volume"],
        "read" => ["en" => "Read Volume", "gd" => "[gd]Read Volume"]
    ];

    $years = GairmRecords::getYears();
    $yearSelectHtml = '<select id="yearOfPublication" name="yearOfPublication"><option value="">-- ' . Functions::e($browseOptions["year"][$lang]) . ' --</option>';
    foreach ($years as $year) {
        $yearEsc = Functions::e($year);
        $yearSelectHtml .= '<option value="' . $yearEsc . '">' . $yearEsc . '</option>';
    }
    $yearSelectHtml .= '</select>';

    $volumes = GairmRecords::getVolumes();
    $volumeSelectHtml = '<select id="volume" name="volume"><option value="">-- ' . Functions::e($browseOptions["volume"][$lang]) . ' --</option>';
    $readVolumeSelectHtml = '<select id="read" name="read"><option value="">-- ' . Functions::e($browseOptions["read"][$lang]) . ' --</option>';

    foreach ($volumes as $volume) {
        $volEsc = Functions::e($volume);
        $volumeSelectHtml .= '<option value="' . $volEsc . '">' . $volEsc . '</option>';
        if ((int)$volume < 11) {
            $readVolumeSelectHtml .= '<option value="' . $volEsc . '">' . $volEsc . '</option>';
        }
    }

    $volumeSelectHtml .= '</select>';
    $readVolumeSelectHtml .= '</select>';

    echo <<<HTML
		<div class="gairmCategory">{$readVolumeSelectHtml}</div>
		<div class="gairmCategory">{$yearSelectHtml}</div>
		<div class="gairmCategory">{$volumeSelectHtml}</div>
HTML;
}

// ---------- Results rendering ----------
$resultsHtml = '<img id="gairmImage" src="/images/gairm/gairmCartoon.jpg" alt="Gairm Cartoon Image" width="730"/>';

if (!empty($queryString)) {

    $encodedQuery = rawurlencode((string)($_SERVER['QUERY_STRING'] ?? ''));

    if (empty($results)) {
        $resultsHtml = "<h2>There were no results for " . Functions::e($queryString) . "</h2>";
    } else {
        $numResults = count($results);
        $resultsTagline = ($numResults === 1) ? "There was one result for " : "There were {$numResults} results for ";

        if (($category ?? '') === "yearOfPublication") {
            $category = "year";
        }

        $displayQuery = str_replace(['||', '|'], ['"', ', '], $queryString);
        $displayQuery = trim($displayQuery, ', ');

        $resultsTagline .= ucfirst((string)($category ?? '')) . ": " . $displayQuery;

        $headings = [
            "title" => ["en"=>"Title", "gd"=>"[gd]Title"],
            "surname" => ["en"=>"Last Name", "gd"=>"[gd]Last Name"],
            "firstname" => ["en"=>"First Name", "gd"=>"[gd]First Name"],
            "origin" => ["en"=>"Origin", "gd"=>"[gd]Origin"],
            "year" => ["en"=>"Year", "gd"=>"[gd]Year"],
            "info" => ["en"=>"Info", "gd"=>"Fios"],
            "read" => ["en"=>"Read", "gd"=>"[gd]Read"],
        ];

        // Precompute headings and tagline for heredoc
        $resultsTaglineEsc = Functions::e($resultsTagline);
        $hTitle = Functions::e($headings["title"][$lang]);
        $hSurname = Functions::e($headings["surname"][$lang]);
        $hFirst = Functions::e($headings["firstname"][$lang]);
        $hOrigin = Functions::e($headings["origin"][$lang]);
        $hYear = Functions::e($headings["year"][$lang]);
        $hInfo = Functions::e($headings["info"][$lang]);
        $hRead = Functions::e($headings["read"][$lang]);

        $resultsHtml = <<<HTML
			<h2>{$resultsTaglineEsc}</h2>
			<table id="gairmResultsTable" class="tablesorter">
				<thead>
					<tr>
						<th class="header">{$hTitle}</th>
						<th class="header">{$hSurname}</th>
						<th class="header" id="firstNameCol">{$hFirst}</th>
						<th class="header">{$hOrigin}</th>
						<th class="header headerSortUp">{$hYear}</th>
						<th>{$hInfo}</th>
						<th>{$hRead}</th>
					</tr>
				</thead>
				<tbody>
HTML;

        $view = ["en" => "view", "gd" => "[gd]view"];
        $viewLbl = Functions::e($view[$lang]);

        foreach ($results as $id => $gairmRecord) {
            $idInt = (int)$id;

            $origin = $gairmRecord->getOrigin();
            $origin = ($origin === "/") ? "" : (string)$origin;

            $volume = (int)$gairmRecord->getVolume();
            $page = (int)$gairmRecord->getFirstPage();

            $volFirstPage = (int)GairmRecords::getFirstPageNoInVolume($volume);
            if ($volFirstPage > 1) {
                $page = ($page - $volFirstPage) + 3;
            }

            $readLinkHtml = "";
            if ($volume < 11) {
                $readUrl = "/gairm/read.php?sub=vol/{$volume}#page/n{$page}/mode/2up";
                $readLinkHtml = '<a class="gairmViewLink" target="_blank" rel="noopener noreferrer" alt="View text" href="' . Functions::e($readUrl) . '">' . $viewLbl . '</a>';
            }

            $title = Functions::e($gairmRecord->getTitle());
            $last  = Functions::e($gairmRecord->getLastName());
            $first = Functions::e($gairmRecord->getFirstName());
            $originEsc = Functions::e($origin);
            $yearPub = Functions::e($gairmRecord->getYearOfPublication());

            $resultsHtml .= <<<HTML
					<tr>
						<td>{$title}</td>
						<td>{$last}</td>
						<td>{$first}</td>
						<td>{$originEsc}</td>
						<td>&nbsp;&nbsp;{$yearPub}&nbsp;&nbsp;</td>
						<td><a href="view.php?id={$idInt}&query={$encodedQuery}" id="gairmRecord_{$idInt}" class="gairmViewLink" title="View record">{$viewLbl}</a></td>
						<td>{$readLinkHtml}</td>
					</tr>
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
			let url = '/gairm/read.php?sub=vol/' + $(this).val();
			window.open(url, '_blank');
		});
		$('#yearOfPublication').change(function() {
			window.location.href = '/gairm/browse/year/' + $(this).val();
		});
		$('#author').change(function() {
			window.location.href = '/gairm/browse/author/' + $(this).val();
		});
		$('#volume').change(function() {
			window.location.href = '/gairm/browse/volume/' + $(this).val();
		});
		$('#pdfVolume').on('click', function() {
			window.open('/gairm/pdf.php?format=volume');
		});
		$('#pdfAuthor').on('click', function() {
			window.open('/gairm/pdf.php?format=author');
		});
	});
</script>
JS;

require_once '../includes/htmlFooter.php';