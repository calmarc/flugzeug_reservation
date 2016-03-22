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
// Berechtigungen pruefen

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

// admin oder benuetzer.. wird unten kontrolliert im .inc.php), weil die zaehler_id zuert
// kontrolliert werden muss.
include_once('landungs_edit.inc.php');

//============================================================================
// HTML

print_html_to_body('Landungs-Eintrag editieren', '');
include_once('includes/usermenu.php');
?>
  <main>
    <div id="formular_innen">

    <h1>Flug-Eintrag editieren</h1>
<?php

// print error (>59 die digits)
if (isset($error_msg) && $error_msg != "")
  echo "<p><b style='color: red;'>$error_msg</b></p>";

// Zaehler_eintrage daten hohlen und als default nehmen
$query = "SELECT * FROM `zaehler_eintraege` WHERE `id` = '{$zaehler_id}' LIMIT 1;";
$res = $mysqli->query($query);
$obj = $res->fetch_object();

$min = intval($obj->zaehler_minute) % 60;
$std = intval($obj->zaehler_minute / 60);
$zaehler_eintrag = $std.'.'.str_pad($min, 2, "0", STR_PAD_LEFT);

list ($jahr, $monat, $tag) = preg_split('/[- ]/', $obj->datum);

list($pilot_nr_pad, $pilot_name) = get_pilot_from_user_id($mysqli, $obj->user_id);

$zaehler_umdrehungen = $obj->zaehler_umdrehungen;

?>
      <form action='landungs_edit.php' method='post'>
        <input type='hidden' name='zaehler_id' value='<?php echo $obj->id; ?>' />
        <input type="hidden" name="flieger_id" value="<?php echo $flieger_id; ?>" />
        <div class='center'>
          <table class='vtable two_standard'>
            <tr class="trblank">
              <td><b>Pilot</b></td>
              <td><b>[<?php echo $pilot_nr_pad.'] '.$pilot_name; ?></b></td>
            </tr>
            <tr class="trblank">
              <td><b>Flieger</b></td>
              <td><b><?php echo $flieger_txt; ?></b></td>
            </tr>
            <tr>
              <td><b>Datum:</b></td>
              <td>
                <select size="1" name="tag" style="width: 46px;">
                  <?php combobox_tag($tag); ?>
                </select> <b>.</b>
                <select size="1" name="monat" style="width: 46px;">
                  <?php combobox_monat($monat); ?>
                </select> <b>.</b>
                <select size="1" name="jahr" style="width: 86px;">
                  <?php combobox_jahr($jahr); ?>
                </select>
              </td>
            </tr>
            <tr>
              <td><b>Zählerstand:</b></td>
              <td><input value="<?php echo $zaehler_eintrag; ?>" name="zaehlerstand" style="width: 6em;" required="required" type="number" step="0.01" /></td>
            </tr>
<?php
if (($_SESSION['name'] == 'Airplus' || $admin_bol) && $flieger_id == 4)
{
      echo "<tr>
              <td><b>Motor-Zählerstand:</b></td>
              <td><input value='{$zaehler_umdrehungen}' name='zaehler_umdrehungen' style='width: 6em;' type='number' step='1' /></td>
            </tr>";
}
?>
          </table>
        <input class='submit_button' type='submit' name='edit' value='Änderungen abschicken' />
        </div>
      </form>

      <hr style="margin: 52px 10px 84px 10px;" />

      <form action='landungs_edit.php' method='post' onsubmit="return confirm('Eintrag wirklich löschen?');">
      <input type="hidden" name="zaehler_id" value="<?php echo $zaehler_id; ?>" />
      <input type="hidden" name="flieger_id" value="<?php echo $flieger_id; ?>" />
        <div class="center">
          <p><input class="sub_loeschen" type='submit' name='loeschen' value='EINTRAG LÖSCHEN' /></p>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
