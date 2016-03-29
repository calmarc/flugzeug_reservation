<?php

include_once 'psl-config.php';   // Needed because functions.php is not included

$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
if ($mysqli->connect_error)
{
    header("Location: /reservationen/login/error.php?err=Unable to connect to MySQL");
    exit();
}

if (!$mysqli->set_charset("utf8"))
{
  printf("Error loading character set utf8: %s\n", $mysqli->error);
  exit();
}

date_default_timezone_set('UTC');
