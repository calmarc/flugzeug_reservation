<?php

function tagesplan_navigation($mysqli, $date, $wochentag, $tag, $monat_txt, $jahr)
{
  DATE_DEFAult_timezone_set("Europe/Zurich");
  $date_td = new DateTime($date);
  $date_td->modify('-1 day');
  $z_tag =  $date_td->format('d');
  $z_monat =  $date_td->format('m');
  $z_jahr =  $date_td->format('Y');
  $date_td->modify('+2 day');
  $v_tag =  $date_td->format('d');
  $v_monat =  $date_td->format('m');
  $v_jahr =  $date_td->format('Y');

  $date_td = new DateTime($date);
  $date_td->modify('-1 month');
  $z_tag_m =  $date_td->format('d');
  $z_monat_m =  $date_td->format('m');
  $z_jahr_m =  $date_td->format('Y');
  $date_td->modify('+2 month');
  $v_tag_m =  $date_td->format('d');
  $v_monat_m =  $date_td->format('m');
  $v_jahr_m =  $date_td->format('Y');

  $date_td = new DateTime($date);
  $date_td->modify('-1 week');
  $z_tag_w =  $date_td->format('d');
  $z_monat_w =  $date_td->format('m');
  $z_jahr_w =  $date_td->format('Y');
  $date_td->modify('+2 week');
  $v_tag_w =  $date_td->format('d');
  $v_monat_w =  $date_td->format('m');
  $v_jahr_w =  $date_td->format('Y');
  date_default_timezone_set("UTC");

  echo "<table id='monat_title'><tr>
<td style='white-space: nowrap; font-weight: bold; font-size: 260%; margin: 0px; padding: 0px 0.4em 0.1em 0px;'>
<a title='Monat zurück' href='/reservationen/index.php?show=tag&amp;monat={$z_monat_m}&amp;jahr={$z_jahr_m}&amp;tag={$z_tag_m}'
><img style='display: inline;' src='bilder/arr_monat_left.png' alt='Jahr zurück' /></a
><a title='Woche zurück' href='/reservationen/index.php?show=tag&amp;monat={$z_monat_w}&amp;jahr={$z_jahr_w}&amp;tag={$z_tag_w}'
><img style='display: inline;' src='bilder/arr_woche_left.png' alt='Woche zurück' /></a
><a title='Tag zurück' href='/reservationen/index.php?show=tag&amp;monat={$z_monat}&amp;jahr={$z_jahr}&amp;tag={$z_tag}'
><img style='display: inline;' src='bilder/arr_tag_left.png' alt='Tag zurück' /></a>
</td>
<td><h1>{$wochentag}, {$tag}.&nbsp;{$monat_txt}&nbsp;{$jahr}</h1></td>
<td>
</td><td style='white-space: nowrap; font-weight: bold; font-size: 260%; padding: 0px 0px 0.1em 0.4em;'>
<a title='Tag vor' href='/reservationen/index.php?show=tag&amp;monat={$v_monat}&amp;jahr={$v_jahr}&amp;tag={$v_tag}'
><img style='display: inline;' src='bilder/arr_tag_right.png' alt='Tag vor' /></a
><a title='Woche vor' href='/reservationen/index.php?show=tag&amp;monat={$v_monat_w}&amp;jahr={$v_jahr_w}&amp;tag={$v_tag_w}'
><img style='display: inline;' src='bilder/arr_woche_right.png' alt='Woche vor' /></a
><a title='Monat vor' href='/reservationen/index.php?show=tag&amp;monat={$v_monat_m}&amp;jahr={$v_jahr_m}&amp;tag={$v_tag_m}'
><img style='display: inline;' src='bilder/arr_monat_right.png' alt='Jahr vor' /></a>
</td>
</tr></table>";
}

function print_main_bands($mysqli, $planeoffset, $jahr, $monat, $tag, $date, $tabs, $w, $admin_bol)
{
  $now_tstamp = time();
  $query = "SELECT * FROM `flugzeug`;";
  $res_f = $mysqli->query($query);

  $yoffset = -$planeoffset;

  //============================================================================
  // Iterate over flieger

  while($obj_f = $res_f->fetch_object())
  {

    //============================================================================
    // SERVICE informationen $in ist das resultat

    // flieger zaehler_minute  ($min) 
    $res_x = $mysqli->query("SELECT MAX(`zaehler_minute`) AS `zaehler_minute` FROM `zaehler_eintraege` WHERE `flugzeug_id` = '{$obj_f->id}';");
    $obj_x = $res_x->fetch_object();
    $min = $obj_x->zaehler_minute;
    // letzer Service zaehler_minute  ($service_min) 
    $res_x = $mysqli->query("SELECT MAX(`zaehler_minute`) AS `zaehler_minute` FROM `service_eintraege` WHERE `flugzeug_id` = '{$obj_f->id}';");
    $obj_x = $res_x->fetch_object();
    $service_min = $obj_x->zaehler_minute;

    // intervall
    $res_x = $mysqli->query("SELECT `service_interval_min`  FROM `flugzeug` WHERE `id` = '{$obj_f->id}';");
    $obj_x = $res_x->fetch_object();
    $service_interval_min = $obj_x->service_interval_min;

    $countdown = ($service_interval_min + $service_min) - $min;

    if ($countdown < 0)
      $s_color = "#ff0000";
    else
      $s_color = "#999999";

    // formatieren die Minuten in xx:00h
    $in = intval($countdown / 60);
    if ($countdown < 0 && $in == 0)
      $in = "-$in";
    $in .= ":".str_pad(abs($countdown % 60), 2, "0", STR_PAD_LEFT)."h";

    //-----------------------------------------------------------------------------

    $yoffset += $planeoffset;
    echo '<text x="97.6%" y="'.($yoffset-28).'px" text-anchor="end" style="fill: #000000; font-size: 120%; font-weight: bold;">'.$obj_f->flugzeug.'</text>'."\n";
    echo '<text x="97.6%" y="'.($yoffset-28-26).'px" text-anchor="end" style="fill: '.$s_color.'; font-size: 90%; font-weight: bold;">[Service in '.$in.']</text>'."\n";

    //--
 
    // flieger buch, eintrag nach landung
    echo '<a xlink:href="res_neu.php?flugzeug_id='.$obj_f->id.'&amp;jahr='.$jahr.'&amp;monat='.$monat.'&amp;tag='.$tag.'">';
    echo '<text x="3.6%" y="'.($yoffset-28).'px" style=" fill: #000099; font-size: 100%; font-weight: bold;">'.$obj_f->kurzname.' buchen</text>'."\n";
    echo '</a>';
    echo '<text x="13.3em" y="'.($yoffset-28).'px" style=" fill: #000099; font-size: 100%; font-weight: bold;">|</text>'."\n";
    echo '<a xlink:href="landungs_eintrag.php?flugzeug_id='.$obj_f->id.'">';
    echo '<text x="14.8em" y="'.($yoffset-28).'px" style="fill: #000099; font-size: 100%; font-weight: bold;">Eintrag nach Landung</text>'."\n";
    echo '</a>';
    // if admin service_liste
    if ($admin_bol)
    {
      echo '<a xlink:href="/reservationen/service_edit.php?flugzeug_id='.$obj_f->id.'">';
      echo '<text x="3.6%" y="'.($yoffset-48).'px" style="fill: #990022; font-size: 100%; font-weight: bold;">Serviceliste</text>'."\n";
      echo '</a>';
    }

    // z.B hier ist jetzt 13:00. stamp muss lokal ermittelt werden.
    
    date_default_timezone_set("Europe/Zurich");
    $stamp7 = strtotime($date." 00:00:00") + (7*60*60); // 30min*..x + 7h to print..
    date_default_timezone_set("UTC");

    for ($i = 0; $i < 28; $i++)
    {
      //////////////////////////// GRUEN (default)

      $color = "gruen";
      // 7*60*60 (7 stunden) + 30*60 (halbe stunde) * i -> startzeit block in
      // die jetzt Zeit... um zwischen gruen und grau unterscheiden zu koennen.
      $print_stamp = $stamp7 + 1800 * $i; // 30min*..x + 7h to print..

      if ($now_tstamp > $print_stamp)
        $color = "grey";

      $minute = ($i % 2 ? 30 : 0);
      $stunde = intval(7 + ($i / 2));

      // nur jedes 2te mal ne hohle linie und ne zeit
      if ($i % 2 == 0)
      {
        // mit link
        if ($color == 'gruen')
          echo "<a xlink:href='res_neu.php?flugzeug_id={$obj_f->id}&amp;jahr={$jahr}&amp;monat={$monat}&amp;tag={$tag}&amp;stunde={$stunde}&amp;minute={$minute}'>";

        echo "<rect x='{$tabs[$i]}%' y='{$yoffset}' width='{$w}%' height='20' style='fill:url(#{$color}1); stroke: #000000; stroke-width: 1px;'></rect>\n";

        // link fertig
        if ($color == 'gruen')
          echo "</a>";

        // H-LINIE
        echo '<line x1="'.$tabs[$i].'%" y1="'.($yoffset-20).'" x2="'.$tabs[$i].'%" y2="'.($yoffset+20).'" style="stroke:#000000; stroke-width: 3px;" />'."\n";

        // ZEITEN
        $tmp = (string) number_format(($tabs[$i]+0.5), 3, '.', '');

        echo '<text x="'.$tmp.'%" y="'.($yoffset-4).'" style="fill: #666666; font-size: 80%;"><tspan>'.($i/2+7).'</tspan><tspan class="hide">:00</tspan></text>'."\n";
      }
      else
      {
        // mit link
        if ($color == 'gruen')
          echo "<a xlink:href='res_neu.php?flugzeug_id={$obj_f->id}&amp;jahr={$jahr}&amp;monat={$monat}&amp;tag={$tag}&amp;stunde={$stunde}&amp;minute={$minute}'>";

        echo "<rect x='{$tabs[$i]}%' y='{$yoffset}' width='{$w}%' height='20' style='fill:url(#{$color}2); stroke: #000000; stroke-width: 1px;'></rect>\n";

        // link fertig
        if ($color == 'gruen')
          echo "</a>";
      }

    }
  }
  $res_f->close();
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  MAIN BUCHUNG's DRAWING LOOOOOP
////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function print_buchungen($mysqli, $planeoffset, $tabs, $date, $boxcol, $textcol, $tag, $monat, $jahr)
{

  // habes jahr zureuck
  date_default_timezone_set("Europe/Zurich");
  $date_xmonth_back = date("Y-m-d H:i:s", time()-5456800); // 62 tage in sekunden
  date_default_timezone_set('UTC');

  $today_stamp_seven = strtotime($date.' 07:00:00');

  $query = "SELECT * FROM `flugzeug`;";
  $res_f = $mysqli->query($query);

  // -------------------------------------------------------------------------------------------------------------
  // ueber die flugzeug iterieren
  // -------------------------------------------------------------------------------------------------------------

  while($obj_f = $res_f->fetch_object())
  {

    list($von_extrem, $bis_extrem) = get_range_of_reservation($mysqli, $obj_f->id, $date_xmonth_back);
    if ($von_extrem == 0 || $bis_extrem == 0)
      continue;

    //============================================================================
    // $bookings initialisieren
    //
    // halb stunden bloecke differenz unserer reservierngen zur initalisierung
    $min_stamp = strtotime($von_extrem);
    $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 1800);

    // it.: if booking[level][hour]=TRUE <- reserved
    $bookings = array(array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE));
    //----------------------------------------------------------------------------

    // today's 7h
    $shift_7hour_block =  intval(($today_stamp_seven - $min_stamp) / 1800);

    // alle hohlen
    $query = "SELECT * FROM `reservationen` WHERE `flugzeug_id` = '{$obj_f->id}' AND `von` >= '{$von_extrem}'  ORDER BY `timestamp` ASC;";
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
      $block_last = intval((strtotime($obj_tang->bis) - $min_stamp) / 1800);

      // look vor level where it can fit
      $level = 0;
      while(TRUE)
      {
        $flag = FALSE;
        for($i = $block_first; $i < $block_last; $i++)
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
      for($i = $block_first; $i < $block_last; $i++)
        $bookings[$level][$i] = TRUE;

      //----------------------------------------------------------------------------
      // die buchungen sind da
      // jetzt gucken ob die bloecke gedruckt werden muessen
      // z.B >= heute 7uhr block und maximal 7uhr block + 28 oder so aehnlich

      $print_first = $block_first - $shift_7hour_block;
      $print_last = $block_last - $shift_7hour_block - 1;

      // liegt komplett auserhalb
      if ($print_first > 27 || $print_last < 0)
        continue; // a booking that does not need to get printed.

      // trim according not printable data...
      if ($print_first < 0) $print_first = 0; // trip the begin
      if ($print_last > 27) $print_last = 27; // trim the end


      // fuer die pilot-nummer
      $center = ($tabs[$print_first] + $tabs[$print_last+1]) / 2;
      $center = number_format ($center, 3, '.', '');

      // plane offset + standby offset
      $yoffset = $planeoffset * ($obj_f->id - 1) + $level * 20;

      // breite des blockes...
      $width = number_format ($tabs[$print_last+1]-$tabs[$print_first], 3, '.', '');

      echo "<rect x='{$tabs[$print_first]}%' y='{$yoffset}' width='{$width}%' height='20' style='fill: {$boxcol[$level]}; stroke: #000000; stroke-width: 1px;'></rect>\n";

      // piloten nummer ergatten und padden auf 3
      $query = "SELECT * from `piloten` where `id` = '{$obj_tang->user_id}';";
      if ($res_id = $mysqli->query($query))
      {
        if ($res_id->num_rows > 0) //  koennte geloescht worden sein..
        {
          $obj_id = $res_id->fetch_object();
          $t_id = str_pad($obj_id->pilot_nr, 3, "0", STR_PAD_LEFT);
        }
        else
          continue;
      }

      $rounded_stamp = time();
      // round up cur_time to half hour blocks
      $rounded_stamp = (intval($rounded_stamp / 1800) + 1) * 1800;

      $txtcolor = $textcol[$level];

      $showlink = FALSE;
      // link zeigen wenn vom user..und groessern jetzt
      if (strtotime($obj_tang->bis) > $rounded_stamp && $obj_tang->user_id == $_SESSION['user_id'])
        $showlink = TRUE;

      // immer zeigen wenn admin
      if (check_admin($mysqli))
      {
        $showlink = TRUE;
        $txtcolor = '#3333ff;';
      }


      // loeschen zeit freigeben tooltop wenn von einme selber - sonst mit allem
      // drum und dran
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
        echo "<a xlink:href='res_loeschen.php?to=ueberblick&amp;action=del&amp;reservierung={$obj_tang->id}&amp;tag={$tag}&amp;monat={$monat}&amp;jahr={$jahr}'>";
      echo '<text '.$tmptxt.' x="'.$center.'%" y="'.($yoffset+16).'" text-anchor="middle" style="fill: '.$txtcolor.'; font-size: 95%; font-weight: bold;">'.$t_id.'</text>'."\n";
      if ($showlink)
        echo '</a>';
    }
  }
}

function tagesansicht($mysqli, $w, $tabs, $boxcol, $textcol, $planeoffset, $tag, $monat, $jahr, $date, $admin_bol)
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

  print_main_bands($mysqli, $planeoffset, $jahr, $monat, $tag, $date, $tabs, $w, $admin_bol);
  remove_zombies($mysqli);
  print_buchungen($mysqli, $planeoffset, $tabs, $date, $boxcol, $textcol, $tag, $monat, $jahr);

  echo "</g></svg>";

  legende_print($boxcol);
  
  tooltip_print(); // entsprechender javascript..  siehe oben

}
?>
