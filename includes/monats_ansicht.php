<?php


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  MAIN BUCHUNG's DRAWING LOOOOOP
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function print_buchungen_monat($mysqli, $flieger_id, $boxcol, $textcol, $jahr, $monat, $tabs, $w, $tag_v_offset)
{

  // habes jahr zureuck
  $date_xmonth_back = date("Y-m-d H:i:s", time()-20736000);

  $monat_2 = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $anzahl_tage = date("t", strtotime("$jahr-$monat_2-01"));

  $stamp_print_minimum = strtotime("$jahr-$monat_2-01 07:00:00");
  $stamp_print_maximum = strtotime("$jahr-$monat_2-$anzahl_tage 23:59:59");

  // NUR ein halbes jahr zurueck gucken. hats ueberhaupt reservationen?
  // sonst Zeit markieren als $von_extrem
  $query = "SELECT `von` FROM `reservationen` WHERE `fliegerid` = '$flieger_id' AND `von` > '$date_xmonth_back'  ORDER BY `von` ASC LIMIT 1;";
  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0)
    {
      $obj = $res->fetch_object();
      $von_extrem = $obj->von;
    }
    else
      return;
  }

  // die max-zukunfstigste (bis)-datum gucken
  // zeit markieren ($bis_extrem)
  $query = "SELECT `bis` FROM `reservationen` WHERE `fliegerid` = '$flieger_id' ORDER BY `bis` DESC LIMIT 1;";
  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0) // eigentilch immer.. oben wurde schon geguckt
    {
      $obj = $res->fetch_object();
      $bis_extrem = $obj->bis;
    }
    else
      return;
  }

  // halbe stunde blocks ganz links nach ganz rechts.
  $min_stamp = strtotime($von_extrem);
  $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 60 / 60 * 2)+1;

  $shift_1ster_monat_block =  intval(($stamp_print_minimum - $min_stamp) / 60 / 60 * 2);

  // only 3 or 4 allowed
  // it.: if booking[level][hour]=TRUE <- reserved
  $bookings = array(array(), array(), array(), array(), array(), array());

  for ($x = 0; $x < 6; $x++) // initialise with FALSE = free.
    for ($i = 0; $i < $half_hour_tot+1; $i++)
      $bookings[$x][$i] = FALSE;

  // alle hohlen
  $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '$flieger_id' AND `von` >= '$von_extrem'  ORDER BY `timestamp` ASC;";
  $res_tang = $mysqli->query($query);

  // 1. order(ed) them by timestamp
  // 2. have a reserved-variable for each level (green, 1.standby, ...)
  //
  // 3. check against each of the reserved-level-variables..
  //    beginning vrom gree, 1.standby, 2. 4.... until it fits
  // 4. accordingly 'book' that into the level-variable
  // 5. print it when there is something to print on that level
  // 6. goto step 4.
  while($obj_tang = $res_tang->fetch_object())
  {
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

    // entweder muss man fuer jeden tag das ganze basteln.. und mischeln etc..
    // oder aber man berechnet die position der reservation.. tia.. das letztere
    // muss wohl gemacht werden....
    // eigentlich muss man halt den tag, und die halbe stunde ermitteln.. und
    // zeichnen.

    // iterate over days....
    //// today's 7h
    // ueber jeden tag durchgehen
    // rechteecke zeichen - jeden 7ten tag.. zeitlinie
    

    $print_first = $block_first - $shift_1ster_monat_block;
    $print_last = $block_last - $shift_1ster_monat_block;

    // nur muessen diese jetzt durch die tage geteilt werden...  im bereich
    // $anzahl_tage . und der rest .. dann die stunden.. etc.. dort einfuegen

    // trim according not printable data...
    // TODO: > monats halbe stunden... $anzahl_tage * 48 - 4 oder so
    //if ($print_first > 27 || $print_last < 0)
      //continue; // a booking that does not need to get printed.

    //if ($print_first < 0) $print_first = 0; // trip the begin
    //if ($print_last > 27) $print_last = 27; // trim the end

    //TODO.. muesste nicht noetig sein.. wird ja kontrolliert
    //if ($level > 3)
      //continue; // don't print more standbys than 3 

    // TODO muss berechnet werden (7std schon drinnen)
    // /48 (halbe stunden) ergibt den tag
    // %48 (halbe stunden) ergibt den block dort

    
    $tag_offset_von = intval($print_first / 48);
    $print_first = intval($print_first % 48);
    $tag_offset_bis = intval($print_last / 48);
    $print_last = intval($print_last % 48);

    $print_first_orig = $print_first;
    $print_last_orig = $print_last;

    $x = $tag_offset_von;

    $print_nr_on = $tag_offset_von + intval(($tag_offset_bis - $tag_offset_von) / 2);

    while ($x <= $tag_offset_bis)
    {

      // von wo bis wo...
      if ($tag_offset_von == $x)
        $print_first = $print_first_orig; // anfange hier
      else
        $print_first = 0; // von 7 uhr

      if ($x == $tag_offset_bis)
        $print_last = $print_last_orig;
      else
        $print_last = 27;

      // zeichenn von print-first on tag_von bis nach tag_bis block_bis
      $center = ($tabs[$print_first] + $tabs[$print_last+1]) / 2;
      $center = number_format ($center, 3, '.', '');

      $width = number_format ($tabs[$print_last+1]-$tabs[$print_first], 3, '.', '');

      $yoffset = $tag_v_offset[$x];

      if ($level > 0)
      {

        $line_length = number_format ($tabs[$print_first]+$width, 3, '.', '');
        echo '<line x1="'.$tabs[$print_first].'%" y1="'.($yoffset+2).'" x2="'.$line_length.'%" y2="'.($yoffset+2).'" style="stroke: '.$boxcol[$level].'; stroke-width: 3px;"></line>'."\n";
        echo '<line x1="'.$tabs[$print_first].'%" y1="'.($yoffset+4).'" x2="'.$line_length.'%" y2="'.($yoffset+4).'" style="stroke: #000000; stroke-width: 1px;"></line>'."\n";
      }
      else
      {
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

        // print pilotennummer
        if ($print_nr_on == $x)
        {
          // always show for admins
          // TODO: too expensive?
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
            echo '<a onclick="return confirm(\'Buchung wirklich löschen/ Zeit freigeben?\')" xlink:href="res_loeschen.php?to=ueberblick&amp;action=del&amp;reservierung='.$obj_tang->id.'&amp;monat='.$monat.'&amp;jahr='.$jahr.'">';
          // TODO: tag geloescht hiere...


          echo '<text '.$tmptxt.' x="'.$center.'%" y="'.($yoffset+16).'" text-anchor="middle" style="fill: '.$txtcolor.'; font-size: 95%; font-weight: bold;">'.$t_id.'</text>'."\n";

          if ($showlink)
          echo '</a>';
        }
      }
     
      $x++; // reiterate if necessary
    }
  }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////
//
// MONATSANSICHT
//
//////////////////////////////////////////////////////////////////////////////////////////////////////////

function print_main_bands_monat($mysqli, $jahr, $monat, $tabs, $w, $flieger_id)
{


  $tag_v_offset = array();

  $monat_2 = str_pad($monat, 2, "0", STR_PAD_LEFT);

  $now_tstamp = time(); // since: 01/01/1970 @ 12:00am (UTC)
  $laufender_stamp = strtotime("$jahr-$monat_2-01 00:00 UTC"); // minus tag - addiert sich dazu

  //date_default_timezone_set("Europe/Berlin");
  echo "\n<!-- ===========================[\n ";
  echo $now_tstamp;
  echo "\n";
  echo $laufender_stamp;
  echo "\n";
  echo $now_tstamp - $laufender_stamp;
  echo "\n";
  echo ($now_tstamp - $laufender_stamp)/60;
  echo "\n";
  echo ($now_tstamp - $laufender_stamp)/60 / 60;
  echo "\n tage \n";
  echo ($now_tstamp - $laufender_stamp)/60 / 60 / 24;
  echo "\n tage \n";
  echo (($now_tstamp - $laufender_stamp)/60 / 60) % 24;
  echo "\n";
  echo date("Y-m-d h:m", time());
  echo "\n ]============================= -->\n";

  $anzahl_tage = date("t", $laufender_stamp);

  $erster_wochentag = date("N", $laufender_stamp); // 1(Mon)-7(Son) TODO: evt zusammen mit oben
  $heute_monats_tag = date ("d", time());

  $day_counter = 0;

  $yoffset = 20;

  // kleine linie ganz links zwischen wochen
  echo '<line x1="1.0%" y1="'.($yoffset+20).'" x2="'.$tabs[0].'%" y2="'.($yoffset+20).'" style="stroke:#000000; stroke-width: 1px;" />'."\n";

  // ERSTE OBERE ZEIT-LINIE
  for ($i = 0; $i < 28; $i++)
  {
    if ($i % 2 == 0)
    {
      echo '<line x1="'.$tabs[$i].'%" y1="'.($yoffset).'" x2="'.$tabs[$i].'%" y2="'.($yoffset+40).'" style="stroke:#000000; stroke-width: 3px;" />'."\n";
      $tmp = number_format(($tabs[$i]+0.5), 3, '.', '');
      echo '<text x="'.$tmp.'%" y="'.($yoffset+16).'" style="fill: #666666; font-size: 80%;"><tspan>'.($i/2+7).'</tspan><tspan class="hide">:00</tspan></text>'."\n";
    }
  }

  $laufender_stamp -= 24*60*60; // kommt grad wieder dazu
  // ueber jeden tag durchgehen
  // rechteecke zeichen - jeden 7ten tag.. zeitlinie
  while($anzahl_tage > $day_counter)
  {
    $day_counter++;
    $laufender_stamp += 24*60*60; // hinzu mit Tag

    $yoffset += 20;

    // ZEIT-LINIE jedes 7te mal
    if (($day_counter + $erster_wochentag - 2) % 7 == 0 && $day_counter > 1) // nicht beim ersten tag.. da wurde schon
    {
      echo '<line x1="1.0%" y1="'.($yoffset).'" x2="'.$tabs[0].'%" y2="'.($yoffset).'" style="stroke:#000000; stroke-width: 1px;" />'."\n";
      echo '<line x1="1.0%" y1="'.($yoffset+20).'" x2="'.$tabs[0].'%" y2="'.($yoffset+20).'" style="stroke:#000000; stroke-width: 1px;" />'."\n";
      for ($i = 0; $i < 28; $i++)
      {
        if ($i % 2 == 0)
        {
          echo '<line x1="'.$tabs[$i].'%" y1="'.($yoffset).'" x2="'.$tabs[$i].'%" y2="'.($yoffset+40).'" style="stroke:#000000; stroke-width: 3px;" />'."\n";
          $tmp = number_format(($tabs[$i]+0.5), 3, '.', '');
          echo '<text x="'.$tmp.'%" y="'.($yoffset+16).'" style="fill: #666666; font-size: 80%;"><tspan>'.($i/2+7).'</tspan><tspan class="hide">:00</tspan></text>'."\n";
        }
      }
      $yoffset += 20;
    }

    // 1-31 ganz links
    $color = "#000000";
    if (($day_counter + $erster_wochentag - 1) % 7 == 0)
      $color = "#0000ff";
    else if (($day_counter + $erster_wochentag) % 7 == 0)
      $color = "#ff0000";


    echo '<a xlink:href="index.php?tag='.$day_counter.'&amp;monat='.$monat.'&amp;jahr='.$jahr.'">';
    echo '<text x="1.6%" y="'.($yoffset+16).'" style="fill: '.$color.'; font-size: 80%; font-weight: bold;">'.str_pad($day_counter, 2, "0", STR_PAD_LEFT).'</text>'."\n";
    echo '</a>';

    array_push($tag_v_offset, $yoffset); // todo: may fine tune.. (needs on buchungen print)

    for ($i = 0; $i < 28; $i++)
    {
      $laufender_stamp += 30 * 60; // halbe stunde hinzu
      $color = "grey";
      if ($laufender_stamp >= $now_tstamp)
        $color = "gruen";

      $t_std = 7+intval($i/2);
      $t_min = ($i % 2) * 30;

      if ($i % 2 == 0)
      {
        if ($color == 'gruen')
          echo '<a xlink:href="reservieren.php?flieger_id='.$flieger_id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$day_counter.'&amp;stunde='.$t_std.'&amp;minute='.$t_min.'">';
        echo '<rect x="'.$tabs[$i].'%" y="'.($yoffset).'" width="'.$w.'%" height="20" style="fill:url(#'.$color.'1); stroke: #000000; stroke-width: 1px;"></rect>'."\n";
        if ($color == 'gruen')
          echo '</a>';
      }
      else
      {
        if ($color == 'gruen')
          echo '<a xlink:href="reservieren.php?flieger_id='.$flieger_id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$day_counter.'&amp;stunde='.$t_std.'&amp;minute='.$t_min.'">';
        echo '<rect x="'.$tabs[$i].'%" y="'.($yoffset).'" width="'.$w.'%" height="20" style="fill:url(#'.$color.'2); stroke: #000000; stroke-width: 1px;"></rect>'."\n";
        if ($color == 'gruen')
          echo '</a>';
      }
    }
  }
  echo '<line x1="1.0%" y1="'.($yoffset+20).'" x2="'.$tabs[0].'%" y2="'.($yoffset+20).'" style="stroke:#000000; stroke-width: 1px;" />'."\n";
  return $tag_v_offset;
}

function monatsansicht($mysqli, $w, $tabs, $boxcol, $textcol, $monat, $jahr)
{
?>

<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="chart_monat">

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

  <g transform="translate(4,4)">
  <?php

// print GREEN etc (lowest layer) stuff

$flieger_id = 1; //TODO from $_GET etc
$tag_v_offset = print_main_bands_monat($mysqli, $jahr, $monat, $tabs, $w, $flieger_id);

// TODO colors etc into defines? konstats etc?
print_buchungen_monat($mysqli, $flieger_id, $boxcol, $textcol, $jahr, $monat, $tabs, $w, $tag_v_offset);

?>
</g>
</svg>

<div class="center" style="margin-top: 16px;" >
  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
        height="130px" style="background-color: transparent; width: 60%; min-width: 660px;" >
    <defs>
      <linearGradient id="gruen0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#66ee66" stop-opacity="1"/>
        <stop offset="100%" stop-color="#99ee99" stop-opacity="1"/>
      </linearGradient>
      <linearGradient id="gelblich0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="<?php echo $boxcol[1];?>" stop-opacity="1"/>
        <stop offset="100%" stop-color="<?php echo $boxcol[2];?>" stop-opacity="1"/>
      </linearGradient>
      <linearGradient id="grey0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#dddddd" stop-opacity="1"/>
        <stop offset="100%" stop-color="#eeeeee" stop-opacity="1"/>
      </linearGradient>
    </defs>
   <g transform="translate(0, 0)">
    <rect x="0.1%" y="0" width="20%" height="24" style="fill:url(#grey0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="10.0%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Vergangenheit</text>
    <rect x="20%" y="0" width="20%" height="24" style="fill:url(#gruen0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="30%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Frei</text>
    <rect x="40%" y="0" width="20%" height="24" style="fill: <?php echo $boxcol[0]; ?>; stroke: #666666; stroke-width: 1px;"></rect>
    <text x="50%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Gebucht</text>
    <rect x="60%" y="0" width="20%" height="24" style="fill: url(#gelblich0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="70%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Standby</text>
    <rect x="79.9%" y="0" width="20%" height="24" style="fill: <?php echo $boxcol[5]; ?>; stroke: #666666; stroke-width: 1px;"></rect>
    <text x="90.0%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Service</text>
  </g>
  </svg>
</div>

<div onclick="document.getElementById('tooltip_div').style.display = 'none';" id="tooltip_div" style="display: none; visibility: hidden;">
  <svg id="tooltip_svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
        height="100px" width="200px" >
    <text id="tooltip_text1" x="3%" y="20px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text2" x="3%" y="45px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text3" x="3%" y="70px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text4" x="3%" y="95px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
  </svg>
</div>
<?php
}

?>
