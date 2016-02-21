<?php

if (isset($_SESSION['username']))
{
?>

  <div id="usertop">
    <p><?php echo htmlentities($_SESSION['username']); ?>:
    [<a href="/reservationen/index.php">Reservationen</a> |
    <a href="/reservationen/user_admin.php">Admin</a> | <a href=
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
