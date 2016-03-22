<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/functions.php');

sec_session_start();

//============================================================================
// Berechtigungen ueberpruefen (admin markieren)

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

$admin_bol = check_admin($mysqli);

//============================================================================
// user_id feststellen  des eintrages (kann auch admin sein)

$user_id = $_SESSION['user_id'];

// falls admin fuer jemanden Eintraege macht.
if (isset($_POST['user_id']))
  $user_id = $_POST['user_id'];


//============================================================================
// GET oder POST Daten verarbeiten
//
include_once('landungs_eintrag.inc.php');


//============================================================================
// HTML

print_html_to_body('Landungs Eintrag', '');
include_once('includes/usermenu.php');

?>
<main>
  <div id="formular_innen">

  <h1>Flug eintragen</h1>

<?php

if (isset($error_msg) && $error_msg != "")
  echo "<p><b style='color: red;'>$error_msg</b></p>";

$query = "SELECT * FROM `flugzeug` WHERE `id` = '{$flugzeug_id}' LIMIT 1;";
$res = $mysqli->query($query);
$obj = $res->fetch_object();
$flugzeugtxt = $obj->flugzeug;

?>
  <form action='landungs_eintrag.php' method='post'>
  <input type='hidden' name='flugzeug_id' value='<?php echo $flugzeug_id; ?>' />
    <div class='center'>
      <table class='vtable'>
        <tr class="trblank">
          <td><b>Pilot:</b></td>

<?php
if ($admin_bol)
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
          <td><b><?php echo $flugzeugtxt; ?></b></td>
        </tr>
        <tr>
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
        <tr>
          <td><b>Zählerstand:</b></td>
          <td><input name="zaehlerstand" style="width: 6em;" required="required" type="number" step="0.01" /></td>
        </tr>
      </table>
    <input class='submit_button' type='submit' name='submit' value='Flug eintragen' />
    </div>
  </form>
  <div class='center'>
    <br />
    <br />
    <br />
    <table class='vertical_table'>
    <tr>
      <th style="background-color: #99ff99;"></th>
      <th>Datum</th>
      <th>Zählerstand</th>
      <th>Dauer</th>
      <th>Pilot</th>
<?php

if (($_SESSION['name'] == 'Airplus' || $admin_bol) && $flugzeug_id == 4)
{
    echo "<th>Motor-Zählerstand</th>";
}
echo '</tr>';

//============================================================================
// Untere Liste ausgeben
// Speziell dass nur die letzten 2 Eintrage fuer nicht-admins.
// Falls airplus etc, drehzahl auch anzeigen.

$query = "SELECT `zaehler_eintraege`.`id`,
                 `zaehler_eintraege`.`user_id`,
                 `piloten`.`name`,
                 `zaehler_eintraege`.`zaehler_minute`,
                 `zaehler_eintraege`.`zaehler_umdrehungen`,
                 `zaehler_eintraege`.`datum`
         FROM `zaehler_eintraege` LEFT OUTER JOIN `piloten` ON `piloten`.`id` = `zaehler_eintraege`.`user_id`
         WHERE `flugzeug_id` = '{$flugzeug_id}'  ORDER BY `zaehler_minute` DESC, `id` DESC LIMIT 30;";

if ($res = $mysqli->query($query))
{
  if ($res->num_rows > 0)
  {
    $flag = TRUE;
    $obj = $res->fetch_object();
    $edit_c = 0; // zaehlt rauf.. damit nur 2 gezeigt werden
    while ($flag)
    {
      list ($jahr, $monat, $tag) = preg_split('/[- ]/', $obj->datum);

      $name = $obj->name;
      $zaehler_min = $obj->zaehler_minute;
      $eintrags_id = $obj->id;
      $user_id = $obj->user_id;
      $z_umdrehungen = $obj->zaehler_umdrehungen;

      if ($obj = $res->fetch_object())
          list($zaehlerstand, $dauer) = zaehler_into($zaehler_min, $obj->zaehler_minute);
      else
      {
          list($zaehlerstand, $dauer) = zaehler_into($zaehler_min, 0);
          $flag = FALSE;
      }

      $edit_link = "";
      // admin + die letzten 2 zum edditieren fuer benutzer
      if (check_admin($mysqli) || ($_SESSION['user_id'] == $user_id && $edit_c < 2))
      {
        $edit_link = "<a href='landungs_edit.php?action=edit&amp;zaehler_id={$eintrags_id}&amp;flugzeug_id={$flugzeug_id}'>[edit]</a>";
        $edit_c++;
      }

      $umdrehungen_txt = "";
      if (($_SESSION['name'] == 'Airplus' || $admin_bol) && $flugzeug_id == 4)
      {
        if ($z_umdrehungen == 0)
          $z_umdrehungen = "";
        $umdrehungen_txt = "<td style='text-align: right;'>{$z_umdrehungen}</td>";
      }

      echo " <tr>
              <td>{$edit_link}</td>
              <td>{$tag}.{$monat}.{$jahr}</td>
              <td style='text-align: right;'>{$zaehlerstand}</td>
              <td style='text-align: right;'>{$dauer}</td>
              <td>{$name}</td>
              {$umdrehungen_txt}
            </tr>";
    }
  }
}

?>
      </table>
    </div>
  </div>
</main>
</body>
</html>
