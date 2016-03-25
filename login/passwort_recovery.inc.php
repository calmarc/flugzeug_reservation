<?php

//============================================================================
// die werte kontrollieren des links
// muss 2 pruefungen erfuellen


if (isset($_GET['secret_string'], $_GET['email']))
{

  $verifiziert = FALSE;
  $pilot_nr = intval($_GET['pilot_nr']);
  $query = "SELECT * FROM `password_recovery` WHERE `email` = '{$_GET['email']}' AND `secret_string` = '{$_GET['secret_string']}' AND `pilot_nr` = {$pilot_nr} LIMIT 1;";
  $res = $mysqli->query($query);
  if ($res->num_rows == 1)
    $verifiziert = TRUE;

  // OK, it's the user with the email here - now check if that combo actually exists
  if ($verifiziert)
  {
    $query = "SELECT `id` FROM `piloten` WHERE `email` = '{$_GET['email']}' AND `pilot_nr` = {$pilot_nr} LIMIT 1;";
    $res = $mysqli->query($query);
    if ($res->num_rows != 1)
      $verifiziert = FALSE;
  }

  if ($verifiziert == FALSE)
  {
    //============================================================================
    // extra Seite ausgeben (abbruch quasi)

    $error_msg = "Die Piloten-ID/Email/Key Kombination konnte nicht gefunden werden.<br />Das Passwort kann nicht wiederhergestellt werden.";
    print_html_to_body('Passwort ändern', '');
    include_once('../includes/usermenu.php');
    echo "<main><div class='center'><h1>Wiederherstellungs-Fehler</h1><p><b>{$error_msg}</b></p></div></main>
          </body> </html>";
    $pilot_nr_pad = str_pad($pilot_nr, 3, "0", STR_PAD_LEFT);
    write_status_message($mysqli, "[Passwort]", "System", "Recovery fehlgeschlagen: [{$pilot_nr_pad}]; {$_GET['email']}");
    exit;
  }
  $obj = $res->fetch_object();
  $id = $obj->id;
}


//============================================================================
// Neues Passwort eintragen
// Man kann nur hierherkommen wenn man die Get oben einmal durchlaufen konnte -
// weil es dort intern einen abbruch gibt sonst falls nicht ok - so dass die
// form gar nie angezeigt wird.

if (isset($_POST['submit']))
{
  $password = ""; if (isset($_POST['password'])) $password = trim($_POST['password']);
  $changepwd = ""; if (isset($_POST['changepwd'])) $changepwd = trim($_POST['changepwd']);

  $error_msg = validate_new_password($password, $changepwd);

  // OK, eintragen
  if ($error_msg == "")
  {
    $query= "SELECT `salt` FROM `piloten` WHERE `id` = {$id};";
    $res = $mysqli->query($query);
    $obj = $res->fetch_object();

    $password = hash('sha512', $password);
    $password = hash('sha512', $password . $obj->salt);

    $query = "UPDATE `mfgcadmin_reservationen`.`piloten` SET `password` = ? WHERE `piloten`.`id` = ? ;";
    if (mysqli_prepare_execute($mysqli, $query, 'si', array ($password, $id)))
      $msg = "<p style='color: green;'>Das Passwort wurde geändert</p>";
    list ($pilot_nr_pad, $pilot_name) = get_pilot_from_user_id($mysqli, $id);
    write_status_message($mysqli, "[Passwort]", "System", "Recovery erfolgreich: [{$pilot_nr_pad}] {$pilot_name}");
  }
}

?>
