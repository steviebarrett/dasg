<?php

require_once 'includes/include.php';

$titleText = array("en"=>"Home", "gd"=>"Dachaigh");

//Gaelic for page title too!
$pageTitle = $lang == "gd" ? "Dachaigh airson Stòras na Gàidhlig" : "Digital Archive of Scottish Gaelic";
$seoTitle = "DASG - " . $pageTitle;

$pageSlug = "home";

$javascriptBlock = <<<HTML

<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

HTML;

require_once 'includes/htmlHeader.php';

$mainText["en"] = <<<HTML
<p>Welcome to DASG, the Digital Archive of Scottish Gaelic, the <a href="https://www.gla.ac.uk" title="University of Glasgow" target="_blank">University of Glasgow's</a> online repository of digitised texts, lexical resources and audio recordings for Scottish Gaelic. DASG has three main components, <a href="/corpus/" title="Corpas na Gàidhlig">Corpas na Gàidhlig</a>, the <a href="/fieldwork" title="Fieldwork Archive">Fieldwork Archive</a> and our <a href="/audio" title="Audio Archive">Audio Archive</a>.</p>

<p><a href="/corpus/" title="Corpas na Gàidhlig">Corpas na Gàidhlig</a> aims to provide a comprehensive electronic corpus of Scottish Gaelic texts for students and researchers of Scottish Gaelic language, literature and culture. <a href="/corpus/" title="Corpas na Gàidhlig">Corpas na Gàidhlig</a> also provides the textual basis for the interuniversity project, <a href="http://www.faclair.ac.uk" target="_blank">Faclair na Gàidhlig</a> (Dictionary of the Scottish Gaelic Language), upon which the future historical dictionary of Gaelic will be based.</p>

<p>The DASG <a href="/fieldwork" title="Fieldwork Archive">Fieldwork Archive</a> consists of a collection of vernacular materials (questionnaires, wordlists and sound recordings) collected throughout Gaelic Scotland and in Nova Scotia between the 1960s and 1980s as part of data collection for the <a href="/about/hdsg/{$lang}" title="HDSG">Historical Dictionary of Scottish Gaelic</a> (HDSG) project, which was based at the University of Glasgow's Department of Celtic between 1966 and 1997.</p>

<p>Work on the <a href="/audio" title="Audio Archive">Cluas ri Claisneachd Audio Archive</a> began in 2015 with the aim of making all recordings in the possession of Celtic and Gaelic at the University of Glasgow available online. Our audio files are published freely online with a full, downloadable transcription, a detailed contents menu and subtitles. They contain both English and Gaelic and a wide variety of dialects and accents.</p>

<p>Your use of this website is subject to our <a href="/about/terms/{$lang}" title="Terms and Conditions">terms and conditions</a>. Please read them carefully before accessing any other pages in the Site.</p>

<p>If you use the DASG resources in your research, we would ask that DASG is acknowledged appropriately. For suggestions on how to cite DASG, please <a href="/about/cite/{$lang}" title="Cite DASG">click here</a>.</p>

<p>We are very interested to receive references to and copies of publications utilising DASG resources. These will be listed in the <a href="/about/publications/{$lang}" title="DASG Publications">publications</a> part of the DASG website.</p>

<hr>

<p>The <a href="/LIL/" title="Language in Lyrics" target="_blank"><strong>Nova Scotia Gaelic Song Index</strong></a> is a searchable list of more than 6,000 Gaelic songs made, sung, or published by Gaels in Nova Scotia.</p>

<!--p><span class="error">New project</span> – <a href="/gairm/" title="Gairm Online"><strong>Gairm Online</strong></a>. We are working to put all of Gairm online. The first ten issues went live on our website on 20 September 2022, marking the 70th anniversary of the periodical. 
</p>

<p><span class="error"><a href="/audio/about/cin/en" title="CIN MacLeod Audio Archive">Latest addition to the Audio Archive</a></span> – Recordings made by Professor Calum Iain N. MacLeod (1913–1977) in Nova Scotia, including collected recordings from other parts of Canada, USA and Scotland. </p>

<p><span class="error"><a href="/briathradan/" target="_blank" title="Am Briathradan">Am Briathradan</a></span> – online terminology system.</p-->

HTML;

$mainText["gd"] = <<<HTML

<p>Fàilte gu DASG, Dachaigh airson Stòras na Gàidhlig, tasgadh air-loidhne <a href="https://www.gla.ac.uk" title="Oilthigh Ghlaschu" target="_blank">Oilthigh Ghlaschu</a> airson sgrìobhainnean didseatach, goireasan leicseachail agus clàraidhean airson Gàidhlig na h-Alba. Tha trì roinnean ann an DASG, <a href="/corpus/" title="Corpas na Gàidhlig">Corpas na Gàidhlig</a>, <a href="/fieldwork" title="Faclan bhon t-Sluagh">Faclan bhon t-Sluagh</a> agus <a href="/audio/about/crc/gd" title="Cluas ri Claisneachd">Cluas ri Claisneachd</a>.</p>

<p>Tha <a href="/corpus/" title="Corpas na Gàidhlig">Corpas na Gàidhlig</a> ag amas air corpas dealantach iomlan a thabhann de sgrìobhainnean Gàidhlig na h-Alba do dh’oileanaich agus luchd-rannsachaidh air cànan, litreachas agus cultar Gàidhlig na h-Alba. Tha <a href="/corpus/" title="Corpas na Gàidhlig">Corpas na Gàidhlig</a> a’ toirt stèidh theacsail airson a’ phròiseict eadar-oilthigheil, <a href="http://www.faclair.ac.uk/" target="_blank" title="Faclair na Gàidhlig">Faclair na Gàidhlig</a>, air am bi faclair eachdraidheil na Gàidhlig air a stèidheachadh.</p>

<p>Is e cruinneachadh de dh’fhiosrachadh a chaidh fhaighinn bhon t-sluagh a tha ann am <a href="/fieldwork" title="Faclan bhon t-Sluagh">Faclan bhon t-Sluagh</a> (ceisteachain, liostaichean-fhacal agus clàraidhean fuaim) bho air feadh sgìrean Gàidhealach na h-Alba agus Alba Nuadh eadar na 1960an agus na 1980an mar phàirt dhen chruinneachadh fiosrachaidh airson <a href="/about/hdsg/gd" title="Faclair Eachdraidheil na Gàidhlig (FEG)">Faclair Eachdraidheil na Gàidhlig (FEG)</a>, a bha stèidhte aig Roinn Ceiltis Oilthigh Ghlaschu eadar 1966 agus 1997.</p>

<p>Thòisich obair air <a href="/audio/about/crc/gd" title="Cluas ri Claisneachd">Cluas ri Claisneachd</a> ann an 2015, is e na amas gum biodh gach clàradh a bha aig Roinn na Ceiltis is na Gàidhlig aig Oilthigh Ghlaschu ri fhaotainn air-loidhne. Tha na clàraidhean rim faotainn saor an-asgaidh, cuide ri tar-sgrìobhaidhean slàn a thèid a luchdachadh a-nuas, fiosrachadh mionaideach air gach cuspair a tha anns a’ chlàr, agus fo-thiotalan. Tha measgachadh de Bheurla agus Gàidhlig rin cluinntinn anns na clàraidhean, le farsaingeachd de dhualchainntean agus blasan-cainnt.</p>

<p>Tha <a href="/about/terms/gd" title="riaghailtean cleachdaidh">riaghailtean cleachdaidh</a> ann airson na làraich seo. Leugh gu cùramach iad mus tèid thu gu duilleag sam bith eile air an làraich.</p>

<p>Ma chleachdas tu goireasan DASG nad chuid rannsachaidh, dh’iarramaid gum biodh aithne iomchaidh air a toirt. Gus molaidhean air mar a bheirear iomradh air DASG, brùth <a href="/about/cite/gd" title="Mar a bheirear iomradh">an seo</a>.</p>

<p>Tha ùidh mhòr againn ann a bhith a’ faighinn iomraidhean agus leth-bhreacan de dh’fhoillseachaidhean a bhios a’ cleachdadh goireasan DASG. Thèid iomradh a thoirt orra ann an earrann nam <a href="/about/publications/gd" title="Foillseachaidhean">foillseachaidhean</a> air làrach-lìn DASG.</p>

<hr>

<p>Tha <a href="/LIL/" title="Language in Lyrics" target="_blank"><strong>Clàr-amais Òrain Ghàidhlig na h-Albann Nuaidh</strong></a> na liosta de chòrr is 6,000 òran a bha air an dèanamh, air an gabhail no a nochd ann an clò aig na Gàidheil ann an Alba Nuaidh.</p>

<!--p><span class="error">Pròiseact ÙR</span> – <a href="/gairm/" title="Gairm Online"><strong>Gairm Online</strong></a>. Tha sinn ag obair gus Gairm air fad a chur air-loidhne. Chuireadh na ciad deich àireamhan suas air an làraich-lìn againn air 20 Sultain 2022, a’ comharrachadh 70mh ceann-bliadhna an ràitheachain. </p>

<p><span class="error"><a href="/audio/about/cin/gd" title="CIN MacLeod Audio Archive">ÙR do Chluas ri Claisneachd</a></span> – Clàraidhean-fuaime a rinn an t-Ollamh Calum Iain M. MacLeòid (1913–1977) ann an Alba Nuadh, agus clàraidhean a chruinnich e à ceàrnaidhean eile de Chanada, na Stàitean Aonaichte agus Alba. </p>

<p><span class="error"><a href="/briathradan/" target="_blank" title="Am Briathradan">Am Briathradan</a></span> – siostam briathrachais air-loidhne.</p-->

HTML;

require_once 'includes/sideMenu.php';

echo <<<HTML
				<div id="homeImage">
					<img src="/images/homeBooks.jpg" alt="Books" width="345px" height="380px"/>
				</div>
		
				<div id="homeText">
					<div id="homeBanner"><h2>{$titleText[$lang]}</h2></div>
					<div>
						{$mainText[$lang]}					
					</div>
				</div>
HTML;

/*
echo <<<HTML
	<h2>The DASG website is currently experiencing technical difficulties.</h2>

	<h3>Apologies for any inconvenience.<h3>
HTML;
*/
require_once 'includes/htmlFooter.php';

