<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('../includes/db_connect.php');
include_once ('../includes/user_functions.php');
include_once ('../includes/html_functions.php');
include_once ('../includes/functions.php');

sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['pilot_nr'], $_POST['password']))
{
  $pilot_nr = $_POST['pilot_nr'];
  $password = hash('sha512', $_POST['password']);


  //============================================================================
  // check captcha if set to show

  $res = $mysqli->query("SELECT `show` FROM `mfgcadmin_reservationen`.`captcha` WHERE `captcha`.`id` =1;");
  $obj = $res->fetch_object();
  if ($obj->show){
    if ($_SESSION['randomnr2'] != md5($_POST['captcha'])) {
      // Login failed
      header('Location: ../index.php?error=2');
      exit();
    }
  }

  if (login($pilot_nr, $password, $mysqli) == true)
  {
    // Login success

    //============================================================================
    // EMAILEN WO NOETIG
    // checkflug gueltig und checkflug < time.. dann email wenn noetig ->  markierung

    date_default_timezone_set("Europe/Zurich");
    $jetzt = date('Y-m-d', time());
    date_default_timezone_set('UTC');

    $query= "SELECT `name`, `id`, `pilot_nr`, `checkflug`, `email_gesch` FROM `piloten`;";
    $res = $mysqli->query($query);

    while ($obj = $res->fetch_object())
    {
      if ($obj->checkflug > "0000-00-00" && $obj->checkflug < $jetzt && $obj->email_gesch == FALSE)
      {
        //mail
        $res2 = $mysqli->query("SELECT * FROM `diverses` WHERE `funktion` = 'bei_gesperrt_email';");
        $obj2 = $res2->fetch_object();
        $to = $obj2->data1;
        $subject = "Checkflug überfällig: '{$obj->name}' [".str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT)."]";
        $txt = $subject;
        $headers   = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=utf-8";
        $headers[] = "From: noreply@mfgc.ch";

        if (mail($to, $subject, $txt, implode("\r\n",$headers)))
        {
          mysqli_prepare_execute($mysqli, "UPDATE `mfgcadmin_reservationen`.`piloten` SET `email_gesch` = '1' WHERE `piloten`.`id` = ?;", 'i', array ($obj->id));
          write_status_message($mysqli, "[Check-Flug]", "System", "Email an [{$to}] <span style='color: green;'>gesendet</span>: {$subject}");
        }
        else
          write_status_message($mysqli, "[Check-Flug]", "System", "Email an <{$to}> konnte <span style='color: red'>nicht</span> geschickt werden!");

      }
    }

    //============================================================================
    // Status loggins.

    $query= "SELECT `name`, `pilot_nr` FROM `piloten` WHERE `pilot_nr` = {$pilot_nr};";
    $res = $mysqli->query($query);
    $obj = $res->fetch_object();

    write_status_message($mysqli, "[Eingeloggt]", $_SESSION['user_id'], "");

    //============================================================================
    // passwort-recovery tabelle aufraeume (alles weg aelter als 4 stunden)

    date_default_timezone_set("Europe/Zurich");
    $local_datetime = date("Y-m-d H:i:s", time() - 60 * 60 * 4);
    date_default_timezone_set('UTC');
    mysqli_prepare_execute($mysqli, "DELETE FROM `password_recovery` WHERE `timestamp` < ?;", 's', array ($local_datetime));

    header("Location: ../index.php");
    exit();
  }
  else
  {
    // Login failed
    header('Location: index.php?error=1');
    exit();
  }
}
else
{
    // The correct POST variables were not sent to this page.
    header('Location: error.php?err=Could not process login');
    exit();
}
