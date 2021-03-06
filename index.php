<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/reservations_functions.php');
include_once ('includes/functions.php');

include_once ('includes/tages_ansicht.php');
include_once ('includes/monats_ansicht.php');
include_once ('includes/kalender.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }

$admin_bol = check_admin($mysqli);

//============================================================================
// HTML

print_html_to_body('Motorfluggruppe Chur Reservierungssystem',
                   "<meta http-equiv='refresh' content='900'>");

include ('includes/usermenu.php');
echo '<main>';

$monate_arr = array ("Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September",
"Oktober", "November", "Dezember");
$tage_arr = array ("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");

// heutiges Datum - oder das vom kalender etc.
list( $tag, $monat, $jahr) = get_date();

$j = str_pad($jahr, 2, "0", STR_PAD_LEFT);
$m = str_pad($monat, 2, "0", STR_PAD_LEFT);
$t = str_pad($tag, 2, "0", STR_PAD_LEFT);

$date = "$j-$m-$t";

date_default_timezone_set("Europe/Zurich");
$wochentag_i = date("w", strtotime($date));
date_default_timezone_set("UTC");

//============================================================================
// Tagesansicht -> title + Kalender

if ($_SESSION['show'] == 'tag')
{
  echo "<div id='calendar' class='hide_on_print'>";
  echo draw_calendar($tag, $monat, $jahr);
  echo '</div>';

  tagesplan_navigation($mysqli, $date, $tage_arr[$wochentag_i], $tag, $monate_arr[$monat-1], $jahr);
}

//============================================================================
// Monatsansicht -> title + navigation

else
{
  // wenns im  GET.. speicherer in die sesssion (neuer flugzeug default monat)
  $flugzeug_id = 1; // default bei erstem Aufruf
  if (isset($_GET['flugzeug_id']))
  {
    $flugzeug_id = $_GET['flugzeug_id'];
    $_SESSION['flugzeug_id'] = $flugzeug_id;
  }
  else if (isset($_SESSION['flugzeug_id']))
    $flugzeug_id = $_SESSION['flugzeug_id'];

  monatsplan_navigation($mysqli, $flugzeug_id, $jahr, $monat, $monate_arr, $tag);

}

//============================================================================
// paar berechnungen/chart-konstanten (tabs, colors) etc und dann die Charts
// zeichen

//$tab_width = number_format (98/28.0, 3, '.', ''); // WIDTH of tabs

$tab_width = number_format (94/28.0, 3, '.', ''); // WIDTH of tabs
$perplus = 3.6; //(shift to right in percent)

$tabs = array(); // TABS to place stuff
for ($i = 0; $i <= 28;  $i++)
  array_push($tabs, number_format ($i * $tab_width + $perplus, 3, '.', ''));

//buchungs-colors: blue       yellow     orange     yellow     orange       red
$boxcol =   array('#33ccff', '#ffff99', '#ffee99', '#ffff99', '#ffee99', '#ff6666');
$textcol =  array('#333333', '#333333', '#333333', '#333333', '#333333', '#333333');

remove_zombies($mysqli);

if ($_SESSION['show'] == 'monat')
  monatsansicht($mysqli, $tab_width, $tabs, $boxcol, $textcol, $monat, $jahr, $flugzeug_id);
else
  tagesansicht($mysqli, $tab_width, $tabs, $boxcol, $textcol, 123, $tag, $monat, $jahr, $date, $admin_bol);
?>

</main>
</body>
</html>
