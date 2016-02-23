<?php

/* 
 * Copyright (C) 2013 peter
 * See <http://www.gnu.org/licenses/>.
 */

include_once 'db_connect.php';
include_once 'psl-config.php';

$error_msg = "";

if (isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirmpwd'])) {

  // Sanitize and validate the data passed in
  $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $email = filter_var($email, FILTER_VALIDATE_EMAIL);

  $_SESSION['regusername'] = $username;
  $_SESSION['regemail'] = $email;

  if (trim($_POST['username']) == "" || trim($_POST['email']) == "" || trim($_POST['password']) == "" || trim($_POST['confirmpwd']) == "")
      $error_msg .= '<p class="error">Bitte alle Felder ausfüllen</p>';

  if (!filter_var($email, FILTER_VALIDATE_EMAIL))
      $error_msg .= '<p class="error">Die eingegebene Email ist nicht gültig</p>';

  // Username validity and password validity have been checked client side.
  // This should should be adequate as nobody gains any advantage from
  // breaking these rules.
  
  $prep_stmt = "SELECT id FROM members WHERE email = ? LIMIT 1";
  $stmt = $mysqli->prepare($prep_stmt);
  
  if ($stmt) {
      $stmt->bind_param('s', $email);
      $stmt->execute();
      $stmt->store_result();
      
      if ($stmt->num_rows == 1) {
          // A user with this email address already exists
          $error_msg .= '<p class="error">Ein Benutzer mit dieser Email existiert bereits.</p>';
      }
  } else {
      $error_msg .= '<p class="error">Datenbank Fehler r</p>';
  }

  if (trim($_POST['password']) != trim($_POST['confirmpwd']))
      $error_msg .= '<p class="error">Passwörter stimmen nicht überein</p>';

  if (strlen(trim($_POST['password'])) < 4)
      $error_msg .= '<p class="error">Passwörter muss mindestend 4 Zeichen lang sein</p>';

  $password  = hash('sha512', trim($_POST['password']));
  if (strlen($password) != 128)
      $error_msg .= '<p class="error">Ungültige Passwort Konfiguration.</p>';
    
  if (empty($error_msg)) {
      // Create a random salt
      $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));

      // Create salted password 
      $password = hash('sha512', $password . $random_salt);

      // Insert the new user into the database 
      if ($insert_stmt = $mysqli->prepare("INSERT INTO members (username, email, password, salt) VALUES (?, ?, ?, ?)")) {
          $insert_stmt->bind_param('ssss', $username, $email, $password, $random_salt);
          // Execute the prepared query.
          if (! $insert_stmt->execute()) {
              header('Location: ../error.php?err=Registration failure: INSERT');
              exit();
          }
      }
      header('Location: ./register_success.php');
      exit();
  }
}
