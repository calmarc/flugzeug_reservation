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
  <meta http-equiv="refresh" content="1800">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/css/reservationen.css">
  <script src="//code.jquery.com/jquery-1.10.2.min.js"></script>

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
<h1 id="buchung">Buchungen: <?php echo "$tag.&nbsp;".$monate[$monat-1];?> 2016</h1>
<?php

// 'konstants' needed below
$w = number_format (98/28.0, 3, '.', ''); // WIDTH of tabs

$tabs = array(); // TABS to place stuff
for ($i = 0; $i <= 28;  $i++)
{
  array_push($tabs, number_format ($i*$w+0.8, 3, '.', ''));
}

$j = str_pad($jahr, 2, "0", STR_PAD_LEFT);
$m = str_pad($monat, 2, "0", STR_PAD_LEFT);
$t = str_pad($tag, 2, "0", STR_PAD_LEFT);
$date = "$j-$m-$t"; // DATE

$now_tstamp = time();

//buchungs-colors: blue       yellow     orange     yellow     orange       red  
$boxcol =   array('#33ccff', '#ffff99', '#ffee99', '#ffff99', '#ffee99', '#ff6666');
$textcol =  array('#000000', '#333333', '#333333', '#333333', '#333333', '#333333');

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
      <stop offset="0%"   stop-color="#a0a0a0" stop-opacity="1"/>
      <stop offset="100%" stop-color="#b0b0b0" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="grey2" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#bbbbbb" stop-opacity="1"/>
      <stop offset="100%" stop-color="#cccccc" stop-opacity="1"/>
    </linearGradient>
    <linearGradient id="gelblich" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="#ffff33" stop-opacity="1"/>
      <stop offset="100%" stop-color="#ffcc33" stop-opacity="1"/>
    </linearGradient>
  </defs>

  <g transform="translate(4,84)">
  <?php

// =================================================================================
// MAIN LOOP
// =================================================================================

// print GREEN etc (lowest layer) stuff
// -------------------------------------
$yoffset = -$planeoffset;

$query = "SELECT * FROM `flieger`;";
$res_f = $mysqli->query($query);

while($obj_f = $res_f->fetch_object())
{
  $yoffset += $planeoffset;
  echo '<text x="98.6%" y="'.($yoffset-28).'px" text-anchor="end" style="fill: #000000; font-size: 120%; font-weight: bold;">'.$obj_f->flieger.'</text>'."\n";
  
  echo '<a xlink:href="reservieren.php?flieger_id='.$obj_f->id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$tag.'">';
  echo '<text x="1%" y="'.($yoffset-28).'px" style=" fill: #000099; font-size: 100%; font-weight: bold;">Flieger buchen</text>'."\n";
  echo '</a>';
  echo '<text x="9.7em" y="'.($yoffset-28).'px" style=" fill: #000099; font-size: 100%; font-weight: bold;">|</text>'."\n";
  echo '<a xlink:href="reservieren.php">';
  echo '<text x="10.5em" y="'.($yoffset-28).'px" style="fill: #000099; font-size: 100%; font-weight: bold;">Eintrag nach Landung</text>'."\n";
  echo '</a>';
  echo '<a xlink:href="reservieren.php">';
  echo '<text x="1%" y="'.($yoffset-48).'px" style="fill: #990022; font-size: 100%; font-weight: bold;">Serviceliste</text>'."\n";
  echo '</a>';

  for ($i = 0; $i < 28; $i++)
  {
    //////////////////////////// GRUEN (default)

    $color = "gruen";
    // 7*60*60 (7 stunden) + 30*60 (halbe stunde) * i -> startzeit block in
    $print_stamp = strtotime($date." 00:00:00") + 30*60*$i + (7*60*60); // 30min*..x + 7h to print..

    if ($now_tstamp > $print_stamp)
      $color = "grey";

    $minute = ($i % 2 ? 30 : 0); 
    $stunde = intval(7 + ($i / 2));

    if ($i % 2 == 0)
    {
      if ($color == 'gruen')
        echo '<a xlink:href="reservieren.php?flieger_id='.$obj_f->id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$tag.'&amp;stunde='.$stunde.'&amp;minute='.$minute.'">';

      echo '<rect x="'.$tabs[$i].'%" y="'.($yoffset).'" width="'.$w.'%" height="20" style="fill:url(#'.$color.'1); stroke: #000000; stroke-width: 1px;"></rect>'."\n";

      if ($color == 'gruen')
        echo '</a>';

      //////////////////////////// H-LINIE
      echo '<line x1="'.$tabs[$i].'%" y1="'.($yoffset-20).'" x2="'.$tabs[$i].'%" y2="'.($yoffset+20).'" style="stroke:#000000; stroke-width: 3px;" />'."\n";

      //////////////////////////// ZEITEN
      $tmp = (string) number_format(($tabs[$i]+0.5), 3, '.', '');

      echo '<text x="'.$tmp.'%" y="'.($yoffset-4).'" style="fill: #666666; font-size: 80%;"><tspan>'.($i/2+7).'</tspan><tspan class="hide">:00</tspan></text>'."\n";
    }
    else
    {
      if ($color == 'gruen')
        echo '<a xlink:href="reservieren.php?flieger_id='.$obj_f->id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$tag.'&amp;stunde='.$stunde.'&amp;minute='.$minute.'">';

      echo '<rect x="'.$tabs[$i].'%" y="'.($yoffset).'" width="'.$w.'%" height="20" style="fill:url(#'.$color.'2); stroke: #000000; stroke-width: 1px;"></rect>'."\n";

      if ($color == 'gruen')
        echo '</a>';
    }

  }
}

$datetime0 = $date." 00:00:00";
$datetime24 = $date." 23:59:59";

$today_stamp_min = strtotime($date.' 07:00:00');
$today_stamp_max = strtotime($date.' 21:00:00');

$res_f->close(); // TODO or re-interate?
$query = "SELECT * FROM `flieger`;";
$res_f = $mysqli->query($query);

// ueber die flieger iterieren
while($obj_f = $res_f->fetch_object())
{
  // alle Reservierungen welche in diesen Zeitraum tangieren.
  // search for youngest on bis and oldest on von.. this is the timeframe to
  // shuffle then.
  $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND ( `bis` > '$datetime0' AND `von` < '$datetime24') ORDER BY `timestamp` ASC;";
  $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND ( `bis` > '$datetime0' AND `von` < '$datetime24') ORDER BY `timestamp` ASC;";

  $res_tang = $mysqli->query($query);

  // should be enough of standby-levels.. else.. well. shit happens
  // TODO: only 3 or 4 allowed
  // it.: if booking[level][hour]=TRUE <- reserved
  $bookings = array(array(), array(), array(), array(), array(), array(), array(), array(), array(), array());

  for ($x = 0; $x < 10; $x++) // initialise with FALSE = free.
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

    #transfer time to blocks limit to today 7-21h
    $von_stamp = strtotime($obj_tang->von);
    $bis_stamp = strtotime($obj_tang->bis);

    if ($von_stamp < $today_stamp_min)
      $von_stamp = $today_stamp_min;

    if ($bis_stamp > $today_stamp_max)
      $bis_stamp = $today_stamp_max;

    // /1800 = halbe stunden
    $block_first = intval(($von_stamp - $today_stamp_min) / 1800); 
    $block_last = intval(($bis_stamp - $today_stamp_min) / 1800)-1; 

    $level = 0;
    while(TRUE) 
    { 
      $flag = FALSE;
      for($i = $block_first; $i <= $block_last; $i++) // max for a day
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
    for($i = $block_first; $i <= $block_last; $i++)
      $bookings[$level][$i] = TRUE;

    //where to unbook??? no need to.. it gets build up from scratch all the time

    $center = ($tabs[$block_first] + $tabs[$block_last+1]) / 2;
    $center = number_format ($center, 3, '.', '');

    if ($level > 4)
      continue; // don't print more standbys than 4 

    $yoffset = $planeoffset * ($obj_f->id - 1) + $level * 20;

    $width = number_format ($tabs[$block_last+1]-$tabs[$block_first], 3, '.', '');


    echo '<a xlink:href="reservieren.php">';
    echo '<rect x="'.$tabs[$block_first].'%" y="'.$yoffset.'" width="'.$width.'%" height="20" style="fill: '.$boxcol[$level].'; stroke: #000000; stroke-width: 1px;"></rect>'."\n";

    echo '</a>';

    $query = "SELECT `pilotid` from `members` where `id` = '".$obj_tang->userid."';";
    $res_id = $mysqli->query($query);
    $obj_id = $res_id->fetch_object();
    $t_id = str_pad($obj_id->pilotid, 3, "0", STR_PAD_LEFT);

    echo '<text x="'.$center.'%" y="'.($yoffset+16).'" text-anchor="middle" style="fill: '.$textcol[$level].'; font-size: 90%; font-weight: bold;">'.$t_id.'</text>'."\n";
  }
}
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
<br />
<br />
</body>
</html>
