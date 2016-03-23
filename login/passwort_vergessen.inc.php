<?php

if (isset($_POST['submit']))
{

  $email = trim($_POST['email']);
  $pilot_nr = $_POST['pilot_nr'];
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
  $txt .= "http://www.mfgc.ch/reservationen/login/passwort_recovery.php?secret_string={$random_string}&email={$email}&pilot_nr={$pilot_nr}";

  $headers   = array();
  $headers[] = "MIME-Version: 1.0";
  $headers[] = "Content-type: text/plain; charset=utf-8";
  $headers[] = "From: noreply@mfgc.ch";

  // wenn email ok, die Lock-Werte eintragen in die passwort_recovery tabelle

  if (mail ($email, $subject, $txt, implode("\r\n",$headers)))
  {
	mysqli_prepare_execute($mysqli, "INSERT INTO `password_recovery` (`id`, `email`, `secret_string`, `pilot_nr`, `timestamp`) VALUES (NULL, ?, ?, ?, NULL );", 'ssi', array ($email, $random_string, intval($pilot_nr)));
	write_status_message($mysqli, "[Passwort Recovery]", "System", "Wurde an &lt;{$email}&gt; <span style='color: green;'>geschickt</span>");
    $status_txt = "Ein Wiederherstellungs-Link wurde an &lt;{$email}&gt; geschickt.";
  }
  else
  {
	write_status_message($mysqli, "[Passwort Recovery]", "System", "Konnte <span style='color: red;'>nicht</span> an &lt;{$email}&gt; gesendet werden.");
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

?>
