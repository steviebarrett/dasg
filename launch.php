<?php

require_once 'includes/include.php';

$pageSlug = "launch";
$pageTitle = "DASG Launch";

$cqpPage = true;

$javascriptBlock = <<<HTML

    <script type="text/javascript" src="/js/jquery.slides.min.js"/></script>

	<script type="text/javascript">
	
			$(document).ready(function() {
            
            $('#launchSlides').slidesjs({
                width: 600,
                height: 400,
                navigation: {
                  active: true
                },
                pagination: {
                  active: false
                },
                play: {
                  active: true,
                  auto: true,
                  interval: 3000,
                  swap: true,
                  effect: "fade"
                },
                effect: {
                    fade: {
                        speed: 1500,
                        crossfade: true
                    }
                }
            });               
        });
        
	</script>

HTML;
	
require_once 'includes/htmlHeader.php';

$slidesHtml = "";

foreach (glob(ROOT . "/images/launch/slides/*.jpg") as $filepath) {
	$fileparts = explode('/', $filepath);
	$file = array_pop($fileparts);
	$slidesHtml .= <<<HTML
        \n<img src="/images/launch/slides/{$file}" alt="DASG Launch Photo"/>
	
HTML;
}

echo <<<HTML

<p>The DASG website was launched in the Senate Room at the University Glasgow on Wednesday, 29 October 2014.</p>
 
<p>Professor Murray Pittock, Vice-Principal and Head of the College of Arts, welcomed a gathering of approximately 80 people to the University. Dr Ken Emond, Head of Research Awards at the British Academy, congratulated the DASG team on their hard work over the past 7 years and for bringing the DASG resources to the public via the new DASG website.</p>
 
<p>Professor Rob Ó Maolalaigh, Director of DASG, added his welcome and introduced the DASG project and its various components. He illustrated the value of the <a href="http://dasg.ac.uk/fieldwork">Faclan bhon t-Sluagh</a> and <a href="http://dasg.ac.uk/corpus/" title="Corpas na Gàidhlig">Corpas na Gàidhlig</a> resources. Dr Sìm Innes described plans for the <a href="http://dasg.ac.uk/seanchas/" title="Seanchas">Seanchas</a> project.</p>
 
<p>Olga Szczesnowicz illustrated how to use the <a href="http://dasg.ac.uk/fieldwork">Faclan bhon t-Sluagh</a> resource and Dr Mark McConville illustrated how <a href="http://dasg.ac.uk/corpus/" title="Corpas na Gàidhlig">Corpas na Gàidhlig</a> could be used. Stephen Barrett thanked the DASG team for their support and particularly the Arts IT support team who had been tremendously supportive over the years.</p>
 
<p>Professor (Emeritus) Donald Meek spoke of his experiences as Assistant Editor (1973–79) of the Historical Dictionary of Scottish Gaelic, drawing particular attention to computerisation work he had carried out with the assistance of Dr William Sharp. Professor Meek paid the DASG project a great honour when he presented DASG with a copy of his father's Gaelic sermons from Tiree.</p>
 
<p>Professor Ó Maolalaigh brought proceedings to a close by thanking the DASG and Faclair na Gàidhlig teams, all funders and many others for their support of the DASG project over the years. (Click <a href="/about/acknowledgements/en" title="Acknowledgements">here</a> to see a list of acknowledgements.)</p>

<div class="launchBlock1">
	<div class="launchImage">
		<img src="/images/launch/launch1.jpg" alt="DASG Launch Screenshot" width="600" height="400"/>
		<div class="launchImageTag">The DASG website is launched</div>
	</div> 
	
	<br/>
	<span class="attrib">British Academy, ‏@britac_news  Nov 3</span><br/>
	<span class="launchQuote">&#147;Great to see public release from British Academy Research Project.&#148;</span>
	<br/><br/><br/>
	<span class="attrib">Professor Hugh Cheape, Sabhal Mòr Ostaig</span><br/>
	<span class="launchQuote">&#147;This offers a resource on a scale hitherto unimaginable (without a lifetime's reading and cross-referencing).&#148;</span>
	<br/><br/><br/>
	<span class="attrib">Dr Will Lamb, University of Edinburgh</span><br/>
	<span class="launchQuote">&#147;A fantastic resource altogether. Congratulations!&#148;</span>
	
	
	<br class="clear"/>

</div>

<div class="launchBlock2">
	<div class="launchImage">
		<img src="/images/launch/audience.jpg" alt="Audience" width="600" height="343"/>
		<div class="launchImageTag">Professor Rob Ó Maolalaigh introduces DASG</div>
	</div>
	
	<br/>
	<span class="attrib">Glucksman Library, ‏@ullibrary  Oct 29</span><br/>
	<span class="launchQuote">&#147;Congratulations @GlasgowUni and @DASG_Glaschu on the launch of your new Gaelic archive.&#148;</span>
	<br/><br/><br/>
	<span class="attrib">Peadar Morgan, Bòrd na Gàidhlig</span><br/>
	<span class="launchQuote">&#147;A' dèanamh fiughair ri cosg ùine a' dol troimhe seo airson mo thlachd fhìn - a' coimhead fìor mhath!&#148;</span>
	<br/><br/><br/>
	<span class="attrib">Dr Cassie Smith-Christmas, University of Highlands and Islands</span><br/>
	<span class="launchQuote">&#147;This is a fantastic resource.&#148;</span>

	<br class="clear"/>
</div>

<div class="launchBlock1">
	<div class="launchImage">
		<img src="/images/launch/speakers.jpg" alt="Speakers" width="600" height="466"/>
		<div class="launchImageTag">Olga Szczesnowicz, Dr Mark McConville, Stephen Barrett, Professor Rob Ó Maolalaigh, Professor (Emeritus) Donald Meek</div>
	</div>
	
	<br/>
	<span class="attrib">Dr Anja Gunderloch, University of Edinburgh</span><br/>
	<span class="launchQuote">&#147;This is a fantastic resource which is going to be tremendously useful in a great many different ways. Nach math a rinn sibh!&#148;</span>
	<br/><br/><br/>
	<span class="attrib">Dr Sharon Arbuthnot, Queen's University Belfast</span><br/>
	<span class="launchQuote">&#147;This is a great resource and appropriate for a wide range of purposes.&#148;</span>
	<br/><br/><br/>
	<span class="attrib">Caoimhín Ó Donnaíle, Sabhal Mòr Ostaig</span><br/>
	<span class="launchQuote">&#147;’S e goireas dha-rìreabh math a tha seo do luchd na Gàidhlig. Sàr obair, deagh bheachd, deagh chur an gnìomh, agus uabhasach fhéin luachmhar.&#148;</span>
	
	<br class="clear"/>
</div>
 
<div class="launchBlock2">
	<div class="launchImage">
		<img src="/images/launch/gift.jpg" alt="Presentation" width="400" height="302"/>
		<div class="launchImageTag">Professor Donald Meek (right) presents Professor Rob Ó Maolalaigh<br/>with his father's Gaelic sermons from Tiree.</div>
	</div>
	
	<br/>
	<span class="attrib">Professor David Adger, Queen Mary University of London</span><br/>
	<span class="launchQuote">&#147;Brilliant initiative!&#148;</span>
	<br/><br/><br/>
	<span class="attrib">Dr Sheila Kidd, University of Glasgow</span><br/>
	<span class="launchQuote">&#147;Really impressed with this, quick and easy to use. I could spend hours on this!&#148;</span>
	<br/><br/><br/>
	<span class="attrib">Arthur Holmer, Lund University</span><br/>
	<span class="launchQuote">&#147;A wonderful tool! Keep up the good work!&#148;</span>
	
	
	<br class="clear"/>
</div>

<div class="launchBlock1">
	<div class="launchImage">
		<img src="/images/launch/team.jpg" alt="DASG Team and Speakers" width="600" height="382"/>
		<div class="launchImageTag">(Back Row) Professor Rob Ó Maolalaigh, Dr Mark McConville, Shelagh Campbell, Stephen Barrett,<br/>Shona Masson, Dr Anndra Wiseman, Abigail Lightbody, Ruaraidh MacIntyre, Alana MacInnes, Olga Szczesnowicz<br/>
		(Front Row) Professor Donald Meek, Angus John Smith, Lorna Pike.</div>
	</div>
	
	<br/><br/>
	<span class="attrib">Seonaidh Caimbeul</span><br/>
	<span class="launchQuote">&#147;This is a wonderful resource.&#148;</span>
	<br/><br/><br/>
	<span class="attrib">EUROLANG, @EUROLANG • Oct 29</span><br/>
	<span class="launchQuote">&#147;Congratulations/ Meal do naidheachd everyone at @DASG_Glaschu on the launch of the archive&#148;</span>
	
	<br class="clear"/>
</div>

<div id="slideshowContainer" style="text-align:center;">

	<h3>A selection of photographs from the launch:</h3>
	<div id="launchSlides">
		{$slidesHtml}
	</div>
	<span class="attrib">Photos kindly supplied by Fiona Dunn</span>
</div>
HTML;

require_once 'includes/htmlFooter.php';

