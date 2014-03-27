<?php
	
//	Load Icons library
	include_once('./icon.php');
	
//	Load icon file
	$icon = \Codefocus\Icons\Icon::createFromFile('test.ico');
	
//	Preferences
	$minWidth		= 16;
	$minBitcount	= 8;
	$maxWidth		= 64;
	$maxBitcount	= 32;
	
//	Extract the preferred icon.
	$image = $icon->getImage($minWidth, $minBitcount, $maxWidth, $maxBitcount);
	
	$pngData = $image->renderPng();
	header('Content-type: image/png');
	echo $pngData;
	