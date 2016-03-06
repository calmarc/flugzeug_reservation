<?php

if (isset($_SESSION['pilotid']))
{
  // check if admin rights
  $query = "SELECT `pilotid`, `name`, `admin`, `gesperrt` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  $admin = "";
  if ($obj->admin == TRUE && $obj->gesperrt == FALSE)
    $admin = '| <a href="/reservationen/user_admin.php">Piloten</a>  ';

  $_SESSION['name'] = htmlentities($obj->name);

  $gesperrt = "";
  if ($obj->gesperrt == TRUE)
  {
    $gesperrt = "<span style='color:red; font-weight: bold; background-color: yellow;'>(Flüge gesperrt. Bitte nachfragen!)</span>";
  }

?>
<nav>
  <div style="float: right;">[ <a href= "/reservationen/login/logout.php">ausloggen</a> ]</div>
  <div><b><?php echo '['.str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT).'] '.htmlentities($obj->name); ?></b> <?php echo $gesperrt; ?>: [ <a href="/reservationen/index.php">Tagesplan</a> | <a href="/reservationen/index.php?show=monatsplan">Monatsplan</a> | <a href="/reservationen/pass_change.php">Passwort ändern</a> <?php echo $admin; ?> | <a target="_blank" href="http://www.mfgc.ch/">mfgc.ch</a> ]</div> 
</nav>
<?php } else { ?>

<nav>
  <div>Du bist nicht eingeloggt! [<a href="/reservationen/login/index.php">einloggen</a>]</div>
</nav>

<?php
}
