<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('login/includes/db_connect.php');
include_once ('login/includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE){ header("Location: /reservationen/login/index.php"); exit; }

// eigentlich ist die pilotid in der Session
$id = $_SESSION['user_id'];
$query= "SELECT `pilotid` FROM `members` WHERE `id` = $id;";
$res = $mysqli->query($query); 
$obj = $res->fetch_object();
$pilotid = $obj->pilotid; 

if (isset($_POST['submit']))
{
  $password = ""; if (isset($_POST['password'])) $password = trim($_POST['password']);
  $changepwd = ""; if (isset($_POST['changepwd'])) $changepwd = trim($_POST['changepwd']);

  $error_msg = "";

  if ($password == "")
    $error_msg .= "<p>Bitte ein Passwort eingeben</p>";

  if (strlen($password) < 4)
    $error_msg .= "<p>Muss mind. 4 Zeichen lang sein</p>";

  if ($changepwd == "")
    $error_msg .= "<p>Bitte das Passwort bestätigen</p>";
  else if ($password != $changepwd)
    $error_msg .= "<p>Passwörter stimmen nicht überrein</p>";
   
  // OK, eintragen
  if ($error_msg == ""){

    $msg = "";
    $query= "SELECT `salt` FROM `members` WHERE `id` = $id;";
    $res = $mysqli->query($query); 
    $obj = $res->fetch_object();

    $password = hash('sha512', $password);
    $password = hash('sha512', $password . $obj->salt);

    if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`members` SET `password` = ? WHERE `members`.`id` = ? ;"))
    {
      $stmt->bind_param('si', $password, $id);
      // Execute the prepared query.
      if (!$stmt->execute()) 
      {
          header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
          exit;
      }
      $msg = "Das Passwort wurde geändert";
    }
  }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Passwort ändern - Administration</title>
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
   <h1>Passwort ändern</h1>

  <?php
  if (!empty($error_msg))
    echo "<b style='color: red;'>$error_msg</b>";
  else if (!empty($msg))
  {
    // logout (delete session)
    $_SESSION = array();
    $params = session_get_cookie_params();
    setcookie(session_name(),'', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    session_destroy();
    echo "<b style='color: green;'>$msg</b>";
    echo "<p>Bitte neu <a href='login/index.php'>einloggen</a></p>";
    echo '</div></main></body></html>';
    exit;
  }
  ?>
    <form action="pass_change.php" method="post" name="login_form"> 			
    <input type="hidden" name="pilotid" value="">
      <table class="formular_eingabe" style="width: 100%;">
          <tr>
              <td><b>Pilot-ID:</b></td>
              <td><input readonly="readonly" type="text" name="pilotid" value="<?php echo $pilotid; ?>" /></td>

          </tr>
          <tr>
              <td style="padding-top: 16px;"><b>Passwort:</b></td>
              <td style="padding-top: 16px;"><input value="" required="required" type="password" name="password" /></td>

          </tr>
          <tr>
              <td><b>Passwort bestätigen:</b></td>
              <td><input value="" required="required" type="password" name="changepwd" /></td>
          </tr> 
      </table>
      <input type="submit" name="submit" value="Ändern" /> 
    </form>
  </div>
</main>
</body>
</html>

