<?php

include_once 'psl-config.php';   // Needed because functions.php is not included

$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
if ($mysqli->connect_error) 
{
    header("Location: /reservationen/login/error.php?err=Unable to connect to MySQL");
    exit();
}

date_default_timezone_set('UTC');
