<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

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

$w = number_format (98/28.0, 3, '.', '');

$tabs = array();
for ($i = 0; $i < 28;  $i++)
{
  array_push($tabs, number_format ($i*$w, 3, '.', ''));
}

?>
<svg class="chart" width="90%" height="320px" >
 <defs>
    <linearGradient id="gruen1" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#33dd33" stop-opacity="1"/>
      <stop offset="100%" stop-color="#66dd66" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="gruen2" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#66ee66" stop-opacity="1"/>
      <stop offset="100%" stop-color="#99ee99" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="gelblich" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#ffff33" stop-opacity="1"/>
      <stop offset="100%" stop-color="#ffcc33" stop-opacity="1"/>
    </linearGradient>
  </defs>


 <g transform="translate(4,84)">
<?php
for ($i = 0; $i < 28; $i++)
{

  //////////////////////////// GRUEN (default)
  if ($i % 2 == 0)
  {
	echo '<rect x="'.$tabs[$i].'%" y="0" width="'.$w.'%" height="20" style="fill:url(#gruen1); stroke: #000000; stroke-width: 1px;"></rect>'."\n";
	//////////////////////////// H-LINIE
	echo '<line x1="'.$tabs[$i].'%" y1="-20" x2="'.$tabs[$i].'%" y2="20" style="stroke:#000000; stroke-width: 3px;" />'."\n";
  }
  else
  {
	echo '<rect x="'.$tabs[$i].'%" y="0" width="'.$w.'%" height="20" style="fill:url(#gruen2); stroke: #000000; stroke-width: 1px;"></rect>'."\n";
  }

  ////////////////////////// BLAU
  if ($i > 20 && $i < 24)
  {
	$tmp = floatval($tabs[$i]) + ($w/2);
    $tmp = number_format ($tmp, 3, '.', '');

    echo '<rect x="'.$tabs[$i].'%" y="0" width="'.$w.'%" height="20" style="fill: #0099ff; stroke: #000000; stroke-width: 1px;"></rect>'."\n";
    echo '<text x="'.$tmp.'%" y="16" text-anchor="middle" style="stroke:#333399; fill: #333399; font-size: 90%;">214</text>'."\n";
  }
  if ($i > 2 && $i < 13)
  {
	$tmp = floatval($tabs[$i]) + ($w/2);
    $tmp = number_format ($tmp, 3, '.', '');

    echo '<rect x="'.$tabs[$i].'%" y="0" width="'.$w.'%" height="20" style="fill: #0099ff; stroke: #000000; stroke-width: 1px;"></rect>'."\n";
	echo '<text x="'.$tmp.'%" y="16" text-anchor="middle" style="stroke:#333399; fill: #333399; font-size: 90%;">214</text>'."\n";
  }
  //////////////////////////// GELB
  if ($i > 8 && $i < 14)
  {
	$tmp = floatval($tabs[$i]) + ($w/2);
    $tmp = number_format ($tmp, 3, '.', '');
    echo '<rect x="'.$tabs[$i].'%" y="20" width="'.$w.'%" height="20" style="fill: #ffff33; stroke: #000000; stroke-width: 1px;"></rect>'."\n";
    echo '<text x="'.$tmp.'%" y="36" text-anchor="middle" style="stroke:#999900; fill: #999900; font-size: 90%;">078</text>'."\n";
  }
  //////////////////////////// DUNKELGELB
  if ($i > 10 && $i < 18)
  {
	$tmp = floatval($tabs[$i]) + ($w/2);
    $tmp = number_format ($tmp, 3, '.', '');
    echo '<rect x="'.$tabs[$i].'%" y="40" width="'.$w.'%" height="20" style="fill: #ffcc33; stroke: #000000; stroke-width: 1px;"></rect>'."\n";
    echo '<text x="'.$tmp.'%" y="56" text-anchor="middle" style="stroke:#999900; fill: #999900; font-size: 90%;">007</text>'."\n";
  }

}
?>
 </g>
 <g transform="translate(12,60)">
<?php
$t = 7;
for ($i = 0; $i < 28; $i += 2)
{
  //////////////////////////// ZEITEN
  echo '<text x="'.$tabs[$i].'%" y="16" style="stroke:#666666; fill: #666666; font-family: monospace; font-size: 100%;">'.$t.':00</text>'."\n";
  $t++;
}
?>
 </g>
 <g transform="translate(4,0)">
  <text x="98%" y="30px" text-anchor="end" style="stroke:#000000; fill: #000000; font-size: 160%; font-weight: bold;">Tecnam P2002-JF</text>
</g>
 <g transform="translate(4, 280)">
  <rect x="0%" y="0" width="6%" height="34" style="fill:url(#gruen2); stroke: #000000; stroke-width: 1px;"></rect>
  <text x="3%" y="24px" text-anchor="middle" style="stroke:#000000; fill: #000000; font-size: 100%; ">Frei</text>
  <rect x="8%" y="0" width="6%" height="34" style="fill: #0099ff; stroke: #000000; stroke-width: 1px;"></rect>
  <text x="11%" y="24px" text-anchor="middle" style="stroke:#000000; fill: #000000; font-size: 100%; ">Gebucht</text>
  <rect x="16%" y="0" width="6%" height="34" style="fill: url(#gelblich); stroke: #000000; stroke-width: 1px;"></rect>
  <text x="19%" y="24px" text-anchor="middle" style="stroke:#000000; fill: #000000; font-size: 100%; ">Standby</text>
  <rect x="24%" y="0" width="6%" height="34" style="fill: #ff0000; stroke: #000000; stroke-width: 1px;"></rect>
  <text x="27%" y="24px" text-anchor="middle" style="stroke:#000000; fill: #000000; font-size: 100%; ">Service</text>
</g>

</svg>
</main>
</body>
</html>
