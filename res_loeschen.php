<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');
include_once ('includes/send_sms.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

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

$action = ""; if (isset($_GET['action'])) $action = $_GET['action']; 
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
  // teilloeschung
  if ($_POST['submit'] == "Teillöschung")
  {
    echo "Teileloeschung";
  }
  else if (isset($_POST['action'], $_POST['reservierung']) && $_POST['action'] == 'del' && intval($_POST['reservierung']) > 0 )
  {
    // entry must be owned by this logged-in user && must be in the future still..
    // OR admin...
    // bigger than next half-hour rounded up
    
    if (check_admin($mysqli))
      $query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = ".$_POST['reservierung']." LIMIT 1;";
    else
      $query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = ".$_POST['reservierung']." AND `userid` = '".$_SESSION['user_id']."' AND `bis` > '$curdate' LIMIT 1;";

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
    
    $res2 = $mysqli->query("SELECT `fliegerid` from `reservationen` WHERE `id` = ".$_POST['reservierung'].";");
    $obj2 = $res2->fetch_object();
    $flieger_id = $obj2->fliegerid;

    $valid_0_pre = get_valid_reserv($mysqli, $flieger_id);
    
    // loeschen
    if (strtotime($obj->von) >= $curstamp || strtotime($obj->bis) <= $curstamp)
    {
      $id_tmp = intval($_POST['reservierung']);

      $begruendung = ""; if (isset($_POST['begruendung'])) $begruendung = $_POST['begruendung'];

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
	  NULL , NULL, '".$obj->userid."', '".$obj->fliegerid."', '".$obj->von."', '".$obj->bis."', '".$_SESSION['user_id']."', '".$begruendung."'
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
    // hat in der vergangenheit angefanne.. -> trimmen
    else
    {
      /// START ZURICH
      date_default_timezone_set("Europe/Zurich");
      $tmp_hour = date("G", $curstamp); // stunden ohne nullen

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

      date_default_timezone_set('UTC');
      /// END ZURICH UTC again

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
    }

    // send sms when a standby is green now.
    $valid_0_after = get_valid_reserv($mysqli, $flieger_id);

    $new_0 = array_diff($valid_0_after, $valid_0_pre);
    if (count($new_0) > 0)
    {
        foreach ($new_0 as $res_id) 
        {
          $res3 = $mysqli->query("SELECT * FROM `members`
                                 JOIN `reservationen` ON `members`.`id` = `reservationen`.`userid`
                                 WHERE `reservationen`.`id` =".$res_id." LIMIT 1;");

          $obj3 = $res3->fetch_object();
          $natel = $obj3->natel;
          $email = $obj3->email;
          $pilot = $obj3->name;
          $res_von = $obj3->von;
          $res_vin = $obj3->bis;

          $res_datum = mysql2chtimef($obj3, TRUE);
          $res4 = $mysqli->query("SELECT * FROM `flieger` WHERE `id` = ".$obj3->fliegerid." ;");
          $obj4 = $res4->fetch_object();
          $flieger = $obj4->flieger;
          $headers = "From: noreply@mfgc.ch";

          $txt = "Deine Reservierung:\n\nPilot: $pilot\nFlieger: $flieger\nDatum: $res_datum\n\nist nun gueltig!";

          mail ($email, "MFGC Reservierung vom $res_datum gültig!", $txt, $headers);

          // TODO uncomment...
          // TODO uncomment...
          // TODO uncomment...
          // send sms.
          // $ret_val = sendsms($natel, $txt);
        }
    }

    $tag = ""; if (isset($_POST['tag'])) $tag = $_POST['tag']; 
    $monat = ""; if (isset($_POST['monat'])) $monat = $_POST['monat']; 
    $jahr = ""; if (isset($_POST['jahr'])) $jahr = $_POST['jahr']; 

    //TODO woher soll post'fliegerid is da wenn von reservieren.php oder?
    if (isset($_POST['backto'], $_POST['flieger_id']) && $_POST['backto'] == "reservieren.php")
    {
       header("Location: /reservationen/reservieren.php?tag=$tag&monat=$monat&jahr=$jahr&flieger_id=".$_POST['flieger_id']);
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Reservierung loeschen</title>
  <meta name="title" content="Reservierung loeschen">
  <meta name="keywords" content="Reservierung loeschen">
  <meta name="description" content="Reservierung loeschen">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="mfgc.ch">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/css/reservationen.css">
</head>

<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<body>
<?php include_once('includes/usermenu.php'); ?>

  <main>
    <div id="formular_innen">
<?php

echo "<h1>$h1</h1>";

$query = "SELECT * FROM `reservationen` 
          LEFT JOIN `members` ON `members`.`id` = `reservationen`.`userid`
          WHERE `reservationen`.`id` = $reservierung
          LIMIT 1";

$res = $mysqli->query($query);
$obj = $res->fetch_object();

$flugzeug = $obj->fliegerid;
$res2 = $mysqli->query("SELECT `flieger` FROM `flieger` where `id` = $flugzeug;");
$obj2 = $res2->fetch_object();
$flugzeug = $obj2->flieger;

?>
    <div class="center">
      <table class="user_admin">
        <tr class="trblank">
          <td><b>Pilot:</b></td>
          <td><?php echo "[".str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT)."] ".$obj->name; ?></td>
        </tr>
        <?php if ($obj->telefon != "") {?>
        <tr class="trblank">
          <td><b>Telefon:</b></td>
          <td><?php echo $obj->telefon; ?></td>
        </tr>
        <?php } ?>
        <tr class="trblank">
          <td><b>Natel:</b></td>
          <td><?php echo $obj->natel; ?></td>
        </tr>
        <tr class="trblank">
          <td><b>Email:</b></td>
          <td><?php echo $obj->email; ?></td>
        </tr>
        <tr class="trblank">
          <td><b>Flugzeug:</b></td>
          <td><?php echo $flugzeug; ?></td>
        </tr>
        <tr class="trblank">
          <td><b>Buchungs-Zeit:</b></td>
          <td><?php echo mysql2chtimef($obj, FALSE); ?></td>
        </tr>
      </table>
    </div>
<!-- <p>Hinweis: Es ist nicht möglich Reservierungen für bereits vergangene Tage zu löschen.</p> -->

<h3><?php echo $h3; ?></h3>
      <form <?php echo $chars_java; ?> action="res_loeschen.php" method="post">
        <input type="hidden" name="action" value='<?php echo $action; ?>' />
        <input type="hidden" name="reservierung" value='<?php echo $reservierung; ?>' />
        <input type="hidden" name="backto" value='<?php echo $backto; ?>' />
        <input type="hidden" name="tag" value='<?php echo $tag; ?>' />
        <input type="hidden" name="monat" value='<?php echo $monat; ?>' />
        <input type="hidden" name="jahr" value='<?php echo $jahr; ?>' />
<?php 

if (!$trimmen)
{ ?>
<textarea id="texta" title="3 characters minimum" style="width: 80%" <?php echo $required; ?> name="begruendung"></textarea>
<?php } ?>
<input class="submit_button" style="margin-top: 20px;" type='submit' name='submit' value='<?php echo $button; ?>' />
</form>

<br />
<hr />
<h1>Teillöschung</h1>

<?php

$res = $mysqli->query("SELECT * FROM `reservationen` WHERE `id` = $reservierung;");
$obj = $res->fetch_object();
$von = $obj->von;
$bis = $obj->bis;

list ($datum, $zeit) =  explode(" ", $obj->von, 2);
list ($von_jahr, $von_monat, $von_tag) = explode("-", $datum, 3);
list ($von_stunde, $von_minute) = explode(":", $zeit, 3);

$datum_v = $datum;

list ($datum, $zeit) =  explode(" ", $obj->bis, 2);
list ($bis_jahr, $bis_monat, $bis_tag) = explode("-", $datum, 3);
list ($bis_stunde, $bis_minute) = explode(":", $zeit, 3);

$show_2_datum = TRUE;
if ($datum_v ==  $datum)
  $show_2_datum = FALSE;

?>

<form action="res_loeschen.php" method="post">
        <input type="hidden" name="action" value='teilloeschung' />
        <input type="hidden" name="reservierung" value='<?php echo $reservierung; ?>' />
        <input type="hidden" name="backto" value='<?php echo $backto; ?>' />
        <input type="hidden" name="tag" value='<?php echo $tag; ?>' />
        <input type="hidden" name="monat" value='<?php echo $monat; ?>' />
        <input type="hidden" name="jahr" value='<?php echo $jahr; ?>' />

<div class="center">
      <table class="user_admin">
<?php
if ($show_2_datum)
{ ?>

        <tr>
          <td><b>Datum von:</b></td>
          <td>
            <select size="1" name="von_tag" style="width: 46px;">
              <?php combobox_tag($von_tag); ?>
            </select> <b>.</b> 
            <select size="1" name="von_monat" style="width: 46px;">
              <?php combobox_monat($von_monat); ?>
            </select> <b>.</b> 
            <select size="1" name="von_jahr" style="width: 86px;">
              <?php combobox_jahr($von_jahr); ?>
            </select>
          </td>
        </tr>
<?php 
}
else
{ ?>
        <tr class="trblank">
          <td><b>Datum:</b></td>
          <td>
            <?php echo "$von_tag.$von_monat.$von_jahr"; ?>
          </td>
        </tr>

<?php 
}
?>
        <tr>
          <td><b>Zeit von:</b></td>
          <td>
            <select size="1" name="von_stunde" style="width: 46px;">
              <?php combobox_stunde($von_stunde); ?>
            </select> <b>:</b>
            <select size="1" name="von_minuten" style="width: 46px;">
              <?php combobox_minute($von_minute); ?>
            </select> <b>Uhr</b>
          </td>
        </tr>
<?php 
if ($show_2_datum)
  { ?>
        <tr>
          <td><b>Datum bis:</b></td>
          <td>
            <select size="1" name="bis_tag" style="width: 46px;">
              <?php combobox_tag($bis_tag); ?>
            </select> <b>.</b> 
            <select size="1" name="bis_monat" style="width: 46px;">
              <?php combobox_monat($bis_monat); ?>
            </select> <b>.</b> 
            <select size="1" name="bis_jahr" style="width: 86px;">
              <?php combobox_jahr($bis_jahr); ?>
            </select>
          </td>
        </tr>
<?php } ?>
        <tr>
          <td><b>Zeit bis:</b></td>
          <td>
            <select size="1" name="bis_stunde" style="width: 46px;">
              <?php combobox_stunde($bis_stunde); ?>
            </select> <b>:</b>
            <select size="1" name="bis_minuten" style="width: 46px;">
              <?php combobox_minute($bis_minute); ?>
            </select> <b>Uhr</b>
          </td>
        </tr>
      </table>
<h3><?php echo $h3; ?></h3>
<textarea id="texta2" title="3 characters minimum" style="width: 80%" <?php echo $required; ?> name="begruendung"></textarea>
<input class="submit_button" style="margin-top: 20px;" type='submit' name='submit' value="Teillöschung"  />
</div>
  
</form>
</div>
  </main>
</body>
</html>
