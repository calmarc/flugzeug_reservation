<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0">
  <title>Flieger-Reservationen</title>
  <meta name="title" content="Flieger-Reservationen">
  <meta name="keywords" content="Reservierungs-System">
  <meta name="description" content="Reservierungs-System">
  <meta name="generator" content="Calmar + Vim + Tidy">
  <meta name="owner" content="calmar.ws">
  <meta name="author" content="candrian.org">
  <meta name="robots" content="all">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/reservationen/reservationen.css">
</head>

<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<?php if (login_check($mysqli) == true) : ?>

<body>

  <?php require('includes/usermenu.php'); ?>

<main>
<h1>MFGC Flieger-Reservationen</h1>


<?php 
include_once ('kalender/include.php');

$datum = get_date();
$tag = $datum[0];
$monat = $datum[1];
$jahr = $datum[2];

echo '<div style="float: right; margin-right: 30px;">';
echo draw_calendar($tag, $monat, $jahr);
echo '</div>';
?>

</main>
</body>
</html>

<?php else :
header("Location: /reservationen/login/index.php");
exit;
endif; ?>
