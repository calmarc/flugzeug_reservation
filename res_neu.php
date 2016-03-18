<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

// TODO ? muss zurich sein..
$curstamp = time(); // wird einige male gebraucht

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

// braucht man auch ganz unten
$user_id = $_SESSION['user_id'];

include_once('res_neu.inc.php');

print_html_to_body('Flugzeug reservieren', '');
include_once('includes/usermenu.php'); ?>

<main>
  <div id="formular_innen">

  <h1 class="hide_on_print">Flugzeug reservieren</h1>

<?php
if (isset($msg) && $msg != "")
{
  echo "$msg</div></main></body></html>";
  exit;
}

if (isset($error_msg) && $error_msg != "")
  echo "<p><b style='color: red;'>$error_msg</b></p>";

if (isset($flieger_id))
{
  $query = "SELECT * FROM `flieger` WHERE `id` = '{$flieger_id}' LIMIT 1;";
  $res = $mysqli->query($query);
  $obj = $res->fetch_object();
  $fliegertxt = $obj->flieger;
  $hidden = "<input type='hidden' name='flieger_id' value="{$flieger_id}" />";
}
else
{
  $flieger_id = "";
  $query = "SELECT * FROM `flieger`;";
  $res = $mysqli->query($query);
  $fliegertxt = "";
  while($obj = $res->fetch_object())
     $fliegertxt .= "<option value='{$obj->id}'>{$obj->flieger} ({$obj->id})</option>";
   $fliegertxt = "<select size='1' name='flieger_id'>{$fliegertxt}<select>";
  $hidden = "";
}

$von_stunde = $_SESSION['von_stunde'];
if ($von_stunde == "")
  $von_stunde = "7";

$von_minute = $_SESSION['von_minuten'];
if ($von_minute == "")
  $von_minute = "0";

$bis_stunde = $_SESSION['bis_stunde'];
if ($bis_stunde == "")
  $bis_stunde = "7";

$bis_minute = $_SESSION['bis_minuten'];
if ($bis_minute == "")
  $bis_minute = "0";

?>
  <form action='res_neu.php' method='post'>
<?php echo $hidden; ?>
    <div class='center hide_on_print'>
      <table class='vtable'>
        <tr class="trblank">
          <td><b>Pilot:</b></td>
          <td><b>[<?php echo str_pad($_SESSION['pilot_id'], 3, "0", STR_PAD_LEFT).'] '.$_SESSION['name']; ?></b></td>
        </tr>
        <tr class="trblank">
          <td><b>Flugzeug:</b></td>
          <td><b><?php echo $fliegertxt; ?></b></td>
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
    <br />
    <div class='center'>
      <h1 class="hide_on_print">Reservationen <a href="javascript:window.print()"><img alt="Ausdrucken" src="/reservationen/bilder/print-out.png" /></a></h1>
      <h1 class="only_on_print">Kommende MFGC Reservationen<br />von: <?php echo "[".str_pad($_SESSION['pilot_id'], 3, "0", STR_PAD_LEFT)."] ".$_SESSION['name']; ?><br />&nbsp; &nbsp;</h1>
    </div>

    <div class='center'>
    <table class='vertical_table'>
  <?php

// jetzt Zeit
date_default_timezone_set("Europe/Zurich");
$date = date("Y-m-d H:i:s", time());
date_default_timezone_set('UTC');

remove_zombies($mysqli);

// get all valid reservation
// later: see if there is an entry.. if not.. yellow (standby)

$res = $mysqli->query("SELECT `id` FROM `flieger`;");

$valid_res = array(array(), array(), array(), array(), array());

$x = 0;
while ($obj = $res->fetch_object())
{
  $valid_res[$x] = get_valid_reserv($mysqli, $obj->id);
  $x++;
}

$query = "SELECT `reservationen`.`id`, `reservationen`.`von`, `reservationen`.`bis`, `flieger`.`flieger`, `reservationen`.`flieger_id` FROM `reservationen` LEFT OUTER JOIN `flieger` ON `flieger`.`id` = `reservationen`.`flieger_id` WHERE `user_id` = {$user_id} AND `bis` >= '{$date}' ORDER BY `von` DESC;";
$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  $yellow = '';
  if ( ! in_array(strval($obj->id), $valid_res[$obj->flieger_id - 1]))
    $yellow = 'style="background-color: #ffff99; color: #ff6600 !important;"';

  $datum = mysql2chtimef($obj->von, $obj->bis, FALSE);
  list( $g_datum, $zeit) = explode(" ", $obj->von);
  list( $g_jahr, $g_monat, $g_tag) = explode("-", $g_datum);
  $g_jahr = intval($g_jahr);
  $g_monat = intval($g_monat);
  $g_tag = intval($g_tag);

  echo " <tr><td><a href='index.php?show=tag&amp;tag={$g_tag}&amp;monat={$g_monat}&amp;jahr={$g_jahr}'>[Tagesplan]</a></td>
          <td {$yellow}>{$datum}</td><td {$yellow}>{$obj->flieger}</td>
        </tr>";
}

$query = "SELECT `reservationen`.`id`, `reservationen`.`von`, `reservationen`.`bis`, `flieger`.`flieger`, `reservationen`.`flieger_id` FROM `reservationen` LEFT OUTER JOIN `flieger` ON `flieger`.`id` = `reservationen`.`flieger_id` WHERE `user_id` = {$user_id} AND `bis` < '{$date}' ORDER BY `von` DESC LIMIT 5;";
$res = $mysqli->query($query);

echo '<tr><td style="background-color: #99ff99;"></td><td style="background-color: #99ff99; text-align: left;" colspan="2">Vergangene:</td></tr>';
while ($obj = $res->fetch_object())
{
  $datum = mysql2chtimef($obj->von, $obj->bis, FALSE);
  echo " <tr><td></td>
          <td style='color: grey;'>{$datum}</td>
          <td style='color: grey;'>{$obj->flieger}</td>
        </tr>";
}

?>
      </table>
    </div>
  </div>
</main>
</body>
</html>
