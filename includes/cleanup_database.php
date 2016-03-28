<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('db_connect.php');
include_once ('user_functions.php');
include_once ('functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }

//============================================================================
// Protokoll sachen aufrauemen/leoschen

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{

  if (isset($_POST['res_teilgeloescht']) && intval($_POST['teil_loeschen_val']) > 60)
  {
      $table = 'reser_getrimmt';
      $days = intval($_POST['teil_loeschen_val']) * 60 * 60 * 24;
      $head_to = "res_teilgeloescht.php";
  }
  else if (isset($_POST['res_geloescht']) && intval($_POST['loeschen_val']) > 60)
  {
      $table = 'reser_geloescht';
      $days = intval($_POST['loeschen_val']) * 60 * 60 * 24;
      $head_to = "res_geloescht.php";
  }
  else if (isset($_POST['reservationen']))
  {
      $table = 'reservationen';
      $days = 2 * 365 * 60 * 60 * 24;
      $head_to = "res_momentan.php";
  }
  else if (isset($_POST['protokoll']))
  {
      $table = 'status_meldungen';
      $days = 549 * 60 * 60 * 24;
      $head_to = "protokoll.php";
  }
  else
  {
    header("Location: /reservationen/diverses.php");
    exit;
  }


  // get the date x days before now and delete before that.
  $time_stamp = time();
  $time_stamp = $time_stamp - $days; // tage

  // timestamp in der datenbank ist auch lokal
  date_default_timezone_set("Europe/Zurich");
  $delete_date = date("Y-m-d H:i:s", $time_stamp);
  date_default_timezone_set('UTC');

  // not needed but anyway (hopefully that is)
  // adds 60 days and must be <= than now.
  if ((strtotime($delete_date) + 60 * 60 * 60 * 24) > time())
  {
    echo "<h1>Grosser Fehler ist aufgetreten - bitte mac@calmar.ws melden.</h1>";
    exit;
  }

  $query = "DELETE FROM `{$table}` WHERE `{$table}`.`timestamp` < ?";
  mysqli_prepare_execute($mysqli, $query, 's', array($delete_date));

  header("Location: /reservationen/{$head_to}");
  exit;
}

?>
