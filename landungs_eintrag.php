<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

// von der uebersicht
if (isset($_GET['flieger_id']) && $_GET['flieger_id'] > 0)
{
  $flieger_id = $_GET['flieger_id'];

  $query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
  $res = $mysqli->query($query); 

  if ($res->num_rows != 1)
  {
    header('Location: /reservationen/index.php');
    exit;
  }
  date_default_timezone_set("Europe/Zurich");
  $_SESSION['tag'] = date('d', time());
  $_SESSION['monat'] = date('m', time());
  $_SESSION['jahr'] = date('Y', time());
  date_default_timezone_set('UTC');
}
else if (isset($_POST['submit']))
{
  $flieger_id = ""; if (isset($_POST['flieger_id'])) $flieger_id = $_POST['flieger_id'];
  $tag = ""; if (isset($_POST['tag'])) $tag = $_POST['tag'];
  $monat = ""; if (isset($_POST['monat'])) $monat = $_POST['monat'];
  $jahr = ""; if (isset($_POST['jahr'])) $jahr = $_POST['jahr'];
  $zaehlerstand = ""; if (isset($_POST['zaehlerstand'])) $zaehlerstand = $_POST['zaehlerstand'];

  $zaehler_minute = intval($zaehlerstand) * 60;
  $zaehler_minute += round($zaehlerstand * 100, 0, PHP_ROUND_HALF_UP) % 100;

  $_SESSION['flieger_id']  = $flieger_id;
  $_SESSION['tag']  = $tag;
  $_SESSION['monat']  = $monat;
  $_SESSION['jahr']  = $jahr;

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);

  $date = "$jahr-$monat-$tag";

  $error_msg = "";

  $z_max = -1;

  // geht nicht.. man muss auch zwischendurch etc.
  //$query = "SELECT MAX(`zaehler_minute`) AS 'zaehler_max' FROM `zaehle_reintraege` WHERE `flieger_id` = '$flieger_id';";
  //$res = $mysqli->query($query); 

  //if ($res->num_rows > 0)
  //{
    //$obj = $res->fetch_object();
    //$z_max = intval($obj->zaehler_max);
  //}
  //if ($z_max >= $zaehler_minute)
  //{
    //$error_msg = "Der Zählerstand ($zaehlerstand) ist nicht grösser als zuvor.<br /><br />Es wurde kein Eintrag gemacht!<br />";
  //}
   
  if ($error_msg == "")
  {
    if ($stmt = $mysqli->prepare("INSERT INTO `calmarws_test`.`zaehler_eintraege` (
        `id` ,
        `user_id` ,
        `flieger_id` ,
        `datum` ,
        `zaehler_minute`
        )
        VALUES (
        NULL , ?, ?, ?, ?
        )"))
    {
      $stmt->bind_param('iisi', $_SESSION['user_id'], $flieger_id, $date, $zaehler_minute);
      if (!$stmt->execute()) 
      {
          header('Location: /reservationen/login/error.php?err=Registration failure: INSERT');
          exit;
      }
    }
  }
}
else
{
  header('Location: /reservationen/index.php');
  exit;
}

print_html_to_body('Landungs Eintrag', ''); 
include_once('includes/usermenu.php'); 

?>
<main>
  <div id="formular_innen">

  <h1>Flug eintragen</h1>

<?php
if (isset($msg) && $msg != "")
{
  echo "$msg</div></main></body></html>";
  exit;
}

if (isset($error_msg) && $error_msg != "")
  echo "<p><b style='color: red;'>$error_msg</b></p>";

$query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
$res = $mysqli->query($query); 
$obj = $res->fetch_object();
$fliegertxt = $obj->flieger;
$hidden = '<input type="hidden" name="flieger_id" value="'.$flieger_id.'" />';
  
?>
  <form action='landungs_eintrag.php' method='post'>
<?php echo $hidden; ?>
    <div class='center'>
      <table class='vtable'>
        <tr class="trblank">
          <td><b>Pilot:</b></td>
          <td><b>[<?php echo str_pad($_SESSION['pilotid'], 3, "0", STR_PAD_LEFT).'] '.$_SESSION['name']; ?></b></td>
        </tr>
        <tr class="trblank">
          <td><b>Flugzeug:</b></td>
          <td><b><?php echo $fliegertxt; ?></b></td>
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
          <td><input name="zaehlerstand" style="width: 80px;" required="required" type="number" step="0.01" /></td>
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
  </tr>
  <?php

$query = "SELECT `zaehler_eintraege`.`id`,
                 `zaehler_eintraege`.`user_id`,
                 `piloten`.`name`,
                 `zaehler_eintraege`.`zaehler_minute`,
                 `zaehler_eintraege`.`datum`
         FROM `zaehler_eintraege` INNER JOIN `piloten` ON `piloten`.`id` = `zaehler_eintraege`.`user_id` 
         WHERE `flieger_id` = '".$flieger_id."'  ORDER BY `zaehler_minute` DESC LIMIT 50;";

if ($res = $mysqli->query($query))
{
  if ($res->num_rows > 0)
  {
    $flag = TRUE;
    $obj = $res->fetch_object();
    $edit_c = 0;
    while ($flag)
    {

      list ($jahr, $monat, $tag) = preg_split('/[- ]/', $obj->datum);

      $name = $obj->name;
      $zaehler_min = $obj->zaehler_minute;
      $eintrags_id = $obj->id;
      $user_id = $obj->user_id;

      if ($obj = $res->fetch_object())
          list($zaehlerstand, $dauer) = zaehler_into($zaehler_min, $obj->zaehler_minute);
      else
      {
          list($zaehlerstand, $dauer) = zaehler_into($zaehler_min, 0);
          $flag = FALSE;
      }

      $edit_link = "";	
      // admin + die letzten 2 zum ediditerne fuer benutzer
      if (check_admin($mysqli) || ($_SESSION['user_id'] == $user_id && $edit_c < 2))
      {
        $edit_link = '<a href="landungs_edit.php?action=edit&amp;zaehler_id='.$eintrags_id.'&amp;flieger_id='.$flieger_id.'"><img alt="edit" src="bilder/edit.png" /></a>';
        $edit_c++;
      }

      echo ' <tr>
              <td>'.$edit_link.'</td>
              <td>'.$tag.'.'.$monat.'.'.$jahr.'</td><td style="text-align: right;">'.$zaehlerstand.'</td><td style="text-align: right;">'.$dauer.'</td><td>'.$name.'</td>
            </tr>';
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
