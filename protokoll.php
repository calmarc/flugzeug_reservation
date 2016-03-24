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
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

///////////////////////////////////////////////////////////////////////////
//  GET loeschen machen

include_once ('protokoll.inc.php');

print_html_to_body('Log Daten', '');
include_once('includes/usermenu.php');
include_once('includes/send_sms.php');

?>
  <main>
    <h1>Protokoll</h1>
    <div id="formular_innen">
      <div class="center">

        <form action='protokoll.php' method='get'>
          Löschen: <select style="width: 15em;" onchange='this.form.submit()' name='loeschen' size="1" id='loeschen'>
            <option value="9876543210">&nbsp;</option>
            <option value="loggings">Ein/Aus-geloggt</option>
            <option value="365">älter als ein Jahr</option>
            <option value="182">älter als ein halbes Jahr</option>
            <option value="90">älter als 3 Monate</option>
            <option value="30">älter als 1 Monat</option>
            <option value="7">älter als 1 Woche</option>
            <option value="1">älter als 1 Tag</option>
            <option value="0">alle</option>
          </select>
        </form>

          <table class='vertical_table'>
          <tr>
          <th><b>Zeit-Stempel</b></th>
            <th><b>Durch</b></th>
            <th><b>Aktion</b></th>
            <th><b>Data</b></th>
          </tr>
<?php

$query = "SELECT * FROM `status_meldungen` ORDER BY `timestamp` DESC;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  $lokal_datum = mysql_stamp_to_ch($mysqli, $obj->timestamp);

  $action = $obj->aktion;
  $durch = $obj->durch;
  $data = $obj->data;

  //============================================================================
  // nach @@...@@ string gucken (die trackingnummer)
  // entsprechend dem resultat, ausgeben oder string ersetzen (UPDATE...)

  if ($action == "[Standby SMS]")
  {
    $t_arr = explode("@@", $data);
    if (count($t_arr) == 3)
    {
      // Daten von der tracking nummer
      $t_arr2 = sms_delivery_status($mysqli, $t_arr[1]);

      if (count($t_arr2) == 2) // eine Exception wurde ausgeloest (falsche tracking . normalerweise)
      {
        $data = "{$t_arr[0]} <span style='color: red;'>{$t_arr2[0]}</span>: {$t_arr2[1]}";
      }
      else
      {
        if ($t_arr2['deliveryStatusBool'])
        {
          $data = "{$t_arr[0]} <span style='color: green;'>{$t_arr2['deliveryStatus']}</span>{$t_arr[2]}";
          mysqli_prepare_execute ($mysqli, "UPDATE `status_meldungen` SET `timestamp` = ?, `data` = ? WHERE `status_meldungen`.`id` = ?;", "ssi", array($obj->timestamp, $data, $obj->id));
        }
        else if ($t_arr2['deliveryStatus'] == 'Not Delivered')
        {
          $data = "{$t_arr[0]} <span style='color: red;'>{$t_arr2['deliveryStatus']}</span>{$t_arr[2]}";
          mysqli_prepare_execute ($mysqli, "UPDATE `status_meldungen` SET `timestamp` = ?, `data` = ? WHERE `status_meldungen`.`id` = ?;", "ssi", array($obj->timestamp,$data, $obj->id));
        }
        else
        {
          $data = "{$t_arr[0]} <span style='color: red;'>{$t_arr2['deliveryStatus']}</span>{$t_arr[2]}";
        }
      }

    }
  }

  echo "\n<tr>
           <td style='text-align: left; background-color: transparent; color: #333333;'>{$lokal_datum}</td>
           <td>{$durch}</td>
           <td>{$action}</td>
           <td>{$data}</td>
        </tr>";
}
?>
          </table>
        </div>
    </div>
  </main>
</body>
</html>
