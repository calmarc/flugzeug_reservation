<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE){ header("Location: /reservationen/login/index.php"); exit; }

//============================================================================
// Neues passwort verarbeiten (submit)

include_once('pass_change.inc.php');


print_html_to_body('Passwort ändern', '');
include_once('includes/usermenu.php');

?>

<main>
<div id="formular_innen">
   <h1>Passwort ändern</h1>

  <?php
  if (!empty($error_msg))
    echo "<b style='color: red;'>$error_msg</b>";
  else if (isset($geandert_msg))
  {
    // logout (delete session)
    $_SESSION = array();
    $params = session_get_cookie_params();
    setcookie(session_name(),'', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    session_destroy();
    echo "<b style='color: green;'>$geandert_msg</b>";
    echo "<p>Bitte neu <a href='login/index.php'>einloggen</a></p>";
    echo '</div></main></body></html>';
    exit;
  }
  ?>
    <form action="pass_change.php" method="post" name="login_form"> 			
    <input type="hidden" name="pilot_id" value="">
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

