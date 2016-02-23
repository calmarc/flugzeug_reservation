<?php

include_once 'db_connect.php';
include_once 'functions.php';

sec_session_start(); // Our custom secure way of starting a PHP session.

if (isset($_POST['email'], $_POST['password'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = hash('sha512', $_POST['password']);


    $res = $mysqli->query("SELECT `show` FROM `calmarws_test`.`captcha` WHERE `captcha`.`id` =1;");
    $obj = $res->fetch_object();
    if ($obj->show){
      if ($_SESSION['randomnr2'] != md5($_POST['captcha'])) {
        // Login failed 
        header('Location: ../index.php?error=2');
        exit();
      }
    }

    if (login($email, $password, $mysqli) == true) {
        // Login success 
        header("Location: ../../index.php");
        exit();
    } else {
        // Login failed 
        header('Location: ../index.php?error=1');
        exit();
    }
} else {
    // The correct POST variables were not sent to this page. 
    header('Location: ../error.php?err=Could not process login');
    exit();
}
