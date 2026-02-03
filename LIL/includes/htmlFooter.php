<?php

$adminHtml = $_SESSION["loggedIn"]
	? '<li><a href="?m=admin"><span class="gaelic">Rianachd</span><br>Admin</a></li><li><a href="?m=admin&a=logout"><span class="gaelic"></span><br>Logout</a></li>'
	: '<li><a href="?m=admin&a=login"><span class="gaelic">Rianachd</span><br>Admin</a></li>';
echo <<<HTML
			</div>  <!-- end row -->
			</div>  <!-- end mainBody -->
    </div>
	<footer>
		<div class='container py-4 mt-5'>
			<div class='row'>
				<!-- <div class='col-md-3'>
					<ul>
						<li><a href='#'>Homepage</a></li>
						<li><a href='#'>Browse Index</a></li>
					</ul>
				</div>
				<div class='col-md-3'>
					<ul>
						<li><a href='#'>Search Index</a></li>
						<li><a href='#'>User Guide</a></li>
					</ul>
				</div>
				<div class='col-md-3'>
					<ul>
						<li><a href='#'>Gratitude & Acknowledgments</a></li>
						<li><a href='#'>Language and Lyrics Team</a></li>
					</ul>
				</div>
				<div class='col-md-3'>
					<ul>
						<li><a href='#'>Contact Us</a></li>
						<li><a href='#'>Admin Login</a></li>
					</ul>
				</div> -->
				<div class='col-12'>
					<ul>
						<li><a href='?m=index'><span class='gaelic'>Dachaigh</span><br>Homepage</a></li>
						<li><a href='?m=about'><span class='gaelic'>Mu dheidhinn</span><br>About</a></li>
						<!-- <li><a href='?m=records&a=list'>Browse Index</a></li> -->
						<!-- <li><a href='?m=records&a=search'>Search Index</a></li> -->
						<!-- <li><a href='?m=faq'>User Guide</a></li> -->
						<li><a href='?m=gratitude'><span class='gaelic'>Taing</span><br>Acknowledgments</a></li>
						<li><a href='?m=team'><span class='gaelic'>An Sgioba</span><br>Project Team</a></li>
						<li><a href='?m=contact'><span class='gaelic'>Sgrìobh thugainn</span><br>Contact Us</a></li>
						{$adminHtml}
					</ul>
				</div>
			</div>
		</div>
	</footer>
</body>
</html>
HTML;
