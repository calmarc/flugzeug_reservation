<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/reservations_functions.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }

//============================================================================
// alle 'gueltigen' ermitteln (der rest dann yellow (standby markieren)
// TODO: identisch wie in res_neu .. auslagern

$valid_res = get_all_valid_reservations($mysqli);

// default
if (!isset($_SESSION['pilot_id']))
  $pilot_id = "";
else
  $pilot_id = $_SESSION['pilot_id'];

if (!isset($_SESSION['res_sort_dir'])) $_SESSION['res_sort_dir'] = "DESC";
if (!isset($_SESSION['res_sort_by'])) $_SESSION['res_sort_by'] = "timestamp";
if (!isset($_SESSION['res_sort_pilot'])) $_SESSION['res_sort_pilot'] = $pilot_id;

if (isset($_GET['pilot_id']))
  $_SESSION['res_sort_pilot'] = $_GET['pilot_id'];

$where_pilot = "";
if ($_SESSION['res_sort_pilot'] != "")
  $where_pilot = "`mem1`.`pilot_id` = ".intval($_SESSION['res_sort_pilot']);

$t_old = $_SESSION['res_sort_by'];
if (isset($_GET['sort']) && $_GET['sort'] != '') $_SESSION['res_sort_by'] = $_GET['sort'];

if (isset($_GET['sort']) && $t_old == $_GET['sort']) // glieche kolumne gedruckt - also dir wechsel
  if ($_SESSION['res_sort_dir'] == "ASC")
      $_SESSION['res_sort_dir'] = "DESC";
  else
      $_SESSION['res_sort_dir'] = "ASC";

$order_by_txt = "ORDER BY `".$_SESSION['res_sort_by']."` ".$_SESSION['res_sort_dir'];

//default
if (!isset($_SESSION['res_sort_bereich_res'])) $_SESSION['res_sort_bereich_res'] = "$-~";
if (isset($_GET['z_bereich']) && $_GET['z_bereich'] != '') $_SESSION['res_sort_bereich_res'] = $_GET['z_bereich'];

date_default_timezone_set("Europe/Zurich");
$lokal_datetime = date("Y-m-d H:i:s", time());
date_default_timezone_set("UTC");

$where_bereich = '';

if ($_SESSION['res_sort_bereich_res'] == "$-~")
{
  $where_bereich = "`reservationen`.`bis` >= '{$lokal_datetime}'";
}
else if ($_SESSION['res_sort_bereich_res'] == "-12-$")
{
  date_default_timezone_set("Europe/Zurich");
  $von_datetime = date("Y-m-d H:i:s", time() - 60 * 60 * 24 * 365);
  date_default_timezone_set("UTC");
  $where_bereich = "`reservationen`.`bis` > '{$von_datetime}' AND `reservationen`.`von` <= '{$lokal_datetime}'";
}
else if ($_SESSION['res_sort_bereich_res'] == "$-+3")
{
  date_default_timezone_set("Europe/Zurich");
  $bis_datetime = date("Y-m-d H:i:s", time() + 60 * 60 * 24 * 93);
  date_default_timezone_set("UTC");
  $where_bereich = "`reservationen`.`bis` >= '{$lokal_datetime}' AND `reservationen`.`von` <= '{$bis_datetime}' ";
}
else if ($_SESSION['res_sort_bereich_res'] == "-1-+1")
{
  date_default_timezone_set("Europe/Zurich");
  $von_datetime = date("Y-m-d H:i:s", time() - 60 * 60 * 24 * 31);
  $bis_datetime = date("Y-m-d H:i:s", time() + 60 * 60 * 24 * 31);
  date_default_timezone_set("UTC");
  $where_bereich = "`reservationen`.`bis` >= '{$von_datetime}' AND `reservationen`.`von` <= '{$bis_datetime}' ";
}
else if ($_SESSION['res_sort_bereich_res'] == "-12-~")
{
  date_default_timezone_set("Europe/Zurich");
  $von_datetime = date("Y-m-d H:i:s", time() - 60 * 60 * 24 * 365);
  date_default_timezone_set("UTC");
  $where_bereich = "`reservationen`.`bis` >= '{$von_datetime}'";
}


$where_txt = '';
if ($where_bereich != '' && $where_pilot != '')
  $where_txt = " WHERE {$where_bereich} AND {$where_pilot} ";
else if ($where_bereich != '')
  $where_txt = " WHERE {$where_bereich} ";
else if ($where_pilot != '')
  $where_txt = " WHERE {$where_pilot} ";

print_html_to_body('Aktuelle Reservationen', '');
include_once('includes/usermenu.php');

?>
  <main>
    <div id="formular_innen">
      <div class="center">
          <h1>Reservationen <a href="javascript:window.print()"><img alt="Ausdrucken" src="/reservationen/bilder/print-out.png" /></a></h1>

          <form style="display: inline-block;" action="res_momentan.php" method='get'>
              <select size="1" onchange='this.form.submit()' style="width: 16em;" name = "pilot_id">
<?php
$res = $mysqli->query("SELECT * FROM `piloten` ORDER BY `pilot_id`;");

echo "<option value=''>alle Piloten</option>";
echo "<option value='{$_SESSION['pilot_id']}'>Eigene Reservationen</option>";

while ($obj = $res->fetch_object())
{
  $selected = "";
  if ($obj->pilot_id == $_SESSION['res_sort_pilot'])
    $selected = 'selected="selected"';
  echo '<option '.$selected.' value="'.$obj->pilot_id.'">['.str_pad($obj->pilot_id, 3, "0", STR_PAD_LEFT).'] '.$obj->name.'</option>';
}

?>
              </select>
          </form>
          <form style="display: inline-block;" action="res_momentan.php" method='get'>
              <select size="1" onchange='this.form.submit()' style="width: 12em;" name = "z_bereich">
                <option <?php if ($_SESSION['res_sort_bereich_res'] == '$-~') echo 'selected="selected"'; ?> value="$-~">&gt; jetzt</option>
                <option <?php if ($_SESSION['res_sort_bereich_res'] == '-12-$') echo 'selected="selected"'; ?> value="-12-$">&lt; jetzt</option>
                <option <?php if ($_SESSION['res_sort_bereich_res'] == '$-+3') echo 'selected="selected"'; ?> value="$-+3">jetzt bis +3 Mt.</option>
                <option <?php if ($_SESSION['res_sort_bereich_res'] == '-1-+1') echo 'selected="selected"'; ?> value="-1-+1">-1 Mt. bis +1 Mt.</option>
                <option <?php if ($_SESSION['res_sort_bereich_res'] == '-12-~') echo 'selected="selected"'; ?> value="-12-~">alle</option>
              </select>
          </form>
          <table class='vertical_table'>
          <tr>
          <th style="background-color: #99ff99;"></th>
          <!--<th><a href="res_momentan.php?sort=timestamp"><b>Eingegeben</b></a></th>-->
            <th><a href="res_momentan.php?sort=pilot_id"><b>Pilot</b></a></th>
            <th><a href="res_momentan.php?sort=flieger"><b>Flugzeug</b></a></th>
            <th><a href="res_momentan.php?sort=von"><b>Datum</b></a></th>
          </tr>
<?php

$query = " SELECT
  `reservationen`.`id` AS 'id',
  `reservationen`.`timestamp` AS 'timestamp',
  `mem1`.`name` AS 'pilot',
  `mem1`.`pilot_id` AS 'pilot_id',
  `flieger`.`flieger` AS 'flieger',
  `flieger`.`id` AS 'flieger_id',
  `reservationen`.`von` AS 'von',
  `reservationen`.`bis` AS 'bis'
      FROM `reservationen`
          LEFT OUTER JOIN `piloten` AS `mem1` ON `reservationen`.`user_id` = `mem1`.`id`
          LEFT OUTER JOIN `flieger` AS `flieger` ON `reservationen`.`flieger_id` = `flieger`.`id`
      {$where_txt} {$order_by_txt} LIMIT 150;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  $yellow = '';
  if (! in_array(strval($obj->id), $valid_res[$obj->flieger_id - 1]))
    $yellow = 'style="background-color: #ffff99; color: #ff6600 !important;"';

  $stamp_datum = $obj->timestamp;
  list( $tag, $zeit) = explode(" ", $obj->timestamp);
  $tmp = explode("-", $tag);
  $stamp_datum = $tmp[2].'.'.$tmp[1].'.'.$tmp[0];

  list( $g_datum, $zeit) = explode(" ", $obj->von);
  list( $g_jahr, $g_monat, $g_tag) = explode("-", $g_datum);
  $g_jahr = intval($g_jahr);
  $g_monat = intval($g_monat);
  $g_tag = intval($g_tag);

  echo "\n<tr>
           <td class='trblank'><a href='index.php?show=tag&amp;tag={$g_tag}&amp;monat={$g_monat}&amp;jahr={$g_jahr}'>[zeigen]</a></td>
           <!--<td style='text-align: left; background-color: transparent; color: #333333;'>{$stamp_datum}</td>-->
           <td {$yellow}>[".str_pad($obj->pilot_id, 3, "0", STR_PAD_LEFT)."] {$obj->pilot}</td>
           <td {$yellow}>{$obj->flieger}</td>
           <td {$yellow}>".mysql2chtimef($obj->von, $obj->bis, FALSE)."</td>
        </tr>";
}
?>
          </table>
        </div>
    </div>
  </main>
</body>
</html>
