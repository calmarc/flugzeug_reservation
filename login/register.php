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
    <main>
  
           
        <div id="formular_innen">
          <h1>Registrieren</h1>

          <!-- Registration form to be output if the POST variables are not
          set or if the registration script caused an error. -->
          <?php
          if (!empty($error_msg)) {
              echo "<b style='color: red;'>$error_msg</b>";
          }
          ?>
          <div style="text-align: left; margin-left: auto; margin-right: auto;">
            <form method="post" name="registration_form" action="register.php">
    
        <div class="center">
              <table>
                <tr>
                <td style="text-align: right;"><b>Benutzernamen:</b></td> <td><input 
                <?php if (isset($_SESSION['regusername'])) echo "value='".$_SESSION['regusername']."'"; ?> 
                                 type='text' name='username' id='username' /></td>
                </tr>
                <tr>
                  <td style="text-align: right;"><b>Email:</b></td> <td><input 
                <?php if (isset($_SESSION['regemail'])) echo "value='".$_SESSION['regemail']."'"; ?> 
                                  type="text" name="email" id="email" /></td>
                </tr>
                <tr>
                  <td style="text-align: right;"><b>Passwort:</b></td> <td><input type="password" name="password" id="password"/></td>
                </tr>
                <tr>
                  <td style="text-align: right;"><b>Passwort bestätigen:</b></td> <td><input type="password" name="confirmpwd" id="confirmpwd" /></td>
                </tr>
              </table> 
              <input class="submit_button"  type="submit" value="Register" />
            </div>
            </form>
          </div>
      </div>
</main>
</body>
</html>
