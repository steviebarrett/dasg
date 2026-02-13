<?php


namespace views;

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}
class index
{
	public function show() {
		$html = <<<HTML
            <!-- <div class="container home-content pt-5">
                <div class="row">
                    <div class="col-12">
                        <h3>Language in Lyrics</h3>
                    </div>
                </div>
            </div> -->

            <div class='container  home-content my-5'>
                <div class='row'>
                    <div class='col-lg-10 offset-lg-1'>
                        <div class='row'>

                            <div class='col-lg-6' style='justify-content: center;display: flex;flex-direction: column'>
                                <h4 class='mb-4'><span class='gaelic'>Clàr-amais Òrain Ghàidhlig na h-Albann Nuaidh</span><br>The Nova Scotia Gaelic Song Index</h4>
                                <p>Tha Clàr-amais Òrain Ghàidhlig na h-Albann Nuaidh na liosta de chòrr is 6,000 òran a bha air an dèanamh, air an gabhail no a nochd ann an clò aig na Gàidheil ann an Alba Nuaidh.</p>
                                <p>The Nova Scotia Gaelic Song Index is a searchable list of more than 6,000 Gaelic songs made, sung, or published by Gaels in Nova Scotia.</p>

                                <div class="justify-content-center d-flex">
                                    <a href="?m=records&a=list" class='btn btn-primary mx-2 mt-3'>Browse the Index</a>
                                    <a href="?m=records&a=search" class='btn btn-primary mx-2 mt-3'>Search the Index</a>
                                </div>
                            </div>
                            <div class='col-lg-6 mt-5 mt-lg-0' style='display: flex;align-items: center;'>
                                <img src='includes/img/map.png' class='w-100'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class='container banner my-5'>
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <img src='/includes/img/map.png' class='d-table mx-auto'>
                    </div>
                </div>
            </div>
            <div class='container home-content pb-5'>
                <div class="row">
                    <div class="col-lg-10 offset-lg-1">
                        <h4> Clàr-amais Òrain Ghàidhlig na h-Albainn Nuaidhe<br><i>The Nova Scotia Gaelic Song Index</i></h4>
                        <p>’S e liosta de bharrachd air 6,000 òrain Ghàidhlig aithnichte ann an Alba Nuadh a th’ anns a’ Chlàr-amais Òrain Ghàidhlig na h-Albainn Nuaidhe.</p>
                        <p>The Nova Scotia Gaelic Song Index is a searchable list of more than 6,000 Gaelic songs known in Nova Scotia.</p>
                    </div>
                    <div class="col-lg-10 offset-lg-1 justify-content-center d-flex">
                        <a href="?m=records&a=list" class='btn btn-primary mx-2 mt-3'>Browse the Index</a>
                        <a href="?m=records&a=search" class='btn btn-primary mx-2 mt-3'>Search the Index</a>
                    </div>
                </div>
            </div> -->
HTML;
		echo $html;
	}
}
