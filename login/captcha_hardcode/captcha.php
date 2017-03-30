<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('../../includes/db_connect.php');
include_once ('../../includes/user_functions.php');
include_once ('../../includes/html_functions.php');
include_once ('../../includes/functions.php');

sec_session_start();

$randomnr = rand(1000, 9999);
$_SESSION['randomnr2'] = md5($randomnr);

$im = imagecreatetruecolor(62, 24);

$white = imagecolorallocate($im, 225, 225, 225);
$grey = imagecolorallocate($im, 138, 138, 138);
$black = imagecolorallocate($im, 70, 70, 70);
$blue = imagecolorallocate($im, 184, 202, 205);

imagefilledrectangle($im, 0, 0, 62, 24, $blue);

//path to font - this is just an example you can use any font you like:

$font = dirName(__FILE__).'/font/karate/Karate.ttf';

imagettftext($im, 15, 5, 11, 23, $grey, $font, $randomnr);
imagettftext($im, 15, 4, 6, 20, $black, $font, $randomnr);

//prevent caching on client side:
header("Expires: Wed, 1 Jan 1997 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header ("Content-type: image/gif");
imagegif($im);
imagedestroy($im);

?>
