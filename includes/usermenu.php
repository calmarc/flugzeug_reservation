<?php 

if (isset($_SESSION['pilot_nr'])) 
{
  // highlight... the active links
  $logout = "";
  $index = "";
  $index_t = "";
  $index_m = "";
  $pass_change = "";
  $pilot_admin = "";
  $res_geloescht = "";
  $res_teilgeloescht = "";
  $res_moment = "";
  $protokoll = "";
  $diverses = "";

  $curr_file = $_SERVER['PHP_SELF'];

  // selected -> for text 
  $style = 'style="color: yellow; font-weight: bold;"'; 

  // selected -> for images
  $style2 = 'style="background-color: #333300; border: 2px #999900 solid;
    filter: brightness(110%);
    -webkit-filter: brightness(110%);
    -moz-filter: brightness(110%);
    -o-filter: brightness(110%);
    -ms-filter: brightness(110%);
    "';

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

  // put that style on whereever current file matches
  if ($curr_file == "/reservationen/index.php")
  {
    if ($_SESSION['show'] == 'monat')
      $index_m = $style;
    else
      $index_t = $style;
  }
  else if ($curr_file == "/reservationen/pass_change.php")
    $pass_change = $style2;
  else if ($curr_file == "/reservationen/pilot_admin.php")
    $pilot_admin = $style2;
  else if ($curr_file == "/reservationen/pilot_edit.php")
    $pilot_admin = $style2;
  else if ($curr_file == "/reservationen/res_geloescht.php")
    $res_geloescht = $style2;
  else if ($curr_file == "/reservationen/res_teilgeloescht.php")
    $res_teilgeloescht = $style2;
  else if ($curr_file == "/reservationen/res_momentan.php")
    $res_moment = $style2;
  else if ($curr_file == "/reservationen/protokoll.php")
    $protokoll = $style2;
  else if ($curr_file == "/reservationen/diverses.php")
    $diverses = $style2;

  // check if admin rights etc and prepare admin menu items
  $query = "SELECT `pilot_nr`, `name`, `admin`, `gesperrt` from `piloten` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
  $res = $mysqli->query($query);
  $obj = $res->fetch_object();
  $admin = "";
  if ($obj->admin == TRUE && $obj->gesperrt == FALSE)
    $admin = "
  <span style='white-space: nowrap;'>[
      <a href='/reservationen/res_geloescht.php'><img {$res_geloescht} src='/reservationen/bilder/reservation-geloescht.png' alt='gelöscht' /></a>
      <a href='/reservationen/res_teilgeloescht.php'><img {$res_teilgeloescht} src='/reservationen/bilder/reservation-teil.png' alt='teil-gelöscht' /></a>
      <a href='/reservationen/pilot_admin.php'><img {$pilot_admin} src='/reservationen/bilder/pilot.png' alt='Piloten' /></a>
      <a href='/reservationen/protokoll.php'><img {$protokoll} src='/reservationen/bilder/log.png' alt='Protokoll' /></a>
      <a href='/reservationen/diverses.php'><img {$diverses} src='/reservationen/bilder/diverses.png' alt='Diverses' /></a> ]
</span>";

  // TODO needed? and if yes.. also needed elsewhere (admin-edit etc)
  $_SESSION['name'] = htmlentities($obj->name);

  $gesperrt = "";
  if ($obj->gesperrt == TRUE)
  {
    $gesperrt = "<span style='color: red; font-weight: bold; background-color: yellow;'>(Gesperrt - Vorstand kontaktieren!)</span>";
  }

  // menu ausgeben: 
?>
<nav>
  <div class="user_menu">
    <?php echo '['.str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT).'] <b>'.htmlentities($obj->name).'</b>'; ?><?php echo $gesperrt; ?>
    <a title="Passwort ändern" href="/reservationen/pass_change.php"><img <?php echo $pass_change; ?> src="/reservationen/bilder/key.png" alt="Passwort ändern" /></a>
    <a title="Ausloggen" href= "/reservationen/login/logout.php"><img class="always" src="/reservationen/bilder/exit.png" alt="Ausloggen" /></a>
  </div>
  <div>
    <span style="white-space: nowrap;">
      [ <a <?php echo $index_t; ?> href="/reservationen/index.php?show=tag">Tagesplan</a>
      | <a <?php echo $index_m; ?> href="/reservationen/index.php?show=monat">Monatsplan</a>
    </span> 
    <span style='white-space: nowrap;'>
    | <a href='/reservationen/res_momentan.php'><img <?php echo $res_moment; ?> src='/reservationen/bilder/reservation.png' alt='Reservationen' /></a>
      |  <a href="http://www.ics.li/cfdocs/flugplragaz/admin/bewegungen.cfm">Startliste Flugplatz</a> ]
    </span> 
   <?php echo $admin; ?>
  </div> 
</nav>
<br style="clear: both;" />

<?php 
} 
else 
{ 
?> 
<nav> 
  <div>
    Du bist nicht eingeloggt! [<a href="/reservationen/login/index.php">einloggen</a>]
  </div> 
</nav>
<br style="clear: both;" />

<?php
}
