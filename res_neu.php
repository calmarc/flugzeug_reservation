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
  </form>
  </div>
</main>
</body>
</html>
