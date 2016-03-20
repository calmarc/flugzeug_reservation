<?php

include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

$status_txt = "";
if (isset($_POST['submit']))
{

  $email = trim($_POST['email']);
  $pilot_id = $_POST['pilot_id'];
  $subject = "MFGC.ch: Passwort Wiederherstellungs-Link";
  $txt = "Dein Bestätigungs-Link. Bitte draufdrücken/öffnen.";
  $txt .= "\n\n";

  // Character List to Pick from
  $chrList = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $chrRepeatMin = 1; // Minimum times to repeat the seed string
  $chrRepeatMax = 10; // Maximum times to repeat the seed string
  $chrRandomLength = 24; // Length of Random String returned
  // The ONE LINE random command with the above variables.
  $random_string = substr(str_shuffle(str_repeat($chrList, mt_rand($chrRepeatMin,$chrRepeatMax))),1,$chrRandomLength);
  $txt .= "http://www.mfgc.ch/reservationen/login/passwort_recovery.php?secret_string={$random_string}&email={$email}&pilot_id={$pilot_id}";

  $headers   = array();
  $headers[] = "MIME-Version: 1.0";
  $headers[] = "Content-type: text/plain; charset=utf-8";
  $headers[] = "From: noreply@mfgc.ch";

  if (mail ($email, $subject, $txt, implode("\r\n",$headers)))
  {
	mysqli_prepare_execute($mysqli, "INSERT INTO `password_recovery` (`id`, `email`, `secret_string`, `pilot_id`, `timestamp`) VALUES (NULL, ?, ?, ?, NULL );", 'ssi', array ($email, $random_string, intval($pilot_id)));
	write_status_message($mysqli, "[Passwort Recovery]", "Wurde an &lt;{$email}&gt; <span style='color: green;'>geschickt</span>");
    $status_txt = "Ein Wiederherstellungs-Link wurde an &lt;{$email}&gt; geschickt.";
  }
  else
  {
	write_status_message($mysqli, "[Passwort Recovery]", "Konnte <span style='color: red;'>nicht</span> an &lt;{$email}&gt; gesendet werden.");
    $status_txt = "Leider konnte kein Wiederherstellungs-Link an &lt;{$email}&gt; gesendet werden.";
  }

  print_html_to_body('Passwort vergessen', '');
  include_once('../includes/usermenu.php');
  echo "
          <main>
            <div id='formular_innen'>
              <h1>Passwort zurücksetzen</h1>
            <p>{$status_txt}</p>
            </div>
          </main>
          </body>
        </html>";
  exit;
}

print_html_to_body('Passwort vergessen', '');
include_once('../includes/usermenu.php');

?>
    <main>
      <div id="formular_innen">
        <h1>Passwort zurücksetzen</h1>

          <form action="passwort_vergessen.php" method="post" class="login_form"> 			
            <table class="formular_eingabe" style="width: 100%;">
              <tr>
                <td><b>Pilot-ID:</b></td>
                <td><input type="number" min="1" max="999" required="required" name="pilot_id" /></td>
              </tr>
              <tr>
                <td><b>Registrierte Email:</b></td>
                <td><input type="email" required="required" name="email" /></td>
              </tr>
            </table>
            <input class="submit_button" type="submit" name="submit" value="Email-Link schicken" />
          </form>
      </div>
  </main>
  </body>
</html>
