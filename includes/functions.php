<?php

//============================================================================
// macht colorierten (raw) CH string draus zum direkt drucken.

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
      $datum = "<span style='color: #990000;'>{$vontag}</span>: <span style='color: green'><i>{$vonzeit} - {$biszeit} Uhr</i></span>";
    else
      $datum = "<span style='color: #990000;'>{$vontag}</span> <span style='color: green'><i>{$vonzeit}</i></span> - <span style='color: #990000;'>{$bistag}</span> <span style='color: green'><i>{$biszeit}</i></span>";
  }
  return $datum;
}

//============================================================================
// 2016-04-30 -> 30.04.2016 (fuer checkflug conversion
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

function mysql_stamp_to_ch($mysqli, $mysqli_stamp)
{
  list( $tag, $zeit) = explode(" ", $mysqli_stamp);
  $datum = explode("-", $tag);
  $zeit = explode(":", $zeit);
  return "{$datum[2]}.{$datum[1]}.{$datum[0]} {$zeit[0]}:{$zeit[1]}";
}

//============================================================================
// aus 2 service-zaehler: in stunden + differenz
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

// schreibt ein email wenn eintrag geloescht wurde an .. 'diverses'...
function bei_geloescht_email($mysqli, $subject_hint, $pilot_nr, $flugzeug_id, $zeit, $begruendung)
{
  $res = $mysqli->query("SELECT * from `diverses` WHERE `funktion` = 'bei_geloescht_email';");
  $obj = $res->fetch_object();
  $to = $obj->data1;

  $res = $mysqli->query("SELECT * from `piloten` WHERE `id` = $pilot_nr;");
  $obj = $res->fetch_object();
  $pilot = str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT). " (".$obj->name.")";

  $res = $mysqli->query("SELECT * from `flugzeug` WHERE `id` = $flugzeug_id;");
  $obj = $res->fetch_object();
  $flugzeug = $obj->flugzeug;

  $subject = "Reservation $subject_hint: $pilot: $zeit";
  $txt = "Bei der Motorfluggruppe Chur wurde folgende Flugzeug-Reservation {$subject_hint}:";
  $txt .= "\n\n";
  $txt .= "Pilot: {$pilot}";
  $txt .= "\n";
  $txt .= "Flugzeug: {$flugzeug}";
  $txt .= "\n";
  $txt .= "Buchungszeit: {$zeit}";
  $txt .= "\n\n";
  $txt .= "Begründung: {$begruendung}";
  $txt .= "\n\n";
  $txt .= "Mit freundlichen Grüssen";
  $txt .= "\n";
  $txt .= "Motorfluggruppe Chur";

  $headers   = array();
  $headers[] = "MIME-Version: 1.0";
  $headers[] = "Content-type: text/plain; charset=utf-8";
  $headers[] = "From: noreply@mfgc.ch";

  mail ($to, $subject, $txt, implode("\r\n",$headers));
}

function mysqli_prepare_execute ($mysqli, $query, $bind_string, $arr)
{
  if ($stmt = $mysqli->prepare($query))
  {
    $count_arr = count($arr);

    if ($count_arr == 1)
      $stmt->bind_param($bind_string, $arr[0]);
    else if ($count_arr == 2)
      $stmt->bind_param($bind_string, $arr[0], $arr[1]);
    else if ($count_arr == 3)
      $stmt->bind_param($bind_string, $arr[0], $arr[1], $arr[2]);
    else if ($count_arr == 4)
      $stmt->bind_param($bind_string, $arr[0], $arr[1], $arr[2], $arr[3]);
    else if ($count_arr == 5)
      $stmt->bind_param($bind_string, $arr[0], $arr[1], $arr[2], $arr[3], $arr[4]);
    else if ($count_arr == 6)
      $stmt->bind_param($bind_string, $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5]);
    else if ($count_arr == 7)
      $stmt->bind_param($bind_string, $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5], $arr[6]);
    else if ($count_arr == 8)
      $stmt->bind_param($bind_string, $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5], $arr[6], $arr[7]);
    else if ($count_arr == 9)
      $stmt->bind_param($bind_string, $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5], $arr[6], $arr[7], $arr[8]);
    else if ($count_arr == 10)
      $stmt->bind_param($bind_string, $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5], $arr[6], $arr[7], $arr[8], $arr[9]);

    // Execute the prepared query.
    if (!$stmt->execute())
    {
        header('Location: /reservationen/login/error.php?err=Registration failure: '.$query);
        exit;
    }
  }
  else
  {
      header('Location: /reservationen/login/error.php?err=Registration failure: prepare:'.mysqli_error($mysqli));
      exit;
  }
  return TRUE;
}

// fuer die service eintraege etc
function compute_minute_from_zaehlerstand($zaehlerstand)
{
  // 44.12 * int - 44 * 60 = minuten.
  $zaehler_minute = intval($zaehlerstand) * 60;
  // 44.12 * 100 4412 % 100 -> 12 minuten dazu (+=)
  $digit_minute = round($zaehlerstand * 100, 0, PHP_ROUND_HALF_UP) % 100;
  $zaehler_minute += $digit_minute;
  return array($zaehler_minute, $digit_minute);
}

// darf nur zwishcn x.00 und z.59 liegen
function check_zaehlerstand($zaehlerstand, $digit_minute)
{
  $error_msg = "";
  if ($digit_minute > 59)
    $error_msg = "Die Nachkommastelle (Minuten) muss zwischen 0 und 59 sein.";

  if (floatval($zaehlerstand) <= 0)
    $error_msg = "Die Zahl muss grösser als 0 sein.";

  return $error_msg;
}

function write_status_message($mysqli, $subjekt, $user_id, $data)
{
  if (!intval($user_id) > 0)
    $durch_txt = $user_id;
  else
  {
    list($pilot_nr_pad, $name) = get_pilot_from_user_id($mysqli, $user_id);
    $durch_txt = "[{$pilot_nr_pad}] {$name}";
  }

  mysqli_prepare_execute($mysqli, "INSERT INTO `status_meldungen` (`id`, `timestamp`, `aktion`, `durch`, `data`) VALUES (NULL, CURRENT_TIMESTAMP, ?, ?, ?);", 'sss', array ($subjekt, $durch_txt, $data));
}

function get_pilot_from_user_id($mysqli, $user_id)
{
  $res = $mysqli->query("SELECT `pilot_nr`, `name` FROM `piloten` WHERE `id` = $user_id LIMIT 1;");
  if ($res->num_rows > 0)
  {
    $obj = $res->fetch_object();
    $pilot_nr_pad = str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT);
    return array($pilot_nr_pad, $obj->name);
  }
    return array("unkown", "unknown");
} 

?>
