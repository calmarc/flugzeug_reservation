<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('../includes/db_connect.php');
include_once ('../includes/user_functions.php');
include_once ('../includes/html_functions.php');
include_once ('../includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

// verarbeitet das forumlar (submit)
include_once 'register.inc.php';

print_html_to_body('Neuen Piloten hinzufügen', '');
include_once('../includes/usermenu.php');

?>
  <main>
      <h1>Neuen Piloten hinzufügen</h1>
      <div id="formular_innen">

<?php
if (!empty($error_msg)) {
    echo "<b style='color: red;'>$error_msg</b>";
}
?>
        <div class="center">
          <form method="post" name="registration_form" action="register.php">
            <div class="center">
              <table class="vtable">
                <tr>
                   <td><b>Pilot-ID:</b></td> <td><input style="text-align: center;" required="required"
                  <?php if (isset($_SESSION['regpilot_nr'])) echo "value='".$_SESSION['regpilot_nr']."'"; ?>
                                   type='number' min="1" max="9999" name='pilot_nr' id='pilot_nr' /></td>
                </tr>
                <tr>
                   <td><b>Name:</b></td> <td><input
                  <?php if (isset($_SESSION['regname'])) echo "value='".$_SESSION['regname']."'"; ?>
                                    type="text" name="name" id="name" /></td>
                </tr>
                <tr>
                  <td><b>Natel:</b></td> <td><input
                <?php if (isset($_SESSION['regnatel'])) echo "value='".$_SESSION['regnatel']."'"; ?>
                                 pattern='\+{0,1}[0-9 ]+'  type="text" name="natel" id="natel" /></td>
                </tr>
                <tr>
                  <td><b>Telefon:</b></td> <td><input
                <?php if (isset($_SESSION['regtel'])) echo "value='".$_SESSION['regtel']."'"; ?>
                                 pattern='\+{0,1}[0-9 ]+'  type="text" name="tel" id="tel" /></td>
                </tr>
                <tr>
                  <td><b>Email:</b></td> <td><input
                <?php if (isset($_SESSION['regemail'])) echo "value='".$_SESSION['regemail']."'"; ?>
                                  type="email" name="email" id="email" /></td>
                </tr>
                <tr>
                  <td><b>Admin:</b></td> <td>
                    <select size="1"  style='width: 4em;' name="admin">
                      <option <?php if (isset($_SESSION['regadmin']) && $_SESSION['regadmin'] == 0 ) echo " selected='selected' "; ?> value="0">nein</option>
                      <option <?php if (isset($_SESSION['regadmin']) && $_SESSION['regadmin'] == 1 ) echo " selected='selected' "; ?> value="1">Ja</option>
                    </select></td>
                </tr>
                <tr>
                  <td><b>Passwort:</b></td> <td><input required="required" type="text" name="password" /></td>
                </tr>
              </table>
              <input class="submit_button" type="submit" value="Hinzufügen" />
            </div>
          </form>
        </div>
    </div>
  </main>
</body>
</html>
