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
  <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>

 <!-- LOAD TESTING LIBRARY -->
      <script src="js/modernizr.js"></script>

      <!-- COOKIE LIBRARY -->
      <script src="js/jquery.cookie.js"></script>

      <!-- CREATE COOKIE -->
      <script>

        var clientInfo = {
          browserWidth: $(window).width(),
          browserHeight: $(window).height(),
          flexboxSupport: Modernizr.flexbox,
          SVGSupport: Modernizr.svg
        };

        var cookieVal = JSON.stringify(clientInfo);

        // was using document.cookie, this plugin worked better
        $.cookie("_clientInfo", cookieVal, {
          expires: 70
        });
      </script>
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

echo '<div id="calendar">';

echo draw_calendar($tag, $monat, $jahr);

?> 
</div>
<h1>Buchungs-Überblick (<?php echo "$tag.&nbsp;".$monate[$monat-1];?>)</h1>
<?php

// 'konstants' needed below
$w = number_format (98/28.0, 3, '.', ''); // WIDTH of tabs

$tabs = array(); // TABS to place stuff
for ($i = 0; $i < 28;  $i++)
{
  array_push($tabs, number_format ($i*$w, 3, '.', ''));
}

$j = str_pad($jahr, 2, "0", STR_PAD_LEFT);
$m = str_pad($monat, 2, "0", STR_PAD_LEFT);
$t = str_pad($tag, 2, "0", STR_PAD_LEFT);
$date = "$j-$m-$t"; // DATE

//buchungs-colors: blue+text,  yellow+text, orange+text, dark-orange+t, red+text
$boxcol = array('#0099ff', '#ffff00', '#ffcc33', '#ffff00', '#ffcc33', '#ffff00');
$textcol = array('#333399', '#999900', '#999933', '#999900', '#999933', '#999900');

$planeoffset = 120;

?>
<svg class="chart" width="90%" height="550px" >
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

// MAIN
// LOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOP


// print GREEN etc (lowest layer) stuff
$yoffset = -$planeoffset;

$query = "SELECT * FROM `flieger`;";
$res_f = $mysqli->query($query);

$nullnullpad = ":00";
if (isset($_COOKIE['_clientInfo']))
{
  $json = $_COOKIE['_clientInfo'];
  $obj = json_decode(stripslashes($json));
  if (isset($obj->browserWidth) && $obj->browserWidth > 10 && $obj->browserWidth < 1000)
    $nullnullpad = "";
}

while($obj_f = $res_f->fetch_object())
{
  $yoffset += $planeoffset;
  echo '<text x="98%" y="'.($yoffset-32).'px" text-anchor="end" style="stroke:#000000; fill: #000000; font-size: 160%; font-weight: bold;">'.$obj_f->flieger.'</text>'."\n";
  for ($i = 0; $i < 28; $i++)
  {
    //////////////////////////// GRUEN (default)
    if ($i % 2 == 0)
    {
      echo '<rect x="'.$tabs[$i].'%" y="'.($yoffset).'" width="'.$w.'%" height="20" style="fill:url(#gruen1); stroke: #000000; stroke-width: 1px;"></rect>'."\n";
      //////////////////////////// H-LINIE
      echo '<line x1="'.$tabs[$i].'%" y1="'.($yoffset-20).'" x2="'.$tabs[$i].'%" y2="'.($yoffset+20).'" style="stroke:#000000; stroke-width: 3px;" />'."\n";
      //////////////////////////// ZEITEN
      $tmp = (string) number_format(($tabs[$i]+0.5), 3, '.', '');

      echo '<text x="'.$tmp.'%" y="'.($yoffset-4).'" style="stroke:#666666; fill: #666666; font-family: monospace; font-size: 100%;">'.($i/2+7).$nullnullpad.'</text>'."\n";
    }
    else
    {
      echo '<rect x="'.$tabs[$i].'%" y="'.($yoffset).'" width="'.$w.'%" height="20" style="fill:url(#gruen2); stroke: #000000; stroke-width: 1px;"></rect>'."\n";
    }

  }
}

$datetime0 = $date." 00:00:00";
$datetime24 = $date." 23:59:59";

$res_f->close(); // TODO or re-interate?
$query = "SELECT * FROM `flieger`;";
$res_f = $mysqli->query($query);

// ueber die flieger iterieren
while($obj_f = $res_f->fetch_object())
{
  // alle Reservierungen welche in diesen Zeitraum tangieren.
  $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND ( `bis` > '$datetime0' AND `von` < '$datetime24') ORDER BY `timestamp` ASC;";

  $res_tang = $mysqli->query($query);

  // should be enough of standby-levels.. else.. well. shit happens
  // it.: if booking[level][hour]=TRUE <- reserved
  $bookings = array(array(), array(), array(), array(), array(), array(), array(), array(), array(), array(), array(), array());

  for ($x = 0; $x < 12; $x++) // initialise with FALSE = free.
    for ($i = 0; $i < 28; $i++)
      $bookings[$x][$i] = FALSE;

  while($obj_tang = $res_tang->fetch_object())
  {
    // 1. order(ed) them by timestamp
    // 2. have a reserved-variable for each level (green, 1.standby, ...)
    //
    // 3. check against each of the reserved-level-variables..
    //    beginning vrom gree, 1.standby, 2. 4.... until it fits
    // 4. accordingly 'book' that into the level-variable
    // 5. goto step 4.

    #transfer time to blocks.
    $vonb = strtotime($obj_tang->von);
    $bisb = strtotime($obj_tang->bis);

    $t1 = intval( date('H', $vonb));
    $t2 = intval( date('i', $vonb));
    $blocks_von = ($t1-7) * 2; //i.e. 8h-7 * 2 = block[2] (3rd)
    $blocks_von += intval(($t2 / 30));


    $t1 = intval( date('H', $bisb));
    $t2 = intval( date('i', $bisb));
    $blocks_bis = ($t1-7) * 2;
    $blocks_bis += intval(($t2 / 30));
    //i.e: 8:30 (8-7)*2 = 2 + (30/30) - 1 = block[2] (3rd)
    
    $level = 0;
    while(TRUE) 
    { 
      $flag = FALSE;
      for($i = $blocks_von; $i < $blocks_bis; $i++)
      {
        if ($bookings[$level][$i] == TRUE)
        {
          // Ops, not free - try next level
          $level++;
          $flag = TRUE;
          break; // out of for loop
        }
      }
      if ($flag == FALSE)
        break;
    }

    //book into level
    for($i = $blocks_von; $i < $blocks_bis; $i++)
      $bookings[$level][$i] = TRUE;

    //where to unbook??? no need to.. it gets build up from scratch all the time

    $center = ($tabs[$blocks_von] + $tabs[$blocks_bis]) / 2;
    $center = number_format ($center, 3, '.', '');

    if ($level > 4)
      continue; // don't print more standbys than 4 

    $yoffset = $planeoffset * ($obj_f->id - 1) + $level * 20;
    $width = number_format ($tabs[$blocks_bis]-$tabs[$blocks_von], 3, '.', '');

    echo '<rect x="'.$tabs[$blocks_von].'%" y="'.$yoffset.'" width="'.$width.'%" height="20" style="fill: '.$boxcol[$level].'; stroke: #000000; stroke-width: 1px;"></rect>'."\n";

    echo '<text x="'.$center.'%" y="'.($yoffset+16).'" text-anchor="middle" style="stroke: '.$textcol[$level].'; fill: '.$textcol[$level].'; font-size: 90%;">'.$obj_tang->userid.'</text>'."\n";
  }
}
  ?>
  </g>
</svg>

<div class="center" style="margin-top: 16px;" >
  <svg height="30px" style="background-color: transparent; width: 60%; min-width: 380px;" >
    <defs>
      <linearGradient id="gruen0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#66ee66" stop-opacity="1"/>
        <stop offset="100%" stop-color="#99ee99" stop-opacity="1"/>
      </linearGradient>
      <linearGradient id="gelblich0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#ffff33" stop-opacity="1"/>
        <stop offset="100%" stop-color="#ffcc33" stop-opacity="1"/>
      </linearGradient>
    </defs>
   <g transform="translate(0, 0)">
    <rect x="10%" y="0" width="20%" height="24" style="fill:url(#gruen0); stroke: #000000; stroke-width: 1px;"></rect>
    <text x="20%" y="18px" text-anchor="middle" style="stroke:#000000; fill: #000000; font-size: 100%; ">Frei</text>
    <rect x="30%" y="0" width="20%" height="24" style="fill: #0099ff; stroke: #000000; stroke-width: 1px;"></rect>
    <text x="40%" y="18px" text-anchor="middle" style="stroke:#000000; fill: #000000; font-size: 100%; ">Gebucht</text>
    <rect x="50%" y="0" width="20%" height="24" style="fill: url(#gelblich0); stroke: #000000; stroke-width: 1px;"></rect>
    <text x="60%" y="18px" text-anchor="middle" style="stroke:#000000; fill: #000000; font-size: 100%; ">Standby</text>
    <rect x="70%" y="0" width="20%" height="24" style="fill: #ff0000; stroke: #000000; stroke-width: 1px;"></rect>
    <text x="80%" y="18px" text-anchor="middle" style="stroke:#000000; fill: #000000; font-size: 100%; ">Service</text>
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
<br />
<br />
<br />
<br />
<br />
<br />
</body>
</html>
