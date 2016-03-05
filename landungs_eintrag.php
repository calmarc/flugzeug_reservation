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
  $_SESSION['tag'] = date('d', time());
  $_SESSION['monat'] = date('m', time());
  $_SESSION['jahr'] = date('Y', time());
}
else if (isset($_POST['submit']))
{
  $flieger_id = ""; if (isset($_POST['flieger_id'])) $flieger_id = $_POST['flieger_id'];
  $tag = ""; if (isset($_POST['tag'])) $tag = $_POST['tag'];
  $monat = ""; if (isset($_POST['monat'])) $monat = $_POST['monat'];
  $jahr = ""; if (isset($_POST['jahr'])) $jahr = $_POST['jahr'];
  $zaehlerstand = ""; if (isset($_POST['zaehlerstand'])) $zaehlerstand = $_POST['zaehlerstand'];

  $zaehler_minute = intval($zaehlerstand) * 60 + (($zaehlerstand * 100) % 100);

  $_SESSION['flieger_id']  = $flieger_id;
  $_SESSION['tag']  = $tag;
  $_SESSION['monat']  = $monat;
  $_SESSION['jahr']  = $jahr;

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);

  $date = "$jahr-$monat-$tag";

  $error_msg = "";

  $z_max = -1;

  $query = "SELECT MAX(`zaehler_minute`) AS 'zaehler_max' FROM `zaehlereintraege` WHERE `flieger_id` = '$flieger_id';";
  $res = $mysqli->query($query); 

  if ($res->num_rows > 0)
  {
    $obj = $res->fetch_object();
    $z_max = intval($obj->zaehler_max);
  }
  if ($z_max >= $zaehler_minute)
  {
    $error_msg = "Der Zählerstand ($zaehlerstand) ist nicht grösser als zuvor.<br /><br />Es wurde kein Eintrag gemacht!<br />";
  }
   
  if ($error_msg == "")
  {
    if ($stmt = $mysqli->prepare("INSERT INTO `calmarws_test`.`zaehlereintraege` (
        `id` ,
        `user_id` ,
        `flieger_id` ,
        `datum` ,
        `zaehler_minute` ,
        `beanstandungen`
        )
        VALUES (
        NULL , ?, ?, ?, ?, 'nil'
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


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Landungs Eintrag</title>
  <meta name="title" content="Benutzer Administration">
  <meta name="keywords" content="Benutzer,Administration">
  <meta name="description" content="Benutzer Administration">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" type="text/css" href="/reservationen/css/reservationen.css">
  <link rel="stylesheet" type="text/css" href="datetime/jquery.datetimepicker.css"/>

</head>
<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<body>

<?php include_once('includes/usermenu.php'); ?>
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
      <table class='user_admin two_standard'>
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
          <td><input value="<?php echo $_SESSION['tag']; ?>" name="tag" style="width: 46px;;" min="1" max="31" required="required" type='number' /> <b>.</b> 
          <input value="<?php echo $_SESSION['monat'] ?>" name="monat" style="width: 46px;;" min="1" max="12" required="required" type='number' /> <b>.</b> 
          <input value="<?php echo $_SESSION['jahr'] ?>" name="jahr" style="width: 80px;" min="2016" max="2050" required="required" type='number' /></td>
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
    <table class='user_admin'>
    <tr>
      <th></th>
      <th>Datum</th>
      <th>Zählerstand</th>
      <th>Dauer</th>
      <th>Beanstandungen</th>
      <th>Pilot</th>
  </tr>
  <?php

$query = "SELECT `zaehlereintraege`.`id`,
                 `zaehlereintraege`.`beanstandungen`,
                 `zaehlereintraege`.`beanstandungen`,
                 `zaehlereintraege`.`user_id`,
                 `members`.`name`,
                 `zaehlereintraege`.`zaehler_minute`,
                 `zaehlereintraege`.`datum`
         FROM `zaehlereintraege` INNER JOIN `members` ON `members`.`id` = `zaehlereintraege`.`user_id` 
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
      $beanstandungen = $obj->beanstandungen;
      if ($beanstandungen != "nil")
        $beanstandungen = '<span style="color: red; font-weight: bold;">'.$beanstandungen."<span>";

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
        $edit_link = '<a href="landungs_edit.php?action=edit&amp;zaehler_id='.$eintrags_id.'&amp;flieger_id='.$flieger_id.'">[edit]</a>';
        $edit_c++;
      }

      echo ' <tr>
              <td>'.$edit_link.'</td>
              <td>'.$tag.'.'.$monat.'.'.$jahr.'</td><td>'.$zaehlerstand.'</td><td>'.$dauer.'</td><td>'.$beanstandungen.'</td><td>'.$name.'</td>
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
