<?php

/**
 * Copyright (C) 2013 peredur.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

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
<html>
    <head>
        <title>Sicheres Einloggen</title>
        <link rel="stylesheet" href="styles/main.css" />
        <script type="text/JavaScript" src="js/sha512.js"></script> 
        <script type="text/JavaScript" src="js/forms.js"></script> 
    </head>
    <body>
        <?php
        if (isset($_GET['error'])) {
            echo '<p class="error">Error Logging In!</p>';
        }
        ?> 
        <form action="includes/process_login.php" method="post" name="login_form"> 			
            Email: <input required="required" type="email" name="email" />
            Passwort: <input type="password" name="password" id="password"/>
            <input type="submit" value="Login" 
                   onclick="formhash(this.form, this.form.password);" /> 
        </form>
        <p> Wenn man kein Konto hat,  <a href="register.php">hier registrieren</a> bitte.</p>
        <p>Wenn man fertig ist, bitte <a href="includes/logout.php">ausloggen</a>.</p>
        <p>Du bist momentan  <?php echo $logged ?>geloggt.</p>
    </body>
</html>
