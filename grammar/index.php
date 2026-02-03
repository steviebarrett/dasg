<?php

require_once '../includes/include.php';

$titleText = array("en"=>"Grammar", "gd"=>"Gràmar");

$pageTitle = $titleText[$lang];
$pageSlug = "grammar";


require_once '../includes/htmlHeader.php';
require_once '../includes/sideMenu.php';

$en = <<<HTML
    <h2>LEACAG Grammar Guidance</h2>

    <p>
      As part of the 2016–2018 Bòrd na Gàidhlig-funded project, Leasachadh Corpais na Gàidhlig
      (LEACAG, Gaelic Corpus Development), members of the DASG team (working with colleagues at
      the University of Edinburgh and the University of the Highlands and Islands) were involved in drafting new, evidence-based grammatical
      guidance for learners and users of modern Scottish Gaelic. This guidance was the result of
      consultation with Gaelic language professionals and traditional speakers in the Western Isles,
      as well as on detailed study of relevant usage in Corpas na Gàidhlig.
    </p>
     <p>
       In drafting this guidance, the research team were advised by the Comataidh Comhairleachaidh Cànain
       (a committee of traditional Gaelic speakers convened by Bòrd na Gàidhlig in 2015).
       The guidance addresses specific questions about usage and seeks to answer those questions.
       It is not intended to be a complete guide to all aspects of the points covered, and is aimed at readers
       who already have a knowledge of Gaelic to act as a reference guide rather than as a basic text for learning the language.
     </p>

    <p>This guidance is available to download <a href="grammar.pdf" target="_blank">here</a>.</p>

    <p>If you have any questions about this guidance, please <a href="mailto:mail@dasg.ac.uk">email DASG</a>.</p>

HTML;

$gd = <<<HTML
    <h2>Stiùireadh Gràmair LEACAG</h2>

    <p>Bha buill sgioba DASG (ag obair còmhla ri luchd-rannsachaidh aig Oilthigh Dhùn Èideann agus Oilthigh na Gàidhealtachd agus nan Eilean) an sàs ann am pròiseact rannsachaidh eadar 2016-2018, air an robh an t-ainm ‘Leasachadh Corpas na Gàidhlig’ (LEACAG).  An dèidh comhairleachaidh le luchd-cleachdaidh proifeiseanta agus luchd-labhairt dùthchasail anns na h-eileanan, sgrìobh sinn  stiùireadh gràmair ùr airson luchd-ionnsachaidh agus luchd-cleachdaidh na Gàidhlig, stèidhichte air fianais bho Chorpas na Gàidhlig.</p>

    <p>Tha an stiùireadh seo ri fhaighinn <a href="grammar.pdf" target="_blank">an seo</a>.</p>

    <p>Ma bhios ceistean sam bi agaibh mun stiùireadh seo, nach cuir sibh <a href="mailto:mail@dasg.ac.uk">post-d gu DASG</a>.</p>
HTML;

$mainText = array("en"=>$en, "gd"=>$gd);

echo $mainText[$lang];

require_once '../includes/htmlFooter.php';
