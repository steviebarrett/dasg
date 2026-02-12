<?php
declare(strict_types=1);

$pageTitle = "Cluas ri Claisneachd";
$pageSlug = "audio";

$cqpPage = true;

$javascriptBlock = <<<HTML
<script>
	var elem;

	$(function() {

		var elems = $('span.highlight').toArray();
		var id = -1;
		$('#findPrev').addClass('disabled');

		$('.findTerm').on('click', function() {
			$('.highlight').removeClass('highlightStrong');
			if ($(this).attr('id') == 'findPrev') {
				id--;
			} else {
				id++;
				$('#findPrev').removeClass('disabled');
			}
			if (id >= elems.length-1) {
				id = elems.length-1;
				$('#findNext').addClass('disabled');
			} else if (id < 1) {
				id = 0;	
				$('#findPrev').addClass('disabled');
				$('#findNext').removeClass('disabled');
			} else {
				$('#findNext').removeClass('disabled');
			}
			elem = elems[id];
			$(elem).addClass('highlightStrong');
			var idPos = $(elem).offset().top;
            $('html,body').animate({scrollTop: idPos-150},'fast');
		});

		$('#transTop').on('click', function() {
			$('html,body').animate({scrollTop: 0},'fast');
			id = -1;
			$('#findPrev').addClass('disabled');
			$('#findNext').removeClass('disabled');
		});
	});
</script>
HTML;

require_once '../includes/include.php';
require_once '../includes/htmlHeader.php';

$q = isset($_GET["q"]) ? Functions::e($_GET["q"]) : "" ;
echo <<<HTML
	<div class="fixedNav">
		<h4 class="transEx">{$q}</h4>
		<div class="controlBar">
			<input id="findPrev" class="findTerm" value="prev" type="button"/>
			<input id="findNext" class="findTerm" value="next" type="button"/>
		</div>
		<input type="button" id="transTop" value="back to top" type="button"/>
	</div>
HTML;

$ALLOWED_TRANSCRIPTIONS = [
    'ABB_Tape2' => 'ABB_Tape2',
    'ABB_Tape5' => 'ABB_Tape5',
    'Calum_MacNeil_2' => 'Calum_MacNeil_2',
    'Calum_MacNeil_3' => 'Calum_MacNeil_3',
    'Calum_MacNeil_4' => 'Calum_MacNeil_4',
    'Calum_MacNeil_5' => 'Calum_MacNeil_5',
    'Calum_MacNeil_6' => 'Calum_MacNeil_6',
    'Courting_on_Scalpay' => 'Courting_on_Scalpay',
    'GnE_Agallamh_Alasdair_MacEachainn' => 'GnE_Agallamh_Alasdair_MacEachainn',
    'GnE_Agallamh_Caitriona_NicCumhais' => 'GnE_Agallamh_Caitriona_NicCumhais',
    'GnE_Agallamh_Domhnall_Fearghasdan' => 'GnE_Agallamh_Domhnall_Fearghasdan',
    'GnE_Agallamh_Floraidh_NicDhomhnaill' => 'GnE_Agallamh_Floraidh_NicDhomhnaill',
    'GnE_Agallamh_Iseabail_Graham' => 'GnE_Agallamh_Iseabail_Graham',
    'GnE_Agallamh_Mairi_NicAonghais' => 'GnE_Agallamh_Mairi_NicAonghais',
    'GnE_Agallamh_Mary_Ellen_Stiubhart' => 'GnE_Agallamh_Mary_Ellen_Stiubhart',
    'GnE_Agallamh_Niall_Domhnallach' => 'GnE_Agallamh_Niall_Domhnallach',
    'GnE_Agallamh_Ruairidh_MacIllEathain' => 'GnE_Agallamh_Ruairidh_MacIllEathain',
    'GnE_Agallamh_Seumas_Domhnallach' => 'GnE_Agallamh_Seumas_Domhnallach',
    'GnE_Agallamh_Seumas_MacIllEidich' => 'GnE_Agallamh_Seumas_MacIllEidich',
    'GnE_Oran_Seumas_MacIllEidich' => 'GnE_Oran_Seumas_MacIllEidich',
    'GU_AJ_Smith_01or02' => 'GU_AJ_Smith_01or02',
    'GU_AJ_Smith_03' => 'GU_AJ_Smith_03',
    'GU_AJ_Smith_04' => 'GU_AJ_Smith_04',
    'GU_AJ_Smith_08' => 'GU_AJ_Smith_08',
    'GU_AJ_Smith_15' => 'GU_AJ_Smith_15',
    'GU_CIN_MacLeod_03' => 'GU_CIN_MacLeod_03',
    'GU_CIN_MacLeod_28' => 'GU_CIN_MacLeod_28',
    'GU_Mrs_Marion_Montgomery' => 'GU_Mrs_Marion_Montgomery',
    'GU_North_Argyll' => 'GU_North_Argyll',
    'GU_Sister_Beatons' => 'GU_Sister_Beatons',
    'GU_William_Mackay' => 'GU_William_Mackay',
    'Origins_and_Religion_from_Barra' => 'Origins_and_Religion_from_Barra',
    'Stories_and_Songs_from_Berneray' => 'Stories_and_Songs_from_Berneray',
    'Stories_from_Gobhaig' => 'Stories_from_Gobhaig',
    'Stories_from_Sollas' => 'Stories_from_Sollas',
];

$ref = isset($_GET["ref"]) ? Functions::e($_GET["ref"]) : "" ;
if (!isset($ALLOWED_TRANSCRIPTIONS[$ref])) {
    Functions::writeError("Sorry, the requested resource could not be found");
} else {
    $arrayRef = $ALLOWED_TRANSCRIPTIONS[$ref];
    $transcription = file_get_contents('transcriptions/' . $arrayRef . '.txt');
}

//colour code the search term
$q = (string)($q ?? '');
$q = Functions::getAccentInsensitive($q, false);

// treat as literal, not regex
$pattern = '/' . preg_quote($q, '/') . '/iu';
$transcription = preg_replace($pattern, '<span class="highlight">$0</span>', $transcription);

$escaped = htmlspecialchars($transcription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
// highlight on escaped text
$escaped = preg_replace($pattern, '<span class="highlight">$0</span>', $escaped);

echo nl2br($escaped);

require_once '../includes/htmlFooter.php';