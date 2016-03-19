<?php

/*
 * Copyright (C) 2013 peredur.net
 * see <http://www.gnu.org/licenses/>.
 */

include_once ('../includes/db_connect.php');
include_once ('../includes/functions.php');
sec_session_start();

$pilot_id_pad = str_pad($_SESSION['pilot_id'], 3, "0", STR_PAD_LEFT);
write_status_message($mysqli, "[Ausgeloggt]", "[{$pilot_id_pad}] {$_SESSION['name']}");

// Unset all session values
$_SESSION = array();

// get session parameters
$params = session_get_cookie_params();

// Delete the actual cookie.
setcookie(session_name(),'', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);

// Destroy session
session_destroy();

header("Location: /reservationen/login/index.php");
exit;

