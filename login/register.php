<?php
/**
 * Copyright (C) 2013 peredur.net
 * See <http://www.gnu.org/licenses/>.
 */
include_once 'includes/register.inc.php';
include_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Registrieren</title>
  <meta name="title" content="Registrieren">
  <meta name="keywords" content="Registrieren">
  <meta name="description" content="Registrieren">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/reservationen.css">
</head>
    <body>

    <?php include_once('../includes/usermenu.php'); ?>

        <div id="formular">
          <div id="formular_innen" style="width: 400px; height: 400px;">

          <!-- Registration form to be output if the POST variables are not
          set or if the registration script caused an error. -->
          <h1>Registrieren</h1>
          <?php
          if (!empty($error_msg)) {
              echo $error_msg;
          }
          ?>
          <div style="text-align: left;">
            <ul>
                <li>Benutzernamen dürfen nur Buchstaben, Zahlen und "_" enthalten</li>
                <li>Passwörter müssen mindestens 6 Zeichen lang sein und Grossbuchtaben (A..Z), Kleinbuchstaben (a..z) und mind. eine  Nummer (0..9) enthalten.</li>
            </ul>
          </div>
          <form method="post" name="registration_form" action="register.php">
            <table>
              <tr>
                <td style="text-align: right;"><b>Benutzernamen:</b></td> <td><input type='text' name='username' id='username' /></td>
              </tr>
              <tr>
                <td style="text-align: right;"><b>Email:</b></td> <td><input type="text" name="email" id="email" /></td>
              </tr>
              <tr>
                <td style="text-align: right;"><b>Passwort:</b></td> <td><input type="password" name="password" id="password"/></td>
              </tr>
              <tr>
                <td style="text-align: right;"><b>Passwort bestätigen:</b></td> <td><input type="password" name="confirmpwd" id="confirmpwd" /></td>
              </tr>
            </table> 
            <input type="button" value="Register" onclick="return regformhash(this.form, this.form.username, this.form.email, this.form.password, this.form.confirmpwd);" />
          </form>
        </div>
      </div>
</body>
</html>
