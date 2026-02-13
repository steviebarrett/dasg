<?php


namespace views;

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}
class team
{
	public function show() {
		$html = <<<HTML
            <div class="container page-content pt-5">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <h3>An Sgioba | Project Team</h3>
                    </div>
                </div>
                <div class="row team pb-5">
                    <div class="col-lg-10 offset-lg-1">
                        <p>The Language in Lyrics project has involved a number of people over the years, although three have remained consistent from the beginning. Dr. Heather Sparling is the project director, Màiri Britton is the project manager, and Mary Jane Lamond is the Nova Scotia corpus assistant. Although each team member has her distinct responsibilities, in truth, we worked together throughout the project.</p>
                    </div>

                    <div class="col-lg-10 offset-lg-1 my-3 team-member">
                        <img src='includes/img/Heather.jpg'>
                        <h4>Heather Sparling</h4>

                        <p>Heather Sparling is the <a href='https://www.chairs-chaires.gc.ca/chairholders-titulaires/profile-eng.aspx?profileId=4658' target='_blank'>Canada Research Chair in Musical Traditions</a> and Professor of Ethnomusicology at <a href='https://www.cbu.ca/' target='_blank'>Cape Breton University</a>. She researches Gaelic song and vernacular dance of Nova Scotia, as well as disaster songs of Atlantic Canada. She is researching how music can drive language revitalization. She is a fluent Gaelic learner and learning the fiddle. </p>
                    </div>

                    <div class="col-lg-10 offset-lg-1 my-3 team-member">
                        <img src='includes/img/Mairi.jpg'>
                        <h4>Màiri Britton</h4>

                        <p>Màiri Britton is a Gaelic educator and musician based in Mabou and Antigonish. Originally from Edinburgh, she moved to Nova Scotia in 2016 to take up the post of Gaelic language instructor at <a href='https://sites.stfx.ca/celtic_studies/' target='_blank'>St. Francis Xavier University</a>, where she has now taught for six years. She also teaches immersion programs for <a href='https://gaeliccollege.edu/faculty/mairi-britton/' target='_blank'>Colaisde na Gàidhlig</a> and runs local community classes and workshop programmes in the province. Màiri performs regularly as a Gaelic singer, step dancer and harpist, both as a solo artist and as a member of the four-piece Gaelic trad group <a href='http://www.farsanband.com/' target='_blank'>Fàrsan</a>.</p>
                    </div>

                    <div class="col-lg-10 offset-lg-1 my-3 team-member">
                        <img src='includes/img/MJ.jpg'>
                        <h4>Mary Jane Lamond</h4>

                        <p>Mary Jane is an internationally-renowned <a href='http://www.maryjanelamond.com/' target='_blank'>Gaelic singer</a> with an extensive knowledge of the Nova Scotia Gaelic song repertoire. She has recorded <a href='http://www.maryjanelamond.com/music/' target='_blank'>six albums</a> and worked in a number of different roles and projects focused on promoting and sharing the Gaelic language and culture of Nova Scotia. Alongside numerous Juno awards and ECMA nominations, Mary Jane received the prestigious <a href='https://artsns.ca/grants-programs/awards/portia-white-prize' target='_blank'>Portia White Prize</a> in 2010 in recognition of her efforts to preserve Gaelic culture through song. Mary Jane also worked on digitizing the catalogue for the <a href='http://gaelstream.stfx.ca/greenstone/cgi-bin/library.cgi?site=localhost&a=p&p=about&c=capebret&l=en&w=utf-8' target='_blank'>Cape Breton Gaelic Folklore Collection</a> while a student at St Francis Xavier University, and on the song content for the website <a href='http://www.androchaid.ca/' target='_blank'>An Drochaid Eadarainn</a>.</p>
                    </div>

                    <div class="col-10 offset-lg-1">
                        <p>Over the years, our work was supported by an outstanding team who helped us to research and transcribe songs, enter song data, proof-read texts, write blog posts, investigate software, and so much more!</p>
                        <ul>
                            <li>Daisy May Boyle</li>
                            <li>Kaleb DeLeskie</li>
                            <li>Johanna Huntley</li>
                            <li>Phaedra Laurie</li>
                            <li>Colleen Lynk</li>
                            <li>Edward MacDonell</li>
                            <li>Stephen MacIntyre</li>
                            <li>Stacey MacLean</li>
                            <li>Chelsey MacPherson</li>
                            <li>Trueman Matheson</li>
                            <li>Becca Ross</li>
                            <li>Aleen Stanton</li>
                            <li>Sìleas Tait</li>
                        </ul>
                    </div>
                </div>
            </div>

HTML;
		echo $html;
	}
}