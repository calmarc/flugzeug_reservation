<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');
include_once ('includes/graphic.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>MFGC Flieger-Reservationen</title>
  <meta name="title" content="Flieger-Reservationen">
  <meta name="keywords" content="Reservierungs-System">
  <meta name="description" content="Reservierungs-System">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <meta http-equiv="refresh" content="450">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/css/reservationen.css">
</head>

<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<body>


<?php 

require('includes/usermenu.php');
echo '<main>';
require('includes/kalender.php');

$monate = array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", 
"Oktober", "November", "Dezember");

// either $_GET or today
list( $tag, $monat, $jahr) = get_date();

$j = str_pad($jahr, 2, "0", STR_PAD_LEFT);
$m = str_pad($monat, 2, "0", STR_PAD_LEFT);
$t = str_pad($tag, 2, "0", STR_PAD_LEFT);

$date = "$j-$m-$t"; // DATE

$wochentag = date("D", strtotime("$j-$m-$t 00:00:00"));

echo '<div id="calendar">';
echo draw_calendar($tag, $monat, $jahr);

?> 
</div>
<h1 id="buchung"><?php echo "$wochentag, $tag.&nbsp;".$monate[$monat-1];?> 2016 Buchungen</h1>
<?php

// 'konstants' needed below
$w = number_format (98/28.0, 3, '.', ''); // WIDTH of tabs

$tabs = array(); // TABS to place stuff
for ($i = 0; $i <= 28;  $i++)
{
  array_push($tabs, number_format ($i*$w+0.8, 3, '.', ''));
}



//buchungs-colors: blue       yellow     orange     yellow     orange       red  
$boxcol =   array('#33ccff', '#ffff99', '#ffee99', '#ffff99', '#ffee99', '#ff6666');
$textcol =  array('#333333', '#333333', '#333333', '#333333', '#333333', '#333333');

$planeoffset = 123;

?>
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="chart" height="560" >
  <defs>
    <linearGradient id="gruen1" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#88dd88" stop-opacity="1"/>
      <stop offset="100%" stop-color="#aaddaa" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="gruen2" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#aaeeaa" stop-opacity="1"/>
      <stop offset="100%" stop-color="#cceecc" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="grey1" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#cccccc" stop-opacity="1"/>
      <stop offset="100%" stop-color="#dddddd" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="grey2" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#dddddd" stop-opacity="1"/>
      <stop offset="100%" stop-color="#eeeeee" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="gelblich" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#ffff33" stop-opacity="1"/>
      <stop offset="100%" stop-color="#ffcc33" stop-opacity="1"/>
    </linearGradient>
  </defs>

  <g transform="translate(4,84)">
  <?php


// print GREEN etc (lowest layer) stuff

print_main_bands($mysqli, $planeoffset, $jahr, $monat, $tag, $date, $tabs, $w);

remove_zombies($mysqli);

// TODO colors etc into defines? konstats etc?
print_buchungen($mysqli, $planeoffset, $tabs, $date, $boxcol, $textcol, $tag, $monat, $jahr);

?>
</g>
</svg>

<div class="center" style="margin-top: 16px;" >
  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
        height="30px" style="background-color: transparent; width: 60%; min-width: 480px;" >
    <defs>
      <linearGradient id="gruen0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#66ee66" stop-opacity="1"/>
        <stop offset="100%" stop-color="#99ee99" stop-opacity="1"/>
      </linearGradient>
      <linearGradient id="gelblich0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="<?php echo $boxcol[1];?>" stop-opacity="1"/>
        <stop offset="100%" stop-color="<?php echo $boxcol[2];?>" stop-opacity="1"/>
      </linearGradient>
    </defs>
   <g transform="translate(0, 0)">
    <rect x="10%" y="0" width="20%" height="24" style="fill:url(#gruen0); stroke: #000000; stroke-width: 1px;"></rect>
    <text x="20%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Frei</text>
    <rect x="30%" y="0" width="20%" height="24" style="fill: <?php echo $boxcol[0]; ?>; stroke: #000000; stroke-width: 1px;"></rect>
    <text x="40%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Gebucht</text>
    <rect x="50%" y="0" width="20%" height="24" style="fill: url(#gelblich0); stroke: #000000; stroke-width: 1px;"></rect>
    <text x="60%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Standby</text>
    <rect x="70%" y="0" width="20%" height="24" style="fill: <?php echo $boxcol[5]; ?>; stroke: #000000; stroke-width: 1px;"></rect>
    <text x="80%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Service</text>
    </g>
  </svg>
</div>
</main>
<!-- so you can scroll, when calendar is in the way since it's fixed -->
<br />
<br />
<br />
<br />
<br />
<br />
</body>
</html>
