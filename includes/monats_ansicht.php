<?php

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Ablauf
//
// 1 die grundstruktur zeichen
//   :requirements:
//   anzahl tage
//   was ist tag am 1ten? (fuer die leocher)
//   'jetzt ist was' (grau/gruen)
//
//   a) zeichnen von
//      . waehrend dem die offsets der tage speichern (fuer buchungen)
//
// 2 die buchungen reinmontieren
//   :requirements:
//   von .. und bis (letztes bis)range ermitteln relvanter buchungen
//
//   a) flieger in dieser range drucken
//      .halbe stund bloecke ermitteln (in range)
//      .die speichern wo gedruckt, fuer standbyes (bookings..)
//      .gucken ob in die tabelle passt (>1ster_monat-block < +blocks im monat)
//      .trimmen
//      .von in tage teilen (fuer starttag + offset
//      .bis in tage teilen (fuer endtag + offset)
//      .print_nr-on - wo die nummer reinschreiben (mitte der tage)
//        (an dem tag (wenns kommt) - mitte ermitteln und dort reinschreiben)
//      . gelb (level = 0) striche sonst blau
//                                       rechteck.
//                                       members infos.. fuer tooltip
//
//
//

////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  MAIN BUCHUNG's DRAWING LOOOOOP
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function monatsplan_navigation($mysqli, $flieger_id, $jahr, $monat, $jahr, $monate, $tag)
{
  // Monatsnavigation
  $z_jahr = $jahr;
  $v_jahr = $jahr;

  $z_monat = $monat - 1;
  $v_monat = $monat + 1;

  if ($z_monat < 1) { $z_monat = 12; $z_jahr--; }
  if ($v_monat > 12) { $v_monat = 1; $v_jahr++; }
  echo "<form method='get' action='index.php'>";
  echo "<div class='center'>";
  echo "<table id='monat_title'><tr>";
  echo "<td style='padding-right: 20px;'>";
  echo "<a href='/reservationen/index.php?flieger_id={$flieger_id}&amp;show=monat&amp;monat={$z_monat}&amp;jahr={$z_jahr}&amp;tag={$tag}'><span>&laquo;</span></a>";
  echo "</td><td>";
  echo $monate[$monat-1];
  echo " $jahr";
  echo "\n";
  echo " &nbsp; &nbsp;";
  echo "</td><td>";
  echo "<input type='hidden' name='show' value='monat' />";
  echo "<input type='hidden' name='tag' value='{$tag}' />";
  echo "<input type='hidden' name='monat' value='{$monat}' />";
  echo "<input type='hidden' name='jahr' value='{$jahr}' />";
  echo "<select size='1' style='width: 10em;' class='flieger_select' name='flieger_id'  onchange='this.form.submit()' >";
  combobox_flieger($mysqli, $flieger_id);
  echo "</select>";
  echo "</td><td style='padding-left: 20px;'>";
  echo "<a href='/reservationen/index.php?flieger_id={$flieger_id}&amp;show=monat&amp;monat={$v_monat}&amp;jahr={$v_jahr}&amp;tag={$tag}'>&raquo;</a>";
  echo "</td>";
  echo "</tr></table>";
  echo "</div>";
  echo "</form>";
}

function print_buchungen_monat($mysqli, $flieger_id, $boxcol, $textcol, $jahr, $monat, $tabs, $w, $tag_v_offset)
{

  // round up cur_time to half hour blocks um zu gucken ob nummer
  // clickable (loeschbar) generell wird unten auf immer true gesetzt bei
  // admin..
  //
  // Es muss die wirklcihe lokale uhrzeit ermittelt werden ann muss der
  // aber per UTC in UNIX-STAMP (quasi neutral ohne sommerzeit und
  // verschiebung) verwandelt werden // weil das ueberall so gemacht wir..
  //
  // muss quasi ueberall gemacht werden wo time() verwendet wird.

  date_default_timezone_set("Europe/Zurich");
  $tmp_date = date("Y-m-d H:i:s", time());
  date_default_timezone_set('UTC');
  $rounded_stamp = strtotime($tmp_date);
  $rounded_stamp = (intval($rounded_stamp / 1800) + 1) * 1800;

  // habes jahr zureuck
  date_default_timezone_set("Europe/Zurich");
  $date_xmonth_back = date("Y-m-d H:i:s", time()-20736000);
  $monat_2 = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $anzahl_tage = date("t", strtotime("$jahr-$monat_2-01"));
  date_default_timezone_set('UTC');

  $stamp_print_minimum = strtotime("$jahr-$monat_2-01 07:00:00");
  $stamp_print_maximum = strtotime("$jahr-$monat_2-$anzahl_tage 20:59:59");
  // half hour blocks
  $block_print_maximum = intval(($stamp_print_maximum - $stamp_print_minimum) / (60 * 30));


  // NUR ein halbes jahr zurueck gucken. hats ueberhaupt reservationen?
  // sonst Zeit markieren als $von_extrem
  $query = "SELECT `von` FROM `reservationen` WHERE `flieger_id` = '$flieger_id' AND `von` > '$date_xmonth_back'  ORDER BY `von` ASC LIMIT 1;";
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
  $query = "SELECT `bis` FROM `reservationen` WHERE `flieger_id` = '$flieger_id' ORDER BY `bis` DESC LIMIT 1;";
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

  $print_number_txt = ""; // at the end for highest z-index

  // alle hohlen
  $query = "SELECT * FROM `reservationen` WHERE `flieger_id` = '$flieger_id' AND `von` >= '$von_extrem'  ORDER BY `timestamp` ASC;";
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

    $print_first = $block_first - $shift_1ster_monat_block;
    $print_last = $block_last - $shift_1ster_monat_block;

    //////////////////////// can't print.. next.
    if ($print_first > $block_print_maximum || $print_last < 0)
      continue; // a booking that does not need to get printed.

    // trim according not printable data...
    if ($print_first < 0) $print_first = 0; // trip the begin
    if ($print_last > $block_print_maximum) $print_last = $block_print_maximum; // trim the end

    // fuer mehrlinige buchungen
    // vorbereitungen und while{}

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

      // zeichnen von print-first on tag_von bis nach tag_bis block_bis
      $center = ($tabs[$print_first] + $tabs[$print_last+1]) / 2;
      $center = number_format ($center, 3, '.', '');

      $width = number_format ($tabs[$print_last+1]-$tabs[$print_first], 3, '.', '');

      $yoffset = $tag_v_offset[$x];

      // yellow little line.. to show there are some standby's
      if ($level > 0)
      {

        $line_length = number_format ($tabs[$print_first]+$width, 3, '.', '');
        echo '<line x1="'.$tabs[$print_first].'%" y1="'.($yoffset+16).'" x2="'.$line_length.'%" y2="'.($yoffset+16).'" style="stroke: '.$boxcol[$level].'; stroke-width: 7px;"></line>'."\n";
        echo '<line x1="'.$tabs[$print_first].'%" y1="'.($yoffset+13).'" x2="'.$line_length.'%" y2="'.($yoffset+13).'" style="stroke: #333333; stroke-width: 1px;"></line>'."\n";
      }
      // normal blue booking
      else
      {
        echo '<rect x="'.$tabs[$print_first].'%" y="'.$yoffset.'" width="'.$width.'%" height="20" style="fill: '.$boxcol[$level].'; stroke: #000000; stroke-width: 1px;"></rect>'."\n";


        $query = "SELECT * from `piloten` where `id` = '".$obj_tang->user_id."';";
        if ($res_id = $mysqli->query($query))
        {
          if ($res_id->num_rows > 0) // eigentilch immer.. oben wurde schon geguckt
          {
            $obj_id = $res_id->fetch_object();
            $t_id = str_pad($obj_id->pilot_id, 3, "0", STR_PAD_LEFT);
          }
          else
            continue;
        }


        $showlink = FALSE;
        if (strtotime($obj_tang->bis) > $rounded_stamp && $obj_tang->user_id == $_SESSION['user_id'])
          $showlink = TRUE;

        $txtcolor = $textcol[$level];

        // print pilotennummer mit tooltips etc. wenn tag ($x) kommt
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
          if ($obj_tang->user_id == $_SESSION['user_id']) // user
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
            $print_number_txt .= '<a xlink:href="res_loeschen.php?to=ueberblick&amp;action=del&amp;reservierung='.$obj_tang->id.'&amp;monat='.$monat.'&amp;jahr='.$jahr.'">';
          // TODO: tag geloescht hiere...


          $print_number_txt .=  '<text '.$tmptxt.' x="'.$center.'%" y="'.($yoffset+16).'" text-anchor="middle" style="fill: '.$txtcolor.'; font-size: 95%; font-weight: bold;">'.$t_id.'</text>'."\n";

          if ($showlink)
          $print_number_txt .=  '</a>';
        }
      }

      $x++; // reiterate if necessary
    }
  }
  echo $print_number_txt;
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

  $laufender_stamp = strtotime("$jahr-$monat_2-01 00:00 UTC"); // minus tag - addiert sich dazu

  // needs real user (RAGAZ) time.
  date_default_timezone_set("Europe/Zurich");
  $now_date = date("Y-m-d H:i:s", time());
  $anzahl_tage = date("t", $laufender_stamp);
  $erster_wochentag = date("N", $laufender_stamp); // 1(Mon)-7(Son) TODO: evt zusammen mit oben
  $heute_monats_tag = date ("d", time());
  date_default_timezone_set('UTC');
  // wieso das hier draussen sein muss - keine ahnung.
  $now_tstamp = strtotime($now_date);

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

  $laufender_stamp += 7*60*60; // start...zeit
  $laufender_stamp -= 10*60*60; // (temp.. kommt grad wieder hinzu
  // ueber jeden tag durchgehen
  // rechteecke zeichen - jeden 7ten tag.. zeitlinie
  while($anzahl_tage > $day_counter)
  {
    $day_counter++;
    $laufender_stamp += 10*60*60; // 10*1 + 28*0.5  = 24h

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
      $color = "#ff0000";
    else if (($day_counter + $erster_wochentag) % 7 == 0)
      $color = "#0000ff";

    echo '<a xlink:href="index.php?show=tag&amp;tag='.$day_counter.'&amp;monat='.$monat.'&amp;jahr='.$jahr.'">';
    echo '<text  text-anchor="end" x="2.8%" y="'.($yoffset+16).'" style="fill: '.$color.'; font-size: 80%; font-weight: bold;">'.str_pad($day_counter, 2, "0", STR_PAD_LEFT).'</text>'."\n";
    echo '</a>';

    array_push($tag_v_offset, $yoffset); // todo: may fine tune.. (needs on buchungen print)

    for ($i = 0; $i < 28; $i++)
    {
      $color = "grey";
      if ($laufender_stamp >= $now_tstamp)
        $color = "gruen";
      // wieso nicht oben.. keine ahnung, aber 'passt' so scheins.. grr
      $laufender_stamp += 30 * 60; // halbe stunde hinzu

      $t_std = 7+intval($i/2);
      $t_min = ($i % 2) * 30;

      if ($i % 2 == 0)
      {
        if ($color == 'gruen')
          echo '<a xlink:href="res_neu.php?&amp;flieger_id='.$flieger_id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$day_counter.'&amp;stunde='.$t_std.'&amp;minute='.$t_min.'">';
        echo '<rect x="'.$tabs[$i].'%" y="'.($yoffset).'" width="'.$w.'%" height="20" style="fill:url(#'.$color.'1); stroke: #000000; stroke-width: 1px;"></rect>'."\n";
        if ($color == 'gruen')
          echo '</a>';
      }
      else
      {
        if ($color == 'gruen')
          echo '<a xlink:href="res_neu.php?flieger_id='.$flieger_id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$day_counter.'&amp;stunde='.$t_std.'&amp;minute='.$t_min.'">';
        echo '<rect x="'.$tabs[$i].'%" y="'.($yoffset).'" width="'.$w.'%" height="20" style="fill:url(#'.$color.'2); stroke: #000000; stroke-width: 1px;"></rect>'."\n";
        if ($color == 'gruen')
          echo '</a>';
      }
    }
  }
  echo '<line x1="1.0%" y1="'.($yoffset+20).'" x2="'.$tabs[0].'%" y2="'.($yoffset+20).'" style="stroke:#000000; stroke-width: 1px;" />'."\n";
  return $tag_v_offset;
}

function monatsansicht($mysqli, $w, $tabs, $boxcol, $textcol, $monat, $jahr, $flieger_id)
{
?>

<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height='740px' class="chart_monat">

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

  <g transform="translate(4,-14)">
  <?php

  // print GREEN etc (lowest layer) stuff

  $tag_v_offset = print_main_bands_monat($mysqli, $jahr, $monat, $tabs, $w, $flieger_id);

  // TODO colors etc into defines? konstats etc?
  print_buchungen_monat($mysqli, $flieger_id, $boxcol, $textcol, $jahr, $monat, $tabs, $w, $tag_v_offset);

  echo '</g></svg>';

  legende_print($boxcol);
  tooltip_print();

}
?>
