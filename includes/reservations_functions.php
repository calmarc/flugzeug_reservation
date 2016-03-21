<?php

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
// ueber die flieger iterieren
// /////////////////////////////////////////////////////////////////////////////////////////////////////////////


// zum loeschen und fuer die letzte 'gute' buchung welche noch reingucken kann.

function remove_zombies($mysqli)
{

  date_default_timezone_set("Europe/Zurich");
  $now_string = date("Y-m-d H:i:s");
  date_default_timezone_set('UTC');
  $query = "SELECT * FROM `flieger`;";
  $res_f = $mysqli->query($query);

  while($obj_f = $res_f->fetch_object())
  {
    // TODO: ich nehmen mal alle.. aber eigentlich alte.. oder 'gute' (geflogen // actually) nicht??
    // TODO: auf ein jahr reduzieren in der vergangenheit...
    //
    // zurueckreichenste punkt allers reservierungen (ganz in der vergangenheit)
    $query = "SELECT `von` FROM `reservationen` WHERE `flieger_id` = '{$obj_f->id}' ORDER BY `von` ASC LIMIT 1;";
    if ($res = $mysqli->query($query))
    {
      if ($res->num_rows > 0)
      {
        $obj = $res->fetch_object();
        $von_extrem = $obj->von;
      }
      else
        continue;
    }

    // zukuenfstigste punkt der reservierungen (ganz in der zukunft)
    //
    $query = "SELECT `bis` FROM `reservationen` WHERE `flieger_id` = '{$obj_f->id}' ORDER BY `bis` DESC LIMIT 1;";
    if ($res = $mysqli->query($query))
    {
      if ($res->num_rows > 0)
      {
        $obj = $res->fetch_object();
        $bis_extrem = $obj->bis;
      }
      else
        continue;
    }

    // it.: if booking[level][hour]=TRUE <- reserved
    $bookings = array(array(), array(), array(), array(), array());

    // halb stunden sein 1971..
    $min_stamp = strtotime($von_extrem);

    // halb stunden bloecke differenz unserer reservierngen
    $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 60 / 60 * 2)+1;

    for ($x = 0; $x < 5; $x++) // initialise with FALSE = free.
      for ($i = 0; $i < $half_hour_tot+1; $i++)
        $bookings[$x][$i] = FALSE;

    $delete_id = array();

    // jetzt alle reservierungen hohlen
    $query = "SELECT * FROM `reservationen` WHERE `flieger_id` = '{$obj_f->id}' AND ( `bis` > '{$von_extrem}' AND `von` < '{$bis_extrem}') ORDER BY `timestamp` ASC;";

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

      #transfer time of booking into blocks (internal time measurement kinda)
      $block_first = intval((strtotime($obj_tang->von) - $min_stamp) / 1800);  // 1800 halbe stunde
      $block_last = intval((strtotime($obj_tang->bis) - $min_stamp) / 1800)-1;

      $level = 0;
      for($i = $block_first; $i <= $block_last; $i++)
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
        for($i = $block_first; $i <= $block_last; $i++)
          $bookings[$level][$i] = TRUE;
    }

    // sodali.. jetzt haben wir bookings welche nicht auf gruen sind...
    // von diesen jene loeschen welche 'vor' dem jetzt sind.

    foreach($delete_id as $di)
    {
      // make copy into reser_zombies
      $query = "INSERT INTO `reser_zombies` (`timestamp`, `user_id`, `flieger_id`, `von`, `bis`)
        SELECT `timestamp`, `user_id`, `flieger_id`, `von`, `bis` FROM `reservationen` WHERE `id` = ? AND `von` < ?;";
      mysqli_prepare_execute($mysqli, $query, 'is', array ($di, $now_string));

      $query = "DELETE FROM `mfgcadmin_reservationen`.`reservationen` WHERE `reservationen`.`id` = ? AND `von` < ?;";
      mysqli_prepare_execute($mysqli, $query, 'is', array ($di, $now_string));
    }
  }
}

function check_level($mysqli, $flieger_id, $von_date, $bis_date)
{
  // TODO: identischer code bald 3 mal... in function reintun!
  // habes jahr zureuck
  date_default_timezone_set("Europe/Zurich");
  $date_xmonth_back = date("Y-m-d H:i:s", time()-20736000);
  date_default_timezone_set('UTC');

  // NUR ein halbes jahr zurueck gucken. hats ueberhaupt reservationen?
  // sonst Zeit markieren als $von_extrem
  $query = "SELECT `von` FROM `reservationen` WHERE `flieger_id` = '{$flieger_id}' AND `von` > '{$date_xmonth_back}'  ORDER BY `von` ASC LIMIT 1;";

  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0)
    {
      $obj = $res->fetch_object();
      $von_extrem = $obj->von;
      if ($von_extrem > $von_date) //new has to get included into that
        $von_extrem = $von_date;
    }
    else
      return 0; // buchung ok.. hat noch keine
  }

  // die max-zukunfstigste (bis)-datum gucken
  // zeit markieren ($bis_extrem)
  $query = "SELECT `bis` FROM `reservationen` WHERE `flieger_id` = '{$flieger_id}' ORDER BY `bis` DESC LIMIT 1;";
  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0) // eigentilch immer.. oben wurde schon geguckt
    {
      $obj = $res->fetch_object();
      $bis_extrem = $obj->bis;
      if ($bis_extrem < $bis_date) //new has to get included into that
        $bis_extrem = $bis_date;
    }
    else
      return 0; // buchung ok.. hat noch keine
  }

  // halbe stunde blocks ganz links nach ganz rechts.
  $min_stamp = strtotime($von_extrem);
  $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 60 / 60 * 2)+1;

  // if booking[level][hour]=TRUE <- reserved
  $bookings = array(array(), array(), array(), array(), array(), array(), array());

  for ($x = 0; $x < 7; $x++) // initialise with FALSE = free.
    for ($i = 0; $i < $half_hour_tot+1; $i++)
      $bookings[$x][$i] = FALSE;

  // alle hohlen
  $query = "SELECT * FROM `reservationen` WHERE `flieger_id` = '{$flieger_id}' AND `von` >= '{$von_extrem}'  ORDER BY `timestamp` ASC;";
  $res_tang = $mysqli->query($query);

  while($obj_tang = $res_tang->fetch_object())
  {
    #transfer time to blocks (1800=30min) of current booking
    $block_first = intval((strtotime($obj_tang->von) - $min_stamp) / 1800);
    $block_last = intval((strtotime($obj_tang->bis) - $min_stamp) / 1800)-1;

    // look vor level where it can fit
    $level = 0;
    while(TRUE)
    {
      $flag = FALSE;
      for($i = $block_first; $i <= $block_last; $i++)
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
    for($i = $block_first; $i <= $block_last; $i++)
      $bookings[$level][$i] = TRUE;
  }

  //////////////////////////////////////////////////////////////
  // den level ermitteln der aktuellen buchung
  #transfer time to blocks (1800=30min) of current booking
  $block_first = intval((strtotime($von_date) - $min_stamp) / 1800);
  $block_last = intval((strtotime($bis_date) - $min_stamp) / 1800)-1;

  // look vor level where it can fit
  $level = 0;
  while(TRUE)
  {
    $flag = FALSE;
    for($i = $block_first; $i <= $block_last; $i++)
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

function get_valid_reserv($mysqli, $flieger_id)
{
  // TODO: identischer code bald 3 mal... in function reintun!
  // habes jahr zureuck
  $level_0 = array();

  date_default_timezone_set("Europe/Zurich");
  $date_xmonth_back = date("Y-m-d H:i:s", time()-20736000);
  date_default_timezone_set('UTC');

  // NUR ein halbes jahr zurueck gucken. hats ueberhaupt reservationen?
  // sonst Zeit markieren als $von_extrem
  $query = "SELECT `von` FROM `reservationen` WHERE `flieger_id` = '{$flieger_id}' AND `von` > '{$date_xmonth_back}'  ORDER BY `von` ASC LIMIT 1;";

  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0)
    {
      $obj = $res->fetch_object();
      $von_extrem = $obj->von;
    }
    else
      return $level_0;
  }

  // die max-zukunfstigste (bis)-datum gucken
  // zeit markieren ($bis_extrem)
  $query = "SELECT `bis` FROM `reservationen` WHERE `flieger_id` = '{$flieger_id}' ORDER BY `bis` DESC LIMIT 1;";
  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0) // eigentilch immer.. oben wurde schon geguckt
    {
      $obj = $res->fetch_object();
      $bis_extrem = $obj->bis;
    }
    else
      return $level_0;
  }

  // halbe stunde blocks ganz links nach ganz rechts.
  $min_stamp = strtotime($von_extrem);
  $half_hour_tot = intval((strtotime($bis_extrem) - $min_stamp) / 60 / 60 * 2)+1;

  // if booking[level][hour]=TRUE <- reserved
  $bookings = array(array(), array(), array(), array(), array(), array(), array());

  for ($x = 0; $x < 7; $x++) // initialise with FALSE = free.
    for ($i = 0; $i < $half_hour_tot+1; $i++)
      $bookings[$x][$i] = FALSE;

  // alle hohlen
  $query = "SELECT * FROM `reservationen` WHERE `flieger_id` = '{$flieger_id}' AND `von` >= '{$von_extrem}'  ORDER BY `timestamp` ASC;";
  $res_tang = $mysqli->query($query);

  while($obj_tang = $res_tang->fetch_object())
  {
    #transfer time to blocks (1800=30min) of current booking
    $block_first = intval((strtotime($obj_tang->von) - $min_stamp) / 1800);
    $block_last = intval((strtotime($obj_tang->bis) - $min_stamp) / 1800)-1;

    // look vor level where it can fit
    $level = 0;
    while(TRUE)
    {
      $flag = FALSE;
      for($i = $block_first; $i <= $block_last; $i++)
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

    for($i = $block_first; $i <= $block_last; $i++)
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
         (`id` , `timestamp`, `user_id`, `flieger_id`, `von`, `bis`, `loescher_id`, `grund`)
  VALUES ( NULL , NULL, ?, ?, ?, ?, ?, ?);";

  mysqli_prepare_execute($mysqli, $query, 'iissis', array ($obj->user_id, $obj->flieger_id, $obj->von, $obj->bis, $user_id, $begruendung));

  // komplett loeschen da komplett in der zukunft oder komplett in der
  // vergangenheit
  $query = "DELETE FROM `mfgcadmin_reservationen`.`reservationen` WHERE `reservationen`.`id` = ? ;";
  mysqli_prepare_execute($mysqli, $query, 'i', array ($id_tmp));
}

function reser_getrimmt_eintrag($mysqli, $obj, $user_id, $begruendung, $loeschen_datum_von, $loeschen_datum_bis)
{
  $query = "INSERT INTO `mfgcadmin_reservationen`.`reser_getrimmt`
            (`id`, `timestamp`, `user_id`, `flieger_id`, `von`, `bis`, `loescher_id`, `grund`, `getrimmt_von`, `getrimmt_bis`)
            VALUES ( NULL , NULL, ?, ?, ?, ?, ?, ?, ?, ?);";
  mysqli_prepare_execute($mysqli, $query, 'iississs', array ($obj->user_id, $obj->flieger_id, $obj->von, $obj->bis, $user_id, $begruendung, $loeschen_datum_von, $loeschen_datum_bis));
}

?>
