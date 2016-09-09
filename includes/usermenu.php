<?php

if (isset($_SESSION['pilot_nr']))
{
  // highlight... the active links
  $logout =  $index = $index_t = $index_m = $pass_change = $pilot_admin = $res_geloescht = $res_teilgeloescht = $res_moment = $protokoll = $diverses = "";

  $curr_file = $_SERVER['PHP_SELF'];

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
      $index_m = "class='menu_text_selected'";
    else
      $index_t = "class='menu_text_selected'";
  }
  else if ($curr_file == "/reservationen/pass_change.php")
    $pass_change = "class='menu_image_selected'";
  else if ($curr_file == "/reservationen/pilot_admin.php")
    $pilot_admin = "class='menu_image_selected'";
  else if ($curr_file == "/reservationen/pilot_edit.php")
    $pilot_admin = "class='menu_image_selected'";
  else if ($curr_file == "/reservationen/res_geloescht.php")
    $res_geloescht = "class='menu_image_selected'";
  else if ($curr_file == "/reservationen/res_teilgeloescht.php")
    $res_teilgeloescht = "class='menu_image_selected'";
  else if ($curr_file == "/reservationen/res_momentan.php")
    $res_moment = "class='menu_image_selected'";
  else if ($curr_file == "/reservationen/protokoll.php")
    $protokoll = "class='menu_image_selected'";
  else if ($curr_file == "/reservationen/diverses.php")
    $diverses = "class='menu_image_selected'";

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
      <a href='/reservationen/protokoll.php'><img {$protokoll} src='/reservationen/bilder/log.png' alt='Protokoll' /></a>
      <a href='/reservationen/pilot_admin.php'><img {$pilot_admin} src='/reservationen/bilder/pilot.png' alt='Piloten' /></a>
      <a href='/reservationen/diverses.php'><img {$diverses} src='/reservationen/bilder/diverses.png' alt='Diverses' /></a> ]
</span>";

  $fluglehrer_bol = check_fluglehrer($mysqli);
  if ($obj->admin == FALSE && $fluglehrer_bol == TRUE)
  $admin = "
<span style='white-space: nowrap;'>[
    <a href='/reservationen/pilot_admin.php'><img {$pilot_admin} src='/reservationen/bilder/pilot.png' alt='Piloten' /></a>&nbsp;]
</span>";

  $_SESSION['name'] = $obj->name;

  // todo evt immer von hier nehmen?
  date_default_timezone_set("Europe/Zurich");
  $heute = date("d.m.Y.j.n", time());
  date_default_timezone_set('UTC');

  list ($tag, $monat, $jahr, $tag_single, $monat_single) = explode (".", $heute);

  $heute_link = "<a href='/reservationen/index.php?tag={$tag_single}&monat={$monat_single}&jahr={$jahr}'>{$tag}.{$monat}.{$jahr}<img src='/reservationen/bilder/today.png' alt='heute' /></a>";

  $gesperrt = "";
  if ($obj->gesperrt == TRUE)
  {
    $gesperrt = "<span style='color: red; font-weight: bold; background-color: yellow;'>(Gesperrt - Vorstand kontaktieren!)</span>";
  }

  // menu ausgeben:
?>
<nav>
  <div class="user_menu">[<?php echo $heute_link; ?>]
    <a title="mfgc.ch" href="http://www.mfgc.ch/"><img id="mfgc_nav_logo" src="/reservationen/bilder/mfgc_icon.png" alt="mfgc.ch" /></a>
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
