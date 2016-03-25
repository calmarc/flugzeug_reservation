<?php

$error_msg = "";

if (isset($_POST['pilot_nr'], $_POST['password'])) {

  // Sanitize and validate the data passed in

  $pilot_nr = intval(trim($_POST['pilot_nr']));
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

  //============================================================================
  // zum wieder die input boxen auffuellen mit den vorhanden werten

  $_SESSION['regpilotid'] = $pilot_nr;
  $_SESSION['regname'] = $name;
  $_SESSION['regnatel'] = $natel;
  $_SESSION['regtel'] = $tel;
  $_SESSION['regemail'] = $email;
  $_SESSION['regadmin'] = $admin;

  //============================================================================
  // Existiert der Benutzer bereits?

  $prep_stmt = "SELECT `id` FROM `piloten` WHERE `pilot_nr` = ? LIMIT 1";
  $stmt = $mysqli->prepare($prep_stmt);

  if ($stmt)
  {
    $stmt->bind_param('i', $pilot_nr);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1)
    {
        // A user with this email address already exists
        $error_msg .= '<p class="error">Ein Pilot mit dieser Nummer existiert bereits.</p>';
    }
  }
  else
  {
      $error_msg .= '<p class="error">Datenbank Fehler</p>';
  }

  // minumum 4 zeichen

  if (strlen(trim($_POST['password'])) < 4)
      $error_msg .= '<p class="error">Passwort muss mindestens 4 Zeichen lang sein</p>';

  $password  = hash('sha512', trim($_POST['password']));
  if (strlen($password) != 128)
      $error_msg .= '<p class="error">Ungültige Passwort Konfiguration.</p>';

  // alles ok - neuer user/piloten eintragen
  if (empty($error_msg)) {
      // Create a random salt
      $random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE));

      // Create salted password
      $password = hash('sha512', $password . $random_salt);

      // Insert the new user into the database
      $query = "INSERT INTO piloten (pilot_nr, email, password, salt, admin, name, telefon, natel) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
      mysqli_prepare_execute($mysqli, $query, 'isssssss', array ($pilot_nr, $email, $password, $random_salt, $admin, $name, $tel, $natel));

      header('Location: ../pilot_admin.php');
      exit();
  }
}
