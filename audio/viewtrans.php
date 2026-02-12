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
    'ABB_Tape2' => true,
    'ABB_Tape5' => true,
    'Calum_MacNeil_2' => true,
    'Calum_MacNeil_3' => true,
    'Calum_MacNeil_4' => true,
    'Calum_MacNeil_5' => true,
    'Calum_MacNeil_6' => true,
    'Courting_on_Scalpay' => true,
    'GnE_Agallamh_Alasdair_MacEachainn' => true,
    'GnE_Agallamh_Caitriona_NicCumhais' => true,
    'GnE_Agallamh_Domhnall_Fearghasdan' => true,
    'GnE_Agallamh_Floraidh_NicDhomhnaill' => true,
    'GnE_Agallamh_Iseabail_Graham' => true,
    'GnE_Agallamh_Mairi_NicAonghais' => true,
    'GnE_Agallamh_Mary_Ellen_Stiubhart' => true,
    'GnE_Agallamh_Niall_Domhnallach' => true,
    'GnE_Agallamh_Ruairidh_MacIllEathain' => true,
    'GnE_Agallamh_Seumas_Domhnallach' => true,
    'GnE_Agallamh_Seumas_MacIllEidich' => true,
    'GnE_Oran_Seumas_MacIllEidich' => true,
    'GU_AJ_Smith_01or02' => true,
    'GU_AJ_Smith_03' => true,
    'GU_AJ_Smith_04' => true,
    'GU_AJ_Smith_08' => true,
    'GU_AJ_Smith_15' => true,
    'GU_CIN_MacLeod_03' => true,
    'GU_CIN_MacLeod_28' => true,
    'GU_Mrs_Marion_Montgomery' => true,
    'GU_North_Argyll' => true,
    'GU_Sister_Beatons' => true,
    'GU_William_Mackay' => true,
    'Origins_and_Religion_from_Barra' => true,
    'Stories_and_Songs_from_Berneray' => true,
    'Stories_from_Gobhaig' => true,
    'Stories_from_Sollas' => true,
];

$ref = isset($_GET["ref"]) ? Functions::e($_GET["ref"]) : "" ;
if (!isset($ALLOWED_TRANSCRIPTIONS[$ref])) {
    Functions::writeError("Sorry, the requested resource could not be found");
} else {
    $arrayRef = $ALLOWED_TRANSCRIPTIONS[$ref];
    $transcription = file_get_contents('transcriptions/' . $arrayRef . '.txt');
}

//colour code the search term
$q = Functions::getAccentInsensitive($q, false);
$transcription = preg_replace("/{$q}/i", '<span class="highlight">$0</span>', $transcription);

echo nl2br($transcription);

require_once '../includes/htmlFooter.php';