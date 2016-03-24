<?php

if (isset($_POST['submit']))
{
  $password = ""; if (isset($_POST['password'])) $password = trim($_POST['password']);
  $changepwd = ""; if (isset($_POST['changepwd'])) $changepwd = trim($_POST['changepwd']);

  $error_msg = validate_new_password($password, $changepwd);

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
    {
      $geandert_msg = "<p style='color: green;'>Das Passwort wurde geändert</p>";
      write_status_message ($mysqli, "[Passwort]", $_SESSION['user_id'], "Wurde geändert");
    }
  }
}

?>
