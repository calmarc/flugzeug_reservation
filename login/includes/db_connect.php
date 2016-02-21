<?php

/* 
 * Copyright (C) 2013 peredur.net
 *
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

include_once 'psl-config.php';   // Needed because functions.php is not included

$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
if ($mysqli->connect_error) {
    header("Location: ../error.php?err=Unable to connect to MySQL");
    exit();
}
