<?php
require_once 'lib/phpthumb/ThumbLib.inc.php';

$filename = $_GET['filename'];
$w = $_GET['w'];
$h = $_GET['h'];

$path_parts = pathinfo($filename);
if(!file_exists($path_parts['dirname'] . '/' . $w.'x'.$h.'-'.$path_parts['basename'])){
	try
	{
		$thumb = PhpThumbFactory::create($filename);
	}
	catch (Exception $e)
	{
		// handle error here however you'd like
	}

	$thumb->adaptiveResize($w, $h);
	$thumb->save($path_parts['dirname'] . '/' . $w.'x'.$h.'-'.$path_parts['basename']);

	header('Content-Type: image/' . $path_parts['extension']);
	readfile($path_parts['dirname'] . '/' . $w.'x'.$h.'-'.$path_parts['basename']);
}else{
	header('Content-Type: image/' . $path_parts['extension']);
	readfile($path_parts['dirname'] . '/' . $w.'x'.$h.'-'.$path_parts['basename']);
}
?>
