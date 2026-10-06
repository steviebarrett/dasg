<?php

if (!defined('DASG_BOOTSTRAPPED')) {
    http_response_code(403);
    exit('Forbidden');
}

echo <<<HTML

			</div> <!-- end main -->
			
			<div id="footer" class="foot {$pageSlug}_foot">

				<div class="linkLogo"><a href="http://www.gla.ac.uk" title="University of Glasgow" target="_blank"><img src="/images/logos/glasgow.png" width="126" alt="University of Glasgow"/></a></div>
				<div class="linkLogo"><a href="http://www.britac.ac.uk" title="British Academy" target="_blank"><img src="/images/logos/britishacademy.png" width="125" alt="British Academy"/></a></div>
				<div class="linkLogo"><a href="http://www.faclair.ac.uk" title="Faclair na Gàidhlig" target="_blank"><img src="/images/logos/faclair.png" height="36" alt="Faclair na Gàidhlig"/></a></div>
				<div class="linkLogo"><a href="http://www.sfc.ac.uk" title="Scottish Funding Council" target="_blank"><img src="/images/logos/sfc.jpg" width="180" height="44" alt="Scottish Funding Council"/></a></div>
				<div class="linkLogo"><a href="http://www.gaidhlig.org.uk/index-en.php" title="Bòrd na Gàidhlig" target="_blank"><img src="/images/logos/bnag-col.png" width="80" alt="Bòrd na Gàidhlig"/></a></div>
				<div class="linkLogo"><a href="http://www.ahrc.ac.uk/Pages/Home.aspx" title="AHRC" target="_blank"><img src="/images/logos/ahrc.png" width="130" alt="AHRC"/></a></div>
				<div class="linkLogo"><a href="http://www.esrc.ac.uk" title="ESRC" target="_blank"><img src="/images/logos/esrc.jpg" width="40" alt="ESRC"/></a></div>
		
				<br class="clear"/>
			</div> <!-- end footer -->
		</div> <!-- end wrapper -->
		
	</body>
	
</html>

HTML;
