<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }

print_html_to_body('Diverse Einstellungen', '');
include_once('includes/usermenu.php');

?>
  <main>
    <div id="formular_innen">
      <div class="center">
        <h1>Einstellungen</h1>
<p>(Bei mehreren Empfängern, die Emails (in Daten1) mit jeweils einem Komma trennen)</p>

          <table class='vertical_table'>
          <tr>
          <th style="background-color: #99ff99;"></th>
          <th><b>Aktion</b></th>
            <th><b>Daten1</b></th>
            <th><b>Daten2</b></th>
          </tr>

<?php

$query = "SELECT * FROM `diverses` ORDER BY `id` ASC;";

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
          </table>
        </div>
    </div>
  </main>
</body>
</html>