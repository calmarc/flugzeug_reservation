<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }

include_once ('res_geloescht.inc.php');

// TODO: diese res_xy haben hier oben alle etwa das gleiche..
// TODO: mal aussortieren in eins.. und so
// default
if (!isset($_SESSION['res_sort_dir'])) $_SESSION['res_sort_dir'] = "DESC";
if (!isset($_SESSION['res_sort_by'])) $_SESSION['res_sort_by'] = "timestamp";
if (!isset($_SESSION['res_sort_pilot'])) $_SESSION['res_sort_pilot'] = "";

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
if (!isset($_SESSION['res_sort_bereich'])) $_SESSION['res_sort_bereich'] = "0";
if (isset($_GET['z_bereich']) && $_GET['z_bereich'] != '') $_SESSION['res_sort_bereich'] = $_GET['z_bereich'];


if ($_SESSION['res_sort_bereich'] == "0")
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

print_html_to_body('Geloeschte Reservationen', '');
include_once('includes/usermenu.php');

?>
  <main>
    <div id="formular_innen">
      <div class="center">
        <h1>Gelöschte Reservationen</h1>

          <form style="display: inline-block;" action="res_geloescht.php" method='get'>
              <select size="1"  onchange='this.form.submit()' style="width: 12em;" name = "pilot_id">
<?php
$res = $mysqli->query("SELECT * FROM `piloten` ORDER BY `pilot_id`;");

echo '<option value="">alle Piloten</option>';
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

          <form style="display: inline-block;" action="res_geloescht.php" method='get'>
              <select size="1" onchange='this.form.submit()' style="width: 10em;" name = "z_bereich">
                <option <?php if ($_SESSION['res_sort_bereich'] == '0') echo 'selected="selected"'; ?> value="0">alle</option>
                <option <?php if ($_SESSION['res_sort_bereich'] == '30') echo 'selected="selected"'; ?> value="30">letze 30 Tage</option>
                <option <?php if ($_SESSION['res_sort_bereich'] == '90') echo 'selected="selected"'; ?>value="90">letze 90 Tage</option>
                <option <?php if ($_SESSION['res_sort_bereich'] == '365') echo 'selected="selected"'; ?> value="365">letze 365 Tage</option>
              </select>
          </form>

          <form style="display: inline-block;" action='res_geloescht.php' method='get'>
            &nbsp; Löschen: <select style="width: 8em;" onchange='this.form.submit()' name='loeschen' size="1" id='loeschen'>
              <option value="9876543210">                </option>
              <option value="365">&gt; 12 Monate</option>
              <option value="182">&gt; 6 Monate</option>
              <option value="90">&gt; 3 Monate</option>
              <option value="30">&gt; 1 Monat</option>
              <option value="7">&gt; 1 Woche</option>
            </select>
          </form>
          <table class='vertical_table'>
          <tr>
          <th><a href="res_geloescht.php?sort=timestamp"><b>Am</b></a></th>
            <th><a href="res_geloescht.php?sort=pilot_id"><b>Pilot</b></a></th>
            <th><a href="res_geloescht.php?sort=flieger"><b>Flugzeug</b></a></th>
            <th><a href="res_geloescht.php?sort=von"><b>Datum</b></a></th>
            <th><b>Grund</b></th>
            <th><a href="res_geloescht.php?sort=loescher_id"><b>Gelöscht durch</b></a></th>
          </tr>

<?php

$query = " SELECT
  `reser_geloescht`.`timestamp` AS 'timestamp',
  `mem1`.`name` AS 'pilot',
  `mem1`.`pilot_id` AS 'pilot_id',
  `mem2`.`name` AS 'loescher_id',
  `flieger`.`flieger` AS 'flieger',
  `reser_geloescht`.`von` AS 'von',
  `reser_geloescht`.`bis` AS 'bis',
  `reser_geloescht`.`grund` AS 'grund'
      FROM `reser_geloescht`
          LEFT OUTER JOIN `piloten` AS `mem1` ON `reser_geloescht`.`user_id` = `mem1`.`id`
          LEFT OUTER JOIN `piloten` AS `mem2` ON `reser_geloescht`.`loescher_id` = `mem2`.`id`
          LEFT OUTER JOIN `flieger` AS `flieger` ON `reser_geloescht`.`flieger_id` = `flieger`.`id`
   $where_txt $order_by_txt LIMIT 600;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{

  $gel_datum = $obj->timestamp;
  list( $tag, $zeit) = explode(" ", $obj->timestamp);
  $tmp = explode("-", $tag);
  $gel_datum = $tmp[2].'.'.$tmp[1].'.'.$tmp[0];

  if ($obj->loescher_id == $obj->pilot)
    $loescher_id = "<span style='color: #999999'>".$obj->loescher_id."</span>";
  else
    $loescher_id = $obj->loescher_id;

  echo "\n<tr>
           <td style='text-align: left; background-color: transparent; color: #333333; font-weight: bold;'>{$gel_datum}</td>
           <td>[".str_pad($obj->pilot_id, 3, "0", STR_PAD_LEFT)."] {$obj->pilot}</td>
           <td>{$obj->flieger}</td>
           <td>".mysql2chtimef($obj->von, $obj->bis, FALSE)."</td>
           <td style='font-weight: bold; color #333333;'>".nl2br($obj->grund)."</td>
           <td>{$loescher_id}</td>
        </tr>";
}
?>
          </table>
        </div>
    </div>
  </main>
</body>
</html>
