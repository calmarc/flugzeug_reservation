<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/reservations_functions.php');
include_once ('includes/functions.php');
include_once ('includes/sort.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }

$admin_bol = check_admin($mysqli);

//============================================================================
// alle 'gueltigen' ermitteln (der rest dann yellow (standby markieren)

$valid_res = get_all_list_active_reserv($mysqli);


//============================================================================
// sortieren nach kolumnen

// defaults
if (!isset($_SESSION['momentan_sort_dir'])) $_SESSION['momentan_sort_dir'] = "ASC";
if (!isset($_SESSION['momentan_sort_by'])) $_SESSION['momentan_sort_by'] = "von";

// get new sorting GET value
$t_old = $_SESSION['momentan_sort_by'];
if (isset($_GET['sort']) && $_GET['sort'] != '') $_SESSION['momentan_sort_by'] = $_GET['sort'];

// glieche kolumne gedruckt - also dir wechsel
if (isset($_GET['sort']) && $t_old == $_GET['sort'])
  if ($_SESSION['momentan_sort_dir'] == "ASC")
      $_SESSION['momentan_sort_dir'] = "DESC";
  else
      $_SESSION['momentan_sort_dir'] = "ASC";

// string generieren
$order_by_txt = "ORDER BY `".$_SESSION['momentan_sort_by']."` ".$_SESSION['momentan_sort_dir'];

// die 3 kolumnen zum ASC/DESC ordnnen - das pfeil-bild generieren
$datum_img = $pilot_img = $flugzeug_img = "";
if ($_SESSION['momentan_sort_by'] == 'pilot_nr')
  $pilot_img = "<img alt='asc/desc' src='bilder/arrow-{$_SESSION['momentan_sort_dir']}.png' />";
else if ($_SESSION['momentan_sort_by'] == 'flugzeug')
  $flugzeug_img = "<img alt='asc/desc' src='bilder/arrow-{$_SESSION['momentan_sort_dir']}.png' />";
else if ($_SESSION['momentan_sort_by'] == 'von')
  $datum_img = "<img alt='asc/desc' src='bilder/arrow-{$_SESSION['momentan_sort_dir']}.png' />";

//---------------------------------------------------------------------------

print_html_to_body('Aktuelle Reservationen', '');
include_once('includes/usermenu.php');

?>
  <main>
  <h1><?php if (!$admin_bol) echo "Deine "; ?>Reservationen <a id="printer" href="javascript:window.print()"><img alt="Ausdrucken" src="/reservationen/bilder/print-out.png" /></a></h1>
    <div id="formular_innen">
      <div class="center">

<?php

if (check_admin($mysqli))
{
 
  ////////////////////////////////////////////////////////////////////////////////////
  // pilot select

  //default to yourself
  if (!isset($_SESSION['mom_where_pilot_nr'])) 
    $_SESSION['mom_where_pilot_nr'] = $_SESSION['pilot_nr'];

  // set to get if there
  if (isset($_GET['pilot_nr']))
    $_SESSION['mom_where_pilot_nr'] = $_GET['pilot_nr'];

  // set when 
  $where_pilot_nr_txt = "";
  if ($_SESSION['mom_where_pilot_nr'] != "")
    $where_pilot_nr_txt = "`mem1`.`pilot_nr` = '{$_SESSION['mom_where_pilot_nr']}'";

  echo select_pilot_nr_momentan($mysqli, $_SESSION['mom_where_pilot_nr'], "res_momentan.php");
}
else
  $where_pilot_nr_txt = "`mem1`.`pilot_nr` = '{$_SESSION['pilot_nr']}'";

////////////////////////////////////////////////////////////////////////////////////
// von select

if (!isset($_SESSION['z_bereich'])) 
  $_SESSION['z_bereich'] = "";

// set to get if there
if (isset($_GET['z_bereich']))
  $_SESSION['z_bereich'] = $_GET['z_bereich'];

// calculate time back...
date_default_timezone_set("Europe/Zurich");
$since_date = date("Y-m-d H:i:s", time());
date_default_timezone_set("UTC");

// set when 
$where_z_bereich_txt = "";
if ($_SESSION['z_bereich'] == "kommende")
  $where_z_bereich_txt = "`reservationen`.`von` >= '$since_date'";
else if ($_SESSION['z_bereich'] == "vergangene")
  $where_z_bereich_txt = "`reservationen`.`von` < '$since_date'";

?>

          <form style="display: inline-block;" action="res_momentan.php" method='get'>
            <select size="1" onchange='this.form.submit()' style="width: 10em;" name = "z_bereich">
              <option <?php if ($_SESSION['z_bereich'] == 'kommende') echo 'selected="selected"'; ?> value="kommende">Kommende</option>
              <option <?php if ($_SESSION['z_bereich'] == 'vergangene') echo 'selected="selected"'; ?> value="vergangene">Vergangene</option>
              <option <?php if ($_SESSION['z_bereich'] == 'alle') echo 'selected="selected"'; ?> value="alle">Alle</option>
            </select>
          </form>
<?php
// --------------------------------------------------------------------------------
?>
          <table class='vertical_table th_filter'>
          <tr>
          <!--<th class="hide_on_print formular_zelle"></th>-->
          <th class='formular_zelle'></th>
            <th style='min-width: 16em;'><a href="res_momentan.php?sort=pilot_nr"><b>Pilot</b><?php echo $pilot_img; ?></a></th>
            <th style='min-width: 13em;'><a href="res_momentan.php?sort=flugzeug"><b>Flugzeug</b><?php echo $flugzeug_img; ?></a></th>
            <th style='min-width: 12em;'><a href="res_momentan.php?sort=von"><b>Datum</b><?php echo $datum_img; ?></a></th>
          </tr>
<?php

$where_txt = generate_where(array($where_pilot_nr_txt, $where_z_bereich_txt));

$query = " SELECT
  `reservationen`.`id` AS 'id',
  `reservationen`.`timestamp` AS 'timestamp',
  `mem1`.`name` AS 'pilot',
  `mem1`.`pilot_nr` AS 'pilot_nr',
  `flugzeug`.`flugzeug` AS 'flugzeug',
  `flugzeug`.`id` AS 'flugzeug_id',
  `reservationen`.`von` AS 'von',
  `reservationen`.`bis` AS 'bis'
      FROM `reservationen`
          LEFT OUTER JOIN `piloten` AS `mem1` ON `reservationen`.`user_id` = `mem1`.`id`
          LEFT OUTER JOIN `flugzeug` AS `flugzeug` ON `reservationen`.`flugzeug_id` = `flugzeug`.`id`
      {$where_txt} {$order_by_txt} LIMIT 150;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  $yellow = '';
  if (! in_array(strval($obj->id), $valid_res[$obj->flugzeug_id - 1]))
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
           <td class='trblank hide_on_print'><a href='index.php?show=tag&amp;tag={$g_tag}&amp;monat={$g_monat}&amp;jahr={$g_jahr}'>[zeig]</a></td>
           <!--<td style='text-align: left; background-color: transparent; color: #333333;'>{$stamp_datum}</td>-->
           <td {$yellow}>[".str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT)."] {$obj->pilot}</td>
           <td {$yellow}>{$obj->flugzeug}</td>
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
