<?php

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

//============================================================================
// Berechtigungen checken

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

//----------------------------------------------------------------------------

print_html_to_body('Benutzer Administration', ''); 
include_once('includes/usermenu.php'); 

?>
  <main>
    <div id="formular_innen">
      <div class="center">
        <h1>Piloten Übersicht</h1>
<?php
$query = "SELECT * FROM `members` ORDER BY `pilotid`;";
$res = $mysqli->query($query); 
?>
          <table class='vertical_table'>
            <tr>
              <th style="background-color: #99ff99;"></th>
              <th><b>Pilot-ID</b></th>
              <th><b>Name</b></th>
              <th><b>Natel</b></th>
              <th><b>Telefon</b></th>
              <th><b>Email</b></th>
              <th><b>Admin</b></th>
              <th><b>Checkflug</b></th>
              <th><b>gesperrt</b></th>
            </tr>
<?php
while ($obj = $res->fetch_object())
{
  if ($obj->admin == 1)
    $admin_txt = "ja";
  else
    $admin_txt = "nein";

  if ($obj->gesperrt == 1)
    $gesperrt_txt = "ja";
  else
    $gesperrt_txt = "nein";


//============================================================================
// Rot machen, wenn checkflug noetig und noch nicht gesendet

  $check_style = "";
  if(!($obj->checkflug > date('Y-m-d', time()) || $obj->checkflug == "0000-00-00") && !$obj->gesperrt )
    $check_style="background-color: #ffdddd; color: red;";

//----------------------------------------------------------------------------

  $checkflug_ch = shortsql2ch_date($obj->checkflug);
    
  echo "\n<tr>
           <td><a href='pilot_edit.php?id=".$obj->id."'><img alt='editieren' src='/reservationen/bilder/edit.png' /></a></td>
           <td style='text-align: center;'>".str_pad($obj->pilotid, 3, "0", STR_PAD_LEFT)."</td>
           <td>".$obj->name."</td>
           <td><span style='white-space: nowrap;'>".$obj->natel."</span></td>
           <td><span style='white-space: nowrap;'>".$obj->telefon."</span></td>
           <td>".$obj->email."</td><td>".$admin_txt."</td>
           <td style='$check_style'>".$checkflug_ch."</td><td>".$gesperrt_txt."</td>";
  echo "</tr>";
}
?>
          </table>
          <div style="text-align: left; margin-left: 6em;">
            <p>&nbsp; &nbsp;<a href="login/register.php"><span style="background-color: #99ff99;">+ neuen Piloten hinzufügen</span></a></p>
          </div>
        </div>
    </div>
  </main>
</body>
</html>
