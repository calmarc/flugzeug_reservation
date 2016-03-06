<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');
include_once ('includes/tages_ansicht.php');
include_once ('includes/monats_ansicht.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>MFGC Flugzeug-Reservationen</title>
  <meta name="title" content="Flugzeug-Reservationen">
  <meta name="keywords" content="Reservierungs-System">
  <meta name="description" content="Reservierungs-System">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <meta http-equiv="refresh" content="900">
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

if (!isset($_GET['show']) || $_GET['show'] != 'monatsplan')
{
  echo '<div id="calendar">';
  echo draw_calendar($tag, $monat, $jahr);
  echo '</div>';
}

?> 
<h1 id="buchung"><?php echo "$wochentag, $tag.&nbsp;".$monate[$monat-1];?> 2016</h1>

<?php

// 'stuff' needed below
//$w = number_format (98/28.0, 3, '.', ''); // WIDTH of tabs
$perplus = 0.8; //(shift to right in percent)

// TODO: if calender.. must be less than 98%
$w = number_format (94/28.0, 3, '.', ''); // WIDTH of tabs
$perplus = 3.6; //(shift to right in percent)

$tabs = array(); // TABS to place stuff
for ($i = 0; $i <= 28;  $i++)
  array_push($tabs, number_format ($i * $w + $perplus, 3, '.', ''));

//buchungs-colors: blue       yellow     orange     yellow     orange       red  
$boxcol =   array('#33ccff', '#ffff99', '#ffee99', '#ffff99', '#ffee99', '#ff6666');
$textcol =  array('#333333', '#333333', '#333333', '#333333', '#333333', '#333333');

remove_zombies($mysqli);

if (isset($_GET['show']) &&  $_GET['show'] = 'monatsplan')
{
  // winterzeit weg.. wenn man differenzen von datum berechnet
  // TODO.. gucken wo das ueberall effekt hat
  date_default_timezone_set('UTC');
  monatsansicht($mysqli, $w, $tabs, $boxcol, $textcol, $monat, $jahr);
}
else
{
  $planeoffset = 123;
  tagesansicht($mysqli, $w, $tabs, $boxcol, $textcol, $planeoffset, $tag, $monat, $jahr, $date);
}

?>
</main>
<!-- so you can scroll, when calendar is in the way since it's fixed -->
<br />
<br />
<br />
<br />
<br />
</body>
</html>
