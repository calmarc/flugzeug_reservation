<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('../includes/db_connect.php');
include_once ('../includes/user_functions.php');
include_once ('../includes/html_functions.php');
include_once ('../includes/functions.php');

sec_session_start();

$id = "";

if (isset($_POST['user_id']))
  $id = $_POST['user_id'];

// einaml muss get stimmen.. (sonst abbruch
// sonst zeigt es das Formular.. dann wird via post gepreuft
include_once ('passwort_recovery.inc.php');

print_html_to_body('Passwort ändern', '');
include_once('../includes/usermenu.php');

?>

<main>
<h1>Passwort neu setzen</h1>
<div id="formular_innen">

  <?php
  if (!empty($error_msg))
  {
    echo "<b style='color: red;'>$error_msg</b>";
  }
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

