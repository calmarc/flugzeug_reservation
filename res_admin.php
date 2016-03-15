<?php

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }

// default
if (!isset($_SESSION['res_sort_dir'])) $_SESSION['res_sort_dir'] = "DESC";
if (!isset($_SESSION['res_sort_by'])) $_SESSION['res_sort_by'] = "timestamp";
if (!isset($_SESSION['res_sort_pilot'])) $_SESSION['res_sort_pilot'] = "";

if (isset($_GET['pilot_id']))
  $_SESSION['res_sort_pilot'] = $_GET['pilot_id'];

$where_pilot = "";
if ($_SESSION['res_sort_pilot'] != "")
  $where_pilot = "`mem1`.`pilotid` = ".intval($_SESSION['res_sort_pilot']);

$t_old = $_SESSION['res_sort_by'];
if (isset($_GET['sort']) && $_GET['sort'] != '') $_SESSION['res_sort_by'] = $_GET['sort'];

if ($t_old == $_GET['sort']) // glieche kolumne gedruckt - also dir wechsel
  if ($_SESSION['res_sort_dir'] == "ASC")
      $_SESSION['res_sort_dir'] = "DESC";
  else
      $_SESSION['res_sort_dir'] = "ASC";

$order_by_txt = "ORDER BY `".$_SESSION['res_sort_by']."` ".$_SESSION['res_sort_dir'];

//default
if (!isset($_SESSION['res_sort_bereich'])) $_SESSION['res_sort_bereich'] = "0";
if (isset($_GET['z_bereich']) && $_GET['z_bereich'] != '') $_SESSION['res_sort_bereich'] = $_GET['z_bereich'];

if ($_SESSION['res_sort_bereich'] == "1.1")
{
  date_default_timezone_set("Europe/Zurich");
  $since_date = date("Y", time());
  date_default_timezone_set("UTC");
  $where_bereich = "`reser_geloescht`.`von` > '$since_date-01-01'";
}
else if ($_SESSION['res_sort_bereich'] == "0")
{
  $where_bereich = '';
}
else
{
  date_default_timezone_set("Europe/Zurich");
  $since_date = date("Y-m-d H:i:s", time()-(intval($_SESSION['res_sort_bereich'])*24*60*60));
  date_default_timezone_set("UTC");
  $where_bereich = "`reser_geloescht`.`von` > '$since_date'";
}

$where_txt = '';
if ($where_bereich != '' && $where_pilot != '')
  $where_txt = "WHERE $where_bereich AND $where_pilot";
else if ($where_bereich != '')
  $where_txt = "WHERE $where_bereich";
else if ($where_pilot != '')
  $where_txt = "WHERE $where_pilot";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Geloeschte Reservationen</title>
  <meta name="title" content="Benutzer Administration">
  <meta name="keywords" content="Benutzer,Administration">
  <meta name="description" content="Benutzer Administration">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/css/reservationen.css">
</head>

<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<body>
<?php include_once('includes/usermenu.php'); ?>
  <main>
    <div id="formular_innen">
      <div class="center">
        <h1>Gelöschte Reservationen</h1>

          <form style="display: inline-block;" action="res_admin.php" method='get'>
              <select  onchange='this.form.submit()' style="width: 16em;" name = "pilot_id">
<?php
$res = $mysqli->query("SELECT * FROM `members` ORDER BY `pilotid`;");

echo '<option value="">alle Piloten</option>';
while ($obj = $res->fetch_object())
{
  $selected = "";
  if ($obj->pilotid == $_SESSION['res_sort_pilot'])
    $selected = 'selected="selected"';
  echo '<option '.$selected.' value="'.$obj->pilotid.'">['.str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT).'] '.$obj->name.'</option>';
}
?>
              </select>
          </form>

          <form style="display: inline-block;" action="res_admin.php" method='get'>
              <select  onchange='this.form.submit()' style="width: 12em;" name = "z_bereich">
                <option <?php if ($_SESSION['res_sort_bereich'] == '0') echo 'selected="selected"'; ?> value="0">alle</option>
                <option <?php if ($_SESSION['res_sort_bereich'] == '30') echo 'selected="selected"'; ?> value="30">letze 30 Tage</option>
                <option <?php if ($_SESSION['res_sort_bereich'] == '90') echo 'selected="selected"'; ?>value="90">letze 90 Tage</option>
                <option <?php if ($_SESSION['res_sort_bereich'] == '180') echo 'selected="selected"'; ?> value="180">letze 180 Tage</option>
                <option <?php if ($_SESSION['res_sort_bereich'] == '365') echo 'selected="selected"'; ?> value="365">letze 365 Tage</option>
                <option <?php if ($_SESSION['res_sort_bereich'] == '1.1') echo 'selected="selected"'; ?> value="1.1">seit Anfang Jahr </option>
              </select>
          </form>
          <table class='vertical_table'>
          <tr>
          <th><a href="res_admin.php?sort=timestamp"><b>Am</b></a></th>
            <th><a href="res_admin.php?sort=pilotid"><b>Pilot</b></a></th>
            <th><a href="res_admin.php?sort=flieger"><b>Flieger</b></a></th>
            <th><a href="res_admin.php?sort=von"><b>Datum</b></a></th>
            <th><b>Grund</b></th>
            <th><a href="res_admin.php?sort=loescher"><b>Gelöscht durch</b></a></th>
          </tr>

<?php 

$query = " SELECT 
  `reser_geloescht`.`timestamp` AS 'timestamp',
  `mem1`.`name` AS 'pilot', 
  `mem1`.`pilotid` AS 'pilotid',
  `mem2`.`name` AS 'loescher',
  `flieger`.`flieger` AS 'flieger',
  `reser_geloescht`.`von` AS 'von',
  `reser_geloescht`.`bis` AS 'bis',
  `reser_geloescht`.`grund` AS 'grund'
      FROM `reser_geloescht`
          LEFT OUTER JOIN `members` AS `mem1` ON `reser_geloescht`.`userid` = `mem1`.`id`
          LEFT OUTER JOIN `members` AS `mem2` ON `reser_geloescht`.`loescher` = `mem2`.`id`
          LEFT OUTER JOIN `flieger` AS `flieger` ON `reser_geloescht`.`fliegerid` = `flieger`.`id`
   $where_txt $order_by_txt LIMIT 600;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{

  $gel_datum = $obj->timestamp;
  list( $tag, $zeit) = explode(" ", $obj->timestamp);
  $tmp = explode("-", $tag);
  $gel_datum = $tmp[2].'.'.$tmp[1].'.'.$tmp[0];

  if ($obj->loescher == $obj->pilot)
    $loescher = "<span style='color: #999999'>".$obj->loescher."</span>";
  else
    $loescher = $obj->loescher;

  echo "\n<tr>
           <td style='text-align: left; background-color: transparent; color: #333333; font-weight: bold;'>$gel_datum</td>
           <td>[".str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT)."] ".$obj->pilot."</td>
           <td>$obj->flieger</td>
           <td>".mysql2chtimef($obj->von, $obj->bis, FALSE)."</td>
           <td style='font-weight: bold; color #333333;'>".nl2br($obj->grund)."</td>
           <td>".$loescher."</td>
        </tr>";
}
?>
          </table>
        </div>
    </div>
  </main>
</body>
</html>