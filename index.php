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

$wochentag = date("l", strtotime("$j-$m-$t"));

if ($_SESSION['show'] == 'tag')
{
  echo '<div id="calendar">';
  echo draw_calendar($tag, $monat, $jahr);
  echo '</div>';
  echo "<h1 id='buchung'>$wochentag, $tag&nbsp;".$monate[$monat-1]." $jahr</h1>";
}
else
{
  $flieger_id = 1;
  if (isset($_GET['flieger_id']))
  {
    $flieger_id = $_GET['flieger_id'];
    $_SESSION['flieger_id'] = $flieger_id;
  }
  else if (isset($_SESSION['flieger_id']))
    $flieger_id = $_SESSION['flieger_id'];

  $select = "<select name='flieger_id' style='vertical-align: middle; font-size: 100%; background-color: #99eeff; border-right: 0px solid #ff4400; border-left: 0px solid #ff4400; border-top: 0px solid #ff4400; border-bottom: 2px solid #ff4400;' onchange='this.form.submit()' >";

  $res = $mysqli->query("SELECT * FROM `flieger`;");
  $x = 0;
  while ($obj = $res->fetch_object())
  {
    $x++;
    $sel = "";

    if ($x == $flieger_id)
      $sel = "selected='selected'";

    $select .= "<option $sel value='$x'>".$obj->flieger."</option>";
  }

  $select .= "</select>";

  $z_jahr = $jahr;
  $v_jahr = $jahr;

  $z_monat = $monat - 1;
  $v_monat = $monat + 1;

  if ($z_monat < 1) { $z_monat = 12; $z_jahr--; }
  if ($v_monat > 12) { $v_monat = 1; $v_jahr++; }
  echo "<form method='get' action='index.php'>";
  echo "<table style='width: 100%; text-align: center;'><tr>";
  echo "<td>";
  echo '<h1>';
  echo '<a href="/reservationen/index.php?flieger_id='.$flieger_id.'&amp;show=monat&amp;monat='.$z_monat.'&amp;jahr='.$z_jahr.'&amp;tag='.$tag.'"><span>&laquo;</span></a> &nbsp; '; 
  echo  $monate[$monat-1];
  echo " $jahr";
  echo "\n";
  echo " &nbsp; &nbsp;";
  echo "<input type='hidden' name='show' value='monat' />";
  echo "<input type='hidden' name='tag' value='$tag' />";
  echo "<input type='hidden' name='monat' value='$monat' />";
  echo "<input type='hidden' name='jahr' value='$jahr' />";
  echo $select;
  //echo "<input type='submit' name='wasauchimmer' value='&lt;' />";
  echo ' &nbsp; <a href="/reservationen/index.php?flieger_id='.$flieger_id.'&amp;show=monat&amp;monat='.$v_monat.'&amp;jahr='.$v_jahr.'&amp;tag='.$tag.'">&raquo;</a> '; 
  echo '</h1>';
  echo "</td>";
  echo "</tr></table>";
  echo "</form>";
}

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

if ($_SESSION['show'] == 'monat')
{
  // winterzeit weg.. wenn man differenzen von datum berechnet
  // TODO.. gucken wo das ueberall effekt hat
  date_default_timezone_set('UTC');
  monatsansicht($mysqli, $w, $tabs, $boxcol, $textcol, $monat, $jahr, $flieger_id);
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
