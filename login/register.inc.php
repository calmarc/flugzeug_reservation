<?php

/* 
 * Copyright (C) 2013 peter
 * See <http://www.gnu.org/licenses/>.
 */

include_once '../includes/db_connect.php';
include_once '../includes/psl-config.php'; // TODO: needed?

$error_msg = "";

if (isset($_POST['pilotid'], $_POST['password'])) {

  // Sanitize and validate the data passed in
  $pilotid = intval(trim($_POST['pilotid']));
  $password = trim($_POST['password']);

  $name = ""; if (isset($_POST['name'])) $name = trim($_POST['name']);
  $natel = ""; if (isset($_POST['natel'])) $natel = trim($_POST['natel']);
  $tel = ""; if (isset($_POST['tel'])) $tel = trim($_POST['tel']);
  $admin = ""; if (isset($_POST['admin'])) $admin = trim($_POST['admin']);
  
  $email = ""; if (isset($_POST['email'])) $email = trim($_POST['email']);
  if ($email != "") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error_msg .= '<p class="error">Die eingegebene Email ist nicht gültig</p>';
  }

  $_SESSION['regpilotid'] = $pilotid;
  $_SESSION['regname'] = $name;
  $_SESSION['regnatel'] = $natel;
  $_SESSION['regtel'] = $tel;
  $_SESSION['regemail'] = $email;
  $_SESSION['regadmin'] = $admin;

  $prep_stmt = "SELECT id FROM members WHERE pilotid = ? LIMIT 1";
  $stmt = $mysqli->prepare($prep_stmt);
  
  if ($stmt) {
      $stmt->bind_param('i', $pilotid);
      $stmt->execute();
      $stmt->store_result();
      
      if ($stmt->num_rows == 1) {
          // A user with this email address already exists
          $error_msg .= '<p class="error">Ein Pilot mit dieser Nummer existiert bereits.</p>';
      }
  } else {
      $error_msg .= '<p class="error">Datenbank Fehler</p>';
  }

  if (strlen(trim($_POST['password'])) < 4)
      $error_msg .= '<p class="error">Passwort muss mindestens 4 Zeichen lang sein</p>';

  $password  = hash('sha512', trim($_POST['password']));
  if (strlen($password) != 128)
      $error_msg .= '<p class="error">Ungültige Passwort Konfiguration.</p>';
    
  if (empty($error_msg)) {
      // Create a random salt
      $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));

      // Create salted password 
      $password = hash('sha512', $password . $random_salt);

      // Insert the new user into the database 
      if ($insert_stmt = $mysqli->prepare("INSERT INTO members (pilotid, email, password, salt, admin, name, telefon, natel) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")) {
          $insert_stmt->bind_param('isssssss', $pilotid, $email, $password, $random_salt, $admin, $name, $tel, $natel);
          // Execute the prepared query.
          if (! $insert_stmt->execute()) {
              header('Location: /reservationen/login/error.php?err=Registration failure: INSERT');
              exit();
          }
      }
      header('Location: ../user_admin.php');
      exit();
  }
}