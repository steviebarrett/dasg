<?php


namespace views;


class faq
{
	public function show() {
		//quick hack to auto show the sources when linked from the about page
		$sourcesShow = $languageShow = "";
		if ($_GET["a"] == "sources") {
			$sourcesShow = "show";
		} else {
			$languageShow = "show";
		}
		$html = <<<HTML
            <!-- <div class='container-fluid banner px-0 mb-4'> -->
                <!-- <img src='/includes/img/fiddle-banner2.jpg' class='w-100'> -->
                <!-- <img src='/includes/img/map.png' class='d-table mx-auto'> -->
            <!-- </div> -->
            <div class="container faq py-5">
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <h2 class='page-title mb-4'>User Guide</h2>
                    </div>
                    <div class="col-lg-10 offset-lg-1">
                        <p>Please note that this index has not been designed for use by mobile devices.</p>
                        
                        <p>If searches seem not to be executing properly, try hard refreshing the page (Shift + 🔄). This will clear the page cache on your computer.</p>
                        
                        <p>This index is designed primarily to document the existence of Gaelic song texts and recordings; only a portion of the records are linked to song transcriptions. However, we have provided links to transcriptions or recordings online whenever possible.</p>

                        <div class="accordion mt-5" id="accordionFAQ">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-6">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-6" aria-expanded="true" aria-controls="collapse-6">
                                        Language
                                    </button>
                                </h2>
                                <div id="collapse-6" class="accordion-collapse collapse {$languageShow}" aria-labelledby="heading-6" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        <p>Some fields contain Gaelic and some contain English. Use Gaelic to search in the following fields: song title; alternative title; air; first-line verse; first-line chorus; composer patronymic; and (sometimes) title of publication. Other fields use English.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-7">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-7" aria-expanded="true" aria-controls="collapse-7">
                                        Spelling
                                    </button>
                                </h2>
                                <div id="collapse-7" class="accordion-collapse collapse" aria-labelledby="heading-7" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        <p>Gaelic text appears as in the original (e.g., first-line verse, first-line chorus, composer patronymic). Therefore, consider spelling variations and inconsistencies when conducting searches. Here are two websites for Gaelic spellings of <a href="https://en.wikipedia.org/wiki/List_of_Scottish_Gaelic_surnames" title="wikipedia" target="_blank">surnames</a> and <a href="https://en.wikipedia.org/wiki/List_of_Scottish_Gaelic_given_names" target="_blank" title="wikipedia">given names</a> that may be helpful. Adding a space between “Mac” and the rest of a surname may also return more search results. 
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-8">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-8" aria-expanded="true" aria-controls="collapse-8">
                                        Optional Search Tools
                                    </button>
                                </h2>
                                <div id="collapse-8" class="accordion-collapse collapse" aria-labelledby="heading-8" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        <p>Several optional tools have been integrated to increase the utility of the search function.</p>
                                        <ul>
                                            <li>“Accent insensitive”: when checked, the search will ignore accents, returning results both with and without accents (e.g., dèanamh and deanamh)</li>
                                            <li>“Lenition insensitive”: when checked, the search will return results both lenited and unlenited (e.g., bòrd and bhòrd)</li>
                                            <li>“Exact word only (no substring)”: when checked, the search will return the exact letter combination you requested, and not longer words in which those letters exist (e.g., seach not toiseachadh)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-1">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-1" aria-expanded="true" aria-controls="collapse-1">
                                        Boolean Operators (AND, OR, NOT)
                                    </button>
                                </h2>
                                <div id="collapse-1" class="accordion-collapse collapse" aria-labelledby="heading-1" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        <p>Boolean operators (AND, OR, and NOT) are available upon clicking on the + sign, and can operate with specific fields or all fields.</p>
                                        <ul>
                                            <li>AND: results will contain all of the words entered (narrows the search)</li>
                                            <li>OR: results will contain any of the words you have entered (expands the search)</li>
                                            <li>NOT: results will contain the word(s) you entered except the word following NOT (narrows the search)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-2">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-2" aria-expanded="true" aria-controls="collapse-2">
                                        Regular Expressions
                                    </button>
                                </h2>
                                <div id="collapse-2" class="accordion-collapse collapse" aria-labelledby="heading-2" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        <p>Regular expressions allow the use of special characters (e.g., *, ?, +) to designate a search pattern, such as within a search term. Regular expressions will not limit a search to the characters requested unless particular expressions are used to limit the search (see below). For example, searching for “tha” will return results such as “dùthaich,” “latha,” and “thall” as well as “tha.” </p>

																				<p>Please note that regular expressions cannot be used in conjunction with the lenition insensitive, accent insensitive, or exact search string options. </p>

																				<p>Please also note that regular expressions cannot recognize accented characters so your search results may exclude some results. Please use the “accent insensitive” option to search for accented letters.</p>
                                        <ul>
                                            <li>[[:alpha:]] will return any character (but not accented letters) (e.g., “th[[:alpha:]]g” will return “thug,” “thig,” “thogadh,” “thighearna,” etc.)</li>
																						<li>[[:alpha:]]* will return any number of characters (e.g., “th[[:alpha:]]*g” will return “thug,” “thig,” “thog,” “thuig,” “thainig,” “chuthag,” etc.)</li>
																						<li>[[:alpha:]]+ will return words that include the characters that precede [[:alpha:]]+; this can be helpful for finding all forms of a root word (e.g., “sheall[[:alpha:]]+” will return “shealladh” “sheallas,” and “shealltainn”)</li>
																						<li>[xyz] : will return words that match any one of the characters inside the square brackets; this can be helpful when searching for variant spellings (e.g., “aos[dt]a” will return “aosda” and “aosta”)</li>
																						<li>[^xyz] : will return words that include any of the characters except those inside the square brackets (e.g., “bh[^io]” will return “bha” but NOT “bhi” or “bho”)</li>
																						<li>^ : indicates the start of a word (e.g., “^seach[[:alpha:]]” will return “seachad,” “seachadh,” “seachran” but NOT “toiseachadh,” “maitseachan”)</li>
																						<li>$ : indicates the end of a word (e.g., “seach$” will return “maiseach,” “toiseach” but NOT “toiseachadh,” “seachad”)</li>
																						<li>? : will return words WITH the character that precedes ? OR without; this can be helpful for finding nouns in both basic and slenderized forms (e.g., “breacai?n” will return both “breacain” AND “breacan,” as well as “breacanan”)</li>
                                        </ul>

                                        <p>Examples:</p>
                                        <p>Searching for a combination of letters that appear in the middle of a word:
																					<ul><li>Searching for “sea[[:alpha:]]*dh” returns “toiseachadh,” “gaiseadh,” “màiseadh”</li></ul>
																				</p>

																				<p>Searching for a combination of letters after a set of specified letters:
																				<ul><li>Searching for “^sea[[:alpha:]]*dh” returns “sealladh,” “seanachaidh,” “seachadh”</li></ul></p>
																				
																				<p>Searching for a word with variant endings:
																				<ul><li>Searching for “^seall[[:alpha:]]*” returns “seall,” “sealladh,” “sealltainn”</li></ul></p>
																				
																				<p>Searching for a word with variant endings in both its lenited and unlenited forms:
																				<ul><li>Searching for “sh?eall[[:alpha:]]*” returns “seall,” “sheall,” “sheallas,” “shealladh,” “sealltainn”</li></ul></p>
																				
																				<p>Searching for variant spellings of the Gaelic form of “MacDonald”
																				<ul>
																					<li>Searching for “Dh?[oò]mhn[[:alpha:]]llai?ch” returns “Dòmhnallach,” “Dòmhnullach,” “Domhnallach,” “Domhnullach”
																					<ul>
																						<li>Dh? – this searches for words with and without the “h” (Domhnallach, Dhomhnallach)</li>
																						<li>[oò] – this searches for both an accented and unaccented “o” (Domhnallach, Dòmhnallach)</li>
																						<li>n[[:alpha:]]ll – this searches for any letter between “n” and “ll,” including “a” and “u” (Domhnallach, Domhnullach)</li>
																						<li>ai?ch – this searches for words both with and without the “i” (Domhnallach, Domhnallaich)</li>
																					</ul>	
																					<li>Searching for “Mac ?Dh?[oò]mhn[[:alpha:]]ill” returns “Mac Dhòmhnaill,” “MacDhomhnuill,” etc.</li>
																					<ul>
																						<li>Mac ? – this searches cases where there’s a space between “Mac” and the rest of the name, as well as cases where there’s no space</li>
																					</ul>
																				</ul>
												
                                        <p class='text-muted'>Additional information about regular expressions can be found <a target="_blank" href="https://www.geeksforgeeks.org/mysql-regular-expressions-regexp/" target="_blank" title="MySQL Regular Exressions">here</a></p>

                                    </div>
                                </div>
                            </div>



                            <!-- <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-4">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-4" aria-expanded="true" aria-controls="collapse-4">
                                        Purchasing Books
                                    </button>
                                </h2>
                                <div id="collapse-4" class="accordion-collapse collapse" aria-labelledby="heading-4" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        <p>Many of the books we referenced are available from major booksellers. But we would encourage you to support smaller, local publishers and sellers, such as:</p>
                                        <ul>
                                            <li><a href='https://nimbus.ca/' target='_blank'>Nimbus Books</a></li>
                                            <li><a href='https://highlandvillagegiftshop.ca/' target='_blank'>Highland Village gift shop</a></li>
                                            <li><a href='https://gaeliccollege.edu/shop/' target='_blank'>Gaelic College gift shop</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div> -->

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-5">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-5" aria-expanded="true" aria-controls="collapse-5">
                                       Fixed Field Term Lists
                                    </button>
                                </h2>
                                <div id="collapse-5" class="accordion-collapse collapse" aria-labelledby="heading-5" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        <p>The contents of most fields are “unfixed,” but some involve fixed lists. Knowing these fixed lists will make it easier to search in these fields. </p>
                                        <p>A note on song classifications: All classification systems are somewhat arbitrary, and ours is no exception. Some of the classification categories refer to the function and some refer to the subject matter of the songs. In addition to these broader categories, most songs have also been tagged with subjects (i.e. ‘boat’, ‘whisky’, ‘wedding’, ‘pig’ etc.) to assist with searching. However, as with classifications, these subjects were somewhat arbitrary, determined by the person entering the metadata.</p>
                                        <p><strong>Field: classifications</strong></p>
                                        <ul>
                                            <li>Ballad</li>
                                            <li>Bawdy</li>
                                            <li>Clapping</li>
                                            <li>Complaint</li>
                                            <li>Dialogue</li>
                                            <li>Drinking</li>
                                            <li>Elegy</li>
                                            <li>Exile</li>
                                            <li>Flyting</li>
                                            <li>Historical</li>
                                            <li>Homeland</li>
                                            <li>Humorous</li>
                                            <li>Instructive</li>
                                            <li>Lament</li>
                                            <li>Local events and characters</li>
                                            <li>Love</li>
                                            <li>Lullaby</li>
                                            <li>Macaronic</li>
                                            <li>Milling</li>
                                            <li>Nature</li>
                                            <li>Pibroch</li>
                                            <li>Political</li>
                                            <li>Puirt-a-beul</li>
                                            <li>Praise</li>
                                            <li>Rann / Duan</li>
                                            <li>Religious</li>
                                            <li>Sailing</li>
                                            <li>Satire</li>
                                            <li>Spiritual</li>
                                            <li>Supernatural</li>
                                            <li>Work</li>
                                        </ul>

                                        <p><strong>Field: Song Structure</strong></p>
                                        <ul>
                                            <li>One line verse</li>
                                            <li>Online verse / three line chorus</li>
                                            <li>One line verse / split chorus</li>
                                            <li>Two line verse</li>
                                            <li>Two line verse / two line chorus</li>
                                            <li>Two line verse / three line chorus</li>
                                            <li>Two line verse / four line chorus</li>
                                            <li>Two line verse / woven</li>
                                            <li>Three line verse</li>
                                            <li>Three line verse / two line chorus</li>
                                            <li>Three line verse / three line chorus</li>
                                            <li>Three line verse / four line chorus</li>
                                            <li>Three line verse / woven</li>
                                            <li>Four line verse</li>
                                            <li>Four line verse / two line chorus</li>
                                            <li>Four line verse / three line chorus</li>
                                            <li>Four line verse / four line chorus</li>
                                            <li>Four line verse / five line chorus</li>
                                            <li>Five line verse</li>
                                            <li>Six line verse</li>
                                            <li>Six line verse / two line chorus</li>
                                            <li>Six line verse / three line chorus</li>
                                            <li>Six line verse / four line chorus</li>
                                            <li>Seven line verse</li>
                                            <li>Eight line verse</li>
                                            <li>Eight line verse / four line chorus</li>
                                            <li>Eight line verse / eight line chorus</li>
                                            <li>Nine line verse</li>
                                            <li>Ten line verse</li>
                                            <li>Twelve line verse</li>
                                            <li>Sixteen line verse</li>
                                            <li>Woven</li>
                                            <li>Split chorus</li>
                                            <li>Woven / split chorus</li>
                                            <li>Irregular</li>
                                        </ul>

                                        <p><strong>Field: Place of Origin</strong></p>
                                        <ul>
                                            <li>Scotland</li>
                                            <li>Nova Scotia</li>
                                            <li>Prince Edward Island</li>
                                            <li>Ontario</li>
                                            <li>United States </li>
                                            <li>Other </li>
                                            <!-- <li>Unknown</li> -->
                                        </ul>

                                        <p><strong>Field: Composer Gender</strong></p>
                                        <ul>
                                            <li>Male</li>
                                            <li>Female</li>
                                            <li>Other</li>
                                            <!-- <li>Unknown</li> -->
                                        </ul>

                                        <p><strong>Field: General Material Description</strong></p>
                                        <ul>
                                            <li>Sound recording</li>
                                            <li>Manuscript</li>
                                            <li>Publication</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-3">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-3" aria-expanded="true" aria-controls="collapse-3">
                                        List of Song Sources
                                    </button>
                                </h2>
                                <div id="collapse-3" class="accordion-collapse collapse {$sourcesShow}" aria-labelledby="heading-3" data-bs-parent="#accordionFAQ">
                                    <div class="accordion-body">
                                        <p>The following sources were indexed:</p>
                                        <ul>
                                            <li>Am Bràighe (quarterly newspaper, 1993-2001)</li>
                                            <li>An Cuairtear Òg Gaelach (monthly magazine, 1851)</li>
                                            <li>Angus R. MacDonald collection, Beaton Institute (manuscript, 1928-1973)</li>
                                            <li>Angus Stephen Beaton collection, St. Francis Xavier University Celtic Collection (manuscript, 18th-20th century)</li>
                                            <li>Às a’ Bhràighe, Effie Rankin (book, 2005)</li>
                                            <li>Bàrdachd à Albainn Nuaidh, C. I. N. MacLeod (book, 1970)</li>
                                            <li>Beaton Institute audio collection (audio archive, 20th century)</li>
                                            <li>Beaton Institute clipping files (archival collection, 20th century)</li>
                                            <li>Beyond the Hebrides, Donald A. Fergusson (book, 1977)</li>
                                            <li>Brìgh an Òrain, John Shaw (book, 2000)</li>
                                            <li>Cape Breton Gaelic Folklore Project / Gaelstream, St. Francis Xavier University Celtic Collection (audio archive, 1962-1982)</li>
                                            <li>Clàrsach na Coille, Alexander MacLean Sinclair (book, 1881)</li>
                                            <li>Cluas ri Claisneachd, DASG (audio collection, 1953-1971)</li>
                                            <li>Dàin a Chòmhnadh Cràbhaidh, Seumas MacGhriogair (book, 1831)</li>
                                            <li>Dàin Spioradail leis a’ Bhàrd Mhic Ill’Eathain, ed. Alexander MacLean Sinclair (book, 1880)</li>
                                            <li>Dia nan Grasan: Laoidhean Ùra Gàidhlig, Mòrag Burke (book, 2014)</li>
                                            <li>Charles Dunn collection, Harvard University (audio archive, 1940s-70s)</li>
                                            <li>Fàilte Cheap Breatainn (book, 1891)</li>
                                            <li>Fear na Cèilidh (monthly newspaper, 1928-30)</li>
                                            <li>Fògradh, Faisneachd, is Filidheachd, Rev. Duncan Blair (book, 2013) </li>
                                            <li>Gaelic Bards of Antigonish, ed. Trueman Matheson (book, 2014)</li>
                                            <li>Gaelic Songs of Nova Scotia, ed. Helen Creighton & C.I.N. MacLeod (book, 1964)</li>
                                            <li>Guthan Prìseil, Anne Landin (book, 2009) </li>
                                            <li>Helen Creighton collection, Nova Scotia Archives (audio archive, 1940s-70s)</li>
                                            <li>History of Christmas Island Parish, Archibald J. MacKenzie (book, 1926)</li>
                                            <li>Mac-Talla (bi-weekly newspaper, 1892-1904)</li>
                                            <li>Mac-Talla nan Cùl (manuscript, n.d.)</li>
                                            <li>MacEdward Leach collection, Memorial University of Newfoundland Folklore and Language Archive (audio archive, 1950s)</li>
                                            <li>Mar a b’ àbhaist às a’ Ghleann, eds. Rod C. MacNeil and Kim Ells (book, 2012) </li>
                                            <li>Mosgladh (monthly newspaper, 1922-33)  </li>
                                            <li>O Cheapaich nan Craobh, ed. Trueman Matheson (book, 2008)</li>
                                            <li>Òrain Fuinn is Cladaich by Murdoch Morrison (book, 1931) </li>
                                            <li>Pictou Bards, compiled by Trueman Matheson (manuscript, n.d.)</li>
                                            <li>Ridge manuscript, St. Francis Xavier University Celtic Collection (manuscript, 19th century)</li>
                                            <li>Rinzler collection, Smithsonian Institute (audio archive, 1964-66)</li>
                                            <li>Seanchaidh na Coille / The Memory-Keeper of the Forest: Anthology of the Scottish-Gaelic Literature of Canada by Michael Newton (book, 2015)</li>
                                            <li>Sidney Robertson Cowell collection, Library of Congress (audio archive, 1939)</li>
                                            <li>Smeòrach nan Cnoc is nan Gleann (book, 1939) </li>
                                            <li>Songs from Framboise (booklet, 1986)</li>
                                            <li>Songs Remembered in Exile, John Lorne Campbell (book, 1990)</li>
                                            <li>Teachdaire nan Gàidheal (monthly newspaper, 1924-1934)</li>
                                            <li>The Cape Breton Highlander (weekly newspaper, 1963-1976)</li>
                                            <li>The Casket (weekly newspaper, 1852-present)</li>
                                            <li>The Emigrant Experience, Margaret MacDonell (book, 1982)</li>
                                        </ul>

                                        <p>Many of the books we referenced are available from major booksellers. But we would encourage you to support smaller, local publishers and sellers, such as:</p>
                                        <ul>
                                            <li><a href='https://nimbus.ca/' target='_blank'>Nimbus Books</a></li>
                                            <li><a href='https://highlandvillagegiftshop.ca/' target='_blank'>Highland Village gift shop</a></li>
                                            <li><a href='https://gaeliccollege.edu/shop/' target='_blank'>Gaelic College gift shop</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
HTML;
		echo $html;
	}
}