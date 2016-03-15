<?php if (isset($_SESSION['pilotid'])) {

  // highlight... the active links
  $logout = "";
  $index = ""; 
  $index_t = ""; 
  $index_m = ""; 
  $pass_change = "";
  $user_admin = "";
  $res_admin = "";
  $res_trimmed = "";
  $res_moment = "";
  
  $style = 'style="color: yellow; font-weight: bold;"'; $curr_file = $_SERVER['PHP_SELF']; 

  // switches between plan-view.
  // will switch when pressed the menu.. only - else it should deliver the same

  // default
  if (!isset($_SESSION['show']))
    $_SESSION['show'] = 'tag';

  // switch if there is a GET
  if (isset($_GET['show']) && $_GET['show'] == 'monat')
    $_SESSION['show'] = 'monat';
  else if (isset($_GET['show']) && $_GET['show'] == 'tag')
    $_SESSION['show'] = 'tag';

  if ($curr_file == "/reservationen/index.php")
  {
    if ($_SESSION['show'] == 'monat')
      $index_m = $style;
    else
      $index_t = $style;
  }
  else if ($curr_file == "/reservationen/pass_change.php")
    $pass_change = $style;
  else if ($curr_file == "/reservationen/user_admin.php")
    $user_admin = $style;
  else if ($curr_file == "/reservationen/user_edit.php")
    $user_admin = $style;
  else if ($curr_file == "/reservationen/res_admin.php")
    $res_admin = $style;
  else if ($curr_file == "/reservationen/res_trimmed.php")
    $res_trimmed = $style;
  else if ($curr_file == "reservationen/res_momentan.php")
    $res_moment = $style;

  // check if admin rights
  $query = "SELECT `pilotid`, `name`, `admin`, `gesperrt` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query); 
  $obj = $res->fetch_object();
  $admin = "";
  if ($obj->admin == TRUE && $obj->gesperrt == FALSE)
    $admin = '<span style="white-space: nowrap;">[ <a '.$user_admin.' href="/reservationen/user_admin.php"><span style="color: #ff3333;">Piloten</span></a> | <a '.$res_moment.' href="/reservationen/res_momentan.php"><span style="color: #ff3333;">Reservationen</span></a> | <a '.$res_admin.' href="/reservationen/res_admin.php"><span style="color: #ff3333;">Gelöscht</span></a></span> <span style="white-space: nowrap;"> | <a '.$res_trimmed.' href="/reservationen/res_trimmed.php"><span style="color: #ff3333;">Teil-gelöscht</span></a> ]</span>';

  $_SESSION['name'] = htmlentities($obj->name);

  $gesperrt = "";
  if ($obj->gesperrt == TRUE)
  {
    $gesperrt = "<span style='color: red; font-weight: bold; background-color: yellow;'>(Flüge gesperrt. Bitte nachfragen!)</span>";
  }

?>
<nav>
  <div style="float: right;"> 
  <?php echo '['.str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT).'] <b>'.htmlentities($obj->name).'</b>'; ?> 
  <?php echo $gesperrt; ?>
  : <a <?php echo $logout; ?> href= "/reservationen/login/logout.php">ausloggen</a></div> 
  <div><span style="white-space: nowrap;">[ <a <?php echo $index_t; ?> href="/reservationen/index.php?show=tag">Tagesplan</a> 
  | <a <?php echo $index_m; ?> href="/reservationen/index.php?show=monat">Monatsplan</a></span> <span style="white-space: nowrap;"> 
  | <a <?php echo $pass_change; ?> href="/reservationen/pass_change.php">Passwort ändern</a> 
  | <a href="http://www.ics.li/cfdocs/flugplragaz/admin/bewegungen.cfm">Startliste Flugplatz</a> ]</span> <?php echo $admin; ?> 
  </div> </nav> 

  <?php } else { ?> <nav> <div>Du bist nicht eingeloggt! [<a href="/reservationen/login/index.php">einloggen</a>]
  </div> </nav> 
<?php
}
