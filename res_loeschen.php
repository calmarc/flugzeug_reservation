<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

$curstamp = time(); // wird einige male gebraucht
// round up cur_time to half hour blocks
$curstamp = (intval($curstamp / 1800) + 1) * 1800;
$curdate = date("Y-m-d H:i:s", $curstamp);

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

$tag = ""; if (isset($_GET['tag'])) $tag = $_GET['tag']; 
$monat = ""; if (isset($_GET['monat'])) $monat = $_GET['monat']; 
$jahr = ""; if (isset($_GET['jahr'])) $jahr = $_GET['jahr']; 

if (isset($_GET['action'], $_GET['reservierung']) && $_GET['action'] == 'del' && intval($_GET['reservierung']) > 0 )
{

  // entry must be owned by this logged-in user && must be in the future still..
  // OR admin...
  // bigger than next half-hour rounded up
  if (check_admin($mysqli))
    $query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = ".$_GET['reservierung']." LIMIT 1;";
  else
    $query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = ".$_GET['reservierung']." AND `userid` = '".$_SESSION['user_id']."' AND `bis` > '$curdate' LIMIT 1;";
  $res = $mysqli->query($query);

  if ($res->num_rows < 1)
  {
    header("Location: index.php?tag=$tag&monat=$monat&jahr=$jahr");
    exit;
  }


  $obj = $res->fetch_object();

  if (strtotime($obj->von) >= $curstamp || strtotime($obj->bis) <= $curstamp)
  {
    $id_tmp = intval($_GET['reservierung']);

    // make copy into reser_geloescht
    $query = "INSERT INTO `reser_geloescht` (`timestamp`, `userid`, `fliegerid`, `von`, `bis`)
      SELECT `timestamp`, `userid`, `fliegerid`, `von`, `bis` FROM `reservationen` WHERE `id` = $id_tmp;";
    $mysqli->query($query);

    // komplett loeschen da komplett in der zukunft oder komplette in der
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
  // hat in der vergangenheit angefanne.. bis also kuerzen.
  else
  {
    
    // wenn in der nacht auf vortag 22:00 kuerzen
    $tmp_hour = date("G", $curstamp);

    $date_trimmed =  $curdate;

    if (date("G", $curstamp) < 7)
    {
      $date00 = strtotime(date("Y-m-d", $curstamp)." 00:00:00");
      $date_trimmed = date("Y-m-d H:i:s", $date00 - 2 * 60 * 60);
    }

    if ($stmt = $mysqli->prepare("UPDATE `calmarws_test`.`reservationen` SET `bis` = ? WHERE `reservationen`.`id` = ?;"))
    {
      $id_tmp = intval($_GET['reservierung']);
      $stmt->bind_param('si', $date_trimmed, $id_tmp);
      if (!$stmt->execute()) 
      {
          header('Location: /reservationen/login/error.php?err=Registration failure: UPDATE');
          exit;
      }
    }
  }

  if (isset($_GET['backto'], $_GET['flieger_id']) && $_GET['backto'] == "reservieren.php")
  {
     header("Location: /reservationen/reservieren.php?tag=$tag&monat=$monat&jahr=$jahr&flieger_id=".$_GET['flieger_id']);
  }
  else
     header("Location: index.php?tag=$tag&monat=$monat&jahr=$jahr");
}
?>
