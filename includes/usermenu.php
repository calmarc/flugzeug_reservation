<?php

if (isset($_SESSION['username']))
{
  // check if admin rights
  $query = "SELECT `admin` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  $admin = "";
  if ($obj->admin == TRUE)
    $admin = '| <a href="/reservationen/user_admin.php">Admin</a> ';
?>

  <div id="usertop">
    <p><?php echo htmlentities($_SESSION['username']); ?>:
    [<a href="/reservationen/index.php">Reservationen</a> <?php echo $admin; ?> | <a href=
    "/reservationen/login/includes/logout.php">ausloggen</a>]</p>
  </div>
<?php
}
else
{
?>

  <div id="usertop">
    <p>Du bist nicht eingeloggt! [<a href="/reservationen/login/index.php">einloggen</a> / <a href="/reservationen/login/register.php">registrieren</a>]</p>
  </div>

<?php
}
