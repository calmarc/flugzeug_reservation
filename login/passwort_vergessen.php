<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('../includes/db_connect.php');
include_once ('../includes/user_functions.php');
include_once ('../includes/html_functions.php');
include_once ('../includes/functions.php');

$status_txt = "";

include_once ('passwort_vergessen.inc.php');

print_html_to_body('Passwort vergessen', '');
include_once('../includes/usermenu.php');

?>
    <main>
      <h1>Passwort zurücksetzen</h1>
      <div id="formular_innen">
      <br />
          <form action="passwort_vergessen.php" method="post" class="login_form"> 			
            <table class="formular_eingabe" style="width: 100%;">
              <tr>
                <td><b>Pilot-Nr:</b></td>
                <td><input type="number" min="1" max="999" required="required" name="pilot_nr" /></td>
              </tr>
              <tr>
                <td><b>Registrierte Email:</b></td>
                <td><input type="email" required="required" name="email" /></td>
              </tr>
            </table>
            <input class="submit_button" type="submit" name="submit" value="Email-Link schicken" />
          </form>
      <br />
      </div>
  </main>
  </body>
</html>
