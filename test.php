<?php
	
//	Load Icons library
	include_once('./icon.php');
	
//	Load icon file
	$icon = \Codefocus\Icons\Icon::createFromFile('test.ico');
	
//	Extract the preferred icon.
	$image = $icon->getImage(
		16,  //  minimum width
		4,   //  minimum bitcount
		64,  //  maximum width
		32   //  maximum bitcount
	);
	
//	Render as PNG
	$pngData = $image->renderPng();
	header('Content-type: image/png');
	echo $pngData;
	