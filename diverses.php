<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }

print_html_to_body('Diverse Einstellungen', '');
include_once('includes/usermenu.php');

?>
  <main>
    <h1>Einstellungen</h1>
    <div id="formular_innen">
      <div class="center">
          <table class='vertical_table'>
          <tr>
          <th class="formular_zelle"></th>
          <th><b>Aktion</b></th>
            <th><b>Daten1</b></th>
            <th><b>Daten2</b></th>
          </tr>

<?php

$query = "SELECT * FROM `diverses` ORDER BY `funktion` ASC;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  echo "\n<tr>
           <td><a href='diverses_edit.php?id={$obj->id}'>[edit]</a></td>
           <td style='text-align: left; background-color: transparent; color: #333333; font-weight: bold;'>{$obj->funktion}</td>
           <td>{$obj->data1}</td>
           <td>{$obj->data2}</td>
        </tr>";
}
?>
          <tr>
            <td colspan="4">&nbsp;</td>
          </tr>
      <tr>
      <td colspan="4" style="text-align: left;">
          <form action='includes/cleanup_database.php' method='post'>
            <input class="submit_button_small" type="submit" name="res_geloescht" value="löschen" /> &nbsp; &nbsp; <b>Gelöschte</b> Reservationen älter als
             <select style="width: 8em;" name='loeschen_val' size="1">
              <option value="">&nbsp;</option>
              <option value="365">12 Monate</option>
              <option value="90">&nbsp;3 Monate</option>
            </select>
          </form>
      </td>
      </tr>
      <tr>
      <td colspan="4" style="text-align: left;">
          <form action='includes/cleanup_database.php' method='post'>
            <input class="submit_button_small" type="submit" name="res_teilgeloescht" value="löschen" /> &nbsp; &nbsp; <b>Teil</b>-Gelöschte Reservationen älter als
             <select style="width: 8em;" name='teil_loeschen_val' size="1">
              <option value="">&nbsp;</option>
              <option value="365">12 Monate</option>
              <option value="90">&nbsp;3 Monate</option>
            </select>
          </form>
      </td>
      </tr>
      <tr>
        <td colspan="4" style="text-align: left;">
          <form style="display: inline-block;" action='includes/cleanup_database.php' method='post'>
          <input class="submit_button_small" type="submit" name="reservationen" value="löschen" /> &nbsp; &nbsp; <b>Reservationen</b> älter als 2 Jahre
          </form>
        </td>
      </tr>
      <tr>
        <td colspan="4" style="text-align: left;">
          <form style="display: inline-block;" action='includes/cleanup_database.php' method='post'>
          <input class="submit_button_small" type="submit" name="protokoll" value="löschen" /> &nbsp; &nbsp; Protokoll-Einträge älter als 1.5 Jahre
          </form>
        </td>
      </tr>
    </table>
    </div>
    </div>
  </main>
</body>
</html>
