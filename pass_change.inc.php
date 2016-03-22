<?php

if (isset($_POST['submit']))
{
  $password = ""; if (isset($_POST['password'])) $password = trim($_POST['password']);
  $changepwd = ""; if (isset($_POST['changepwd'])) $changepwd = trim($_POST['changepwd']);

  // validate entries
  $error_msg = "";

  if ($password == "")
    $error_msg .= "<p>Bitte ein Passwort eingeben</p>";

  if (strlen($password) < 4)
    $error_msg .= "<p>Muss mind. 4 Zeichen lang sein</p>";

  if ($changepwd == "")
    $error_msg .= "<p>Bitte das Passwort bestätigen</p>";
  else if ($password != $changepwd)
    $error_msg .= "<p>Passwörter stimmen nicht überrein</p>";

  // OK, eintragen
  if ($error_msg == "")
  {

    $query= "SELECT `salt` FROM `piloten` WHERE `id` = {$_SESSION['user_id']};";
    $res = $mysqli->query($query);
    $obj = $res->fetch_object();

    $password = hash('sha512', $password);
    $password = hash('sha512', $password . $obj->salt);

    $query = "UPDATE `mfgcadmin_reservationen`.`piloten` SET `password` = ? WHERE `piloten`.`id` = ? ;";
    if (mysqli_prepare_execute($mysqli, $query, 'si', array ($password, intval($_SESSION['user_id']))))
      $geandert_msg = "<p style='color: green;'>Das Passwort wurde geändert</p>";
    // TODO status meldung ausgeben
  }
}

?>
