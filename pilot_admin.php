<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/functions.php');

sec_session_start();

//============================================================================
// Berechtigungen checken

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE && check_fluglehrer($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

$is_fluglehrer = FALSE;
if (check_fluglehrer($mysqli) == TRUE && check_admin($mysqli) == FALSE )
{
  $is_fluglehrer = TRUE;
}

//============================================================================
// HTML

print_html_to_body('Benutzer Administration', '');
include_once('includes/usermenu.php');

?>
  <main>
    <h1>Piloten Übersicht</h1>
    <div id="formular_innen">
      <div class="center">
<?php
$query = "SELECT * FROM `piloten` ORDER BY `pilot_nr`;";
$res = $mysqli->query($query);
?>
          <table class='vertical_table'>
            <tr>
              <th class="formular_zelle"></th>
              <th><b>Pilot-Nr</b></th>
              <th><b>Name</b></th>
              <th><b>Natel</b></th>
              <th><b>Telefon</b></th>
              <th><b>Email</b></th>
<?php if (!$is_fluglehrer) { ?>
              <th><b>Admin</b></th>
<?php } ?>
              <th><b>Lehrer</b></th>
              <th><b>Nächster<br />Checkflug</b></th>
<?php if (!$is_fluglehrer) { ?>
              <th><b>gesperrt</b></th>
<?php } ?>
            </tr>
<?php

while ($obj = $res->fetch_object())
{
  if ($obj->admin == 1)
    $admin_txt = "ja";
  else
    $admin_txt = "nein";

  if ($obj->fluglehrer == 1)
    $fluglehrer_txt = "ja";
  else
    $fluglehrer_txt = "nein";

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

  // supress admin und gesperrt column if only fluglehrer
  $admin_tab = "<td>{$admin_txt}</td>";
  $gesperrt_tab = "<td>{$gesperrt_txt}</td>";

  if ($is_fluglehrer)
  {
    $admin_tab = "";
    $gesperrt_tab = "";
  }

  echo "\n<tr>
           <td><a href='pilot_edit.php?id={$obj->id}'>[edit]</a></td>
           <td style='text-align: center;'>".str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT)."</td>
           <td>{$obj->name}</td>
           <td><span style='white-space: nowrap;'>{$obj->natel}</span></td>
           <td><span style='white-space: nowrap;'>{$obj->telefon}</span></td>
           <td>{$obj->email}</td>{$admin_tab}<td>{$fluglehrer_txt}</td>
           <td style='$check_style'>{$checkflug_ch}</td>{$gesperrt_tab}";
  echo "</tr>";
}
?>
          </table>
          <div style="text-align: left; margin-left: 5em;">
            <p><a href="login/register.php"><span class="formular_zelle">+ neuen Piloten hinzufügen</span></a></p>
          </div>
        </div>
    </div>
  </main>
</body>
</html>
