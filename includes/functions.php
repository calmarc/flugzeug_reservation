<?php

include_once 'psl-config.php';

function sec_session_start() {
    $session_name = 'sec_session_id';   // Set a custom session name 
    $secure = SECURE;

    // This stops JavaScript being able to access the session id.
    $httponly = true;

    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) {
        header("Location: /reservationen/login/error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }

    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);

    // Sets the session name to the one set above.
    session_name($session_name);

    session_start();            // Start the PHP session 
    session_regenerate_id();    // regenerated the session, delete the old one. 
}

function login($pilotid, $password, $mysqli) {
    // Using prepared statements means that SQL injection is not possible. 
    if ($stmt = $mysqli->prepare("SELECT id, pilotid, password, salt FROM members WHERE pilotid = ? LIMIT 1")) {
        $stmt->bind_param('s', $pilotid);  // Bind "$email" to parameter.
        $stmt->execute();    // Execute the prepared query.
        $stmt->store_result();

        // get variables from result.
        $stmt->bind_result($user_id, $pilotid, $db_password, $salt);
        $stmt->fetch();

        $password = hash('sha512', $password . $salt);

        if ($stmt->num_rows == 1) {

            // more than 5 bad logins.. turn on captcha  - else turn off
            checkbrute($mysqli);

            // Check if the password in the database matches 
            // the password the user submitted.
            if ($db_password == $password) {
                // Password is correct!
                // Get the user-agent string of the user.
                $user_browser = $_SERVER['HTTP_USER_AGENT'];

                // XSS protection as we might print this value
                $user_id = preg_replace("/[^0-9]+/", "", $user_id);
                $_SESSION['user_id'] = $user_id;

                // XSS protection as we might print this value
                $pilotid = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $pilotid);

                $_SESSION['pilotid'] = $pilotid;
                $_SESSION['login_string'] = hash('sha512', $password . $user_browser);

                // Login successful. 
                return true;
            } else {
                // Password is not correct 
                // We record this attempt in the database 
                $now = time();
                if (!$mysqli->query("INSERT INTO login_attempts(user_id, time) 
                                VALUES ('$user_id', '$now')")) {
                    header("Location: /reservationen/login/error.php?err=Database error: login_attempts");
                    exit();
                }

                return false;
            }
        } else {
            // No user exists. 
            return false;
        }
    } else {
        // Could not create a prepared statement
        header("Location: /reservationen/login/error.php?err=Database error: cannot prepare statement");
        exit();
    }
}

function checkbrute($mysqli) {
    // Get timestamp of current time 
    $now = time();

    // cleaning up last failed attempts (60 mins)
    $delete_old = $now - (60 * 60);
    $mysqli->query("DELETE FROM `calmarws_test`.`login_attempts` WHERE `login_attempts`.`time` < $delete_old;");

    // All login attempts are counted from the past 1 minutes
    $valid_attempts = $now - (2 * 60);

    if ($stmt = $mysqli->prepare("SELECT time FROM login_attempts WHERE time > '$valid_attempts'")) {

        // Execute the prepared query. 
        $stmt->execute();
        $stmt->store_result();

        // If there have been more than 5 failed logins in the last minute
        if ($stmt->num_rows > 5) {
            $mysqli->query("UPDATE `calmarws_test`.`captcha` SET `show` = '1' WHERE `captcha`.`id` =1;");
            return;
        } else {
            // no 
            $mysqli->query("UPDATE `calmarws_test`.`captcha` SET `show` = '0' WHERE `captcha`.`id` =1;");
            return;
        }
    } else {
        // Could not create a prepared statement
        header("Location: /reservationen/login/error.php?err=Database error: cannot prepare statement");
        exit();
    }
}

function login_check($mysqli) {
    // Check if all session variables are set 
    if (isset($_SESSION['user_id'], $_SESSION['pilotid'], $_SESSION['login_string'])) {
        $user_id = $_SESSION['user_id'];
        $login_string = $_SESSION['login_string'];
        $pilotid = $_SESSION['pilotid'];

        // Get the user-agent string of the user.
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        if ($stmt = $mysqli->prepare("SELECT password 
				      FROM members 
				      WHERE id = ? LIMIT 1")) {
            // Bind "$user_id" to parameter. 
            $stmt->bind_param('i', $user_id);
            $stmt->execute();   // Execute the prepared query.
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                // If the user exists get variables from result.
                $stmt->bind_result($password);
                $stmt->fetch();
                $login_check = hash('sha512', $password . $user_browser);

                if ($login_check == $login_string) {
                    // Logged In!!!! 
                    return true;
                } else {
                    // Not logged in 
                    return false;
                }
            } else {
                // Not logged in 
                return false;
            }
        } else {
            // Could not prepare statement
            header("Location: /reservationen/login/error.php?err=Database error: cannot prepare statement");
            exit();
        }
    } else {
        // Not logged in 
        return false;
    }
}

function esc_url($url) {

    if ('' == $url) {
        return $url;
    }

    $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);
    
    $strip = array('%0d', '%0a', '%0D', '%0A');
    $url = (string) $url;
    
    $count = 1;
    while ($count) {
        $url = str_replace($strip, '', $url, $count);
    }
    
    $url = str_replace(';//', '://', $url);

    $url = htmlentities($url);
    
    $url = str_replace('&amp;', '&#038;', $url);
    $url = str_replace("'", '&#039;', $url);

    if ($url[0] !== '/') {
        // We're only interested in relative links from $_SERVER['PHP_SELF']
        return '';
    } else {
        return $url;
    }
}

function mysql2chtimef ($von, $bis, $raw)
{
  list( $tag, $zeit) = explode(" ", $von);
  $tmp = explode(":", $zeit);
  $vonzeit = $tmp[0].':'.$tmp[1];
  $tmp = explode("-", $tag);
  $vontag = $tmp[2].'.'.$tmp[1].'.'.preg_replace('/../',"",$tmp[0], 1);
  list( $tag, $zeit)  = explode(" ", $bis);
  $tmp = explode(":", $zeit);
  $biszeit = $tmp[0].':'.$tmp[1];
  $tmp = explode("-", $tag);
  $bistag = $tmp[2].'.'.$tmp[1].'.'.preg_replace('/../',"",$tmp[0], 1);

  if ($raw)
  {
    if ($vontag == $bistag)
      $datum = "$vontag: $vonzeit-$biszeit Uhr";
    else
      $datum = "$vontag $vonzeit - $bistag $biszeit";
  }
  else
  {
    if ($vontag == $bistag)
      $datum = "<span style='color: #990000;'>$vontag</span>: <span style='color: green'><i>$vonzeit - $biszeit Uhr</i></span>";
    else
      $datum = "<span style='color: #990000;'>$vontag</span> <span style='color: green'><i>$vonzeit</i></span> - <span style='color: #990000;'>$bistag</span> <span style='color: green'><i>$biszeit</i></span>";
  }
  return $datum;
}

function check_admin($mysqli)
{
  $query = "SELECT `admin` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  return $obj->admin;
}
function check_gesperrt($mysqli)
{
  $query = "SELECT `gesperrt` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  return $obj->gesperrt;
}

function zaehler_into($zaehler_minute, $zaehler_minute_vor)
{
  $dauer = $zaehler_minute - $zaehler_minute_vor;
  $t_h = intval($dauer / 60);
  $t_m = $dauer % 60;
  $t_m = str_pad($t_m, 2, "0", STR_PAD_LEFT);
  $dauer = "$t_h:$t_m";

  $t_h = intval($zaehler_minute / 60);
  $t_m = $zaehler_minute % 60;
  $t_m = str_pad($t_m, 2, "0", STR_PAD_LEFT);
  $zaehlerstand = "$t_h.$t_m";

  return array($zaehlerstand, $dauer);
}

function shortsql2ch_date ($sql_date)
{
  if($sql_date != "")
  {
    list ($y, $m, $t) =  explode("-", $sql_date, 3);
    if ($t == "00" || $m == "00" || $y == "0000")
      $checkflug_ch = "";
    else
      $checkflug_ch = "$t.$m.$y";
  }
  else
      $checkflug_ch = "";

  return $checkflug_ch;
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
    $query = "SELECT `von` FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' ORDER BY `von` ASC LIMIT 1;";
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
    $query = "SELECT `bis` FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' ORDER BY `bis` DESC LIMIT 1;";
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


    // TODO: only 3 or 4 allowed - limit on buchen
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
    $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$obj_f->id."' AND ( `bis` > '$von_extrem' AND `von` < '$bis_extrem') ORDER BY `timestamp` ASC;";

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
      //TODO: mark them as zombies... don't delelte.. for recovery reason (false //time etc)
      
      // make copy into reser_zombies
      $query = "INSERT INTO `reser_zombies` (`timestamp`, `userid`, `fliegerid`, `von`, `bis`)
        SELECT `timestamp`, `userid`, `fliegerid`, `von`, `bis` FROM `reservationen` WHERE `id` = $di AND `von` < '$now_string';";
      $mysqli->query($query);

      if ($stmt = $mysqli->prepare("DELETE FROM `calmarws_test`.`reservationen` WHERE `reservationen`.`id` = ? AND `von` < ?;"))
      {
        $stmt->bind_param('is', $di, $now_string);
        if (!$stmt->execute()) 
        {
            header('Location: /reservationen/login/error.php?err=Registration failure: STANDBY DELETE ');
            exit;
        }
      }
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
  $query = "SELECT `von` FROM `reservationen` WHERE `fliegerid` = '".$flieger_id."' AND `von` > '$date_xmonth_back'  ORDER BY `von` ASC LIMIT 1;";

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
  $query = "SELECT `bis` FROM `reservationen` WHERE `fliegerid` = '".$flieger_id."' ORDER BY `bis` DESC LIMIT 1;";
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
  $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$flieger_id."' AND `von` >= '$von_extrem'  ORDER BY `timestamp` ASC;";
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
  $query = "SELECT `von` FROM `reservationen` WHERE `fliegerid` = '".$flieger_id."' AND `von` > '$date_xmonth_back'  ORDER BY `von` ASC LIMIT 1;";

  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0)
    {
      $obj = $res->fetch_object();
      $von_extrem = $obj->von;
    }
  }

  // die max-zukunfstigste (bis)-datum gucken
  // zeit markieren ($bis_extrem)
  $query = "SELECT `bis` FROM `reservationen` WHERE `fliegerid` = '".$flieger_id."' ORDER BY `bis` DESC LIMIT 1;";
  if ($res = $mysqli->query($query))
  {
    if ($res->num_rows > 0) // eigentilch immer.. oben wurde schon geguckt
    {
      $obj = $res->fetch_object();
      $bis_extrem = $obj->bis;
    }
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
  $query = "SELECT * FROM `reservationen` WHERE `fliegerid` = '".$flieger_id."' AND `von` >= '$von_extrem'  ORDER BY `timestamp` ASC;";
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

function print_options($default, $t_array)
{
  foreach ($t_array as $item)
  {
    if (intval($default) == intval($item)) 
      echo '<option selected="selected">'.$item.'</option>';
    else
      echo '<option>'.$item.'</option>';
  }
}

function combobox_tag($default)
{
  $t_array = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31"];
  print_options($default, $t_array);
}

function combobox_monat($default)
{
  $t_array = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
  print_options($default, $t_array);
}
function combobox_jahr($default)
{
  date_default_timezone_set("Europe/Zurich");
  $jahr = date("Y", time());
  date_default_timezone_set("UTC");

  $t_array = array();
  for ($item = intval($jahr); $item < intval($jahr) + 2; $item++)
    array_push($t_array, "$item");
  print_options($default, $t_array);

}
function combobox_stunde($default)
{
  $t_array = ["07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21"];
  print_options($default, $t_array);
}
function combobox_minute($default)
{
  $t_array = ["00", "30"];
  print_options($default, $t_array);
}


function legende_print($boxcol)
{ ?>
<div class="legende">
  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
        height="130px" style="background-color: transparent; width: 60%; min-width: 660px;" >
    <defs>
      <linearGradient id="gruen0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#66ee66" stop-opacity="1"/>
        <stop offset="100%" stop-color="#99ee99" stop-opacity="1"/>
      </linearGradient>
      <linearGradient id="gelblich0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="<?php echo $boxcol[1];?>" stop-opacity="1"/>
        <stop offset="100%" stop-color="<?php echo $boxcol[2];?>" stop-opacity="1"/>
      </linearGradient>
      <linearGradient id="grey0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#dddddd" stop-opacity="1"/>
        <stop offset="100%" stop-color="#eeeeee" stop-opacity="1"/>
      </linearGradient>
    </defs>
   <g transform="translate(0, 0)">
    <rect x="20%" y="0" width="20%" height="24" style="fill:url(#grey0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="30%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Vergangenheit</text>
    <rect x="40%" y="0" width="20%" height="24" style="fill:url(#gruen0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="50%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Frei</text>
    <rect x="60%" y="0" width="20%" height="24" style="fill: <?php echo $boxcol[0]; ?>; stroke: #666666; stroke-width: 1px;"></rect>
    <text x="70%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Gebucht</text>
    <rect x="80%" y="0" width="20%" height="24" style="fill: url(#gelblich0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="90%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Standby</text>

    <!--<rect x="0.1%" y="0" width="20%" height="24" style="fill:url(#grey0); stroke: #666666; stroke-width: 1px;"></rect>-->
    <!--<text x="10.0%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Vergangenheit</text>-->
    <!--<rect x="20%" y="0" width="20%" height="24" style="fill:url(#gruen0); stroke: #666666; stroke-width: 1px;"></rect>-->
    <!--<text x="30%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Frei</text>-->
    <!--<rect x="40%" y="0" width="20%" height="24" style="stroke: #666666; stroke-width: 1px;"></rect>-->
    <!--<text x="50%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Gebucht</text>-->
    <!--<rect x="60%" y="0" width="20%" height="24" style="fill: url(#gelblich0); stroke: #666666; stroke-width: 1px;"></rect>-->
    <!--<text x="70%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Standby</text>-->
    <!--<rect x="79.9%" y="0" width="20%" height="24" style="stroke: #666666; stroke-width: 1px;"></rect>-->
    <!--<text x="90.0%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Service</text>-->
  </g>
  </svg>
</div>
<?php
}

function tooltip_print()
{
?>
<div onclick="document.getElementById('tooltip_div').style.display = 'none';" id="tooltip_div" style="display: none; visibility: hidden;">
  <svg id="tooltip_svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
        height="100px" width="200px">
    <text id="tooltip_text1" x="3%" y="20px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text2" x="3%" y="45px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text3" x="3%" y="70px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text4" x="3%" y="95px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
  </svg>
</div>
<?php
}

function delete_reservation($mysqli, $id_tmp, $begruendung, $user_id)
{
  $query = "SELECT * from `reservationen` WHERE `id` = $id_tmp LIMIT 1;";
  $res = $mysqli->query($query);
  $obj = $res->fetch_object();

  // make copy into reser_geloescht
  $query = "INSERT INTO `calmarws_test`.`reser_geloescht` (
  `id` ,
  `timestamp` ,
  `userid` ,
  `fliegerid` ,
  `von` ,
  `bis` ,
  `loescher` ,
  `grund`
  )
  VALUES (
  NULL , NULL, '".$obj->userid."', '".$obj->fliegerid."', '".$obj->von."', '".$obj->bis."', '".$user_id."', '".$begruendung."'
  );";

  $mysqli->query($query);

  // komplett loeschen da komplett in der zukunft oder komplett in der
  // vergangenheit
  if ($stmt = $mysqli->prepare("DELETE FROM `calmarws_test`.`reservationen` WHERE `reservationen`.`id` = ? ;"))
  {
    $stmt->bind_param('i', $id_tmp);
    if (!$stmt->execute()) 
    {
        header('Location: /reservationen/login/error.php?err=Registration failure: DELETE');
        exit;
    }
  }
}

function reser_getrimmt_eintrag($mysqli, $obj, $user_id, $begruendung, $loeschen_datum_von, $loeschen_datum_bis)
{
  $query = "INSERT INTO `calmarws_test`.`reser_getrimmt` (
  `id` ,
  `timestamp` ,
  `userid` ,
  `fliegerid` ,
  `von` ,
  `bis` ,
  `loescher` ,
  `grund`,
  `getrimmt_von`,
  `getrimmt_bis`
  )
  VALUES (
  NULL , NULL, '".$obj->userid."', '".$obj->fliegerid."', '".$obj->von."', '".$obj->bis."', '".$user_id."', '".$begruendung."', '".$loeschen_datum_von."', '".$loeschen_datum_bis."'
  );";
  $mysqli->query($query);
}

function bei_geloescht_email($mysqli, $subject_hint, $pilot_id, $flieger_id, $zeit, $begruendung)
{

  $res = $mysqli->query("SELECT * from `diverses` WHERE `funktion` = 'bei_geloescht';");
  $obj = $res->fetch_object();
  $to = $obj->email;

  $res = $mysqli->query("SELECT * from `members` WHERE `id` = $pilot_id;");
  $obj = $res->fetch_object();
  $pilot = str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT). " (".$obj->name.")";

  $res = $mysqli->query("SELECT * from `flieger` WHERE `id` = $flieger_id;");
  $obj = $res->fetch_object();
  $flieger = $obj->flieger;

  $subject = "Reservierung $subject_hint: $pilot: $zeit";
  $txt = "Bei der Motorfluggruppe Chur wurde folgende Flugzeug-Reservierung $subject_hint:";
  $txt .= "\n\n";
  $txt .= "Pilot: $pilot";
  $txt .= "\n";
  $txt .= "Flugzeug: $flieger";
  $txt .= "\n";
  $txt .= "Buchungszeit: $zeit";
  $txt .= "\n\n";
  $txt .= "Begreundung: $begruendung";
  $txt .= "\n\n";
  $txt .= "Mit freundlichen Gruessen";
  $txt .= "\n";
  $txt .= "Motorfluggruppe Chur";

  $headers = "From: noreply@mfgc.ch";

  mail ($to, $subject, $txt, $headers);
}

?>



