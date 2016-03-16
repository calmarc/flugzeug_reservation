<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

date_default_timezone_set("Europe/Zurich");
if (!isset($_SESSION['tag'])) $_SESSION['tag'] = date('d', time());
if (!isset($_SESSION['monat'])) $_SESSION['monat'] = date('m', time());
if (!isset($_SESSION['jahr'])) $_SESSION['jahr'] = date('Y', time());
date_default_timezone_set('UTC');

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

include_once('service_edit.inc.php');

print_html_to_body('Service Eintrag', ''); 
include_once('includes/usermenu.php'); 

?>
<main>
  <div id="formular_innen">

  <h1>Service Liste - <span style="color: #cc3300;"><?php echo $flieger_name; ?></span></h1>

<?php
$hidden = '<input type="hidden" name="flieger_id" value="'.$flieger_id.'" />';
?>
  <form action='/reservationen/service_edit.php' method='post'>
<?php echo $hidden; ?>
    <div class='center'>
      <table class='vtable'>
        <tr class="trblank">
          <td><b>Datum:</b></td>
          <td>
            <select size="1" name="tag" style="width: 46px;">
              <?php combobox_tag($_SESSION['tag']); ?>
            </select> <b>.</b> 
            <select size="1" name="monat" style="width: 46px;">
              <?php combobox_monat($_SESSION['monat']); ?>
            </select> <b>.</b> 
            <select size="1" name="jahr" style="width: 86px;">
              <?php combobox_jahr($_SESSION['jahr']); ?>
            </select>
          </td>
        </tr>
        <tr class="trblank">
          <td><b>Verantwortlich:</b></td>
          <td>
<?php

$res = $mysqli->query("SELECT * FROM `piloten` WHERE `admin` > 0 ORDER BY `pilotid` ASC;");
echo '<select size="1" style="width: 15em;" name="verantwortlich">';
while ($obj = $res->fetch_object())
{
  $selected = "";
  if ($_SESSION['user_id'] == $obj->pilotid)
    $selected = "selected='selected'";
  echo "<option $selected value='".$obj->pilotid."'>".$obj->name."</option>";
}
echo '</select>';

?>
          </td>
        </tr>
        <tr class="trblank">
          <td><b>Bei Zählerstand:</b></td>
          <td><input name="zaehlerstand" style="width: 80px;" required="required" type="number" step="0.01" /></td>
        </tr>
      </table>
    <input class='submit_button' type='submit' name='submit' value='Neuen Service eintragen' />
    </div>
  </form>
    <br />
    <br />
    <hr />
    <br />
    <h3><span style="color: #cc0000;"><?php echo $flieger_name; ?></span> Service-Einträge</h3>

  <div class='center'>
    <table class='vertical_table'>
    <tr>
      <th style="background-color: #99ff99;"></th>
      <th>Datum</th>
      <th>Zählerstand</th>
      <th>Verantwortlich</th>
    </tr>
  <?php

$query = "SELECT `service_eintraege`.`id`,
                 `service_eintraege`.`user_id`,
                 `piloten`.`name`,
                 `service_eintraege`.`zaehler_minute`,
                 `service_eintraege`.`datum`
         FROM `service_eintraege` LEFT OUTER JOIN `piloten` ON `piloten`.`pilotid` = `service_eintraege`.`user_id` 
         WHERE `flieger_id` = '".$flieger_id."'  ORDER BY `zaehler_minute` DESC LIMIT 50;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  list ($jahr, $monat, $tag) = preg_split('/[- ]/', $obj->datum);

  $z_min = $obj->zaehler_minute;

  $zaehlerstand = intval($z_min / 60).".";
  $zaehlerstand .= str_pad(intval($z_min % 60), 2, "0", STR_PAD_LEFT)."h";

  $name = $obj->name;
  $zaehler_min = $obj->zaehler_minute;
  $service_id = $obj->id;
  $user_id = $obj->user_id;

  $edit_link = '<a onclick="return confirm(\'Service-Eintrag wirklich löschen?\')" href="service_edit.php?action=del&amp;service_id='.$service_id.'&amp;flieger_id='.$flieger_id.'"><img src="/reservationen/bilder/delete.png" alt="loeschen" /></a>';

  echo ' <tr>
          <td>'.$edit_link.'</td>
          <td>'.$tag.'.'.$monat.'.'.$jahr.'</td><td style="text-align: right;">'.$zaehlerstand.'</td><td>'.$obj->name.'</td>
        </tr>';
}

?>
      </table>
    </div>
  </div>
</main>
</body>
</html>
