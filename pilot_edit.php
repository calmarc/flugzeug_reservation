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
// Berechtigungen checken

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

//============================================================================
// loeschen form wurde gedrueckt

include_once ('pilot_edit.inc.php');

//============================================================================
// HTML

print_html_to_body('Piloten editieren - Administration', '');
include_once('includes/usermenu.php');
?>
  <main>
    <h1>Piloten editieren</h1>
    <div id="formular_innen">

<?php
if (isset($_GET['id']))
{
  $user_id = $_GET['id'];
}
else
{
  echo "<h3>Keine gültgie ID erhalten. Bitte <a href='pilot_admin.php'>wiederhohlen</a> oder an mac@calmar.ws melden</h3>";
  exit;
}

$query = "SELECT * FROM `piloten` WHERE `piloten`.`id` = '{$user_id}'";

$res = $mysqli->query($query);
$obj = $res->fetch_object();

if ($obj->admin == 1)
  $admin_txt = "ja";
else
  $admin_txt = "nein";

if ($obj->fluglehrer == 1)
  $fluglehrer_txt = "ja";
else
  $fluglehrer_txt = "nein";

if ($obj->gesperrt == 1)
  $gesperrt = "ja";
else
  $gesperrt = "nein";

echo "
<form action='pilot_edit.php' method='post'>
  <input type='hidden' name='user_id' value='{$obj->id}' />
    <div class='center'>
    <table class='vtable'>
      <tr>
        <td><b>Pilot-Nr:</b></td><td><input type='text' name='pilot_nr' value='".str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT)."'></td></tr>
      <tr>
        <td><b>Name:</b></td><td><input type='text' name='name' value='{$obj->name}'></td>
      </tr>
      <tr>
        <td><b>Natel:</b></td><td><input pattern='\+{0,1}[0-9 ]+' type='text' name='natel' value='{$obj->natel}'></td>
      </tr>
      <tr>
        <td><b>Telefon:</b></td><td><input pattern='\+{0,1}[0-9 ]+' type='text' name='telefon' value='{$obj->telefon}'></td>
      </tr>
      <tr>
        <td><b>Email:</b></td><td><input type='email' name='email' value='{$obj->email}'></td>
      </tr>
      <tr>
        <td><b>Admin:</b></td><td><select style='width: 4em;' size='1' name='admin'>";

if ($admin_txt == "nein")
{
  echo "<option selected='selected'>nein</option>";
  echo "<option>ja</option>";
}
else
{
  echo "<option>nein</option>";
  echo "<option selected='selected'>ja</option>";
}

echo "  </select>
        </td>
      </tr>
      <tr>
        <td><b>Fluglehrer:</b></td><td><select style='width: 4em;' size='1' name='fluglehrer'>";

if ($fluglehrer_txt == "nein")
{
  echo "<option selected='selected'>nein</option>";
  echo "<option>ja</option>";
}
else
{
  echo "<option>nein</option>";
  echo "<option selected='selected'>ja</option>";
}

$checkflug_ch = shortsql2ch_date ($obj->checkflug);

echo "  </select>
        </td>
      </tr>
      <tr>
        <td><b>Checkflug:</b></td><td><input pattern='[0-3]?[0-9]\.[0-1]?[0-9]\.20[1-9][0-9]' type='text' name='checkflug' value='{$checkflug_ch}'></td>
      </tr>
      <tr>
        <td><b>Gesperrt:</b></td><td><select style='width: 4em;' size='1' name='gesperrt'>";

if ($gesperrt == "nein")
{
  echo "<option selected='selected'>nein</option>";
  echo "<option>ja</option>";
}
else
{
  echo "<option>nein</option>";
  echo "<option selected='selected'>ja</option>";
}

echo "  </select>
        </td>
      </tr>
      <tr>
        <td><b>Passwort</b></td><td><input placeholder='****' type='text' name='password' value=''></td>
      </tr>
    </table>
    <input class='submit_button' type='submit' name='updaten' value='Aenderungen abschicken' />
  </div>
</form>";

//============================================================================
// pilot loeschen button unten

$pilot_nr_pad = str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT);

?>

      <hr style="margin: 52px 10px 84px 10px;" />

      <form action='pilot_edit.php' method='post' onsubmit="return confirm('Wirklich Pilot-Nr <?php echo "[{$pilot_nr_pad}] {$obj->name}"; ?> löschen?\nAlle verbundenen Reservierungen\nwerden ebenfalls gelöscht!');">
      <input type="hidden" name="user_id" value="<?php echo $obj->id; ?>" />
        <div class="center">
          <p><b>Pilot: <?php echo "[{$pilot_nr_pad}] {$obj->name}"; ?></b></p>
          <p><input class="sub_loeschen" type='submit' name='loeschen' value='LÖSCHEN' /></p>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
