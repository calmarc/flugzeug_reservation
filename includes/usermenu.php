<?php

if (isset($_SESSION['username']))
{
  // check if admin rights
  $query = "SELECT `admin`, `username` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  $admin = "";
  if ($obj->admin == TRUE)
    $admin = '| <a href="/reservationen/user_admin.php">Admin</a> ';
?>

  <nav>
    <div style="float: right;">[ <a href= "/reservationen/login/includes/logout.php">ausloggen</a> ]</div>
    <div><b><?php echo htmlentities($obj->username); ?></b>: [ <a href="/reservationen/index.php">Überblick</a> | <a href="/reservationen/reservieren.php">Reservieren</a> | <a href="/reservationen/pass_change.php">Passwort ändern</a> <?php echo $admin; ?> | <a target="_blank" href="http://www.mfgc.ch/">mfgc.ch</a> ]</div> 
  </nav>
<?php
}
else
{
?>

  <nav>
    <div>Du bist nicht eingeloggt! [<a href="/reservationen/login/index.php">einloggen</a> / <a href="/reservationen/login/register.php">registrieren</a>]</div>
  </nav>

<?php
}
