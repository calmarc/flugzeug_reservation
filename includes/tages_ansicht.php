<?php

function print_main_bands($mysqli, $planeoffset, $jahr, $monat, $tag, $date, $tabs, $w)
{
  $now_tstamp = time();
  $query = "SELECT * FROM `flieger`;";
  $res_f = $mysqli->query($query);

  $yoffset = -$planeoffset;

  $arr = ["8:45h", "44m", "44:05h", "2:20h"];
  $arr2 = ["#666666", "#ff3333", "#666666", "#ff0000"];
  $iii = -1;
  while($obj_f = $res_f->fetch_object())
  {
    $iii++;

    $yoffset += $planeoffset;
    echo '<text x="98.6%" y="'.($yoffset-28).'px" text-anchor="end" style="fill: #000000; font-size: 120%; font-weight: bold;">'.$obj_f->flieger.'</text>'."\n";
    //$query= "SELECT MAX(`zaehler_minute`) FROM `zaehlereintraege` WHERE `flieger_id` = '".$obj_f->id.";";
    //$res4 = $mysqli->query() 
    echo '<text x="98.6%" y="'.($yoffset-28-26).'px" text-anchor="end" style="fill: '.$arr2[$iii].'; font-size: 90%; font-weight: bold;">[Service in '.$arr[$iii].']</text>'."\n";
    
    echo '<a xlink:href="reservieren.php?flieger_id='.$obj_f->id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$tag.'">';
    echo '<text x="1%" y="'.($yoffset-28).'px" style=" fill: #000099; font-size: 100%; font-weight: bold;">'.$obj_f->kurzname.' buchen</text>'."\n";
    echo '</a>';
    echo '<text x="10.8em" y="'.($yoffset-28).'px" style=" fill: #000099; font-size: 100%; font-weight: bold;">|</text>'."\n";
    echo '<a xlink:href="landungs_eintrag.php?flieger_id='.$obj_f->id.'">';
    echo '<text x="11.5em" y="'.($yoffset-28).'px" style="fill: #000099; font-size: 100%; font-weight: bold;">Eintrag nach Landung</text>'."\n";
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
  $res_f->close(); // TODO or re-interate?
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  MAIN BUCHUNG's DRAWING LOOOOOP
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function print_buchungen($mysqli, $planeoffset, $tabs, $date, $boxcol, $textcol, $tag, $monat, $jahr)
{

  // habes jahr zureuck
  date_default_timezone_set("Europe/Zurich");
  $date_xmonth_back = date("Y-m-d H:i:s", time()-20736000);
  date_default_timezone_set('UTC');

  $today_stamp_seven = strtotime($date.' 07:00:00');

  $query = "SELECT * FROM `flieger`;";
  $res_f = $mysqli->query($query);

  // -------------------------------------------------------------------------------------------------------------
  // ueber die flieger iterieren
  // -------------------------------------------------------------------------------------------------------------

  while($obj_f = $res_f->fetch_object())
  {

    // NUR ein halbes jahr zurueck gucken. hats ueberhaupt reservationen?
    // sonst Zeit markieren als $von_extrem
    $query = "SELECT `von` FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND `von` > '$date_xmonth_back'  ORDER BY `von` ASC LIMIT 1;";
    if ($res = $mysqli->query($query))
    {
      if ($res->num_rows > 0)
      {
        $obj = $res->fetch_object();
        $von_extrem = $obj->von;
      }
      else
        continue; // neuer flieger
    }

    // die max-zukunfstigste (bis)-datum gucken
    // zeit markieren ($bis_extrem)
    $query = "SELECT `bis` FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' ORDER BY `bis` DESC LIMIT 1;";
    if ($res = $mysqli->query($query))
    {
      if ($res->num_rows > 0) // eigentilch immer.. oben wurde schon geguckt
      {
        $obj = $res->fetch_object();
        $bis_extrem = $obj->bis;
      }
      else
        continue;
    }

    // halbe stunde blocks ganz links nach ganz rechts.
    $min_stamp = strtotime($von_extrem);
    $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 60 / 60 * 2)+1;

    // today's 7h
    $shift_7hour_block =  intval(($today_stamp_seven - $min_stamp) / 60 / 60 * 2);

    // should be enough of standby-levels.. else.. well. shit happens
    // TODO: only 3 or 4 allowed
    // it.: if booking[level][hour]=TRUE <- reserved
    $bookings = array(array(), array(), array(), array(), array(), array());

    for ($x = 0; $x < 6; $x++) // initialise with FALSE = free.
      for ($i = 0; $i < $half_hour_tot+1; $i++)
        $bookings[$x][$i] = FALSE;

    // alle hohlen
    $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND `von` >= '$von_extrem'  ORDER BY `timestamp` ASC;";
    $res_tang = $mysqli->query($query);

    while($obj_tang = $res_tang->fetch_object())
    {
      // 1. order(ed) them by timestamp
      // 2. have a reserved-variable for each level (green, 1.standby, ...)
      //
      // 3. check against each of the reserved-level-variables..
      //    beginning vrom gree, 1.standby, 2. 4.... until it fits
      // 4. accordingly 'book' that into the level-variable
      // 5. goto step 4.

      #transfer time to blocks (1800=30min) of current booking
      $block_first = intval((strtotime($obj_tang->von) - $min_stamp) / 1800); 
      $block_last = intval((strtotime($obj_tang->bis) - $min_stamp) / 1800)-1; 

      // look vor level where it can fit
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

      //book into according level
      for($i = $block_first; $i <= $block_last; $i++)
        $bookings[$level][$i] = TRUE;


      $print_first = $block_first - $shift_7hour_block;
      $print_last = $block_last - $shift_7hour_block;

      // trim according not printable data...
      if ($print_first > 27 || $print_last < 0)
        continue; // a booking that does not need to get printed.

      if ($print_first < 0) $print_first = 0; // trip the begin
      if ($print_last > 27) $print_last = 27; // trim the end


      $center = ($tabs[$print_first] + $tabs[$print_last+1]) / 2;
      $center = number_format ($center, 3, '.', '');

      $yoffset = $planeoffset * ($obj_f->id - 1) + $level * 20;

      $width = number_format ($tabs[$print_last+1]-$tabs[$print_first], 3, '.', '');

      echo '<rect x="'.$tabs[$print_first].'%" y="'.$yoffset.'" width="'.$width.'%" height="20" style="fill: '.$boxcol[$level].'; stroke: #000000; stroke-width: 1px;"></rect>'."\n";


      $query = "SELECT * from `members` where `id` = '".$obj_tang->userid."';";
      if ($res_id = $mysqli->query($query))
      {
        if ($res_id->num_rows > 0) // eigentilch immer.. oben wurde schon geguckt
        {
          $obj_id = $res_id->fetch_object();
          $t_id = str_pad($obj_id->pilotid, 3, "0", STR_PAD_LEFT);
        }
        else
          continue;
      }

      $rounded_stamp = time();
      // round up cur_time to half hour blocks
      $rounded_stamp = (intval($rounded_stamp / 1800) + 1) * 1800;

      $showlink = FALSE;
      if (strtotime($obj_tang->bis) > $rounded_stamp && $obj_tang->userid == $_SESSION['user_id'])
        $showlink = TRUE;

      $txtcolor = $textcol[$level];

      // always show for admins
      if (check_admin($mysqli))
      {
        $showlink = TRUE;
        $txtcolor = '#3333ff;';
      }


      $tmptxt = "";
      if ($obj_tang->userid == $_SESSION['user_id']) // user
      {
        if ($showlink)
        {
          $txtcolor = '#3333ff;';
          $tmptxt = 'onmousemove="ShowTooltip(evt, \'Löschen / Zeit freigeben\', \'\', \'\', \'\')" onmouseout="HideTooltip(evt)"';
        }
      }
      else
      {
        $tmptxt = 'onmousemove="ShowTooltip(evt, \''.addslashes(htmlspecialchars($obj_id->name)).'\', \''.addslashes(htmlspecialchars($obj_id->natel)).'\',  \''.addslashes(htmlspecialchars($obj_id->telefon)).'\', \''.addslashes(htmlspecialchars($obj_id->email)).'\')" onmouseout="HideTooltip(evt)"';
      }

      if ($showlink)
        echo '<a xlink:href="res_loeschen.php?to=ueberblick&amp;action=del&amp;reservierung='.$obj_tang->id.'&amp;tag='.$tag.'&amp;monat='.$monat.'&amp;jahr='.$jahr.'">';


      echo '<text '.$tmptxt.' x="'.$center.'%" y="'.($yoffset+16).'" text-anchor="middle" style="fill: '.$txtcolor.'; font-size: 95%; font-weight: bold;">'.$t_id.'</text>'."\n";

      if ($showlink)
        echo '</a>';
    }
  }
}

function tagesansicht($mysqli, $w, $tabs, $boxcol, $textcol, $planeoffset, $tag, $monat, $jahr, $date)
{
?>

<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="chart" height="560" >

  <script type="text/ecmascript"> 
  <![CDATA[

	function sleep(milliseconds) 
    {
	  var start = new Date().getTime();
	  for (var i = 0; i < 1e7; i++) 
	  {
		if ((new Date().getTime() - start) > milliseconds)
		{
		  break;
		}
	  }
	}
    function ShowTooltip(evt, name, natel, telefon, email) 
    {
      sleep(80);
      var x = +evt.clientX - 100;
      var y = +evt.clientY + 10;
      var element;
      var counter;
      if (x < 50)
      {
        x = x + 50;
      }
      document.getElementById("tooltip_div").setAttributeNS(null, "style", "display: block; visibility: visible; position: absolute; left: " + x + "px; top: " + y + "px;");

      element = document.getElementById("tooltip_svg").firstChild;
      element = element.nextSibling;


      counter = 0;
      if (name != "")
      {
        element.firstChild.data = name;
        element = element.nextSibling;
        element = element.nextSibling;
        counter++;
      }
      if (natel != "")
      {
        element.firstChild.data = natel;
        element = element.nextSibling;
        element = element.nextSibling;
        counter++;
      }
      if (telefon != "")
      {
        element.firstChild.data = telefon;
        element = element.nextSibling;
        element = element.nextSibling;
        counter++;
      }
      if (email != "")
      {
        element.firstChild.data = email;
        counter++;
      }

      document.getElementById("tooltip_svg").setAttributeNS(null, "height", (counter * 25) + "px");
    }
    function HideTooltip(evt) 
    {
      sleep(80);
      document.getElementById("tooltip_div").setAttributeNS(null, "style", "display: none; visibility: hidden;");
    }

  ]]>
  </script>

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

  <g transform="translate(4,72)">
  <?php

  // print GREEN etc (lowest layer) stuff

  print_main_bands($mysqli, $planeoffset, $jahr, $monat, $tag, $date, $tabs, $w);

  remove_zombies($mysqli);

  // TODO colors etc into defines? konstats etc?
  print_buchungen($mysqli, $planeoffset, $tabs, $date, $boxcol, $textcol, $tag, $monat, $jahr);

  echo '</g></svg>';

  legende_print($boxcol);
  tooltip_print();

}
?>
