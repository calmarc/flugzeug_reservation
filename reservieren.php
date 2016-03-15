<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');
include_once ('includes/graphic.php');

sec_session_start();

// TODO ? muss zurich sein..
$curstamp = time(); // wird einige male gebraucht

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

// braucht man auch ganz unten
$userid = $_SESSION['user_id'];

if (isset($_POST['submit']))
{
  $flieger_id = ""; if (isset($_POST['flieger_id'])) $flieger_id = $_POST['flieger_id'];
  $von_tag = ""; if (isset($_POST['von_tag'])) $von_tag = $_POST['von_tag'];
  $von_monat = ""; if (isset($_POST['von_monat'])) $von_monat = $_POST['von_monat'];
  $von_jahr = ""; if (isset($_POST['von_jahr'])) $von_jahr = $_POST['von_jahr'];
  $von_stunde = ""; if (isset($_POST['von_stunde'])) $von_stunde = $_POST['von_stunde'];
  $von_minuten = ""; if (isset($_POST['von_minuten'])) $von_minuten = $_POST['von_minuten'];
  $bis_tag = ""; if (isset($_POST['bis_tag'])) $bis_tag = $_POST['bis_tag'];
  $bis_monat = ""; if (isset($_POST['bis_monat'])) $bis_monat = $_POST['bis_monat'];
  $bis_jahr = ""; if (isset($_POST['bis_jahr'])) $bis_jahr = $_POST['bis_jahr'];
  $bis_stunde = ""; if (isset($_POST['bis_stunde'])) $bis_stunde = $_POST['bis_stunde'];
  $bis_minuten = ""; if (isset($_POST['bis_minuten'])) $bis_minuten = $_POST['bis_minuten'];

  $_SESSION['flieger_id']  = $flieger_id;
  $_SESSION['von_tag']  = $von_tag;
  $_SESSION['von_monat']  = $von_monat;
  $_SESSION['von_jahr']  = $von_jahr;
  $_SESSION['von_stunde']  = $von_stunde;
  $_SESSION['von_minuten']  = $von_minuten;
  $_SESSION['bis_tag']  = $bis_tag;
  $_SESSION['bis_monat']  = $bis_monat;
  $_SESSION['bis_jahr']  = $bis_jahr;
  $_SESSION['bis_stunde']  = $bis_stunde;
  $_SESSION['bis_minuten']  = $bis_minuten;

  $von_tag = str_pad($von_tag, 2, "0", STR_PAD_LEFT);
  $von_monat = str_pad($von_monat, 2, "0", STR_PAD_LEFT);
  $von_stunde = str_pad($von_stunde, 2, "0", STR_PAD_LEFT);
  $von_minuten = str_pad($von_minuten, 2, "0", STR_PAD_LEFT);
  $bis_tag = str_pad($bis_tag, 2, "0", STR_PAD_LEFT);
  $bis_monat = str_pad($bis_monat, 2, "0", STR_PAD_LEFT);
  $bis_stunde = str_pad($bis_stunde, 2, "0", STR_PAD_LEFT);
  $bis_minuten = str_pad($bis_minuten, 2, "0", STR_PAD_LEFT);

  $von_date = "$von_jahr-$von_monat-$von_tag $von_stunde:$von_minuten";
  $bis_date = "$bis_jahr-$bis_monat-$bis_tag $bis_stunde:$bis_minuten";

  $vonstamp = strtotime($von_date);
  $bisstamp = strtotime($bis_date);

  // TODO: check values...
  $error_msg = "";
  if ($bisstamp <= $vonstamp)
    $error_msg .= "'Von' Zeit nicht grösser als 'bis' Zeit.<br />";

  if ($bis_stunde == "21" && $bis_minuten == "30")
    $error_msg .= "21:30 liegt ausserhalb der Grenzen. Es kann nur bis 21:00 reserviert werden.<br />";

  if ($vonstamp <= $curstamp)
    $error_msg .= "Die Reservierung liegt in der Vergangenheit.<br /><br />Es wurde keine Reservierung gebucht!<br />";

  if (intval($bis_stunde) == 7 && intval($bis_minuten) == 0)
    $error_msg .= "Auf 7:00 Uhr kann man nicht reservieren.<br />Bitte stattdessen auf den Vortag 21:00 Uhr buchen!<br />";

  // CHECK LEVEL of standby
  
  remove_zombies($mysqli);

  $level = check_level($mysqli, $flieger_id, $von_date, $bis_date) - 1;
  if ($level >= 3)
    $error_msg .= "Es hat bereits zuviele Standby's [$level] in diesem Zeitraum.<br /><br />Es wurde keine Reservierung gebucht!<br />";
   
  if ($error_msg == ""){

    $query = "INSERT INTO `calmarws_test`.`reservationen` 
      ( `id` , `timestamp` , `userid` , `fliegerid` , `von` , `bis`) VALUES 
      ( NULL , CURRENT_TIMESTAMP , '$userid', '$flieger_id', FROM_UNIXTIME($vonstamp), FROM_UNIXTIME($bisstamp));";

    $mysqli->query($query);

    if (isset($_SESSION['plan']) && $_SESSION['plan'] == 'monatsplan')
      header("Location: index.php?show=monatsplan&tag=$von_tag&monat=$von_monat&jahr=$von_jahr");
    else
      header("Location: index.php?tag=$von_tag&monat=$von_monat&jahr=$von_jahr");
  }
}
else if (isset($_GET['flieger_id']) && isset($_GET['tag']) && isset($_GET['monat']) && isset($_GET['jahr']))
{

  $_SESSION['von_stunde'] = ""; if (isset($_GET['stunde'])) $_SESSION['von_stunde'] = $_GET['stunde'];
  $_SESSION['von_minuten'] = ""; if (isset($_GET['minute'])) $_SESSION['von_minuten'] = $_GET['minute'];

  $_SESSION['flieger_id']  = $_GET['flieger_id'];
  $flieger_id = $_SESSION['flieger_id'];
  $_SESSION['von_tag']  = $_GET['tag'];
  $_SESSION['von_monat']  = $_GET['monat'];
  $_SESSION['von_jahr']  = $_GET['jahr'];

  $_SESSION['bis_tag']  = $_SESSION['von_tag'];
  $_SESSION['bis_monat']  = $_SESSION['von_monat'];
  $_SESSION['bis_jahr']  = $_SESSION['von_jahr'];
  $_SESSION['bis_stunde']  = $_SESSION['von_stunde'];
  $_SESSION['bis_minuten']  = $_SESSION['von_minuten'];
}
else
{
  header('Location: /reservationen/index.php');
  // else nothing to do so
}

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
  $query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  $fliegertxt = $obj->flieger;
  $hidden = '<input type="hidden" name="flieger_id" value="'.$flieger_id.'" />';
}
else
{
  $flieger_id = "";
  $query = "SELECT * FROM `flieger`;";
  $res = $mysqli->query($query); 
  $fliegertxt = "";
  while($obj = $res->fetch_object())
     $fliegertxt .= "<option value='".$obj->id."'>".$obj->flieger." (".$obj->id.")</option>";
   $fliegertxt = "<select size='1' name='flieger_id'>$fliegertxt<select>";
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
  <form action='reservieren.php' method='post'>
<?php echo $hidden; ?>
    <div class='center hide_on_print'>
      <table class='user_admin'>
        <tr class="trblank">
          <td><b>Pilot:</b></td>
          <td><b>[<?php echo str_pad($_SESSION['pilotid'], 3, "0", STR_PAD_LEFT).'] '.$_SESSION['name']; ?></b></td>
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
      <h1 class="only_on_print">Kommende MFGC Reservationen<br />von: <?php echo "[".str_pad($_SESSION['pilotid'], 3, "0", STR_PAD_LEFT)."] ".$_SESSION['name']; ?><br />&nbsp; &nbsp;</h1>
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

$query = "SELECT `reservationen`.`id`, `reservationen`.`von`, `reservationen`.`bis`, `flieger`.`flieger`, `reservationen`.`fliegerid` FROM `reservationen` LEFT OUTER JOIN `flieger` ON `flieger`.`id` = `reservationen`.`fliegerid` WHERE `userid` = $userid AND `bis` >= '$date' ORDER BY `von` DESC;";
$res = $mysqli->query($query); 

while ($obj = $res->fetch_object())
{
  $yellow = '';
  if ( ! in_array(strval($obj->id), $valid_res[$obj->fliegerid - 1]))
    $yellow = 'style="background-color: #ffff99; color: #ff6600 !important;"';

  $datum = mysql2chtimef($obj->von, $obj->bis, FALSE);
  list( $g_datum, $zeit) = explode(" ", $obj->von);
  list( $g_jahr, $g_monat, $g_tag) = explode("-", $g_datum);
  $g_jahr = intval($g_jahr);
  $g_monat = intval($g_monat);
  $g_tag = intval($g_tag);

  echo ' <tr><td><a href="index.php?show=tag&amp;tag='.$g_tag.'&amp;monat='.$g_monat.'&amp;jahr='.$g_jahr.'">[Tagansicht]</a></td>
          <td '.$yellow.'>'.$datum.'</td><td '.$yellow.'>'.$obj->flieger.'</td>
        </tr>';
}

$query = "SELECT `reservationen`.`id`, `reservationen`.`von`, `reservationen`.`bis`, `flieger`.`flieger`, `reservationen`.`fliegerid` FROM `reservationen` LEFT OUTER JOIN `flieger` ON `flieger`.`id` = `reservationen`.`fliegerid` WHERE `userid` = $userid AND `bis` < '$date' ORDER BY `von` DESC LIMIT 5;";
$res = $mysqli->query($query); 

echo '<tr><td style="background-color: #99ff99;"></td><td style="background-color: #99ff99; text-align: left;" colspan="2">Vergangene:</td></tr>';
while ($obj = $res->fetch_object())
{
  $datum = mysql2chtimef($obj->von, $obj->bis, FALSE);
  echo ' <tr><td></td>
          <td style="color: grey;">'.$datum.'</td>
          <td style="color: grey;">'.$obj->flieger.'</td>
        </tr>';
}

?>
      </table>
    </div>
  </div>
</main>
</body>
</html>
