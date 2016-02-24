<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('login/includes/db_connect.php');
include_once ('login/includes/functions.php');

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
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/reservationen.css">

</head>

<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<body>
<?php 
require('includes/usermenu.php');
echo '<main>';

require('includes/kalender.php');

// either $_GET or today
list( $tag, $monat, $jahr) = get_date();

?> <div style="float: right; margin-right: 30px; margin-top: 0px;"> <?php

echo draw_calendar($tag, $monat, $jahr);

?> 
</div>
<h1>Buchungs Überblick</h1>
<?php

$query = "SELECT * FROM `flieger`;";
$res = $mysqli->query($query);

while($obj_flieger = $res->fetch_object())
{
  $j = str_pad($jahr, 2, "0", STR_PAD_LEFT);
  $m = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $t = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $date0 = "$j-$m-$t 00:00:00";
  $date24 = "$j-$m-$t 23:59:59";
  $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = ".$obj_flieger->id." AND `bis` >= '$date0' AND `von` <= '$date24' ORDER BY `timestamp`;";
  $res2 = $mysqli->query($query);

  echo '<table><tr><td><b>'.$obj_flieger->flieger.'</b></td>';

  while($obj = $res2->fetch_object())
  {
    echo "<tr>";
    echo '<td>'.$obj->von.'</td>';
    echo '<td>'.$obj->userid.'</td>';
    echo '<td>'.$obj->bis.'</td>';
    echo "</tr>";
  }
  echo '</table>';
}
?>

<svg class="chart" width="80%" >
 <g transform="translate(25,22)">
	<rect x="0.00000%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="4.16666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="8.33333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="12.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="16.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="24.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="29.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="33.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="37.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="41.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="45.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="49.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="54.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="58.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="62.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="66.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="70.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="74.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="79.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="83.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="87.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="91.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="95.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
 </g>
 <g transform="translate(0,0)">
	<!--<text x="0.00001%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">0:00</text>-->
	<!--<text x="4.16666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">1:00</text>-->
	<!--<text x="8.33333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">2:00</text>-->
	<text x="12.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">&nbsp;3:00</text>
	<!--<text x="16.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">4:00</text>-->
	<!--<text x="20.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">5:00</text>-->
	<text x="24.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">&nbsp;6:00</text>
	<!--<text x="29.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">7:00</text>-->
	<!--<text x="33.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">8:00</text>-->
	<text x="37.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">&nbsp;9:00</text>
	<!--<text x="41.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">10:00</text>-->
	<!--<text x="45.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">11:00</text>-->
	<text x="49.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">12:00</text>
	<!--<text x="54.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">13:00</text>-->
	<!--<text x="58.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">14:00</text>-->
	<text x="62.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">15:00</text>
	<!--<text x="66.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">16:00</text>-->
	<!--<text x="70.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">17:00</text>-->
	<text x="74.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">18:00</text>
	<!--<text x="79.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">19:00</text>-->
	<!--<text x="83.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">20:00</text>-->
	<text x="87.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">21:00</text>
	<!--<text x="91.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">22:00</text>-->
	<!--<text x="95.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">23:00</text>-->
 </g>
</svg>
<svg class="chart" width="80%" >
 <g transform="translate(25,8)">
	<rect x="0.00000%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="4.16666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="8.33333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="12.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="16.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="24.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="29.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="33.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="37.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="41.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="45.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="49.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="54.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="58.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="62.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="66.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="70.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="74.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="79.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="83.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="87.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="91.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="95.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
 </g>
 <g transform="translate(0,12)">
	<!--<text x="0.00001%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">0:00</text>-->
	<!--<text x="4.16666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">1:00</text>-->
	<!--<text x="8.33333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">2:00</text>-->
	<text x="12.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">&nbsp;3:00</text>
	<!--<text x="16.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">4:00</text>-->
	<!--<text x="20.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">5:00</text>-->
	<text x="24.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">&nbsp;6:00</text>
	<!--<text x="29.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">7:00</text>-->
	<!--<text x="33.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">8:00</text>-->
	<text x="37.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">&nbsp;9:00</text>
	<!--<text x="41.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">10:00</text>-->
	<!--<text x="45.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">11:00</text>-->
	<text x="49.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">12:00</text>
	<!--<text x="54.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">13:00</text>-->
	<!--<text x="58.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">14:00</text>-->
	<text x="62.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">15:00</text>
	<!--<text x="66.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">16:00</text>-->
	<!--<text x="70.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">17:00</text>-->
	<text x="74.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">18:00</text>
	<!--<text x="79.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">19:00</text>-->
	<!--<text x="83.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">20:00</text>-->
	<text x="87.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;  font-size: 14px;">21:00</text>
	<!--<text x="91.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">22:00</text>-->
	<!--<text x="95.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">23:00</text>-->
 </g>
 <g transform="translate(25,34)">
	<rect x="0.00000%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="4.16666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="8.33333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="12.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="16.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="24.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="29.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="33.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="37.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="41.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="45.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="49.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="54.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="58.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="62.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="66.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="70.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="74.9999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="79.1666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="83.3333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="87.4999%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="91.6666%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="95.8333%" y="0" width="4.1666%" height="9" class="top" style="stroke:#006600; fill: #009999;"></rect>
 </g>

</svg>
<svg class="chart" width="80%" >
 <g transform="translate(30,6)">
	<rect x="0.00000%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="4.16666%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="8.33333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="12.4999%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="16.6666%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="24.9999%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="29.1666%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="33.3333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="37.4999%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="41.6666%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="45.8333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="49.9999%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="54.1666%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="58.3333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="62.4999%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="66.6666%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="70.8333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #ffff00;"></rect>
	<rect x="74.9999%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="79.1666%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="83.3333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="87.4999%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="91.6666%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
	<rect x="95.8333%" y="0" width="4.1666%" height="32" class="top" style="stroke:#006600; fill: #009999;"></rect>
 </g>
 <g transform="translate(5,10)">
	<!--<text x="0.00001%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">0:00</text>-->
	<!--<text x="4.16666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">1:00</text>-->
	<!--<text x="8.33333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">2:00</text>-->
	<text x="12.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;   font-size: 14px;">&nbsp;3:00</text>
	<!--<text x="16.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">4:00</text>-->
	<!--<text x="20.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">5:00</text>-->
	<text x="24.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;   font-size: 14px;">&nbsp;6:00</text>
	<!--<text x="29.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">7:00</text>-->
	<!--<text x="33.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">8:00</text>-->
	<text x="37.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;   font-size: 14px;">&nbsp;9:00</text>
	<!--<text x="41.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">10:00</text>-->
	<!--<text x="45.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">11:00</text>-->
	<text x="49.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;   font-size: 14px;">12:00</text>
	<!--<text x="54.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">13:00</text>-->
	<!--<text x="58.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">14:00</text>-->
	<text x="62.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;   font-size: 14px;">15:00</text>
	<!--<text x="66.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">16:00</text>-->
	<!--<text x="70.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">17:00</text>-->
	<text x="74.9999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;   font-size: 14px;">18:00</text>
	<!--<text x="79.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">19:00</text>-->
	<!--<text x="83.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">20:00</text>-->
	<text x="87.4999%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace;   font-size: 14px;">21:00</text>
	<!--<text x="91.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">22:00</text>-->
	<!--<text x="95.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">23:00</text>-->
 </g>

</svg>
<svg class="chart" width="80%" >
 <g transform="translate(20,24)">
	<rect x="0.00000%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="4.16666%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="8.33333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="12.4999%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="16.6666%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="20.8333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="24.9999%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="29.1666%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="33.3333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="37.4999%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="41.6666%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="45.8333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffcc00;"></rect>
	<rect x="49.9999%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffff00;"></rect>
	<rect x="54.1666%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffff00;"></rect>
	<rect x="58.3333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffff00;"></rect>
	<rect x="62.4999%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffff00;"></rect>
	<rect x="66.6666%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffff00;"></rect>
	<rect x="70.8333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #ffff00;"></rect>
	<rect x="74.9999%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="79.1666%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="83.3333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="87.4999%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="91.6666%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
	<rect x="95.8333%" y="0" width="4.1666%" height="9" class="top" style="font-family: monospace; stroke:#006600; fill: #009999;"></rect>
 </g>
 <g transform="translate(0,0)">
	<!--<text x="0.00001%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">0:00</text>-->
	<text x="4.16666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">1:00</text>
	<text x="8.33333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">2:00</text>
	<text x="12.4999%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">3:00</text>
	<text x="16.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">4:00</text>
	<text x="20.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">5:00</text>
	<text x="24.9999%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">6:00</text>
	<text x="29.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">7:00</text>
	<text x="33.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">8:00</text>
	<text x="37.4999%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">9:00</text>
	<text x="41.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">10:00</text>
	<text x="45.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">11:00</text>
	<text x="49.9999%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">12:00</text>
	<text x="54.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">13:00</text>
	<text x="58.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">14:00</text>
	<text x="62.4999%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">15:00</text>
	<text x="66.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">16:00</text>
	<text x="70.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">17:00</text>
	<text x="74.9999%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">18:00</text>
	<text x="79.1666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">19:00</text>
	<text x="83.3333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">20:00</text>
	<text x="87.4999%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">21:00</text>
	<text x="91.6666%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">22:00</text>
	<text x="95.8333%" y="20" style="stroke:#000000; fill: #000000; font-size: 14px;">23:00</text>
 </g>
</svg>

</main>
</body>
</html>
