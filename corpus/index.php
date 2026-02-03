<?php

require_once '../includes/include.php';

$pageTitle = 'Corpas na Gaidhlig';

$cqpPage = false;

require_once '../includes/htmlHeader.php';

$mainText["en"] = <<<HTML
    <h3>Update</h3>
 
    <p>Corpus na Gàidhlig has been moved to <a href="https://dasg.arts.gla.ac.uk/CQPweb" title="CQPWeb">https://dasg.arts.gla.ac.uk/CQPweb</a>.</p>
    <p>In order to access Corpas na Gàidhlig, users will need to register with CQPweb (Corpus Query Processor) unless you have already registered. It’s free and all you need is your name and email address. To create an account, please visit <a href="https://dasg.arts.gla.ac.uk/CQPweb" title="CQPWeb">here</a>. Please do not use special characters such as $, @, /, etc. or an email address as your user name.</p>
    <p>Corpus na Gàidhlig has been extended with new texts added and a number of former features restored. These include</p>
    <ul> 
        <li>accent (in)sensitive searching</li>
        <li>lenited forms searching</li>
        <li>more intelligent handling of hyphens and apostrophes in search queries</li>
        <li>metadata for texts where such exists at present.</li>
    </ul>

HTML;

$mainText["gd"] = <<<HTML
    <h3>Fiosrachadh ùr</h3>
 
<p>Tha Corpas na Gàidhlig air imrich gu <a href="https://dasg.arts.gla.ac.uk/CQPweb" title="CQPWeb">https://dasg.arts.gla.ac.uk/CQPweb</a>.</p>
<p>Ma tha thu airson Corpas na Gàidhlig a chleachadh, feumaidh tu clàradh le CQPweb (Corpus Query Processor). Tha e saor agus an-asgaidh, agus chan eil agad ri dheanamh ach t’ ainm agus do phost-dealain a thoirt seachad. Airson cunntas a chruthachadh, tadhail an <a href="https://dasg.arts.gla.ac.uk/CQPweb" title="CQPWeb">seo</a>. Na cleachd siombalan sònraichte, leithid $, @, /, etc. no seòladh puist-dealain mar ainm-cleachdaiche.</p>
<p>Thathar air barrachd theacsaichean a chur ri Corpas na Gàidhlig agus àireamh de dh’fheartan ath-stèidheachadh:</p>

<ul>
    <li>rannsachadh le / às aonais stràcan</li>
    <li>rannsachaidh chruthan le / às aonais sèimheachadh</li>
    <li>rannsachadh chruthan nas toinnte le tàthanan agus asgairean</li>
    <li>metadata airson theacsaichean far a bheil a leithid ann.</li>
</ul>
HTML;

echo <<<HTML
	<div>
	    {$mainText[$lang]}
	</div>
HTML;


require_once '../includes/htmlFooter.php';