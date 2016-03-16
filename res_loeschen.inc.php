<?php 

$required = 'required="required"';
$chars_java = "onsubmit=\"var text = document.getElementById('texta').value; if(text.length < 7) { alert('Ausführlichere Begruendung bitte!'); return false; } return true;\"";
$chars_java2 = "onsubmit=\"var text = document.getElementById('texta2').value; if(text.length < 7) { alert('Ausführlichere Begruendung bitte!'); return false; } return true;\"";
$optional = "";
if (check_admin($mysqli))
{
  $required = '';
  $optional = " (optional)";
  $chars_java = "";
}

$tag = ""; if (isset($_GET['tag'])) $tag = $_GET['tag']; 
$monat = ""; if (isset($_GET['monat'])) $monat = $_GET['monat']; 
$jahr = ""; if (isset($_GET['jahr'])) $jahr = $_GET['jahr']; 

$reservierung = ""; if (isset($_GET['reservierung'])) $reservierung = $_GET['reservierung']; 
$backto = ""; if (isset($_GET['backto'])) $backto = $_GET['backto']; 
$flieger_id = ""; if (isset($_GET['flieger_id'])) $flieger_id = $_GET['flieger_id']; 

$curstamp = time(); // wird einige male gebraucht
// round up cur_time to half hour blocks
$curstamp = (intval($curstamp / 1800) + 1) * 1800;
date_default_timezone_set("Europe/Zurich");
$curdate = date("Y-m-d H:i:s", $curstamp);
list ($stunde_block, $minute_block) =  explode(":", date("H:i", $curstamp), 2);
date_default_timezone_set("UTC");
$curstamp = strtotime($curdate);

// TODO: PS unten gibts rounde_time und so?!

$minute_block = intval($minute_block);
$stunde_block = intval($stunde_block);

if ($minute_block > 30)
{
  $minute_block = 0 ;
  $stunde_block++;
}
else if ($minute_block > 0 && $minute_block <= 30)
  $minute_block = 30;

$jetzt_rounded = str_pad($stunde_block, 2, "0", STR_PAD_LEFT).":".str_pad($minute_block, 2, "0", STR_PAD_LEFT);


if (isset($_POST['submit']))
{
  if (isset($_POST['reservierung']) && intval($_POST['reservierung']) > 0 )
  {
    // entry must be owned by this logged-in user && must be in the future still..
    // OR admin...
    // bigger than next half-hour rounded up
    
    if (check_admin($mysqli))
      $query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = ".$_POST['reservierung']." LIMIT 1;";
    else
      $query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = ".$_POST['reservierung']." AND `user_id` = '".$_SESSION['user_id']."' AND `bis` > '$curdate' LIMIT 1;";

    $res = $mysqli->query($query);

    if ($res->num_rows < 1)
    {
      header("Location: index.php?tag=$tag&monat=$monat&jahr=$jahr");
      exit;
    }

    $obj = $res->fetch_object();

    // find all valid reservatiions in the future and store them.
    // den delte/trim.
    // find again all valid reservation.
    // array_diff will find occurance not found in the pre.
    
    $res2 = $mysqli->query("SELECT `flieger_id` from `reservationen` WHERE `id` = ".$_POST['reservierung'].";");
    $obj2 = $res2->fetch_object();
    $flieger_id = $obj2->flieger_id;

    // vor den loeschungen die gueltigen speichern
    $valid_0_pre = get_valid_reserv($mysqli, $flieger_id);

    $id_tmp = intval($_POST['reservierung']);
    $begruendung = ""; if (isset($_POST['begruendung'])) $begruendung = $_POST['begruendung'];

    $query = "SELECT * from `reservationen` WHERE `id` = $id_tmp LIMIT 1;";
    $res = $mysqli->query($query);
    $obj = $res->fetch_object();

    
    //
    // teilloeschung
    //
    if ($_POST['submit'] == "Teillöschung")
    {
      $von_jahr = ""; if (isset($_POST['von_jahr'])) $von_jahr = $_POST['von_jahr'];
      $von_monat = ""; if (isset($_POST['von_monat'])) $von_monat = $_POST['von_monat'];
      $von_tag = ""; if (isset($_POST['von_tag'])) $von_tag = $_POST['von_tag'];
      $von_stunde = ""; if (isset($_POST['von_stunde'])) $von_stunde = $_POST['von_stunde'];
      $von_minute = ""; if (isset($_POST['von_minute'])) $von_minute = $_POST['von_minute'];

      $bis_jahr = ""; if (isset($_POST['bis_jahr'])) $bis_jahr = $_POST['bis_jahr'];
      $bis_monat = ""; if (isset($_POST['bis_monat'])) $bis_monat = $_POST['bis_monat'];
      $bis_tag = ""; if (isset($_POST['bis_tag'])) $bis_tag = $_POST['bis_tag'];
      $bis_stunde = ""; if (isset($_POST['bis_stunde'])) $bis_stunde = $_POST['bis_stunde'];
      $bis_minute = ""; if (isset($_POST['bis_minute'])) $bis_minute = $_POST['bis_minute'];


      //todo am besten ueberall machen.. oder halt fehlermeldung
      //unguelties 21:30 auf 21:00 machen.
      if ($von_stunde == "21" && $von_minute != "00")
        $von_minute = "00";
      if ($bis_stunde == "21" && $bis_minute != "00")
        $bis_minute = "00";

      $res_datum_von = $obj->von;
      $res_datum_bis = $obj->bis;

      $loeschen_datum_von = "$von_jahr-$von_monat-$von_tag $von_stunde:$von_minute:00";
      $loeschen_datum_bis = "$bis_jahr-$bis_monat-$bis_tag $bis_stunde:$bis_minute:00";

      $loeschen_datum_von_orig =  $loeschen_datum_von;
      $loeschen_datum_bis_orig =  $loeschen_datum_bis;

      // brute force.. korrigieren... (in den Bereich draengen)
      // TODO: besser warnung ausgeben und nichts machen.

      // |7---------21|7-----------21|7-----------21|7----------21|
      //               **************
      // |7---------21|7-----------21|7-----------21|7----------21|
      //      *********
      // |7---------21|7-----------21|7-----------21|7----------21|
      //                                            ********
      //
      // ein geloescht-von 0:00-7:00  muss ein 21:00 uhr Vor-tag werden
      // ein geloescht-von > 21:00  muss ein 21:00 uhr werden (oder // fehlermeldung)
      // ein geloescht-bis >= 21:00  muss ein 7:00 uhr naechste-tag werden
      
      $loeschen_stamp_von = strtotime($loeschen_datum_von);
      $loeschen_stamp_bis = strtotime($loeschen_datum_bis);
      
      // 'von' ausdehnen... <=7 auf 21:00 vortag
      if (date("H:i", $loeschen_stamp_von) <= "07:00")
      {
        $date22 = strtotime(date("Y-m-d", $loeschen_stamp_von)." 00:00:00");
        $loeschen_datum_von = date("Y-m-d H:i:s", $date22 - 3 * 60 * 60);
      }
      //  'bis' ausdehnen...  >=21 -->  7:00 naechster tag
      if (date("H:i", $loeschen_stamp_bis) >= "21:00")
      {
        echo "wir sind drinnen <br />";
        $date07 = strtotime(date("Y-m-d", $loeschen_stamp_bis)." 23:00:00");
        $loeschen_datum_bis = date("Y-m-d H:i:s", $date07 + 8 * 60 * 60);
      }

      // start und endzeit = groesser reservation - komplett loeschen
      if ($loeschen_datum_von <= $res_datum_von && $loeschen_datum_bis >= $res_datum_bis)
      {
        $begruendung = ""; if (isset($_POST['begruendung'])) $begruendung = $_POST['begruendung'];
        delete_reservation($mysqli, $id_tmp, $begruendung, $_SESSION['user_id']);

        bei_geloescht_email($mysqli, "gelöscht", $obj->user_id, $obj->flieger_id, 
                            mysql2chtimef($obj->von, $obj->bis, TRUE), $_POST['begruendung']);
      }
      // Anfang kuerzen
      else if ($loeschen_datum_von <= $res_datum_von && $loeschen_datum_bis < $res_datum_bis)
      {
        if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`reservationen` SET `von` = ? WHERE `reservationen`.`id` = ?;"))
        {
          $stmt->bind_param('si', $loeschen_datum_bis, $id_tmp);
          if (!$stmt->execute()) 
          {
              header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
              exit;
          }
        }

        reser_getrimmt_eintrag($mysqli, $obj, $_SESSION['user_id'], $begruendung, $loeschen_datum_von_orig, $loeschen_datum_bis_orig);
      }
      // Ende kuerzen
      else if ($loeschen_datum_von > $res_datum_von && $loeschen_datum_bis >= $res_datum_bis)
      {
        if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`reservationen` SET `bis` = ? WHERE `reservationen`.`id` = ?;"))
        {
          $stmt->bind_param('si', $loeschen_datum_von, $id_tmp);
          if (!$stmt->execute()) 
          {
              header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
              exit;
          }
        }

        reser_getrimmt_eintrag($mysqli, $obj, $_SESSION['user_id'], $begruendung, $loeschen_datum_von_orig, $loeschen_datum_bis_orig);
      }
      else
      {
        // eintrag clonen (inklusive timestamp - ohne ID)
        //
        $query = "INSERT INTO `calmarws_test`.`reservationen` (
        `id` ,
        `timestamp` ,
        `user_id` ,
        `flieger_id` ,
        `von` ,
        `bis`
        )
        VALUES (
        NULL , '".$obj->timestamp."', '".$obj->user_id."', '".$obj->flieger_id."', '".$loeschen_datum_bis."', '".$obj->bis."'
        );";

        $mysqli->query($query);

        // update the initial one (bis ... to loeschen_von..)
        if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`reservationen` SET `bis` = ? WHERE `reservationen`.`id` = ?;"))
        {
          $stmt->bind_param('si', $loeschen_datum_von, $id_tmp);
          if (!$stmt->execute()) 
          {
              header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
              exit;
          }
        }

        reser_getrimmt_eintrag($mysqli, $obj, $_SESSION['user_id'], $begruendung, $loeschen_datum_von_orig, $loeschen_datum_bis_orig);

        $res_t = $mysqli->query("SELECT `id` FROM `reservationen` ORDER BY `id` DESC LIMIT 1;");
        $obj_t = $res_t->fetch_object();
        $not_new_no_notification = $obj_t->id;
      }
    }
    //
    // loeschen
    //
    else if (strtotime($obj->von) >= $curstamp || strtotime($obj->bis) <= $curstamp)
    {
      $begruendung = ""; if (isset($_POST['begruendung'])) $begruendung = $_POST['begruendung'];
      delete_reservation($mysqli, $id_tmp, $begruendung, $_SESSION['user_id']);

      bei_geloescht_email($mysqli, "gelöscht", $obj->user_id, $obj->flieger_id, 
                          mysql2chtimef($obj->von, $obj->bis, TRUE), $_POST['begruendung']);
    }
    //
    // hat in der vergangenheit angefanne.. -> trimmen
    //
    else
    {
      /// START ZURICH
      date_default_timezone_set("Europe/Zurich");
      $tmp_hour = date("G", $curstamp); // stunden ohne nullen

      //  TODO: muss man da nicht nur bei date zurich und utc immer? strtotime
      //  auch sensible nicht?
      if (date("G", $curstamp) < 7)
      {
        // new end: yesterday 10:00  
        $date00 = strtotime(date("Y-m-d", $curstamp)." 00:00:00");
        $new_end_date = date("Y-m-d H:i:s", $date00 - 3 * 60 * 60);
      }
      else if (date("G", $curstamp) > 21)
      {
        // new end: today 21:00
        $date21 = strtotime(date("Y-m-d", $curstamp)." 21:00:00");
        $new_end_date = date("Y-m-d H:i:s", $date21);
      }
      else
      {
        // new end: now rounded up half hour
        $rounded_stamp = (intval($cur_stamp / 1800) + 1) * 1800;
        $new_end_date =  date("Y-m-d H:i:s", $rounded_stamp);
      }


      if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`reservationen` SET `bis` = ? WHERE `reservationen`.`id` = ?;"))
      {
        $id_tmp = intval($_POST['reservierung']);
        $stmt->bind_param('si', $new_end_date, $id_tmp);
        if (!$stmt->execute()) 
        {
            header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
            exit;
        }
      }
      // get the trimmed stuff...
      if (date("G", $curstamp) > 21)
      {
        // naechster tag 7 uhr
        $date07 = strtotime(date("Y-m-d", $curstamp)." 23:00:00");
        $del_start_date = date("Y-m-d H:i:s", $date07 + 8 * 60 * 60);
      }
      else if (date("G", $curstamp) < 7)
      {
        $del_start_date = date("Y-m-d", $curstamp)." 07:00:00";
      }

      reser_getrimmt_eintrag($mysqli, $obj, $_SESSION['user_id'], $begruendung, $del_start_date, $obj->bis);

      date_default_timezone_set("UTC");
      /// END ZURICH UTC again
    }

    // send sms when a standby is green now.
    
    $valid_0_after = get_valid_reserv($mysqli, $flieger_id);

    $new_0 = array_diff($valid_0_after, $valid_0_pre);
    if (count($new_0) > 0)
    {
        foreach ($new_0 as $res_id) 
        {
          // when its the new once created by splitting.
          if ($res_id == $not_new_no_notification)
            continue;

          $res3 = $mysqli->query("SELECT * FROM `piloten`
                                 JOIN `reservationen` ON `piloten`.`id` = `reservationen`.`user_id`
                                 WHERE `reservationen`.`id` =".$res_id." LIMIT 1;");

          $obj3 = $res3->fetch_object();
          $natel = $obj3->natel;
          $email = $obj3->email;
          $pilot = $obj3->name;
          $res_von = $obj3->von;
          $res_vin = $obj3->bis;

          $res_datum = mysql2chtimef($obj3->von, $obj3->bis, TRUE);
          $res4 = $mysqli->query("SELECT * FROM `flieger` WHERE `id` = ".$obj3->flieger_id." ;");
          $obj4 = $res4->fetch_object();
          $flieger = $obj4->flieger;
          $headers = "From: noreply@mfgc.ch";

          $txt = "Deine Reservierung:\n\nPilot: $pilot\nFlieger: $flieger\nDatum: $res_datum\n\nist nun gueltig!";

          mail ($email, "MFGC Reservierung vom $res_datum gültig!", $txt, $headers);

          // send sms.
          $ret_val = sendsms($natel, $txt);
        }
    }

    $tag = ""; if (isset($_POST['tag'])) $tag = $_POST['tag']; 
    $monat = ""; if (isset($_POST['monat'])) $monat = $_POST['monat']; 
    $jahr = ""; if (isset($_POST['jahr'])) $jahr = $_POST['jahr']; 

    //TODO woher soll post'flieger_id is da wenn von res_neu.php oder?
    if (isset($_POST['backto'], $_POST['flieger_id']) && $_POST['backto'] == "res_neu.php")
    {
       header("Location: /reservationen/res_neu.php?tag=$tag&monat=$monat&jahr=$jahr&flieger_id=".$_POST['flieger_id']);
    }
    else
       header("Location: index.php?tag=$tag&monat=$monat&jahr=$jahr");
  }
}

// check if trimmer or delete
$query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = $reservierung LIMIT 1;";
$res = $mysqli->query($query);

if ($res->num_rows < 1)
{
  header("Location: index.php?tag=$tag&monat=$monat&jahr=$jahr");
  exit;
}
$obj = $res->fetch_object();

if (!(strtotime($obj->von) >= $curstamp || strtotime($obj->bis) <= $curstamp))
{
	$trimmen = TRUE;	
	$h1 = "Reservation freigeben";
    $h3 = "";
    $button = "Ab ".$jetzt_rounded."h freigeben";
}
else
{
	$trimmen = FALSE;	
	$h1 = "Reservation löschen";
    $h3 = "Begründung$optional";
    $button = "Reservation löschen";
}

?>
