<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

if (isset($_POST['loeschen']))
{
  $pilotid = $_POST['pilotid'];
  if ($stmt = $mysqli->prepare("DELETE FROM `calmarws_test`.`members` WHERE `members`.`pilotid` = ?;"))
  {
    $stmt->bind_param('i', $pilotid);
    if (!$stmt->execute()) 
    {
        header('Location: /reservationen/login/error.php?err=Registration failure: DELETE');
        exit;
    }
  }

  // man hat sich selber geloesch.. delete $_SESSION (ausloggen)
  if (intval($_SESSION['pilotid']) ==  intval($pilotid)) {
    header("Location: /reservationen/login/logout.php");
    exit;
  }

  header("Location: user_admin.php");
  exit;
}

if (isset($_POST['submit']))
{
  $id = ""; if (isset($_POST['id'])) $id = intval($_POST['id']);
  $pilotid = ""; if (isset($_POST['pilotid'])) $pilotid = intval($_POST['pilotid']);
  $name = ""; if (isset($_POST['name'])) $name = trim($_POST['name']);
  $natel = ""; if (isset($_POST['natel'])) $natel = trim($_POST['natel']);
  $telefon = ""; if (isset($_POST['telefon'])) $telefon = trim($_POST['telefon']);
  $password = ""; if (isset($_POST['password'])) $password = trim($_POST['password']);
  $admin = ""; if (isset($_POST['admin'])) $admin = $_POST['admin'];
  $email = ""; if (isset($_POST['email'])) $email = trim($_POST['email']);
  $checkflug = ""; if (isset($_POST['checkflug'])) $checkflug = trim($_POST['checkflug']);
  $gesperrt = ""; if (isset($_POST['gesperrt'])) $gesperrt = trim($_POST['gesperrt']);



  if ($admin == "ja")
    $admin_nr = 1;
  else
    $admin_nr = 0;

  if ($checkflug != "")
  {
    list ($t, $m, $y) =  explode(".", $checkflug);
    $t = str_pad($t, 2, "0", STR_PAD_LEFT);
    $m = str_pad($m, 2, "0", STR_PAD_LEFT);
    $checkflug = "$y-$m-$t";
  }
  else
    $checkflug = "0000-00-00";

  // diese ID berichtigen.. (koennte ja sein dass noetig)
  $res = $mysqli->query("SELECT * FROM `members` WHERE `members`.`id` = $id LIMIT 1;");
  $obj = $res->fetch_object();

  date_default_timezone_set("Europe/Zurich");
  $date_t = date("Y-m-d", time(); 
  date_default_timezone_set('UTC');

  if ($obj->email_gesch == TRUE && ($checkflug > $date_t) || $checkflug == "0000-00-00")) // alles io wieder
  {
      if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`members` SET `email_gesch` = '0' WHERE `members`.`id` = ?;")) 
      {
        $stmt->bind_param('i', $id);
        if (!$stmt->execute()) 
        {
           header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
           exit;
        }
     }
  }

  if ($gesperrt == "ja")
    $gesperrt_bol = TRUE;
  else
    $gesperrt_bol = FALSE;

  $passquery = "";
  if ($password != "")
  {
    $query= "SELECT `salt` FROM `members` WHERE `id` = $id LIMIT 1;";
    $res = $mysqli->query($query); 
    $obj = $res->fetch_object();

    $password = hash('sha512', $password);
    $password = hash('sha512', $password . $obj->salt);

    $passquery = "`password` = ?, ";
  }

  // UPDATE USER DATA
  if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`members` SET $passquery `pilotid` = ?, `email` = ?, `admin` = ?, `name` = ?, `telefon` = ?, `natel` = ?, `checkflug` = ?, `gesperrt` = ? WHERE `members`.`id` = ?; ")) {

    if ($passquery == "")
      $stmt->bind_param('isissssii', $pilotid, $email, $admin_nr, $name, $telefon, $natel, $checkflug, $gesperrt_bol, $id);
    else
     $stmt->bind_param('sisissssii', $password, $pilotid, $email, $adminnr, $name, $telefon, $natel, $checkflug, $gesperrt_bol, $id);

    // Execute the prepared query.
    if (!$stmt->execute()) {
        header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
        exit;
    }
  }
  else
  {
      header('Location: /reservationen/login/error.php?err=Registration failure: prepare:'.mysqli_error($mysqli));
      exit;
  }
  header("Location: user_admin.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Benutzer editieren - Administration</title>
  <meta name="title" content="Benutzer editieren">
  <meta name="keywords" content="Benutzer editieren">
  <meta name="description" content="Benutzer editieren">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/css/reservationen.css">
</head>

<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<body>
<?php include_once('includes/usermenu.php'); ?>

  <main>
    <div id="formular_innen">

    <h1>Benutzer editieren</h1>

<?php
if (isset($_GET['id']))
{
  $id = $_GET['id'];
}
else {
  echo "<h3>Keine gültgie ID erhalten. Bitte <a href='user_admin.php'>wiederhohlen</a> oder an mac@calmar.ws melden</h3>";
  exit;
}

$query = "SELECT * FROM `members` WHERE `members`.`id` = '$id'";

$res = $mysqli->query($query); 
$obj = $res->fetch_object();

if ($obj->admin == 1)
  $admin_txt = "ja";
else
  $admin_txt = "nein";
  
if ($obj->gesperrt == 1)
  $gesperrt = "ja";
else
  $gesperrt = "nein";

echo "
<form action='user_edit.php' method='post'>
  <input type='hidden' name='id' value='".$obj->id."' />
    <div class='center'>
    <table class='user_admin'>
      <tr>
        <td><b>Pilot-ID:</b></td><td><input type='text' name='pilotid' value='".str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT)."'></td></tr>
      <tr>
        <td><b>Name:</b></td><td><input type='text' name='name' value='".$obj->name."'></td>
      </tr>
      <tr>
        <td><b>Natel:</b></td><td><input pattern='\+{0,1}[0-9 ]+' type='text' name='natel' value='".$obj->natel."'></td>
      </tr>
      <tr>
        <td><b>Telefon:</b></td><td><input type='text' name='telefon' value='".$obj->telefon."'></td>
      </tr>
      <tr>
        <td><b>Email:</b></td><td><input type='email' name='email' value='".$obj->email."'></td>
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

$checkflug_ch = shortsql2ch_date ($obj->checkflug);

echo "  </select>
        </td>
      </tr>
      <tr>
        <td><b>Checkflug:</b></td><td><input pattern='[0-3]?[0-9]\.[0-1]?[0-9]\.20[1-9][0-9]' type='text' name='checkflug' value='".$checkflug_ch."'></td>
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
    <input class='submit_button' type='submit' name='submit' value='Aenderungen abschicken' />
  </div>
</form>";
?>

      <hr style="margin: 52px 10px 84px 10px;" />

      <form action='user_edit.php' method='post' onsubmit="return confirm('Wirklich Pilot-ID [<?php echo $obj->pilotid; ?>] löschen?');">
      <input type="hidden" name="pilotid" value="<?php echo $obj->pilotid; ?>" />
        <div class="center">
          <p>Benutzer &bdquo;<b><?php echo $obj->name; ?></b>&rdquo; mit Piloten-ID <b>[<?php echo str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT); ?>]</b></p>
          <p><input class="sub_loeschen" type='submit' name='loeschen' value='LÖSCHEN' /></p>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
