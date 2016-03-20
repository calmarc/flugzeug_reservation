<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('../includes/db_connect.php');
include_once ('../includes/functions.php');

sec_session_start();

$id = "";

if (isset($_POST['user_id']))
  $id = $_POST['user_id'];

$verifiziert = FALSE;
if (isset($_GET['secret_string'], $_GET['email']))
{
  $pilot_id = intval($_GET['pilot_id']);
  $query = "SELECT * FROM `password_recovery` WHERE `email` = '{$_GET['email']}' AND `secret_string` = '{$_GET['secret_string']}' AND `pilot_id` = {$pilot_id} LIMIT 1;";
  $res = $mysqli->query($query); 
  if ($res->num_rows == 1)
    $verifiziert = TRUE;
  $query = "SELECT `id` FROM `piloten` WHERE `email` = '{$_GET['email']}' AND `pilot_id` = {$pilot_id} LIMIT 1;";
  $res = $mysqli->query($query); 
  if ($res->num_rows != 1)
  {
    $error_msg = "Die Email/Piloten-ID konnte nicht gefunden werden. Das Passwort kann nicht wiederhergestellt werden.";
  }
  else
  {
    $obj = $res->fetch_object();
    $id = $obj->id;
  }
}

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
  if ($error_msg == "")
  {
    $query= "SELECT `salt` FROM `piloten` WHERE `id` = {$id};";
    $res = $mysqli->query($query);
    $obj = $res->fetch_object();

    $password = hash('sha512', $password);
    $password = hash('sha512', $password . $obj->salt);

    $query = "UPDATE `mfgcadmin_reservationen`.`piloten` SET `password` = ? WHERE `piloten`.`id` = ? ;";
    if (mysqli_prepare_execute($mysqli, $query, 'si', array ($password, $id)))
      $msg = "<p style='color: green;'>Das Passwort wurde geändert</p>";
  }
}

print_html_to_body('Passwort ändern', '');
include_once('../includes/usermenu.php');

?>

<main>
<div id="formular_innen">
   <h1>Passwort neu setzen</h1>

  <?php
  if (!empty($error_msg))
    echo "<b style='color: red;'>$error_msg</b>";
  else if (isset($msg))
  {
    echo "<b style='color: green;'>$msg</b>";
    echo "<p>Bitte neu <a href='index.php'>einloggen</a></p>";
    echo '</div></main></body></html>';
    exit;
  }
  ?>
    <form action="passwort_recovery.php" method="post" name="login_form"> 			
    <input type="hidden" name="user_id" value="<?php echo $id; ?>">
      <table class="vtable" style="width: 100%;">
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

