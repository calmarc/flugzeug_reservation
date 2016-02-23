<?php

include_once ('login/includes/db_connect.php');
include_once ('login/includes/functions.php');

sec_session_start();

if (login_check($mysqli) == true):

  // check if admin rights
  $query = "SELECT `admin` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  if ($obj->admin == FALSE)
    header("Location: /reservationen/index.php");

  if (isset($_POST['loeschen']))
  {
    $id = $_POST['id'];
    $query="DELETE FROM `calmarws_test`.`members` WHERE `members`.`id` = $id;";
    $res = $mysqli->query($query); 
    header("Location: user_admin.php");
    exit;
  }

  if (isset($_POST['submit']))
  {
    $id = ""; if (isset($_POST['id'])) $id = $_POST['id'];
    $usernamen = ""; if (isset($_POST['usernamen'])) $usernamen = $_POST['usernamen'];
    $namen = ""; if (isset($_POST['namen'])) $namen = $_POST['namen'];
    $email = ""; if (isset($_POST['email'])) $email = $_POST['email'];
    $natel = ""; if (isset($_POST['natel'])) $natel = $_POST['natel'];
    $telefon = ""; if (isset($_POST['telefon'])) $telefon = $_POST['telefon'];
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

      $passquery = "`password` = '$password', ";
    }


    $query="UPDATE `calmarws_test`.`members` SET $passquery `username` = '$usernamen', `email` = '$email', `admin` = '$admin_nr', `name` = '$namen', `telefon` = '$telefon', `natel` = '$natel' WHERE `members`.`id` = $id; ";

    $res = $mysqli->query($query); 

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
    
  echo "<form action='user_edit.php' method='post'>";

  echo "<div class='center'>";
  echo "<table class='user_admin'>";

  $obj = $res->fetch_object();

  if ($obj->admin == 1)
    $admin_txt = "ja";
  else
    $admin_txt = "nein";
      
  echo "\n<tr><td><b>ID</b></td><td style='text-align: center; background-color: green; color: white;'><b>".str_pad($obj->id, 3, "0", STR_PAD_LEFT)."<input type='hidden' name='id' value='".$obj->id."'></b></td></tr>";
  echo "\n<tr><td><b>Nick</b></td><td><input type='text' name='usernamen' value='".$obj->username."'></td></tr>";
  echo "\n<tr><td><b>Namen</b></td><td><input type='text' name='namen' value='".$obj->name."'></td></tr>";
  echo "\n<tr><td><b>email</b></td><td><input type='text' name='email' value='".$obj->email."'></td></tr>";
  echo "\n<tr><td><b>Natel</b></td><td><input type='text' name='natel' value='".$obj->natel."'></td></tr>";
  echo "\n<tr><td><b>Telefon</b></td><td><input type='text' name='telefon' value='".$obj->telefon."'></td></tr>";
  echo "\n<tr><td><b>Passwort</b></td><td><input type='text' name='password' value=''></td></tr>";
  echo "\n<tr><td><b>Admin</b></td><td><select size='1' name='admin'>";

  if ($admin_txt == "nein"){
    echo "<option selected='selected'>nein</option>";
    echo "<option>ja</option>";
  }
  else {
    echo "<option>nein</option>";
    echo "<option selected='selected'>ja</option>";
  }
  echo "</select></td></tr>";

  echo "</table>";
  echo "<input class='submit_button' type='submit' name='submit' value='Aenderungen abschicken' />";
  echo "</div>";
  echo "</form>";

  ?>

  <hr style="margin: 8% 10px 16% 10px;" />

  <form action='user_edit.php' method='post' onsubmit="return confirm('WirklichID [<?php echo $obj->id; ?>] loeschen?');">
  <input type="hidden" name="id" value="<?php echo $obj->id; ?>" />
  <div class="center">
  <p><span style="font-size: 120%; color: red;">Benutzer &bdquo;<b><?php echo $obj->username; ?></b>&rdquo; mit ID <b>[<?php echo str_pad($obj->id, 3, "0", STR_PAD_LEFT); ?>] </b></span></p>
  <p><input style='background-color: #ffcccc; margin: 10px;' type='submit' name='loeschen' value='LÖSCHEN'  /></p>
  </div>
  </form>

</div>
</main>
</body>
</html>

<?php else :
header("Location: /reservationen/login/index.php");
exit;
endif; 
?>
