<?php


namespace views;


class contact
{
	public function show() {
		$html = <<<HTML
            <div class="container page-content pt-5">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <h3>Sgrìobh Thugainn | Contact Us</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <p>Please contact Dr. Heather Sparling (Cape Breton University) with any questions about the Language in Lyrics project: <a href='mailto:heather_sparling@cbu.ca'>heather_sparling@cbu.ca</a>.</p>

                        <p>Please contact DASG with any technical questions about the Nova Scotia Gaelic song index or digitized lyrics: <a href='mailto:mail@dasg.ac.uk'>mail@dasg.ac.uk</a>. </p>

                        <p>Digitized song texts are accessible for purposes of education and research. We’ve indicated what we know about copyright and rights of privacy, publicity, or trademark. Due to the nature of archival collections, we are not always able to identify this information. We are eager to hear from any rights owners, so that we may obtain accurate information. Upon request, we’ll remove material from public view while we address a rights issue.</p>

                    </div>
                </div>
            </div>
HTML;
		echo $html;
	}
}