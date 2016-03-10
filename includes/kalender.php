<?php

function get_date(){

  //date_default_timezone_set("Europe/Berlin");
  //TODO....
  //setlocale(LC_ALL, 'de_DE');

  // default = today month/year else.. $_GET()
  if (isset($_GET["tag"])) $tag = intval($_GET["tag"]); else $tag = intval(date("j"));
  if (isset($_GET["monat"])) $monat = intval($_GET["monat"]); else $monat = intval(date("n"));
  if (isset($_GET["jahr"])) $jahr = intval($_GET["jahr"]); else $jahr = intval(date("Y"));

  if ($jahr < 2010 || $jahr > intval(date("Y")+2))
  {
      echo "<h3 style='color: red;'>Das Jahr [$jahr] ist ungültig - Der Kalender wird auf 'heute' zurueckgesetzt</h3>";
      $jahr = intval(date("Y"));
  }
  if ($tag < 1 || $tag > 31)
  {
      //echo "<h3 style='color: red;'>Der Tag [$tag] ist ungültig - Der Tag wird auf den ersten (1) zurueckgesetzt</h3>";
      $tag = 1;
  }
  if ($monat < 1 || $monat > 12)
  {
      echo "<h3 style='color: red;'>Der Tag [$monat] ist ungültig - Der Kalender wird auf 'heute' zurueckgesetzt</h3>";
      $monat = intval(date("n"));
      $jahr = intval(date("Y"));
  }

  return array($tag, $monat, $jahr);
}


/* draws a calendar */
function draw_calendar($day, $month,$year){

  date_default_timezone_set("Europe/Zurich");
  // --------------------------------------------------------
  // START Vor / back arrows
  // --------------------------------------------------------

  // monat zurueck
  // --------------------------------------------------------------------------
  $z_jahr = $year;
  $z_monat = $month - 1;
  if ($z_monat < 1) { $z_monat = 12; $z_jahr--; }
  $z_day = 1;

  $zurueck = "<a title='Monat zurück' class='vorzurueck' href='index.php?tag=$z_day&amp;monat=$z_monat&amp;jahr=$z_jahr'><img id='laquo' alt='<<' src='/reservationen/bilder/laquo.png' /></a>";

  // monat vor
  // --------------------------------------------------------------------------
  $v_jahr = $year;
  $v_monat = $month + 1;
  if ($v_monat > 12) { $zvmonat = 1; $z_jahr++; }
  $v_day = 1;

  $vor = "<a title='Monat vor' class='vorzurueck' href='index.php?tag=$v_day&amp;monat=$v_monat&amp;jahr=$v_jahr'><img id='raquo' alt='<<' src='/reservationen/bilder/raquo.png' /></a>";

  // tag zurueck
  // --------------------------------------------------------------------------
  $z_jahr = $year;
  $z_monat = $month;
  $z_day = $day - 1;

  if ($z_day < 1) 
  { 
    $z_monat--; 
    if ($z_monat < 1) { $z_monat = 12; $z_jahr--; }
    $days_in_month = date('t',mktime(0,0,0,$z_monat,1,$z_jahr));
    $z_day = $days_in_month; 
  }
  $zurueck .= "<a title='Tag zurück' class='vorzurueck' href='index.php?tag=$z_day&amp;monat=$z_monat&amp;jahr=$z_jahr'><img id='lsaquo' alt='<' src='/reservationen/bilder/lsaquo.png' /></a>";

  // tag vor
  // --------------------------------------------------------------------------
  
  $v_jahr = $year;
  $v_monat = $month;
  $v_day = $day + 1;
  $days_in_month = date('t',mktime(0,0,0,$v_monat,1,$v_jahr));

  if ($v_day > $days_in_month) 
  { 
    $v_monat++; 
    if ($v_monat > 12) { $v_monat = 1; $v_jahr++; }
    $v_day = 1; 
  }
  $vor = "<a title='Tag vor' class='vorzurueck' href='index.php?tag=$v_day&amp;monat=$v_monat&amp;jahr=$v_jahr'><img id='rsaquo' alt='<' src='/reservationen/bilder/rsaquo.png' /></a>".$vor;

  // END Vor / back ---------------------------------------

  $timestamp = time();
  $today_day = intval(date("d",$timestamp));
  $today_month = intval(date("m",$timestamp));
  $today_year = intval(date("Y",$timestamp));
  $heute = "<a title='Heute' href='index.php?tag=$today_day&amp;monat=$today_month&amp;jahr=$today_year'><img style='margin-bottom:-3px;' alt='heute' src='bilder/today.png' /></a>";

  $calendar = '<table class="calendar"><tr style="white-space: nowrap;">';
  $calendar .= "<td>$zurueck</td>";
  //$calendar .= "<td id='datum' colspan='4'> <span style='color: #666666;'>$day.</span> ".$monate[$month-1]." ".str_replace("20","'",$year)."</td>";
  $calendar .= "<td id='datum' colspan='4'>".str_pad($day, 2, "0", STR_PAD_LEFT).".".str_pad($month, 2, "0", STR_PAD_LEFT).".".str_replace("20","",$year)."</td>";
  $calendar .= "<td>$heute</td>";
  $calendar .= "<td>$vor</td></tr>";
  

  /* table headings */
  $calendar .= '<tr class="calendar-row">';
  $calendar .= '<td class="calendar-day-head">Mo</td>';
  $calendar .= '<td class="calendar-day-head">Di</td>';
  $calendar .= '<td class="calendar-day-head">Mi</td>';
  $calendar .= '<td class="calendar-day-head">Do</td>';
  $calendar .= '<td class="calendar-day-head">Fr</td>';
  $calendar .= '<td class="calendar-day-head samstag">Sa</td>';
  $calendar .= '<td class="calendar-day-head sonntag">So</td>';
  $calendar .= '</tr>';

  // erster tag (nummerisch) der Woche - hier Monat: 0 - 6
  $running_day = date('w',mktime(0,0,0,$month,1,$year));

  $running_day--;
  // sonntag = letzter Tag
  if ($running_day == -1)
      $running_day = 6;


  $days_in_this_week = 1;

  // row for week one
  $calendar.= '<tr>';

  // print "blank" days until the first of the current week
  for($x = 0; $x < $running_day; $x++){
      $calendar.= '<td class="calendar-day-np"> </td>';
      $days_in_this_week++;
  }

  // runterhobeln die Tage bis $days_in_month.... 
  // Umschlagen wenn $days_in_this_week = 7 (1-7 = 7 Tage) 

  for($list_day = 1; $list_day <= $days_in_month; $list_day++){

      $tmp = "";
      if ($days_in_this_week == 6) $tmp = " samstag";
      if ($days_in_this_week == 7) $tmp = " sonntag";
      $calendar.= "<td class='calendar-day$tmp'>";

      $vergangenheit = "";
      if (($list_day < $today_day && $today_month == $month && $today_year == $year)
         || ($today_month > $month && $today_year == $year) || ($today_year > $year))
        $vergangenheit = 'class="vergangenheit"';

      // Tag einfuegen
      if ($today_day == $list_day && $today_month == $month && $today_year == $year)
        $vergangenheit = 'class="heute"';

      $tmp2 = "";
      if ($day == $list_day)
        $tmp2 = 'id="aktiv"';

      $calendar.= "<div $tmp2 $vergangenheit><a href='index.php?tag=$list_day&amp;monat=$month&amp;jahr=$year'>$list_day</a></div>";
      $calendar.= "</td>\n";

      if ($days_in_this_week == 7){ // Voll -> umschlagen
          $calendar.= "</tr>\n";
          if ($list_day < $days_in_month) $calendar.= '<tr class="calendar-row">'; // neue reihe
          $days_in_this_week = 0;
      }

      $days_in_this_week++; 
  }

  // finish the rest of the days in the week
  if ($days_in_this_week > 1 && $days_in_this_week < 8){
      for($x = 1; $x <= (8 - $days_in_this_week); $x++){
          $calendar.= '<td class="calendar-day-np"> </td>';
      }
  }

  $calendar.= '</table>';

  date_default_timezone_set('UTC');
  return $calendar;
}
?>
