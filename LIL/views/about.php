<?php


namespace views;


class about
{
	public function show() {
		$html = <<<HTML
            <div class="container page-content pt-5">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <h3>Mu Dheidhinn a’ Phròiseict | About the Project</h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <p>Language in Lyrics was a four-year <a href='https://www.sshrc-crsh.gc.ca/' target='_blank'>SSHRC</a>-funded research project through which the Nova Scotia Gaelic Song Index was developed. For more information about the evolution of the Index, and for information on other outcomes of the project (including the Gaelic Song Education Project, the “Cànain Cheòlmhor” symposium, and the special issue of the Journal of Multilingual and Multicultural Development on language revitalization with music), please visit the <a href='https://languageinlyrics.com/' target='_blank'>Language in Lyrics website</a>.</p>
                        <p>The index identifies more than 6,000 songs (although this includes duplicates) found in published books, newspapers, manuscripts, and archival recordings (a list of the sources we indexed can be seen <a href="index.php?m=faq&a=sources#heading-3" title="sources">here</a>). The index was initially designed to make it possible to choose a small set of song lyrics covering a range of time periods, dialects, subjects, and genres to be uploaded to <a href='https://dasg.ac.uk/corpus/' target='_blank'>Corpus na Gàidhlig</a>, hosted by the <a href='https://dasg.ac.uk/' target='_blank'>Digital Archive of Scottish Gaelic</a>, for the purposes of linguistic research and other projects, but it became a valuable project in and of itself, which is why we have made it publicly available here.</p>
                        <p>While a linguistic corpus can draw on many different kinds of sources, when thinking about building a Gaelic corpus for Nova Scotia, it made sense to start with songs. Anyone who has spent time among Gaels knows how we love to sing, and songs are an incredibly important aspect of Gaelic culture. Many people are inspired to start learning the Gaelic language by their love of the songs, and songs function as helpful teaching aides for language learning. They also span geographical locations, time periods, themes and linguistic registers, and are connected with different activities and forms of expression, from milling (or waulking) songs to laments to puirt-à-beul. A linguistic corpus – such as the one that is available through Corpus na Gàidhlig – serves as an important language collection that can be studied and used in various ways.</p>
                    </div>
                </div>
            </div>


HTML;
		echo $html;
	}
}