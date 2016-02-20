<?php

function get_date(){

  date_default_timezone_set("Europe/Berlin");
  setlocale(LC_ALL, 'de_DE');

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
      echo "<h3 style='color: red;'>Der Tag [$tag] ist ungültig - Der Tag wird auf den ersten (1) zurueckgesetzt</h3>";
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

    // turn over year

    $z_jahr = $year;
    $v_jahr = $year;
    $z_monat = $month - 1;
    if ($z_monat < 1) { $z_monat = 12; $z_jahr--; }
    $v_monat = $month + 1;
    if ($v_monat > 12) { $v_monat = 1; $v_jahr++; }

    $monate = array("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", 
    "Oktober", "November", "Dezember");

    // totale Tage im Monat
    $days_in_month = date('t',mktime(0,0,0,$month,1,$year));

    // reduce to max
    if ($day >= $days_in_month)
      $day = $days_in_month;

    $zurueck = "<a href='index.php?tag=$day&amp;monat=$z_monat&amp;jahr=$z_jahr'>&lt;</a>";
    $vor = "<a href='index.php?tag=$day&amp;monat=$v_monat&amp;jahr=$v_jahr'>&gt;</a>";

    $timestamp = time();
    $today_day = intval(date("d",$timestamp));
    $today_month = intval(date("m",$timestamp));
    $today_year = intval(date("Y",$timestamp));
    $heute = "<a href='index.php?tag=$today_day&amp;monat=$today_month&amp;jahr=$today_year'><span style='color: grey; font-size: small;'>[heute]</span></a>";

    $calendar = '<table class="calendar"><tr>';
    $calendar .= "<td id='vor'>$zurueck</td>";
    $calendar .= "<td id='datum' colspan='4'> <span style='color: #666666;'>$day.</span> ".$monate[$month-1]." ".str_replace("20","'",$year)."</td>";
    $calendar .= "<td>$heute</td>";
	$calendar .= "<td id='nach'>$vor</td></tr>";
    

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
        if ($days_in_this_week == 6) $tmp = "samstag";
        if ($days_in_this_week == 7) $tmp = "sonntag";
        $calendar.= "<td class='calendar-day $tmp'>";

        // Tag einfuegen
        $tmp = "";
        if ($today_day == $list_day && $today_month == $month && $today_year == $year)
          $tmp = 'class="heute"';

        $tmp2 = "";
        if ($day == $list_day)
          $tmp2 = 'id="aktiv"';

        $calendar.= "<div $tmp $tmp2><a href='index.php?tag=$list_day&amp;monat=$month&amp;jahr=$year'>$list_day</a></div>";
        $calendar.= "</td>\n";

        if($days_in_this_week == 7){ // Voll -> umschlagen
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

    $calendar.= '</tr>';
    $calendar.= '</table>';
    return $calendar;
}
?>
