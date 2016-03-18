<?php

include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start();

if (login_check($mysqli) == true) { header("Location: /reservationen/index.php"); exit; }

print_html_to_body('Benutzer einloggen', '');
include_once('../includes/usermenu.php');

?>
    <main>
<?php

if (isset($_GET['error']))
{
  $err= "Pilot-ID / Passwort Kombination stimmen nicht!";
  if ($_GET['error'] == 2)
    $err= "Captcha stimmt nicht überein!";
  else
    $err= "Pilot-ID / Passwort Kombination stimmen nicht!";

  echo "<div class='center'>
          <h3 class='error'>$err</h3>
        </div>";
}
?>

      <div id="formular_innen">
        <h1>Einloggen</h1>

          <form action="process_login.php" method="post" name="login_form" class="login_form"> 			
            <table class="formular_eingabe" style="width: 100%;">
              <tr>
                <td><b>Pilot-ID:</b></td>
                <td ><input required="required" type="number"  min="1" max="999" name="pilot_id" /></td>
              </tr>
              <tr>
                <td><b>Passwort:</b></td>
                <td><input required="required"  type="password" name="password" id="password"/></td>
              </tr>

<?php
  $res = $mysqli->query("SELECT `show` FROM `mfgcadmin_reservationen`.`captcha` WHERE `captcha`.`id` =1;");
  $obj = $res->fetch_object();
  if ($obj->show)
{ ?>
                <tr>
                    <td style="text-align: right;"><b>Captcha:</b></td>
                    <td style="text-align: left;"><input name="captcha" type="text" id="captcha" size="4" maxlength="4" /><img style="vertical-align:middle;" src="/reservationen/login/captcha_hardcode/captcha.php" /></td>
                </tr>
<?php } ?>

              <tr>
                <td></td>
                <td style="text-align: center; padding-top: 50px; padding-right: 30px;"><input required="required" style="width: 20px;" type="checkbox" name="zustimmen" value="" />
                Ich bestätige die Einhaltung<br />der <a target="_blank" href="/reservationen/reservationspraxis.pdf">Reservationspraxis</a></td>
              </tr>
            </table>
              <input class="submit_button" type="submit" value="Login" />
          </form>
      </div>
  </main>
  </body>
</html>
