<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }

include_once 'includes/register.inc.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Neuen Piloten hinzufügen</title>
  <meta name="title" content="Neuen Piloten hinzufügen">
  <meta name="keywords" content="Neuen Piloten hinzufügen">
  <meta name="description" content="Neuen Piloten hinzufügen">
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
          <h1>Neuen Piloten hinzufügen</h1>

          <!-- Registration form to be output if the POST variables are not
          set or if the registration script caused an error. -->
          <?php
          if (!empty($error_msg)) {
              echo "<b style='color: red;'>$error_msg</b>";
          }
          ?>
          <div class="center">
            <form method="post" name="registration_form" action="register.php">
              <div class="center">
                <table>
                  <tr>
                  <td style="text-align: right;"><b>Pilot-ID:</b></td> <td><input 
                  <?php if (isset($_SESSION['regpilotid'])) echo "value='".$_SESSION['regpilotid']."'"; ?> 
                                   type='text' name='pilotid' id='pilotid' /></td>
                  </tr>
                  <tr>
                    <td style="text-align: right;"><b>Name:</b></td> <td><input 
                  <?php if (isset($_SESSION['regname'])) echo "value='".$_SESSION['regname']."'"; ?> 
                                    type="text" name="name" id="name" /></td>
                  </tr>
                  <tr>
                    <td style="text-align: right;"><b>Natel:</b></td> <td><input 
                  <?php if (isset($_SESSION['regnatel'])) echo "value='".$_SESSION['regnatel']."'"; ?> 
                                    type="text" name="natel" id="natel" /></td>
                  </tr>
                  <tr>
                    <td style="text-align: right;"><b>Telefon:</b></td> <td><input 
                  <?php if (isset($_SESSION['regtel'])) echo "value='".$_SESSION['regtel']."'"; ?> 
                                    type="text" name="tel" id="tel" /></td>
                  </tr>
                  <tr>
                    <td style="text-align: right;"><b>Email:</b></td> <td><input 
                  <?php if (isset($_SESSION['regemail'])) echo "value='".$_SESSION['regemail']."'"; ?> 
                                    type="text" name="email" id="email" /></td>
                  </tr>
                  <tr>
                    <td style="text-align: right;"><b>Admin:</b></td> <td style="text-align: left;"><select name="admin">
                      <option <?php if (isset($_SESSION['regadmin']) && $_SESSION['regadmin'] == 0 ) echo " selected='selected' "; ?> value="0">nein</option>
                      <option <?php if (isset($_SESSION['regadmin']) && $_SESSION['regadmin'] == 1 ) echo " selected='selected' "; ?> value="1">Ja</option></select>
                   
                  </tr>
                  <tr>
                    <td style="text-align: right;"><b>Passwort:</b></td> <td><input type="text" name="password" /></td>
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
