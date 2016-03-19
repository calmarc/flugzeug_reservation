<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/functions.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_admin($mysqli) == FALSE) { header("Location: /reservationen/index.php"); exit; }


print_html_to_body('Log Daten', '');
include_once('includes/usermenu.php');
include_once('includes/send_sms.php');

?>
  <main>
    <div id="formular_innen">
      <div class="center">
        <h1>Protokoll</h1>

          <table class='vertical_table'>
          <tr>
          <th><b>Am</b></th>
            <th><b>Aktion</b></th>
            <th><b>Data</b></th>
          </tr>

<?php

$query = "SELECT * FROM `status_meldungen` ORDER BY `timestamp` DESC LIMIT 100;";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{

  // to LOKAL zeit.. TODO funktion?
  $lokal_datum = $obj->timestamp;
  list( $tag, $zeit) = explode(" ", $obj->timestamp);
  $tmp = explode("-", $tag);
  $tmp2 = explode(":", $zeit);
  $lokal_datum = "{$tmp[2]}.{$tmp[1]}.{$tmp[0]} {$tmp2[0]}:{$tmp2[1]}";

  $action = $obj->aktion;
  $data = $obj->data;

  if ($action == "[SMS]")
  {
    $t_arr = explode("@@", $data);
    if (count($t_arr) == 3)
    {
      $t_arr2 = sms_delivery_status($mysqli, $t_arr[1]);
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

  echo "\n<tr>
           <td style='text-align: left; background-color: transparent; color: #333333; font-weight: bold;'>{$lokal_datum}</td>
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
