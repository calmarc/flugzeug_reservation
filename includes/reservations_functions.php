<?php


// Man koennte gewisse Sachen noch  rausmontieren - viel identischer code,
// aber ist schon ok so.. auch am 'billigsten'.

// hilfsfunktion um den ersten punkt (von_extrem) und letzten punkt der
// reservationen zu erhalten

function get_range_of_reservation($mysqli, $flugzeug_id, $date_xmonth_back)
{
  $query = "SELECT `von` FROM `reservationen` WHERE `flugzeug_id` = '{$flugzeug_id}' AND `von` > '{$date_xmonth_back}'  ORDER BY `von` ASC LIMIT 1;";

  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0)
    {
      $obj = $res->fetch_object();
      $von_extrem = $obj->von;
    }
    else
      return array(0, 0); // buchung ok.. hat noch keine
  }

  // die max-zukunfstigste (bis)-datum gucken
  // zeit markieren ($bis_extrem)
  $query = "SELECT `bis` FROM `reservationen` WHERE `flugzeug_id` = '{$flugzeug_id}' ORDER BY `bis` DESC LIMIT 1;";
  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0) // eigentilch immer.. oben wurde schon geguckt
    {
      $obj = $res->fetch_object();
      $bis_extrem = $obj->bis;
    }
    else
      return array(0, 0); // buchung ok.. hat noch keine
  }

  return array($von_extrem, $bis_extrem);
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ZOMBIES ENTFERNEN = alle buchungen in der vergangenheit.. welche nicht aktiv
// wuerden...
// (nachher nimmt man alle buchen welhce ins jetzt reichen (die eine maximal)
// und die in der  unlimitierte zukunft.
//
// dafuer erst mischlen.. mal alles was da ist..
//
// dann die finden welche aktiv ist und ins jetzt reinreicht.
// mit aktiv markieren... den rest (ausser hat aktive flag gesetzt) loeschen.


// /////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ueber die flugzeug iterieren
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////


// zum loeschen und fuer die letzte 'gute' buchung welche noch reingucken kann.

function remove_zombies($mysqli)
{
  // wird gebraucht zum loeschen testen.. von < jetzt -> zombie
  date_default_timezone_set("Europe/Zurich");
  $now_string = date("Y-m-d H:i:s");
  date_default_timezone_set('UTC');

  // 3 Monate in der Vergangenheit nur gucken.
  // wenn niemand einloggt/reserviert 3 monate. koennte eine standby da
  // durchgeben, aber das waere dann ja eh nicht mehr relevant, da die mit
  // maximal 1monat dauer nicht ins jetzt gucken kann.
  // (1 monat wuerde also theoretisch genuegen)

  $date_xmonth_back = strtotime($now_string);
  $date_xmonth_back = date("Y-m-d H:i:s", $date_xmonth_back - 8035200); //  61 * 60 * 24 * 93

  //============================================================================
  // ueber die flieger iterieren jeweils

  $query = "SELECT * FROM `flugzeug`;";
  $res_f = $mysqli->query($query);

  while($obj_f = $res_f->fetch_object())
  {
    // zurueckreichenste punkt ($von_extrem) allers reservierungen (ganz in der vergangenheit - LIMIT siehe oben)

    list($von_extrem, $bis_extrem) = get_range_of_reservation($mysqli, $obj_f->id, $date_xmonth_back);
    if ($von_extrem == 0 || $bis_extrem == 0)
      continue;

    //============================================================================
    // $bookings initialisieren
    //
    // halb stunden bloecke differenz unserer reservierngen zur initalisierung
    $min_stamp = strtotime($von_extrem);
    $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 1800);

    // it.: if booking[level][hour]=TRUE <- reserved
    $bookings = array(array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE));
    //----------------------------------------------------------------------------

    // hier kommen die zu loeschenen zombies rein
    $delete_id = array();

    // jetzt alle reservierungen hohlen im von_extrem - bis_extrem bereich
    //$query = "SELECT * FROM `reservationen` WHERE `flugzeug_id` = '{$obj_f->id}' AND ( `bis` > '{$von_extrem}' AND `von` < '{$bis_extrem}') ORDER BY `timestamp` ASC;";
    $query = "SELECT * FROM `reservationen` WHERE `flugzeug_id` = '{$obj_f->id}' AND `von` >= '{$von_extrem}'  ORDER BY `timestamp` ASC;";

    $res_tang = $mysqli->query($query);
    while($obj_tang = $res_tang->fetch_object())
    {
      // 1. order(ed) them by timestamp
      // 2. have a reserved-variable for each level (green, 1.standby, ...)
      //
      // 3. check against each of the reserved-level-variables..
      //    beginning vrom gree, 1.standby, 2. 4.... until it fits
      // 4. accordingly 'book' that into the level-variable
      // 5. goto step 3.

      //transfer time of booking into blocks (internal time measurement kinda)
      // Z.B.: 8 Uhr - 8:30 - gibt nur eine Total. unixtime 7:00 = 0
      // $min_stamp = 3600 (8 uhr)
      // $block_first = 3600 - 3600 = 0;
      // block_last = 5400 (8:30) - 3600 = 1800 -> 1
      // ergibt eine iteration.. wo gebucht wird
      // 


      $block_first = intval((strtotime($obj_tang->von) - $min_stamp) / 1800);  // 1800 halbe stunde
      $block_last = intval((strtotime($obj_tang->bis) - $min_stamp) / 1800);

      $level = 0;
      for($i = $block_first; $i < $block_last; $i++)
      {
        if ($bookings[$level][$i] == TRUE)
        {
          // Ops, not free - increase level (checked later)
          $level++;
          break;
        }
      }

      // not green -> mark..
      if ($level > 0)
        array_push ($delete_id, $obj_tang->id);
      else
        //book into level 0 only - enough for this procedure
        for($i = $block_first; $i < $block_last; $i++)
          $bookings[$level][$i] = TRUE;
    }
    //----------------------------------------------------------------------------

    // sodali.. jetzt haben wir bookings welche nicht auf gruen sind...
    // von diesen jene loeschen welche 'vor' dem jetzt sind.

    foreach($delete_id as $di)
    {
      // make copy into reser_zombies
      $query = "INSERT INTO `reser_zombies` (`timestamp`, `user_id`, `flugzeug_id`, `von`, `bis`)
        SELECT `timestamp`, `user_id`, `flugzeug_id`, `von`, `bis` FROM `reservationen` WHERE `id` = ? AND `von` < ?;";
      mysqli_prepare_execute($mysqli, $query, 'is', array ($di, $now_string));

      $query = "DELETE FROM `mfgcadmin_reservationen`.`reservationen` WHERE `reservationen`.`id` = ? AND `von` < ?;";
      mysqli_prepare_execute($mysqli, $query, 'is', array ($di, $now_string));
    }
  }
}

function check_level($mysqli, $flugzeug_id, $von_date, $bis_date)
{
  // 2 monate zureuck
  date_default_timezone_set("Europe/Zurich");
  $date_xmonth_back = date("Y-m-d H:i:s", time()-5456800); // 60*60*24*62
  date_default_timezone_set('UTC');

  // NUR 2 monate zurueck gucken (1monats reservationen sind maximum). hats ueberhaupt reservationen?
  // sonst Zeit markieren als $von_extrem

  list($von_extrem, $bis_extrem) = get_range_of_reservation($mysqli, $flugzeug_id, $date_xmonth_back);
  if ($von_extrem == 0 || $bis_extrem == 0)
    return 0;

  if ($von_extrem > $von_date) //new has to get included into that range
    $von_extrem = $von_date;
  if ($bis_extrem < $bis_date) //new has to get included into that
    $bis_extrem = $bis_date;

    //============================================================================
    // $bookings initialisieren
    //
    // halb stunden bloecke differenz unserer reservierngen zur initalisierung
    $min_stamp = strtotime($von_extrem);
    $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 1800);

    $bookings = array(array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE));
    //----------------------------------------------------------------------------

  // alle hohlen
  $query = "SELECT * FROM `reservationen` WHERE `flugzeug_id` = '{$flugzeug_id}' AND `von` >= '{$von_extrem}'  ORDER BY `timestamp` ASC;";
  $res_tang = $mysqli->query($query);

  //============================================================================
  // alle aktuellen buchungen speichern mal.... in bookings

  while($obj_tang = $res_tang->fetch_object())
  {
    //transfer time to blocks (1800=30min) of current booking
    $block_first = intval((strtotime($obj_tang->von) - $min_stamp) / 1800);
    $block_last = intval((strtotime($obj_tang->bis) - $min_stamp) / 1800);

    // look vor level where it can fit
    $level = 0;
    while(TRUE)
    {
      $flag = FALSE;
      for($i = $block_first; $i < $block_last; $i++)
      {
        if ($bookings[$level][$i] == TRUE)
        {
          // Ops, not free - try next level
          $level++;
          $flag = TRUE;
          break; // out of for loop
        }
      }
      if ($flag == FALSE)
        break;
    }

    //book into according level
    for($i = $block_first; $i < $block_last; $i++)
      $bookings[$level][$i] = TRUE;
  }

  //////////////////////////////////////////////////////////////
  // den level ermitteln der aktuellen buchung
  // transfer time to blocks (1800=30min) of current booking

  $block_first = intval((strtotime($von_date) - $min_stamp) / 1800);
  $block_last = intval((strtotime($bis_date) - $min_stamp) / 1800);

  // look vor level where it can fit
  $level = 0;
  while(TRUE)
  {
    $flag = FALSE;
    for($i = $block_first; $i < $block_last; $i++)
    {
      if ($bookings[$level][$i] == TRUE)
      {
        // Ops, not free - try next level
        $level++;
        $flag = TRUE;
        break; // out of for loop
      }
    }
    if ($flag == FALSE)
      break;
  }
  return $level; // this is the level it would be put in
}

// gibt die Liste aller aktive Reservierungen (pro flugzeug)
function get_list_active_reserv($mysqli, $flugzeug_id)
{
  $level_0 = array();

  date_default_timezone_set("Europe/Zurich");
  $date_xmonth_back = date("Y-m-d H:i:s", time()-5456800); // 60*60*24*62
  date_default_timezone_set('UTC');

  list($von_extrem, $bis_extrem) = get_range_of_reservation($mysqli, $flugzeug_id, $date_xmonth_back);
  if ($von_extrem == 0 || $bis_extrem == 0)
    return $level_0;

    //============================================================================
    // $bookings initialisieren
    //
    // halb stunden bloecke differenz unserer reservierngen zur initalisierung
    $min_stamp = strtotime($von_extrem);
    $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 1800);

    // it.: if booking[level][hour]=TRUE <- reserved
    $bookings = array(array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE),
                      array_fill(0, $half_hour_tot, FALSE));
    //----------------------------------------------------------------------------

  // alle hohlen
  $query = "SELECT * FROM `reservationen` WHERE `flugzeug_id` = '{$flugzeug_id}' AND `von` >= '{$von_extrem}'  ORDER BY `timestamp` ASC;";
  $res_tang = $mysqli->query($query);

  while($obj_tang = $res_tang->fetch_object())
  {
    //transfer time to blocks (1800=30min) of current booking
    $block_first = intval((strtotime($obj_tang->von) - $min_stamp) / 1800);
    $block_last = intval((strtotime($obj_tang->bis) - $min_stamp) / 1800);

    // look vor level where it can fit
    $level = 0;
    while(TRUE)
    {
      $flag = FALSE;
      for($i = $block_first; $i < $block_last; $i++)
      {
        if ($bookings[$level][$i] == TRUE)
        {
          // Ops, not free - try next level
          $level++;
          $flag = TRUE;
          break; // out of for loop
        }
      }
      if ($flag == FALSE)
        break;
    }

    //book into according level

    for($i = $block_first; $i < $block_last; $i++)
      $bookings[$level][$i] = TRUE;
    if ($level == 0)
      array_push($level_0, $obj_tang->id);
  }
  return $level_0;
}

function delete_reservation($mysqli, $id_tmp, $begruendung, $user_id)
{
  $query = "SELECT * from `reservationen` WHERE `id` = $id_tmp LIMIT 1;";
  $res = $mysqli->query($query);
  $obj = $res->fetch_object();

  // make copy into reser_geloescht
  $query = "INSERT INTO `mfgcadmin_reservationen`.`reser_geloescht`
         (`id` , `timestamp`, `user_id`, `flugzeug_id`, `von`, `bis`, `loescher_id`, `grund`)
  VALUES ( NULL , NULL, ?, ?, ?, ?, ?, ?);";

  mysqli_prepare_execute($mysqli, $query, 'iissis', array ($obj->user_id, $obj->flugzeug_id, $obj->von, $obj->bis, $user_id, $begruendung));

  $query = "DELETE FROM `mfgcadmin_reservationen`.`reservationen` WHERE `reservationen`.`id` = ? ;";
  mysqli_prepare_execute($mysqli, $query, 'i', array ($id_tmp));
}

function reser_getrimmt_eintrag($mysqli, $obj, $user_id, $begruendung, $loeschen_datum_von, $loeschen_datum_bis)
{
  $query = "INSERT INTO `mfgcadmin_reservationen`.`reser_getrimmt`
            (`id`, `timestamp`, `user_id`, `flugzeug_id`, `von`, `bis`, `loescher_id`, `grund`, `getrimmt_von`, `getrimmt_bis`)
            VALUES ( NULL , NULL, ?, ?, ?, ?, ?, ?, ?, ?);";
  mysqli_prepare_execute($mysqli, $query, 'iississs', array ($obj->user_id, $obj->flugzeug_id, $obj->von, $obj->bis, $user_id, $begruendung, $loeschen_datum_von, $loeschen_datum_bis));
}

//function get_all_valid_reservations($mysqli)
function get_all_list_active_reserv($mysqli)
{
  $res = $mysqli->query("SELECT `id` FROM `flugzeug`;");
  $valid_res = array(array(), array(), array(), array(), array());
  $x = 0;
  while ($obj = $res->fetch_object())
  {
    $valid_res[$x] = get_list_active_reserv($mysqli, $obj->id);
    $x++;
  }
  return $valid_res;
}

?>
