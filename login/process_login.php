<?php

include_once '../includes/db_connect.php';
include_once '../includes/functions.php';

sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['pilotid'], $_POST['password'])) {
    $pilotid = $_POST['pilotid'];
    $password = hash('sha512', $_POST['password']);


    $res = $mysqli->query("SELECT `show` FROM `calmarws_test`.`captcha` WHERE `captcha`.`id` =1;");
    $obj = $res->fetch_object();
    if ($obj->show){
      if ($_SESSION['randomnr2'] != md5($_POST['captcha'])) {
        // Login failed 
        header('Location: ../index.php?error=2');
        exit();
      }
    }

    if (login($pilotid, $password, $mysqli) == true) {
        // Login success 
      
        // EMAILEN WO NOETIG
        // checkflug gueltig und checkflug < time.. dann email wenn noetig ->  markierung 
        date_default_timezone_set("Europe/Zurich");
        $jetzt = date('Y-m-d', time());
        date_default_timezone_set('UTC');

        $query= "SELECT `name`, `id`, `pilotid`, `checkflug`, `email_gesch` FROM `members`;";
        $res = $mysqli->query($query); 

        while ($obj = $res->fetch_object())
        {
          if ($obj->checkflug > "0000-00-00" && $obj->checkflug < $jetzt && $obj->email_gesch == FALSE)
          {
            //mail
            $res2 = $mysqli->query("SELECT * FROM `diverses`;");
            $obj2 = $res2->fetch_object();
            $to = $obj2->gesperrt_email;
            $subject = "Checkflug ueberfaellig: '".$obj->name."' [".str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT)."]";
			$txt = $subject;
			$headers = "From: noreply@mfgc.ch";

            if (mail($to,$subject,$txt,$headers))
              $mysqli->query("UPDATE `calmarws_test`.`members` SET `email_gesch` = '1' WHERE `members`.`id` = ".$obj->id." ;");
          } 
        }

        header("Location: ../index.php");
        exit();
    } else {
        // Login failed 
        header('Location: index.php?error=1');
        exit();
    }
} else {
    // The correct POST variables were not sent to this page. 
    header('Location: error.php?err=Could not process login');
    exit();
}
