<?php

include_once 'includes/db_connect.php';
include_once 'includes/functions.php';

sec_session_start();

if (login_check($mysqli) == true) {
    $logged = 'ein';
} else {
    $logged = 'aus';
}
?>
  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content=
    "width=device-width, initial-scale=1.0">
    <title>Benutzer einloggen</title>
    <meta name="title" content="Benutzer Einloggen">
    <meta name="keywords" content="Benutzer,einloggen">
    <meta name="description" content="Benutzer Einloggen">
    <meta name="generator" content="Calmar + Vim + Tidy">
    <meta name="owner" content="calmar.ws">
    <meta name="author" content="candrian.org">
    <meta name="robots" content="all">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/reservationen/reservationen.css">
    </head>
    <body>

    <?php include_once('../includes/usermenu.php'); ?>

        <?php
        if (isset($_GET['error'])) {
            echo '<p class="error">Error Logging In!</p>';
        }
        ?> 

        <div id="formular">
          <div id="formular_innen">

              <form action="includes/process_login.php" method="post" name="login_form"> 			
                <table style="width: 100%;">
                    <tr>
                        <td style="text-align: right;"><b>Email:</b></td>
                        <td style="overflow: hidden;"><input required="required" type="email" name="email" /></td>
  
                    </tr>
                    <tr>
                        <td style="text-align: right;"><b>Passwort:</b></td>
                        <td style="overflow: hidden;"><input type="password" name="password" id="password"/></td>
                    </tr> 
                </table>
                  <input type="submit" value="Login" /> 
              </form>
          </div>
        </div>
    </body>
</html>
