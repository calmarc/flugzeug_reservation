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
?>

<svg class="chart" width="80%" >
 <g transform="translate(28,22)">

<?php
$w = number_format (90/28.0, 3, '.', '');

for ($i = 0; $i < 28;  $i++)
{
  $n = 90/28.0 * $i;
  $x = number_format($n, 3, '.', '');

  echo '<rect x="'.$x.'%" y="0" width="'.$w.'%" height="19" style="stroke: #ffffff; fill: #009900;"></rect>'."\n";
}
?>

 </g>
 <g transform="translate(0,0)">

<?php

$tp = 100/28 * 26; // som reserve / width single unit

$w = number_format ($tp/28.0, 3, '.', '');
$t = 7;

for ($i = 0; $i < 28;  $i++)
{
  $n = $tp/28.0 * $i;
  $x = number_format($n, 3, '.', '');
  $n = $tp/28.0 * ++$i;
  $x2 = number_format($n, 3, '.', '');
  echo '<text x="'.$x.'%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace; font-size: 80%;">&nbsp;'.$t.':00</text>'."\n";
  //echo '<text x="'.$x2.'%" y="20" style="stroke:#000000; fill: #000000;font-family: monospace; font-size: 80%;">&nbsp;'.$t.':30</text>'."\n";
  $t++;
}

?>

 </g>
 <g transform="translate(0,100)">

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

</main>
</body>
</html>
