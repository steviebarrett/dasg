<?php


namespace views;


class gratitude
{
	public function show() {
		$html = <<<HTML
            <div class="container page-content pt-5">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <h3>Taing | Gratitude</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <p>The Language in Lyrics project was a collaboration between several individuals and organizations.</p>
                        <p>Individual partners include:</p>
                        <ul>
                            <li><a href='https://www.cbu.ca/faculty-staff/directory/heather-sparling/' target='_blank'>Dr. Heather Sparling</a> (Canada Research Chair and Professor of Ethnomusicology, Cape Breton University);</li>
                            <li><a href='https://www.gla.ac.uk/schools/humanities/staff/roibeardomaolalaigh/' target='_blank'>Professor Roibeard Ó Maolalaigh</a> (Vice Principal / Head of the College of Arts and Director of the Digital Archive of Scottish Gaelic, University of Glasgow); and</li>
                            <li>Lewis MacKinnon (Executive Director, Gaelic Affairs, Nova Scotia).</li>
                        </ul>
                        <p>Organizational partners include:</p>
                        <ul>
                            <li><a href='https://dasg.ac.uk/' target='_blank'>The Digital Archive of Scottish Gaelic</a> | <a href='https://dasg.ac.uk/?lang=gd' target='_blank'>Dachaigh airson Stòras na Gàidhlig</a> (University of Glasgow); </li>
                            <li><a href='https://beta.novascotia.ca/government/gaelic-affairs' target='_blank'>Gaelic Affairs</a> (Nova Scotia); </li>
                            <li><a href='https://www.cbu.ca/community/beaton-institute/' target='_blank'>The Beaton Institute</a> (Cape Breton University); and</li>
                            <li><a href='https://highlandvillage.novascotia.ca/' target='_blank'>Highland Village Museum | Baile nan Gàidheal</a></li>
                        </ul>

                        <p>Lewis MacKinnon and Gaelic Affairs inspired the initial project. We are grateful for their advice and assistance with Gaelic song transcriptions, song information, project promotion, and community connections.</p>

                        <p>The Highland Village Museum kindly allowed their fluent Gaelic-speaking staff to partake in our initial “transcription frolics” through which we crowd-sourced song transcriptions of archival audio recordings. Their staff also assisted with song information.</p>

                        <p>The Beaton Institute, particularly under the stewardship of Jane Arnold, generously gave the Language in Lyrics team full access to their extensive Gaelic collections and finding aids. To support the Language in Lyrics project, the Beaton Institute hired a full-time Gaelic-speaking researcher, Stacey MacLean, to assist with documenting songs and singers in their collections.</p>

                        <p>We would especially like to thank Robby Ó Maolalaigh and the amazing DASG team members who helped us from the beginning of the Language in Lyrics project. Dr. Mark McConville, DASG Project Manager, and Stephen Barrett, Research Systems Developer, were stalwart partners throughout the Language in Lyrics project, providing the Language in Lyrics team with much-needed advice about documenting Gaelic songs. Stephen, with Mark’s support, also created the amazing Nova Scotia Gaelic Song Index, spending many hours fixing bugs and tweaking code to get the index to function as we wanted it to. Olga Szczesnowicz provided early training for the Language in Lyrics team as well as resources for training our own team members. Abi Lightbody walked us through the <a href='https://dasg.ac.uk/audio/about/crc/en' target='_blank'>Cluas ri Claisneachd</a> project as a possible model for our own. We cannot say enough about the DASG team members, who were always incredibly supportive, generous with their time, and willing to share their considerable skills and knowledge.</p>

												<p>Thanks also to Lorna Pike, Project Manager of Faclair na Gàidhlig, who introduced us to the DASG team and who provided invaluable early advice on setting a reasonable and useful scope for the project.</p>
												
                        <p>We are so thankful to all the wonderfully amazing, passionate, and skilled folks who’ve worked for and on the Language in Lyrics project over the years. They are all Gaelic speakers to various degrees and were always keen to use whatever Gaelic they could when interacting with other team members. We honour their contributions to the team by using the names we used when working with them: Daisy May Boyle, Friseal DeLeskie, Ìomhair Dòmhnullach, Johanna Huntley, Phaedra Laurie, Cailín Linc, Steafan Mac an t-Saoir, Trueman MacMhathain, Tealsaidh Nic a' Phearsain, Steiseag NicIlleathain, Beathag Ros, Aleen Stanton, and Sìleas Tait. Mòran taing dhuibh uile!</p>

                        <p>We are also grateful for the opportunity to benefit from the valuable work of numerous individuals, past and present, who collected, transcribed, and published Gaelic songs in Nova Scotia. A full list of the sources from which we drew is available in the <a href='?m=faq'>User Guide</a>.</p>
		
												<p>We so appreciate the assistance we received from Susan Cameron at the Angus L. Macdonald library at St. Francis Xavier University, who provided team members with access to manuscripts, historical newspapers, and archival recordings, and from Mary Rose Laureys, who provided us with scans of hundreds of songs from The Casket.</p>
												
                        <!-- <p>We are also grateful for the opportunity to benefit from the valuable work of those who’ve collected, transcribed, and published Gaelic songs, including (among others) Effie Rankin, Anne Landin, Trueman Matheson, Michael Linkletter, and Kim Ells.</p> -->

                        <p>The Language in Lyrics project and the Nova Scotia Gaelic Song Index would not have been possible without generous funding from the <a href='https://www.sshrc-crsh.gc.ca/home-accueil-eng.aspx' target='_blank'>Social Sciences and Humanities Research Council (SSHRC) of Canada</a>, with additional funding provided by <a href='https://www.cbu.ca/' target='_blank'>Cape Breton University</a>. We are sincerely grateful for their willingness to fund research relating to Gaelic language and culture, and to believe that our group of partners and collaborators was the right team for the job.</p>

                        <p>Finally, we would like to thank Sarah Jones for the evocative Gaelic song map image, and <a href='https://novastream.ca/' target='_blank'>Novastream</a> for the beautiful web design.</p>
                        <!-- <p>The Language in Lyrics project is a collaboration between the <a href='http://www.chairs-chaires.gc.ca/chairholders-titulaires/profile-eng.aspx?profileId=3022' target='_blank'>Canada Research Chair in Musical Traditions</a> (Cape Breton University), the <a href='http://dasg.ac.uk/index.php' target='_blank'>Digital Archive of Scottish Gaelic</a> (Glasgow University) and <a href='https://gaelic.novascotia.ca/' target='_blank'>Nova Scotia Gaelic Affairs</a>. It is supported by a partnership with the Beaton Institute and the <a href='https://highlandvillage.novascotia.ca/' target='_blank'>Highland Village Museum (Baile nan Gàidheal)</a>, and is funded by a grant from the <a href='http://www.sshrc-crsh.gc.ca/home-accueil-eng.aspx' target='_blank'>Social Sciences and Humanities Research Council (SSHRC) of Canada.</a></p> -->
                        <!-- <p>For the purposes of this short-term three-year project, we have had to limit its scope to the primary partnerships listed above, but we hope to extend and expand the project in future and look forward to working with more project partners in time. In the meantime, we look forward to connecting with institutions and individuals in possession of Nova Scotia Gaelic song collections, such as <a href='http://stfx.libguides.com/celticstudies' target='_blank'>St. Francis Xavier University</a>, <a href='http://gaeliccollege.edu/' target='_blank'>Colaisde na Gàidhlig</a>, the <a href='http://www.celticmusiccentre.com/' target='_blank'>Celtic Music Interpretive Centre</a>, and many more.</p> -->
                    </div>
                    <div class="col-lg-10 offset-lg-1">
                        <div class="row logos">
                            <div class="col-md-6 col-lg-4 logo">
                                <a href="https://www.cbu.ca/" target="_blank">
                                    <img src='includes/img/cbu.png'>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-4 logo">
                                <a href="https://www.cbu.ca/community/beaton-institute/" target="_blank">
                                    <img src='includes/img/cbub.png'>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-4 logo">
                                <a href="https://www.sshrc-crsh.gc.ca/" target="_blank">
                                    <img src='includes/img/sshrc.png'>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-4 logo">
                                <a href="https://beta.novascotia.ca/government/gaelic-affairs" target="_blank">
                                    <img src='includes/img/gaelicaffairs.png'>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-4 logo">
                                <a href="https://dasg.ac.uk/" target="_blank">
                                    <img src='includes/img/dasg.png'>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-4 logo">
                                <a href="https://highlandvillage.novascotia.ca/" target="_blank">
                                    <img src='includes/img/hv.png'>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

HTML;
		echo $html;
	}
}