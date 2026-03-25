<?php

//ini_set("display_errors", 1);

require_once '../includes/include.php';

// CSRF protection
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
    Csrf::validateRequest();
}

// initialise page variables
$lenitedHtml = '';
$accentSelectedHtml = '';
$searchAllHtml = '';
$searchHeadHtml = '';
$searchDefHtml = '';
$resultsHtml = '';
$query = '';

$pageTitle = "Search the Fieldwork Archive";
$pageSlug = "fieldwork";

// Raw input for logic
$id = $_GET['id'] ?? '';
$q = $_GET['q'] ?? '';
$l = $_GET['l'] ?? '';
$as = $_GET['as'] ?? '';
$searchScope = $_GET['searchScope'] ?? '';

// Escaped values for output only
$idHtml = Functions::e($id);
$qHtml = Functions::e($q);

$jsonId = json_encode($id);
$backLink = '/fieldwork/search?q=' . rawurlencode($q);

$javascriptBlock = <<<'HTML'
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>

<script type="text/javascript">
    var id = __ID__;

    $(document).ready(function () {
        
        $('#fieldworkSearchBox form').on('submit', function () {
            $('#loading').show();
        });
    
        if ($('#slideshowSlides').length) {
            $('#slideshowSlides').slick({
                autoplay: true,
                autoplaySpeed: 4000,
                arrows: false,
                dots: false,
                fade: true,
                speed: 1500
            });
        }


        $('#reset').on('click', function () {
            $('#q').val('').focus();
            $('#lenited').prop('checked', false);
            $('#accInsens').prop('checked', false);
            $('#searchScopeAll').prop('checked', true);
        });

        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 500) {
                $('.scrollToTop').fadeIn();
            } else {
                $('.scrollToTop').fadeOut(10);
            }
        });

        $('.scrollToTop').on('click', function (e) {
            e.preventDefault();
            $('html, body').animate({scrollTop: 0}, 800);
        });

        if (id !== '') {
            goToByScroll(id);
        }
    });

    function updateFieldworkDoc(url, id, headword, query, resultId)
    {
        $.ajax({
            url: url,
            dataType: "html"
        }).done(function (data) {
            data = data.replace(
                '<a id="' + id + '"/>' + headword,
                '<a id="' + id + '"/><span class="hi">' + headword + '</span>'
            );

            data = highlightWords(data, query);

            $('#contentContainer').hide();
            $('#documentContainer').html(data).show();
            goToByScroll(id);
        });

        $('.backLink')
            .attr('href', '#')
            .attr(
                'onclick',
                "$('#documentContainer').hide();$('#contentContainer').show();goToByScroll('" + resultId + "');return false;"
            );

        $('.backToResults').show();
    }

    function goToByScroll(id)
    {
        var $target = $('#' + id);

        if (!$target.length) {
            return;
        }

        var idPos = $target.offset().top;
        $('html, body').animate({scrollTop: idPos - 30}, 'slow');
    }

    function highlightWords(line, word)
    {
        if (!word) return line;

        var escaped = word.replace(/[.*+?^${}()|[\]\\]/g, '\\\\$&');
        var regex = new RegExp('(' + escaped + ')', 'gi');

        return line.replace(regex, '<span class="hi">$1</span>');
    }

   
</script>
HTML;

$javascriptBlock = str_replace('__ID__', $jsonId, $javascriptBlock);

$cqpPage = true;

require_once '../includes/htmlHeader.php';

$enteredQuery = $q;

$slidesHtml = '';

$slides = array(
    "7" => array("Edinburgh C Ferguson moine", "", ""),
    "1" => array("Ardhasaig D J MacLeod wool", "", ""),
    "3" => array("Campbeltown M MacLeod moine", "", ""),
    "4" => array("Clachan N MacDonald taigh", "", ""),
    "5" => array("Clarkston R MacNeil aiteach", "", ""),
    "6" => array("Edinburgh C Ferguson moine", "", ""),
    "8" => array("Glasgow M MacLeod drawings", "", ""),
    "9" => array("Glasgow M MacLeod drawings", "", ""),
    "10" => array("Glasgow M MacLeod drawings", "", ""),
    "11" => array("Glasgow M MacLeod sheep", "", ""),
    "12" => array("Harris Gobhaig MacLennan eathar", "", ""),
    "13" => array("Lewis John Smith et al iasgach", "", ""),
    "14" => array("Lewis John Smith et al iasgach", "", ""),
    "15" => array("Lewis John Smith et al iasgach", "", ""),
    "16" => array("Lewis John Smith et al iasgach", "", ""),
    "17" => array("Lewis John Smith et al iasgach", "", ""),
    "18" => array("Lionel Junior Secondary personal misc", "", ""),
    "19" => array("Tiree Barrapol H MacLean agriculture", "", ""),
    "20" => array("Tiree Barrapol H MacLean agriculture", "", ""),
    "21" => array("Tiree Barrapol H MacLean agriculture", "", "")
);

foreach ($slides as $filename => $item) {
    $pageName = str_replace(' ', '', $item[0]);
    $encodedItem = base64_encode($pageName . "|{$item[1]}|{$item[2]}");

    $slidesHtml .= <<<HTML

<a href="/fieldwork/view/{$encodedItem}" title="DASG Fieldwork Item">
    <img src="/images/slides/{$filename}.jpg" alt="DASG Fieldwork Image"/>
</a>

HTML;
}

if ($l) {
    $lenitedHtml = 'checked="checked"';
}

if ($as) {
    $accentSelectedHtml = 'checked="checked"';
}

if ($searchScope === '' || $searchScope === 'all') {
    $searchAllHtml = 'checked="checked"';
} elseif ($searchScope === 'headOnly') {
    $searchHeadHtml = 'checked="checked"';
} elseif ($searchScope === 'defOnly') {
    $searchDefHtml = 'checked="checked"';
}

echo <<<HTML

<div id="dasg_cqp_main_menu">
    <div class="menuItem menuSelected">
        <a href="/fieldwork/search">Search</a>
    </div>
    <div class="menuItem">
        <a href="/fieldwork/browse">Browse</a>
    </div>
    <br class="clear"/>
</div>

<div class="backToResults clear">
    <a class="backLink" href="{$backLink}" title="Back to results">
        <br/>&lt; Back to results
    </a>
</div>

<div id="documentContainer"></div>

<div id="contentContainer">
    <div>
        <br/>
        <h2>Search</h2>

        <div id="fieldworkDoc">
            <div id="slideshowContainer">
                <div id="slideshowSlides">
                    {$slidesHtml}
                </div>
            </div>
        </div>

        <div class="searchColumn">
            <div id="fieldworkSearchBox">
                <form action="">
                    <input type="text" name="q" id="q" value="{$qHtml}"/>
                    <br/>

                    <div id="accentsBox">
                        <a href="#" onclick="addCharacterToSearch('à', 'q');return false;">à</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('è', 'q');return false;">è</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('ì', 'q');return false;">ì</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('ò', 'q');return false;">ò</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('ù', 'q');return false;">ù</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('á', 'q');return false;">á</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('é', 'q');return false;">é</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('í', 'q');return false;">í</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('ó', 'q');return false;">ó</a>&#160;&#160;
                        <a href="#" onclick="addCharacterToSearch('ú', 'q');return false;">ú</a>
                    </div>

                    <div id="wildcards">
                        <ul id="wildcardList">
                            <li>
                                <a href="#" onclick="addCharacterToSearch('?', 'q');return false;">?</a> - Any single letter
                            </li>
                            <li>
                                <a href="#" onclick="addCharacterToSearch('~', 'q');return false;">~</a> - Any sequence of vowels
                            </li>
                            <li>
                                <a href="#" onclick="addCharacterToSearch('*', 'q');return false;">*</a> - Sequence of any letters
                            </li>
                        </ul>
                    </div>

                    <label for="lenited">Lenited forms:</label>
                    <input type="checkbox" id="lenited" name="l" {$lenitedHtml}/>&#160;&#160;&#160;&#160;

                    <label for="accInsens">Accent insensitive:</label>
                    <input type="checkbox" id="accInsens" name="as" {$accentSelectedHtml}/>

                    <fieldset style="padding-top: 0;margin:0;">
                        <legend>Search Scope</legend>
                        <ul>
                            <li>
                                <label for="searchScopeAll">All:</label>
                                <input type="radio" id="searchScopeAll" name="searchScope" value="all" {$searchAllHtml}/>&nbsp;
                            </li>
                            <li>
                                <label for="searchScopeHead">Headwords Only:</label>
                                <input type="radio" id="searchScopeHead" name="searchScope" value="headOnly" {$searchHeadHtml}/>&nbsp;
                            </li>
                            <li>
                                <label for="searchScopeDef">Definitions Only:</label>
                                <input type="radio" id="searchScopeDef" name="searchScope" value="defOnly" {$searchDefHtml}/>
                            </li>
                        </ul>
                    </fieldset>

                    <br/>
                    <input id="search" class="dasg_medButton" type="submit" value="search"/>&#160;&#160;
                    <input id="reset" class="dasg_smlButton" type="button" value="reset"/>
                </form>
            </div>

            <img id="loading" alt="Loading..." src="/images/loading.gif" height="100px" style="display:none;"/>
HTML;

if (!empty($enteredQuery)) {

    $query = mb_strtolower(trim($enteredQuery), 'UTF-8');
    $query = str_replace("'", "’", $query);

    if ($l) {
        $query = Functions::getLenited($query);
    }

    try {
        $queryRegExp = Functions::buildFieldworkWildcardRegex($query);
    } catch (RuntimeException $e) {
        Functions::writeError("Regex query too large");
        require_once '../includes/htmlFooter.php';
        exit;
    }

    if ($as) {
        $queryRegExp = Functions::getAccentInsensitiveRegex($queryRegExp);
    }

    $pattern = '/(?<![' . ACCENT_CHARSET . '])(' . $queryRegExp . ')(?![' . ACCENT_CHARSET . '])/iu';

    $itemQ = '*//item';
    $results = array();

    foreach (glob($_SERVER['DOCUMENT_ROOT'] . '/xml/archive/*.xml') as $filepath) {
        $xml = simplexml_load_file($filepath);

        if ($xml === false) {
            continue;
        }

        $itemNodes = $xml->xpath($itemQ);
        $hitNodes = array();

        foreach ($itemNodes as $node) {
            $headwordText = (string)$node->headword;
            $descriptionText = (string)$node->description;

            if ($searchScope === 'headOnly') {
                if (preg_match($pattern, $headwordText)) {
                    $hitNodes[] = $node;
                }
            } elseif ($searchScope === 'defOnly') {
                if (preg_match($pattern, $descriptionText)) {
                    $hitNodes[] = $node;
                }
            } else {
                if (
                    preg_match($pattern, $descriptionText) ||
                    preg_match($pattern, $headwordText)
                ) {
                    $hitNodes[] = $node;
                }
            }
        }

        foreach ($hitNodes as $node) {
            $headword = (string)$node->headword;
            $origins = array();

            foreach ($xml->informant as $informant) {
                if (!empty($informant->origin)) {
                    $origins[] = (string)$informant->origin;
                }
            }

            $originText = implode(' or ', $origins);
            $nodeId = (string)$node->headword['id'];
            $description = (string)$node->description;
            $filename = basename($filepath);
            $item = str_replace('.xml', '', $filename);
            $url = '/ajax/fieldworkItem.php?item=' . $item;

            $results[] = array(
                'headword' => $headword,
                'origins' => $originText,
                'location' => (string)$xml->location,
                'title' => (string)$xml->title,
                'url' => $url,
                'id' => $nodeId,
                'description' => $description,
                'filename' => $filename
            );
        }
    }

    $numHits = count($results);

    if ($numHits === 0) {
        $resultsHtml = '<h3>There were no results for ' . Functions::e($enteredQuery) . '</h3>';
    } else {
        $resultsHtml = $numHits === 1
            ? '<h3>There was 1 hit for ' . Functions::e($enteredQuery) . '</h3>'
            : '<h3>There were ' . $numHits . ' hits for ' . Functions::e($enteredQuery) . '</h3>';

        usort($results, function ($a, $b) {
            return strcasecmp($a['headword'], $b['headword']);
        });

        $resultsHtml .= '<dl id="fieldworkResults">';

        $i = 0;

        foreach ($results as $result) {
            $i++;

            $headword = $result['headword'];

            if ($headword === '') {
                $headwordHtml = '--blank--';
            } else {
                $headwordHtml = Functions::highlightFieldworkText($headword, $pattern, false);
            }

            $descriptionHtml = '';
            if ($searchScope !== 'headOnly') {
                $descriptionHtml = Functions::highlightFieldworkText($result['description'], $pattern, true);
            }

            $filename = str_replace('.xml', '', $result['filename']);
            $encodedItem = base64_encode(
                $filename . '|' .
                $headword . '|' .
                $result['id'] . '||' .
                $enteredQuery . '|r' . $i . '|' .
                $l . '|' . $as . '|' . $searchScope
            );

            $geogHtml = '';
            if (!empty($result['origins'])) {
                $geogHtml = '<strong>Origin:</strong> <em>' . Functions::e($result['origins']) . '</em> <br/>';
            } elseif (!empty($result['location'])) {
                $geogHtml = '<strong>Location:</strong> <em>' . Functions::e($result['location']) . '</em> <br/>';
            }

            $titleHtml = Functions::e($result['title']);

            $resultsHtml .= <<<HTML
<dt>
    <a id="r{$i}" href="/fieldwork/view/{$encodedItem}" class="fieldwork_result">
        {$headwordHtml}
    </a>
</dt>
<dd>
    {$descriptionHtml}
    <div class="fieldworkMeta">
        {$geogHtml}
        <strong>Category:</strong> <em>{$titleHtml}</em>
    </div>
</dd>
HTML;
        }

        $resultsHtml .= '</dl>';
    }
}

echo <<<HTML
            <div id="searchResults">
                <div>{$resultsHtml}</div>
            </div>
        </div>
    </div>
    <br class="clear"/>
</div>

<a href="#" class="scrollToTop">^ Return To Top ^</a>

<div class="backToResults clear">
    <a class="backLink" href="{$backLink}" title="Back to results">
        <br/>&lt; Back to results
    </a>
</div>
HTML;

require_once '../includes/htmlFooter.php';