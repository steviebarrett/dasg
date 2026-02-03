<?php


echo <<<HTML

<html>
<head>
	<title>Mac-Talla Volume {$_GET["v"]} Issue {$_GET["i"]}</title>
</head>

<body>
	<embed width="100%" height="100%" src="/81_Mac-Talla/vol_{$_GET["v"]}/no{$_GET["i"]}.pdf#page={$_GET["p"]}">
</body>

</html>

HTML;

