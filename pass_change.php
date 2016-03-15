<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE){ header("Location: /reservationen/login/index.php"); exit; }

// eigentlich ist die pilotid in der Session
$id = $_SESSION['user_id'];

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

    $query= "SELECT `salt` FROM `members` WHERE `id` = $id;";
    $res = $mysqli->query($query); 
    $obj = $res->fetch_object();

    $password = hash('sha512', $password);
    $password = hash('sha512', $password . $obj->salt);

    if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`members` SET `password` = ? WHERE `members`.`id` = ? ;"))
    {
      $stmt->bind_param('si', $password, $id);
      if (!$stmt->execute()) 
      {
          header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
          exit;
      }
      $msg = "<p style='color: green;'>Das Passwort wurde geändert</p>";
    }
  }
}


print_html_to_body('Passwort ändern', ''); 
include_once('includes/usermenu.php'); 

?>

<main>
<div id="formular_innen">
   <h1>Passwort ändern</h1>

  <?php
  if (!empty($error_msg))
    echo "<b style='color: red;'>$error_msg</b>";
  else if (isset($msg))
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
      <table class="user_admin" style="width: 100%;">
          <tr>
              <td><b>Neues Passwort:</b></td>
              <td><input value="" required="required" type="password" name="password" /></td>

          </tr>
          <tr>
              <td><b>Passwort bestätigen:</b></td>
              <td><input value="" required="required" type="password" name="changepwd" /></td>
          </tr> 
      </table>
      <input class="submit_button" type="submit" name="submit" value="Ändern" /> 
    </form>
  </div>
</main>
</body>
</html>

