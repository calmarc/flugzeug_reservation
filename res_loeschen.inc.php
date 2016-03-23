<?php

// wenn man kommt vom chart her kommt.. daten checken

$tag = ""; if (isset($_GET['tag'])) $tag = $_GET['tag'];
$monat = ""; if (isset($_GET['monat'])) $monat = $_GET['monat'];
$jahr = ""; if (isset($_GET['jahr'])) $jahr = $_GET['jahr'];
$reservierung = ""; if (isset($_GET['reservierung'])) $reservierung = $_GET['reservierung'];
$backto = ""; if (isset($_GET['backto'])) $backto = $_GET['backto'];
$flugzeug_id = ""; if (isset($_GET['flugzeug_id'])) $flugzeug_id = $_GET['flugzeug_id'];

// sonst .. brauchts auch wenn man vom submit kommt

if (isset($_POST['tag'])) $tag = $_POST['tag'];
if (isset($_POST['monat'])) $monat = $_POST['monat'];
if (isset($_POST['jahr'])) $jahr = $_POST['jahr'];
if (isset($_POST['reservierung'])) $reservierung = $_POST['reservierung'];

$begruendung = ""; if (isset($_POST['begruendung'])) $begruendung = $_POST['begruendung'];

$rounded_stamp = (intval(time() / 1800) + 1) * 1800;
date_default_timezone_set("Europe/Zurich");
$rounded_datetime = date("Y-m-d H:i:s", $rounded_stamp);
date_default_timezone_set("UTC");

if (isset($_POST['submit'], $_POST['reservierung']) && intval($_POST['reservierung']) > 0)
{
  // id ok?
  // daten ok + vorarbeiten
  //   -> teilloeschung
  //   -> komplett
  //   -> trimmen
  //   -> sms senden allenfalls
  //   -> chart-seite weiterleiten
  //   ---------------------------
  // html...


  //============================================================================
  // Kontrollieren ob die ID existiert und auch modifiziert werden darf

  if (check_admin($mysqli))
    $query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = {$_POST['reservierung']} LIMIT 1;";
  else
    $query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = {$_POST['reservierung']} AND `user_id` = '{$_SESSION['user_id']}' AND `bis` >= '{$rounded_datetime}' LIMIT 1;";

  $res = $mysqli->query($query);

  if ($res->num_rows < 1)
  {
    header("Location: index.php?tag={$tag}&monat={$monat}&jahr={$jahr}");
    exit;
  }

  //============================================================================
  // preparationen (falls eingabe-fehler kommt nachher.. etwas ueberschuss diese get_vali_reserv.. anu

  $res = $mysqli->query("SELECT `flugzeug_id` from `reservationen` WHERE `id` = {$_POST['reservierung']};");
  $obj = $res->fetch_object();
  $flugzeug_id = $obj->flugzeug_id;

  // vor den loeschungen die aktiven vor-speichern (differenz...-> neu aktiv)
  $valid_0_pre = get_list_active_reserv($mysqli, $flugzeug_id);
  // beim splitten (zwischendrin) wird da die neue reservation gespeichert
  // damit man da keine sms sendet daraufhin.
  $not_new_no_notification = "";

  $query = "SELECT * from `reservationen` WHERE `id` = {$reservierung} LIMIT 1;";
  $res = $mysqli->query($query);
  $obj = $res->fetch_object();


  $error_msg = "";
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

    $loeschen_datum_von = "{$von_jahr}-{$von_monat}-{$von_tag} {$von_stunde}:{$von_minute}:00";
    $loeschen_datum_bis = "{$bis_jahr}-{$bis_monat}-{$bis_tag} {$bis_stunde}:{$bis_minute}:00";

    $res_datum_von = $obj->von;
    $res_datum_bis = $obj->bis;

    if ($loeschen_datum_bis <= $loeschen_datum_von)
      $error_msg .= "Bis-Zeit muss später Von-Zeit sein.<br />";

    if ($von_stunde == "21" && $von_minute != "00")
      $error_msg .= "21:30 liegt ausserhalb des Bereiches.<br />";

    if ($bis_stunde == "21" && $bis_minute != "00")
      $error_msg .= "21:30 liegt ausserhalb des Bereiches.<br />";

    if ($von_stunde == "21")
      $error_msg .= "Ab 21 Uhr kann man keine Reservierung machen. Nächster Tag verwenden.<br />";

    if ($bis_stunde == "07" && $bis_minute == "00")
      $error_msg .= "Bis 7 Uhr kann man keine Reservierung machen. Vorheriger Tag verwenden.<br />";

    if ($loeschen_datum_von < $res_datum_von)
      $error_msg .= "Neue Von-Zeit darf nicht vor der ursprüngliche Von-Zeit liegen.<br />";

    if ($loeschen_datum_bis > $res_datum_bis)
      $error_msg .= "Neue Bis-Zeit darf nicht nach der ursprüngliche Bis-Zeit liegen.<br />";

    if ($loeschen_datum_von < $rounded_datetime)
      $error_msg .= "Neue Von-Zeit darf nicht in der Vergangenheit liegen.<br />";
  }

  // nur wenn: nicht (teilloeschung aber mit error)
  if (!($_POST['submit'] == "Teillöschung" && $error_msg != ""))
  {
    //============================================================================
    // Teilloeschung

    if ($_POST['submit'] == "Teillöschung")
    {

      // back_21 und vor_7 werden fuer die buchungen gebraucht.
      // um ausserhab der 'nacht' zu bleiben.
      $loeschen_datum_von_back_21 = $loeschen_datum_von;
      $loeschen_datum_bis_vor_7 = $loeschen_datum_bis;

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
        $date21 = strtotime(date("Y-m-d", $loeschen_stamp_von)." 00:00:00");
        $loeschen_datum_von_back_21 = date("Y-m-d H:i:s", $date21 - 3 * 60 * 60);
      }
      //  'bis' ausdehnen...  >=21 -->  7:00 naechster tag
      if (date("H:i", $loeschen_stamp_bis) >= "21:00")
      {
        $date07 = strtotime(date("Y-m-d", $loeschen_stamp_bis)." 23:00:00");
        $loeschen_datum_bis_vor_7 = date("Y-m-d H:i:s", $date07 + 8 * 60 * 60);
      }

      // start und endzeit = groesser reservation -> komplett loeschen
      if ($loeschen_datum_von == $res_datum_von && $loeschen_datum_bis == $res_datum_bis)
      {
        delete_reservation($mysqli, $reservierung, $begruendung, $_SESSION['user_id']);

        $datum = mysql2chtimef ($loeschen_datum_von_back_21, $loeschen_datum_bis_vor_7, FALSE);
        write_status_message($mysqli, "[Reservation]", $_SESSION['user_id'], "Gelöscht: {$datum}");

        bei_geloescht_email($mysqli, "gelöscht", $obj->user_id, $obj->flugzeug_id,
                            mysql2chtimef($obj->von, $obj->bis, TRUE), $_POST['begruendung']);
      }
      // Anfang wegschneiden
      else if ($loeschen_datum_von == $res_datum_von && $loeschen_datum_bis < $res_datum_bis)
      {
        // neues von ist gleich loeschen bis (auf 7 gestreched wenn noetig)
        $query = "UPDATE `mfgcadmin_reservationen`.`reservationen` SET `von` = ? WHERE `reservationen`.`id` = ?;";
        mysqli_prepare_execute($mysqli, $query, 'si', array ($loeschen_datum_bis_vor_7, $reservierung));

        $datum = mysql2chtimef ($loeschen_datum_von, $loeschen_datum_bis, FALSE);
        write_status_message($mysqli, "[Reservation]", $_SESSION['user_id'], "Teilgelöscht: {$datum}");

        reser_getrimmt_eintrag($mysqli, $obj, $_SESSION['user_id'], $begruendung, $loeschen_datum_von, $loeschen_datum_bis);
      }
      // Ende wegschneiden
      else if ($loeschen_datum_von > $res_datum_von && $loeschen_datum_bis == $res_datum_bis)
      {
        // neues bis ist gleich loeschen von (auf 21 zurueckgeschraubt wenn noetig)
        $query = "UPDATE `mfgcadmin_reservationen`.`reservationen` SET `bis` = ? WHERE `reservationen`.`id` = ?;";
        mysqli_prepare_execute($mysqli, $query, 'si', array ($loeschen_datum_von_back_21, $reservierung));

        $datum = mysql2chtimef ($loeschen_datum_von, $loeschen_datum_bis, FALSE);
        write_status_message($mysqli, "[Reservation]", $_SESSION['user_id'], "Teilgelöscht: {$datum}");

        reser_getrimmt_eintrag($mysqli, $obj, $_SESSION['user_id'], $begruendung, $loeschen_datum_von, $loeschen_datum_bis);
      }
      else // liegt wo dazwischen
      {
        // eintrag 'fast-clonen' (inklusive timestamp - ohne ID und mit neuem bis
        $query = "INSERT INTO `mfgcadmin_reservationen`.`reservationen` (
        `id` , `timestamp` , `user_id` , `flugzeug_id` , `von` , `bis`)
        VALUES (NULL ,? ,? ,? ,? ,?);";

        mysqli_prepare_execute($mysqli, $query, 'siiss', array ($obj->timestamp, $obj->user_id, $obj->flugzeug_id, $loeschen_datum_bis_vor_7, $res_datum_bis));

        // update the initial one (bis ... to loeschen_von..)
        $query = "UPDATE `mfgcadmin_reservationen`.`reservationen` SET `bis` = ? WHERE `reservationen`.`id` = ?;";
        mysqli_prepare_execute($mysqli, $query, 'si', array ($loeschen_datum_von_back_21, $reservierung));

        reser_getrimmt_eintrag($mysqli, $obj, $_SESSION['user_id'], $begruendung, $loeschen_datum_von, $loeschen_datum_bis);

        $datum = mysql2chtimef ($loeschen_datum_von, $loeschen_datum_bis, FALSE);
        write_status_message($mysqli, "[Reservation]", $_SESSION['user_id'], "Teilgelöscht: {$datum}");

        $res_t = $mysqli->query("SELECT `id` FROM `reservationen` ORDER BY `id` DESC LIMIT 1;");
        $obj_t = $res_t->fetch_object();
        $not_new_no_notification = $obj_t->id;
      }
    }

    //============================================================================
    // Loeschen (ganz)

    else if ($obj->von >= $rounded_datetime || $obj->bis <= $rounded_datetime)
    {
      $begruendung = ""; if (isset($_POST['begruendung'])) $begruendung = $_POST['begruendung'];
      delete_reservation($mysqli, $reservierung, $begruendung, $_SESSION['user_id']);

      $datum = mysql2chtimef ($obj->von, $obj->bis, FALSE);
      write_status_message($mysqli, "[Reservation]", $_SESSION['user_id'], "Gelöscht: {$datum}");

      bei_geloescht_email($mysqli, "gelöscht", $obj->user_id, $obj->flugzeug_id,
                          mysql2chtimef($obj->von, $obj->bis, TRUE), $_POST['begruendung']);
    }

    //============================================================================
    // aufs 'JETZT' trimmen

    else
    {
      //  braucht keine Zurich.. reine H:i Hohlung.. auf 'neutralem' string
      //  zurich wurde aber auch gehen.. sofern bei beiden.

      //  wenn ZEIT nach >21 <7 ----> 7 nachster tag 'von'
      //  sollte nicht passieren, aber orig bis muss > sein als neues von
      //  TODO function? validate_22_7_time or so?
      $tmp_hour_min = date("H:i", strtotime($rounded_datetime));

      if ($tmp_hour_min < "07:00")
      {
        // new end: 7 uhr
        $date07 = strtotime(date("Y-m-d", strtotime($rounded_datetime))." 07:00:00");
        $new_end_date = date("Y-m-d H:i:s", $date07);
      }
      else if ($tmp_hour_min >= "21:00")
      {
        // new end: 7 uhr next day
        $date07 = strtotime(date("Y-m-d", strtotime($rounded_datetime))." 23:00:00");
        $new_end_date = date("Y-m-d H:i:s", $date07 + 8 * 60 * 60);
      }
      else
      {
        // new end: now rounded up half hour
        $new_end_date = $rounded_datetime;
      }

      $query = "UPDATE `mfgcadmin_reservationen`.`reservationen` SET `bis` = ? WHERE `reservationen`.`id` = ?;";
      mysqli_prepare_execute($mysqli, $query, 'si', array ($new_end_date, $reservierung));

      // fuer den eintrag . muss alles auf  21:00 zurueck.. falls "07:00" freigegeben
      // loeschen bis ende abend.. quasi..

      $tmp_d = date("H:i", strtotime($new_end_date));

      if ($tmp_d == "07:00")
      {
        $new_end_date = date("Y-m-d H:i:s", strtotime($new_end_date) - 10 * 60 * 60);
      }

      $datum = mysql2chtimef ($obj->von, $new_end_date, FALSE);
      write_status_message($mysqli, "[Reservation]", $_SESSION['user_id'], "Freigegeben: {$datum}");

      reser_getrimmt_eintrag($mysqli, $obj, $_SESSION['user_id'], $begruendung, $obj->von, $new_end_date);
    }

    //============================================================================
    // Eventuell standy jetzt gueltig - sms + email senden.

    $valid_0_after = get_list_active_reserv($mysqli, $flugzeug_id);

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
                                 WHERE `reservationen`.`id` ={$res_id} LIMIT 1;");

          $obj3 = $res3->fetch_object();
          $natel = $obj3->natel;
          $email = $obj3->email;
          $pilot = $obj3->name;
          $res_von = $obj3->von;
          $res_vin = $obj3->bis;

          $res_datum_raw = mysql2chtimef($obj3->von, $obj3->bis, TRUE);
          $res_datum = mysql2chtimef($obj3->von, $obj3->bis, FALSE);

          // TODO get_flugzeug_from_id() ?
          $res4 = $mysqli->query("SELECT * FROM `flugzeug` WHERE `id` = {$obj3->flugzeug_id} ;");
          $obj4 = $res4->fetch_object();
          $flugzeug = $obj4->flugzeug;

          $headers   = array();
          $headers[] = "MIME-Version: 1.0";
          $headers[] = "Content-type: text/plain; charset=utf-8";
          $headers[] = "From: noreply@mfgc.ch";

          $txt = "Deine Reservation:\n\nPilot: {$pilot}\nFlugzeug: {$flugzeug}\nDatum: {$res_datum_raw}\n\nwurde aktiviert!";

          $pilot_nr_pad = str_pad($obj3->pilot_nr, 3, "0", STR_PAD_LEFT);

          if ($email != "")
          {
            if (mail ($email, "MFGC Reservation vom {$res_datum_raw} aktiviert!", $txt, implode("\r\n",$headers)))

              write_status_message($mysqli, "[Standby Email]", "System",  "An [{$pilot_nr_pad}] {$pilot}: Reservation vom: {$res_datum}<br />Es wurde <span style='color: green'>eine</span> Email geschickt.");
            else
              write_status_message($mysqli, "[Standby Email]", "System", "An [{$pilot_nr_pad}] {$pilot}: Reservation vom: {$res_datum}<br />Es wurde <span style='color: red'>keine</span> Email geschickt.");
          }
          else
              write_status_message($mysqli, "[Standby Email]", "System", "An [{$pilot_nr_pad}] {$pilot}: Reservation vom: {$res_datum}<br />Pilot hat <span style='color: red;'>keine</span> Email angegeben.");


          if ($natel != "")
          {
            // send sms and log
            list($credits, $tracking_number, $ret_val) = sendsms($mysqli, $natel, $txt);

            write_status_message($mysqli, "[Standby SMS]", "System", "An [{$pilot_nr_pad}] {$pilot}: Reservation vom: {$res_datum}<br />Credits: {$credits}; @@{$tracking_number}@@; {$ret_val} ");
          }
          else
              write_status_message($mysqli, "[Standby SMS]", "System", "An [{$pilot_nr_pad}] {$pilot}: Reservation vom: {$res_datum}<br />Pilot hat <span style='color: red;'>keine</span> Natel-Nummer angegeben.");
        }
    }

    //============================================================================
    // alles erledigt.. zueruck von wo man hergekommen ist

    header("Location: index.php?tag={$tag}&monat={$monat}&jahr={$jahr}");
    exit;
  }
}

?>
