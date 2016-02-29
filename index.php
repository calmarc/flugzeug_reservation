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

function xy($mysqli, $planeoffset)
{
  $query = "SELECT * FROM `flieger`;";
  $res_f = $mysqli->query($query);

  $yoffset = -$planeoffset;

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
}

xy($mysqli, $planeoffset);


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ZOMBIES ENTFERNEN = alle buchungen in der vergangenheit.. welche nicht aktiv
// wuerden...
// (nachher nimmt man alle buchen welhce ins jetzt reichen (die eine maximal)
// und die in der  unlimitierte zukunft.
//
// dafuer erst mischlen.. mal alles was da ist..
//
// dann die finden welche aktiv ist und ins jetzt reinreicht.
// mit aktiv markieren... den rest (ausser hat aktive flag gesetzt) loeschen.


// /////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ueber die flieger iterieren
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////


// zum loeschen und fuer die letzte 'gute' buchung welche noch reingucken kann.

//$now_string = date("Y-m-d H:i:s");

//while($obj_f = $res_f->fetch_object())
//{
  //echo "\n<!-- ===========================[\n ";
  //var_dump($obj_f);
  //var_dump($obj_f);
  //var_dump($obj_f);
  //echo "\n ]============================= -->\n";
  //echo "\n ]============================= -->\n";
  //echo "\n ]============================= -->\n";
  //echo "\n ]============================= -->\n";
  //echo "\n ]============================= -->\n";
  //echo "\n ]============================= -->\n";
  //echo "\n ]============================= -->\n";
  //// TODO: ich nehmen mal alle.. aber eigentlich alte.. oder 'gute' (geflogen // actually) nicht??
  ////
  //$query = "SELECT `von` FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' ORDER BY `von` ASC LIMIT 1;";
  //if ($res = $mysqli->query($query))
  //{
    //if ($res->num_rows > 0)
    //{
      //$obj = $res->fetch_object();
      //$von_extrem = $obj->von;
    //}
    //else
      //continue;
  //}
    
  //$query = "SELECT `bis` FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' ORDER BY `bis` DESC LIMIT 1;";
  //if ($res = $mysqli->query($query))
  //{
    //if ($res->num_rows > 0)
    //{
      //$obj = $res->fetch_object();
      //$bis_extrem = $obj->bis;
    //}
    //else
      //continue;
  //}

  //$query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND ( `bis` > '$von_extrem' AND `von` < '$bis_extrem') ORDER BY `timestamp` ASC;";

  //$res_tang = $mysqli->query($query);

  //// should be enough of standby-levels.. else.. well. shit happens
  //// TODO: only 3 or 4 allowed
  //// it.: if booking[level][hour]=TRUE <- reserved
  //$bookings = array(array(), array(), array(), array(), array(), array(), array(), array(), array(), array());

  //// diff of von_ext to bis_ext and then half-hour blocks.. initialise
  //$min_stamp = strtotime($von_extrem);
  //$half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 60 / 60 * 2)+1;

  //// 1. block is time $von_extrem .. 'today's 7h block is?
  //$shift_today_block =  intval(($today_stamp_min - $min_stamp) / 60 / 60 * 2);

  //for ($x = 0; $x < 10; $x++) // initialise with FALSE = free.
    //for ($i = 0; $i < $half_hour_tot+1; $i++)
      //$bookings[$x][$i] = FALSE;

  //$delete_id = array();

  //while($obj_tang = $res_tang->fetch_object())
  //{
    //// 1. order(ed) them by timestamp
    //// 2. have a reserved-variable for each level (green, 1.standby, ...)
    ////
    //// 3. check against each of the reserved-level-variables..
    ////    beginning vrom gree, 1.standby, 2. 4.... until it fits
    //// 4. accordingly 'book' that into the level-variable
    //// 5. goto step 4.

    //#transfer time to blocks limit to today 7-21h
    //$von_stamp = strtotime($obj_tang->von);
    //$bis_stamp = strtotime($obj_tang->bis);

    //// /1800 = halbe stunde
    //$block_first = intval(($von_stamp - $min_stamp) / 1800); 
    //$block_last = intval(($bis_stamp - $min_stamp) / 1800)-1; 

    //// TODO: muss doch wesentlich einfacher schneller gehen, als den ganzen
    //// Muell ... aber mal egal..
    //$level = 0;
    ////while(TRUE) 
    ////{ 
      ////$flag = FALSE;
    //for($i = $block_first; $i <= $block_last; $i++) // max for a day
    //{
      //if ($bookings[$level][$i] == TRUE)
      //{
        //// Ops, not free - try next level
        //$level++;
        ////$flag = TRUE;
        //break; // out of for loop
      //}
    //}
      ////if ($flag == FALSE)
        ////break;
    ////}

    //// not green.... mark..later when here and start in past.. delete
    //if ($level > 0)
      //array_push ($delete_id, $obj_tang->id);
    //else
      ////book into level 0 only
      //for($i = $block_first; $i <= $block_last; $i++)
        //$bookings[$level][$i] = TRUE;
  //}

  //// sodali.. jetzt haben wir bookings welche nicht.. mit den levels.. hm..

  //foreach($delete_id as $di)
  //{
    //if ($stmt = $mysqli->prepare("DELETE FROM `calmarws_test`.`reservationen` WHERE `reservationen`.`id` = ? AND `von` < ?;"))
    //{
      //$stmt->bind_param('is', $di, $now_string);
      //if (!$stmt->execute()) 
      //{
          //header('Location: /reservationen/login/error.php?err=Registration failure: STANDBY DELETE ');
          //exit;
      //}
    //}
  //}
//}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  MAIN BUCHUNG's DRAWING LOOOOOP
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//$datetime0 = $date." 00:00:00";
//$t = intval(substr($datetime0, 0, 4));
//$n = $t-1;  // daten ein jahr in die vergangenheit abrufbar
//$datetime0 = strtr($datetime0, $t, $n);

// limit query to 240days (-15 in sec) before (60*24*30*3)
// allows to scan back in history to see there.

$datetime0 = date("Y-m-d H:i:s", time()-20736000);

$today_stamp_min = strtotime($date.' 07:00:00');

$res_f->close(); // TODO or re-interate?
$query = "SELECT * FROM `flieger`;";
$res_f = $mysqli->query($query);

// -------------------------------------------------------------------------------------------------------------
// ueber die flieger iterieren
// -------------------------------------------------------------------------------------------------------------

while($obj_f = $res_f->fetch_object())
{

  // hat neue (oder die einen aktive die reingucken mag) reservierungen beim flieger?
  // wenn nicht.. neuer flieger.
  $query = "SELECT `von` FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND `von` > '$datetime0'  ORDER BY `von` ASC LIMIT 1;";
  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0)
    {
      $obj = $res->fetch_object();
      $von_extrem = $obj->von;
    }
    else
      continue;
  }

  // die letzte letzte gucken (muss in der kukunft liegen)
  $query = "SELECT `bis` FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' ORDER BY `bis` DESC LIMIT 1;";
  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0)
    {
      $obj = $res->fetch_object();
      $bis_extrem = $obj->bis;
    }
    else
      continue;
  }

  // alle.. aber von den fruehere nur die eine welche ueberahupt reingucken kann
  // - es kann nur eine sein.... die ist relevant.. der rest hinter der nicht
  $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND `von` >= '$von_extrem'  ORDER BY `timestamp` ASC;";
  $res_tang = $mysqli->query($query);

  // should be enough of standby-levels.. else.. well. shit happens
  // TODO: only 3 or 4 allowed
  // it.: if booking[level][hour]=TRUE <- reserved
  $bookings = array(array(), array(), array(), array(), array(), array(), array(), array(), array(), array());

  // diff of von_ext to bis_ext and then half-hour blocks.. initialise
  $min_stamp = strtotime($von_extrem);
  $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 60 / 60 * 2)+1;

  // 1. block is time $von_extrem .. 'today's 7h block is?
  $shift_today_block =  intval(($today_stamp_min - $min_stamp) / 60 / 60 * 2);

  for ($x = 0; $x < 10; $x++) // initialise with FALSE = free.
    for ($i = 0; $i < $half_hour_tot+1; $i++)
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

    // / 1800 = halbe stunden
    $block_first = intval(($von_stamp - $min_stamp) / 1800); 
    $block_last = intval(($bis_stamp - $min_stamp) / 1800)-1; 

    $level = 0;
    while(TRUE) 
    { 
      $flag = FALSE;
      for($i = $block_first; $i <= $block_last; $i++)
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


    $block_first = $block_first - $shift_today_block;
    $block_last = $block_last - $shift_today_block;

    // trim according not printable data...
    if ($block_first > 27 || $block_last < 0)
      continue; // a booking that does not need to get printed.

    if ($block_first < 0) $block_first = 0; // trip the begin
    if ($block_last > 27) $block_last = 27; // trim the end


    $center = ($tabs[$block_first] + $tabs[$block_last+1]) / 2;
    $center = number_format ($center, 3, '.', '');

    if ($level > 4)
      continue; // don't print more standbys than 4 

    $yoffset = $planeoffset * ($obj_f->id - 1) + $level * 20;

    $width = number_format ($tabs[$block_last+1]-$tabs[$block_first], 3, '.', '');


    echo '<rect x="'.$tabs[$block_first].'%" y="'.$yoffset.'" width="'.$width.'%" height="20" style="fill: '.$boxcol[$level].'; stroke: #000000; stroke-width: 1px;"></rect>'."\n";


    $query = "SELECT `pilotid` from `members` where `id` = '".$obj_tang->userid."';";
    $res_id = $mysqli->query($query);
    $obj_id = $res_id->fetch_object();
    $t_id = str_pad($obj_id->pilotid, 3, "0", STR_PAD_LEFT);

    echo '<a xlink:href="reservieren.php">';
    echo '<text x="'.$center.'%" y="'.($yoffset+16).'" text-anchor="middle" style="fill: '.$textcol[$level].'; font-size: 90%; font-weight: bold;">'.$t_id.'</text>'."\n";
    echo '</a>';
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
