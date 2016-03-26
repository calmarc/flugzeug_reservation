<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/functions.php');
include_once ('includes/sort.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }

include_once ('res_teilgeloescht.inc.php');

// default
if (!isset($_SESSION['res_sort_dir'])) $_SESSION['res_sort_dir'] = "DESC";
if (!isset($_SESSION['res_sort_by'])) $_SESSION['res_sort_by'] = "timestamp";
if (!isset($_SESSION['geloescht_default_pilot'])) $_SESSION['geloescht_default_pilot'] = "";

if (isset($_GET['pilot_nr']))
  $_SESSION['geloescht_default_pilot'] = $_GET['pilot_nr'];

$where_pilot = "";
if ($_SESSION['geloescht_default_pilot'] != "")
  $where_pilot = "`mem1`.`pilot_nr` = ".intval($_SESSION['geloescht_default_pilot']);

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
  $where_bereich = "`reser_getrimmt`.`von` > '$since_date'";
}

$where_txt = '';
if ($where_bereich != '' && $where_pilot != '')
  $where_txt = "WHERE $where_bereich AND $where_pilot";
else if ($where_bereich != '')
  $where_txt = "WHERE $where_bereich";
else if ($where_pilot != '')
  $where_txt = "WHERE $where_pilot";

print_html_to_body('Teil-geloeschte Reservationen', '');
include_once('includes/usermenu.php');

?>
  <main>
    <h1>Teil-gelöschte Reservationen</h1>
    <div id="formular_innen">
      <div class="center">

<?php

////////////////////////////////////////////////////////////////////////////////////
// pilot select

if (!isset($_SESSION['where_pilot_nr'])) 
  $_SESSION['where_pilot_nr'] = "";

// set to get if there
if (isset($_GET['pilot_nr']))
  $_SESSION['where_pilot_nr'] = $_GET['pilot_nr'];

// set when 
$where_pilot_nr_txt = "";
if ($_SESSION['where_pilot_nr'] != "")
{
  $pilot_nr_pad = str_pad($_SESSION['where_pilot_nr'], 3, "0", STR_PAD_LEFT);
  $where_pilot_nr_txt = "`durch` LIKE '%{$pilot_nr_pad}%'";
}
echo select_pilot_nr_geloescht($mysqli, $_SESSION['where_pilot_nr'], "res_teilgeloescht.php");

////////////////////////////////////////////////////////////////////////////////////
// von select

if (!isset($_SESSION['where_von'])) 
  $_SESSION['where_von'] = "";

// set to get if there
if (isset($_GET['where_von']))
  $_SESSION['where_von'] = $_GET['where_von'];

// calculate time back...
date_default_timezone_set("Europe/Zurich");
$since_date = date("Y-m-d H:i:s", time()-(intval($_SESSION['where_von'])*24*60*60));
date_default_timezone_set("UTC");
$where_bereich = "`reser_getrimmt`.`von` > '$since_date'";

// set when 
$where_von_txt = "";
if ($_SESSION['where_von'] != "")
  $where_von_txt = "`reser_getrimmt`.`von` > '{$since_date}'";

?>
          <form style="display: inline-block;" action="res_geloescht.php" method='get'>
              <select size="1"  onchange='this.form.submit()' style="width: 10em;" name="where_von">
                <option <?php if ($_SESSION['where_von'] == '30') echo 'selected="selected"'; ?> value="30">letze 30 Tage</option>
                <option <?php if ($_SESSION['where_von'] == '90') echo 'selected="selected"'; ?>value="90">letze 90 Tage</option>
                <option <?php if ($_SESSION['where_von'] == '365') echo 'selected="selected"'; ?> value="365">letze 365 Tage</option>
                <option <?php if ($_SESSION['where_von'] == '') echo 'selected="selected"'; ?> value="">alle</option>
              </select>
          </form>
<?php 
//-------------------------------------------------------------------------------- 
?>
          <table class='vertical_table'>
          <tr>
          <th><a href="res_teilgeloescht.php?sort=timestamp"><b>Zeit-Stempel</b></a></th>
            <th><a href="res_teilgeloescht.php?sort=pilot_nr"><b>Pilot</b></a></th>
            <th><a href="res_teilgeloescht.php?sort=flugzeug"><b>Flugzeug</b></a></th>
            <th><a href="res_teilgeloescht.php?sort=von"><b>Datum</b></a></th>
            <th><b>Gelöscht</b></th>
            <th><b>Grund</b></th>
            <th><a href="res_teilgeloescht.php?sort=loescher_id"><b>Gelöscht durch</b></a></th>
          </tr>

<?php

$query = " SELECT
  `reser_getrimmt`.`timestamp` AS 'timestamp',
  `mem1`.`name` AS 'pilot',
  `mem1`.`pilot_nr` AS 'pilot_nr',
  `mem2`.`name` AS 'loescher_id',
  `flugzeug`.`flugzeug` AS 'flugzeug',
  `reser_getrimmt`.`von` AS 'von',
  `reser_getrimmt`.`bis` AS 'bis',
  `reser_getrimmt`.`grund` AS 'grund',
  `reser_getrimmt`.`getrimmt_von` AS 'getrimmt_von',
  `reser_getrimmt`.`getrimmt_bis` AS 'getrimmt_bis'
      FROM `reser_getrimmt`
          LEFT OUTER JOIN `piloten` AS `mem1` ON `reser_getrimmt`.`user_id` = `mem1`.`id`
          LEFT OUTER JOIN `piloten` AS `mem2` ON `reser_getrimmt`.`loescher_id` = `mem2`.`id`
          LEFT OUTER JOIN `flugzeug` AS `flugzeug` ON `reser_getrimmt`.`flugzeug_id` = `flugzeug`.`id`
  {$where_txt} {$order_by_txt} LIMIT 600;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  $gel_datum = mysql_stamp_to_ch($mysqli, $obj->timestamp);

  if ($obj->loescher_id == $obj->pilot)
    $loescher_id = "<span style='color: #999999'>{$obj->loescher_id}</span>";
  else
    $loescher_id = $obj->loescher_id;

  echo "\n<tr>
           <td style='text-align: left; background-color: transparent; color: #333333;'>{$gel_datum}</td>
           <td>[".str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT)."] {$obj->pilot}</td>
           <td>{$obj->flugzeug}</td>
           <td>".mysql2chtimef($obj->von, $obj->bis, FALSE)."</td>
           <td>".mysql2chtimef($obj->getrimmt_von, $obj->getrimmt_bis, FALSE)."</td>
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
