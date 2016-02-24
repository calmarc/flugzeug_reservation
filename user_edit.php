<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('login/includes/db_connect.php');
include_once ('login/includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }

  // check if admin rights
  $query = "SELECT `admin` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  if ($obj->admin == FALSE)
    header("Location: /reservationen/index.php");

  if (isset($_POST['loeschen']))
  {
    $pilotid = $_POST['pilotid'];
    $query="DELETE FROM `calmarws_test`.`members` WHERE `members`.`pilotid` = $pilotid;";

    $mysqli->query($query); 
    // man hat sich selber geloesch.. delete $_SESSION (ausloggen)
    if (intval($_SESSION['pilotid']) ==  intval($pilotid)) {
      header("Location: /reservationen/login/includes/logout.php");
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
    $email = ""; if (isset($_POST['email'])) $email = trim($_POST['email']);
    $natel = ""; if (isset($_POST['natel'])) $natel = trim($_POST['natel']);
    $telefon = ""; if (isset($_POST['telefon'])) $telefon = trim($_POST['telefon']);
    $password = ""; if (isset($_POST['password'])) $password = trim($_POST['password']);
    $admin = ""; if (isset($_POST['admin'])) $admin = $_POST['admin'];
    if ($admin == "ja")
      $admin_nr = 1;
    else
      $admin_nr = 0;

    $passquery = "";
    // empty password hash
    if ($password != "")
    {

      $query= "SELECT `salt` FROM `members` WHERE `id` = $id;";
      $res = $mysqli->query($query); 
      $obj = $res->fetch_object();

      $password = hash('sha512', $password);
      $password = hash('sha512', $password . $obj->salt);

      $passquery = "`password` = ?, ";
    }


    // UPATE USER DATA
    if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`members` SET $passquery `pilotid` = ?, `email` = ?, `admin` = ?, `name` = ?, `telefon` = ?, `natel` = ? WHERE `members`.`id` = ?; ")) {

      if ($passquery == "")
        $stmt->bind_param('isisssi', $pilotid, $email, $admin_nr, $name, $telefon, $natel, $id);
      else
       $stmt->bind_param('sisisssi', $password, $pilotid, $email, $adminnr, $name, $telefon, $natel, $id);

      // Execute the prepared query.
      if (!$stmt->execute()) {
          header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
          exit;
      }
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
    <title>Benutzer Editieren - Administration</title>
    <meta name="title" content="Benutzer Administration">
    <meta name="keywords" content="Benutzer,Administration">
    <meta name="description" content="Benutzer Administration">
    <meta name="generator" content="Calmar + Vim + Tidy">
    <meta name="owner" content="calmar.ws">
    <meta name="author" content="candrian.org">
    <meta name="robots" content="all">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/reservationen/reservationen.css">
    <script type="text/JavaScript" src="js/sha512.js"></script> 
    <script type="text/JavaScript" src="js/forms.js"></script> 
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
        $id = $_GET['id'];
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
    
  echo "<form action='user_edit.php' method='post'>";
  echo "<input type='hidden' name='id' value='".$obj->id."' />";
  echo "<div class='center'>";
  echo "<table class='formular_eingabe'>";
  echo "\n<tr><td><b>Pilot-ID:</b></td><td><input type='text' name='pilotid' value='".str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT)."'></td></tr>";
  echo "\n<tr><td><b>Namen:</b></td><td><input type='text' name='name' value='".$obj->name."'></td></tr>";
  echo "\n<tr><td><b>Email:</b></td><td><input type='email' name='email' value='".$obj->email."'></td></tr>";
  echo "\n<tr><td><b>Natel:</b></td><td><input type='text' name='natel' value='".$obj->natel."'></td></tr>";
  echo "\n<tr><td><b>Telefon:</b></td><td><input type='text' name='telefon' value='".$obj->telefon."'></td></tr>";
  echo "\n<tr><td><b>Admin:</b></td><td><select size='1' name='admin'>";

  if ($admin_txt == "nein"){
    echo "<option selected='selected'>nein</option>";
    echo "<option>ja</option>";
  }
  else {
    echo "<option>nein</option>";
    echo "<option selected='selected'>ja</option>";
  }
  echo "</select></td></tr>";
  echo "\n<tr><td><b>Passwort</b></td><td><input placeholder='******' type='text' name='password' value=''></td></tr>";

  echo "</table>";
  echo "<input class='submit_button' type='submit' name='submit' value='Aenderungen abschicken' />";
  echo "</div>";
  echo "</form>";

  ?>

  <hr style="margin: 8% 10px 16% 10px;" />

  <form action='user_edit.php' method='post' onsubmit="return confirm('WirklichID [<?php echo $obj->pilotid; ?>] loeschen?');">
  <input type="hidden" name="pilotid" value="<?php echo $obj->pilotid; ?>" />
  <div class="center">
  <p><span style="font-size: 120%; color: red;">Benutzer &bdquo;<b><?php echo $obj->name; ?></b>&rdquo; mit Piloten-ID <b>[<?php echo str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT); ?>] </b></span></p>
  <p><input style='background-color: #ffcccc; margin: 10px;' type='submit' name='loeschen' value='LÖSCHEN' /></p>
  </div>
  </form>

</div>
</main>
</body>
</html>
