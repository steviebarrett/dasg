<?php

require_once 'includes/include.php';

$pageSlug = "taing";
$pageTitle = "Taing";

$cqpPage = true;

    


$javascriptBlock = <<<HTML

<script type="text/javascript" src="/js/jquery.titlesequence.js"></script>
		
<script type="text/javascript">
		
var terms = ["Brian Aitken",
"An t-Ollamh Anders Ahlqvist",
"An Dr Marc Alexander",
"Stephanie Alexander",
"Jean Anderson",
"An Dr Wendy Anderson",
"An Dr Douglas Ansdell",
"Isabel Banks",
"Mìcheal Bauer",
"Cathy Bell",
"Mike Black",
"Ailean Boidhd",
"Raymond Brasas",
"Liz Broe",
"An t-Ollamh Dauvit Broun",
"An Dr Michel Byrne",
"Alasdair Caimbeul",
"Maoilios Caimbeul",
"Tormod Caimbeul",
"Mairead Chaimbeul",
"Mòrag Chaimbeul",
"Catrìona Chaimbeul",
"An t-Ollamh Ùisdean Cheape",
"Andy Christian",
"An t-Ollamh Thomas Owen Clancy",
"An t-Ollamh John Corbett",
"Richard A. V. Cox",
"Artair Dòmhnallach",
"Iain Dòmhnallach",
"Mairead Dhòmhnallach",
"An Dr Aidan Doyle",
"Catrìona M. Dunn",
"Fiona Dunn",
"Wojtek Dziejma",
"An Dr Ken Emond",
"An Dr Katherine Forsyth",
"Anna C. Frater",
"Lisa Gallagher",
"Fiona Gehrmann",
"An Dr Anja Gunderloch",
"An Dr Anette Hagan",
"An Dr Anthony Harvey",
"Andrew Hawke",
"Donalda Henderson",
"Bill Innes",
"An t-Ollamh Christian Kay",
"An Dr Sheila Kidd",
"Ruggero Lancia",
"An Dr William Lamb",
"An t-Ollamh Pierre-Yves Lambert",
"Garbhan MacAoidh",
"Iain A. Mac a’ Phearsain",
"An Dr Aonghas MacCoinnich",
"Uilleam MacDhòmhnaill",
"Fearghas MacFhionnlaigh",
"An t-Ollamh Uilleam MacGill’Ìosa",
"Raghnall MacIlledhuibh",
"Gillebrìde MacIlleMhaoil",
"Donnchadh MacIllÌosa",
"Annot Macinnes",
"An Dr Catrìona Mackie",
"Ishbel Maclean",
"Fionnlagh MacLeòid",
"Iain MacLeòid",
"Dòmhnall Murchadh MacMhathain",
"Aonghas MacNeacail",
"An Dr Catherine Martin",
"An Dr Raibeart Leith MacThòmais†",
"Elizabeth McAlavey",
"An Dr Mark McConville",
"Chris McGlashan",
"An Dr Nancy McGuire",
"Pauline McLachlan",
"An t-Ollamh Dòmhnall E. Meek",
"Iain Moireasdan",
"Lachlann Moireasdan",
"An Dr Peadar Morgan",
"An t-Ollamh Joseph F. Nagy",
"Carol Nic a’ Ghobhainn",
"Agnes NicDhòmhnaill",
"Joan NicDhòmhnaill",
"Kathleen NicDhòmhnaill",
"Shona NicDhòmhnaill",
"Catrìona NicLeòid",
"Màiri NicLeòid",
"An Dr Donalda NicThòm",
"An t-Ollamh Kenneth Nilsen†",
"An t-Ollamh Colm Ó Baoill",
"An t-Ollamh Tomás Ó Cathasaigh",
"An Dr Brian Ó Curnáin",
"Ailig O’Henley",
"An t-Ollamh Ruairí Ó hUiginn",
"Kathryn O’Loan",
"An t-Ollamh Mícheál Ó Mainnín",
"Lorna Pike",
"An t-Ollamh Murray Pittock",
"Louise Pollock",
"Agnes Rennie",
"An t-Ollamh Boyd Robasdan",
"Margaret Ryan",
"Gillian Shaw",
"An t-Ollamh Piotr Stalmaszczyk",
"Lisa Storey",
"An t-Ollamh Elmar Ternes",
"Ashley Theunissen",
"An t-Ollamh Greg Toner",
"Rosemary Ward",
"An Dr Moray Watson",
"Seosamh Watson",
"Ronald Macaskill Watt",
"Anne Wheeler",
"Mona Wilson",
"Mary M. M. M. Yardley",
"Acair",
"Acadamaidh Bhreatainn",
"Bòrd na Gàidhlig",
"Ceiltis agus Eòlas na h-Alba, Oilthigh Dhùn Èideann",
"Ceiltis is Gàidhlig, Oilthigh Ghlaschu",
"Colaiste nan Ealain, Oilthigh Ghlaschu",
"Comhairle an Rannsachadh Eaconamaich agus Shòisealta (ESRC)",
"Comhairle Maoineachaidh na h-Alba",
"Comhairle nan Eilean",
"Comhairle Rannsachaidh nan Ealain is nan Daonnachdan (AHRC)",
"Comhairle nan Leabhraichean",
"Comunn na Gàidhlig",
"Faclair na Gàidhlig",
"Ionad Didseataidh Uibhist",
"Leabharlann Nàiseanta na h-Alba",
"Leasachadh agus Seann Oileanaich, Oilthigh Ghlaschu",
"Luchd-obrach, luchd-obrach saor-thoileach agus luchd-taic DASG (an-dràsta agus san àm a dh’fhalbh)",
"Oilthigh Dhùn Èideann",
"Oilthigh Ghlaschu",
"Sgoil nan Daonnachdan, Oilthigh Ghlaschu",
"Soillse",
"Tobar an Dualchais",
"Urras Brosnachaidh na Gàidhlig",
"Urras Nàiseanta na h-Alba"];

function rotateTerm() {
  var ct = $("#rotate").data("term") || 0;
  $("#rotate").data("term", ct == terms.length -1 ? 0 : ct + 1).text(terms[ct])
              .fadeIn(1000).delay(3000).fadeOut(600, rotateTerm);
}

$(rotateTerm);

		
</script>

HTML;

require_once 'includes/htmlHeader.php';


echo <<<HTML
	<h2 style="text-align:center;color:#666666;font-size:36pt;">Our thanks to</h2>
	<div style="text-align:center;font-size:48pt;padding:0px 0px 100px 0px;;color:#aaaaaa;">
		
		<br/>
		<span id="rotate"></span>
	</div>
HTML;

			
require_once 'includes/htmlFooter.php';
