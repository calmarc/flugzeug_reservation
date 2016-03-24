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
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

$admin_bol = check_admin($mysqli);

include_once('res_neu.inc.php');

print_html_to_body('Flugzeug reservieren', '');
include_once('includes/usermenu.php'); ?>

<main>
  <h1 class="hide_on_print">Flugzeug reservieren</h1>
  <div id="formular_innen">


<?php

if (isset($error_msg) && $error_msg != "")
  echo "<p><b style='color: red;'>$error_msg</b></p>";

$flugzeug = get_flugzeug_from_id($mysqli, $flugzeug_id);

?>
  <form action='res_neu.php' method='post'>
  <input type='hidden' name='flugzeug_id' value='<?php echo $flugzeug_id; ?>' />
    <div class='center hide_on_print'>
      <table class='vtable'>
        <tr class="trblank">
          <td><b>Pilot:</b></td>

<?php if ($admin_bol)
{
  echo "<td><select size='1' style='width: 16em' name='user_id'>";
  combobox_piloten($mysqli, $_SESSION['pilot_nr']);
  echo "</select></td>";
}
else
{
   echo "<td><b>[".str_pad($_SESSION['pilot_nr'], 3, "0", STR_PAD_LEFT)."] {$_SESSION['name']}</b>";
   echo "<input type='hidden' name='user_id' value='{$user_id}' />";
   echo "</td>";
}
?>
        </tr>
        <tr class="trblank">
          <td><b>Flugzeug:</b></td>
          <td><b><?php echo $flugzeug; ?></b></td>
        </tr>
        <tr>
          <td><b>Datum von:</b></td>
          <td>
            <select size="1" name="von_tag" style="width: 46px;">
              <?php combobox_tag($_SESSION['von_tag']); ?>
            </select> <b>.</b>
            <select size="1" name="von_monat" style="width: 46px;">
              <?php combobox_monat($_SESSION['von_monat']); ?>
            </select> <b>.</b>
            <select size="1" name="von_jahr" style="width: 86px;">
              <?php combobox_jahr($_SESSION['von_jahr']); ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><b>Zeit von:</b></td>
          <td>
            <select size="1" name="von_stunde" style="width: 46px;">
              <?php combobox_stunde($_SESSION['von_stunde']); ?>
            </select> <b>:</b>
            <select size="1" name="von_minuten" style="width: 46px;">
              <?php combobox_minute($_SESSION['von_minuten']); ?>
            </select> <b>Uhr</b>
          </td>
        </tr>
        <tr>
          <td><b>Datum bis:</b></td>
          <td>
            <select size="1" name="bis_tag" style="width: 46px;">
              <?php combobox_tag($_SESSION['bis_tag']); ?>
            </select> <b>.</b>
            <select size="1" name="bis_monat" style="width: 46px;">
              <?php combobox_monat($_SESSION['bis_monat']); ?>
            </select> <b>.</b>
            <select size="1" name="bis_jahr" style="width: 86px;">
              <?php combobox_jahr($_SESSION['bis_jahr']); ?>
            </select>
          </td>
        </tr>
        <tr>
          <td><b>Zeit bis:</b></td>
          <td>
            <select size="1" name="bis_stunde" style="width: 46px;">
              <?php combobox_stunde($_SESSION['bis_stunde']); ?>
            </select> <b>:</b>
            <select size="1" name="bis_minuten" style="width: 46px;">
              <?php combobox_minute($_SESSION['bis_minuten']); ?>
            </select> <b>Uhr</b>
          </td>
        </tr>
      </table>
    <input class='submit_button' type='submit' name='submit' value='Reservierung abschicken' />
    </div>
<br />
<br />
<hr />
<br />
<h3>Deine kommenden Reservationen</h3>
          <table class='vertical_table th_filter'>
          <tr>
          <!--<th class="hide_on_print formular_zelle"></th>-->
          <th class='formular_zelle'></th>
          <!--<th><a href="res_momentan.php?sort=timestamp"><b>Eingegeben</b></a></th>-->
            <th style='min-width: 10em;'><b>Pilot</b></th>
            <th style='min-width: 10em;'><b>Flugzeug</b></th>
            <th style='min-width: 10em;'><b>Datum</b></th>
          </tr>
<?php

$valid_res = get_all_list_active_reserv($mysqli);

$where_txt = "WHERE `user_id` = {$_SESSION['user_id']} AND `bis` > '{$local_datetime}'";
$order_by_txt = "ORDER BY `von` ASC";
$query = " SELECT
  `reservationen`.`id` AS 'id',
  `reservationen`.`timestamp` AS 'timestamp',
  `mem1`.`name` AS 'pilot',
  `mem1`.`pilot_nr` AS 'pilot_nr',
  `mem1`.`id` AS 'user_id',
  `flugzeug`.`flugzeug` AS 'flugzeug',
  `flugzeug`.`id` AS 'flugzeug_id',
  `reservationen`.`von` AS 'von',
  `reservationen`.`bis` AS 'bis'
      FROM `reservationen`
          LEFT OUTER JOIN `piloten` AS `mem1` ON `reservationen`.`user_id` = `mem1`.`id`
          LEFT OUTER JOIN `flugzeug` AS `flugzeug` ON `reservationen`.`flugzeug_id` = `flugzeug`.`id`
      {$where_txt} {$order_by_txt} LIMIT 15;";

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
  </form>
  </div>
</main>
</body>
</html>
