<?php

//============================================================================
// Datenbank login und settings

include_once 'psl-config.php';

//============================================================================
// sicherer Session start

function sec_session_start()
{
  $session_name = 'sec_session_id';   // Set a custom session name
  $secure = SECURE;

  // This stops JavaScript being able to access the session id.
  $httponly = true;

  // Forces sessions to only use cookies.
  if (ini_set('session.use_only_cookies', 1) === FALSE) {
      header("Location: /reservationen/login/error.php?err=Could not initiate a safe session (ini_set)");
      exit();
  }

  // Gets current cookies params.
  $cookieParams = session_get_cookie_params();
  session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);

  // Sets the session name to the one set above.
  session_name($session_name);

  session_start();            // Start the PHP session
  session_regenerate_id();    // regenerated the session, delete the old one.
}

function login($pilot_nr, $password, $mysqli)
{
  // Using prepared statements means that SQL injection is not possible.
  if ($stmt = $mysqli->prepare("SELECT id, pilot_nr, password, salt FROM piloten WHERE pilot_nr = ? LIMIT 1"))
  {
    $stmt->bind_param('s', $pilot_nr);  // Bind "$email" to parameter.
    $stmt->execute();    // Execute the prepared query.
    $stmt->store_result();

    // get variables from result.
    $stmt->bind_result($user_id, $pilot_nr, $db_password, $salt);
    $stmt->fetch();

    $password = hash('sha512', $password . $salt);

    if ($stmt->num_rows == 1)
    {
      // more than 5 bad logins.. turn on captcha  - else turn off
      checkbrute($mysqli);

      // Check if the password in the database matches
      // the password the user submitted.
      if ($db_password == $password)
      {
          // Password is correct!
          // Get the user-agent string of the user.
          $user_browser = $_SERVER['HTTP_USER_AGENT'];

          // XSS protection as we might print this value
          $user_id = preg_replace("/[^0-9]+/", "", $user_id);
          $_SESSION['user_id'] = $user_id;

          // XSS protection as we might print this value
          $pilot_nr = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $pilot_nr);

          $_SESSION['pilot_nr'] = $pilot_nr;
          $_SESSION['login_string'] = hash('sha512', $password . $user_browser);

          // Login successful.
          return true;
      }
      else
      {
        // Password is not correct
        // We record this attempt in the database
        $now = time();
        if (!$mysqli->query("INSERT INTO login_attempts(user_id, time)
                        VALUES ('$user_id', '$now')"))
        {
          header("Location: /reservationen/login/error.php?err=Database error: login_attempts");
          exit();
        }

      return false;
      }
    }
    else
    {
      // No user exists.
      return false;
    }
  }
  else
  {
      // Could not create a prepared statement
      header("Location: /reservationen/login/error.php?err=Database error: cannot prepare statement");
      exit();
  }
}

function checkbrute($mysqli)
{
  // Get timestamp of current time
  $now = time();

  // cleaning up last failed attempts (60 mins)
  $delete_old = $now - (60 * 60);

  mysqli_prepare_execute($mysqli, "DELETE FROM `mfgcadmin_reservationen`.`login_attempts` WHERE `login_attempts`.`time` < ?;", 'i', array ($delete_old));

  // All login attempts are counted from the past 1 minutes
  $valid_attempts = $now - (2 * 60);

  if ($stmt = $mysqli->prepare("SELECT time FROM login_attempts WHERE time > '{$valid_attempts}'"))
  {
    // Execute the prepared query.
    $stmt->execute();
    $stmt->store_result();

    // If there have been more than 5 failed logins in the last minute
    if ($stmt->num_rows > 5)
    {
        $mysqli->query("UPDATE `mfgcadmin_reservationen`.`captcha` SET `show` = '1' WHERE `captcha`.`id` =1;");
        return;
    }
    else
    {
        // no
        $mysqli->query("UPDATE `mfgcadmin_reservationen`.`captcha` SET `show` = '0' WHERE `captcha`.`id` =1;");
        return;
    }
  }
  else
  {
    // Could not create a prepared statement
    header("Location: /reservationen/login/error.php?err=Database error: cannot prepare statement");
    exit();
  }
}

//============================================================================
// ist man immer noch eingeloggt?

function login_check($mysqli)
{
  // Check if all session variables are set
  if (isset($_SESSION['user_id'], $_SESSION['pilot_nr'], $_SESSION['login_string']))
  {
    $user_id = $_SESSION['user_id'];
    $login_string = $_SESSION['login_string'];
    $pilot_nr = $_SESSION['pilot_nr'];

    // Get the user-agent string of the user.
    $user_browser = $_SERVER['HTTP_USER_AGENT'];

    if ($stmt = $mysqli->prepare("SELECT password FROM piloten WHERE id = ? LIMIT 1"))
    {
      // Bind "$user_id" to parameter.
      $stmt->bind_param('i', $user_id);
      $stmt->execute();   // Execute the prepared query.
      $stmt->store_result();

      if ($stmt->num_rows == 1)
      {
        // If the user exists get variables from result.
        $stmt->bind_result($password);
        $stmt->fetch();
        $login_check = hash('sha512', $password . $user_browser);

        if ($login_check == $login_string)
        {
            // Logged In!!!!
            return true;
        }
        else
        {
            // Not logged in
            return false;
        }
      }
      else
      {
          // Not logged in
          return false;
      }
    }
    else
    {
      // Could not prepare statement
      header("Location: /reservationen/login/error.php?err=Database error: cannot prepare statement");
      exit();
    }
  }
  else
  {
    // Not logged in
    return false;
  }
}

function check_admin($mysqli)
{
  $query = "SELECT `admin` from `piloten` where `id` = {$_SESSION['user_id']} LIMIT 1;";
  $res = $mysqli->query($query);
  $obj = $res->fetch_object();
  return $obj->admin;
}
function check_gesperrt($mysqli)
{
  $query = "SELECT `gesperrt` from `piloten` where `id` = {$_SESSION['user_id']} LIMIT 1;";
  $res = $mysqli->query($query);
  $obj = $res->fetch_object();
  return $obj->gesperrt;
}

?>
