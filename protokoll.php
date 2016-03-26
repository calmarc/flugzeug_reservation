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
include_once('includes/sort.php');

// default
if (!isset($_SESSION['proto_sort_dir'])) $_SESSION['proto_sort_dir'] = "DESC";
if (!isset($_SESSION['proto_sort_by'])) $_SESSION['proto_sort_by'] = "timestamp";

$t_old = $_SESSION['proto_sort_by'];
if (isset($_GET['sort']) && $_GET['sort'] != '') $_SESSION['proto_sort_by'] = $_GET['sort'];

if (isset($_GET['sort']) && $t_old == $_GET['sort']) // glieche kolumne gedruckt - also dir wechsel
  if ($_SESSION['proto_sort_dir'] == "ASC")
      $_SESSION['proto_sort_dir'] = "DESC";
  else
      $_SESSION['proto_sort_dir'] = "ASC";

// string generieren
$order_by_txt = "ORDER BY `".$_SESSION['proto_sort_by']."` ".$_SESSION['proto_sort_dir'];

// die 3 kolumnen zum ASC/DESC ordnnen - das pfeil-bild generieren
$timestamp_img = $durch_img = $aktion_img = $data_img = "";
if ($_SESSION['proto_sort_by'] == 'timestamp')
  $timestamp_img = "<img alt='asc/desc' src='bilder/arrow-{$_SESSION['proto_sort_dir']}.png' />";
else if ($_SESSION['proto_sort_by'] == 'durch')
  $durch_img = "<img alt='asc/desc' src='bilder/arrow-{$_SESSION['proto_sort_dir']}.png' />";
else if ($_SESSION['proto_sort_by'] == 'aktion')
  $aktion_img = "<img alt='asc/desc' src='bilder/arrow-{$_SESSION['proto_sort_dir']}.png' />";
else if ($_SESSION['proto_sort_by'] == 'data')
  $data_img = "<img alt='asc/desc' src='bilder/arrow-{$_SESSION['proto_sort_dir']}.png' />";

?>
  <main>
    <h1>Protokoll</h1>
    <div id="formular_innen">
      <div class="center">
<?php

////////////////////////////////////////////////////////////////////////////////////
// pilot select

if (!isset($_SESSION['where_pilot_nr'])) 
  $_SESSION['where_pilot_nr'] = "";

// set to get if there
if (isset($_GET['pilot_nr']))
  $_SESSION['where_pilot_nr'] = $_GET['pilot_nr'];

// set when 
$where_pilot_nr_txt = "";
if ($_SESSION['where_pilot_nr'] != "")
{
  $pilot_nr_pad = str_pad($_SESSION['where_pilot_nr'], 3, "0", STR_PAD_LEFT);
  $where_pilot_nr_txt = "`durch` LIKE '%{$pilot_nr_pad}%'";
}

echo select_pilot_nr_status_meldungen($mysqli, $_SESSION['where_pilot_nr']);

////////////////////////////////////////////////////////////////////////////////////
// aktion select

if (!isset($_SESSION['where_aktion'])) 
  $_SESSION['where_aktion'] = "ohne_ea";

// set to get if there
if (isset($_GET['aktion']))
  $_SESSION['where_aktion'] = $_GET['aktion'];

// query
$where_aktion_txt = "";
if ($_SESSION['where_aktion'] != "")
{
  if ($_SESSION['where_aktion'] == "ohne_ea")
  {
    $where_aktion_txt = "`aktion` NOT IN ('[Eingeloggt]', '[Ausgeloggt]')";
  }
  else
    $where_aktion_txt = "`aktion` = '{$_SESSION['where_aktion']}'";
}

echo select_aktion_status_meldungen($mysqli, $_SESSION['where_aktion']);
// ----------------------------------------------------------------------------------

?>
          <table class='vertical_table th_filter'>
          <tr>
          <th><a href="protokoll.php?sort=timestamp"><b>Zeit-Stempel</b><?php echo $timestamp_img; ?></a></th>
            <th><a href="protokoll.php?sort=durch"><b>Durch</b><?php echo $durch_img; ?></a></th>
            <th><a href="protokoll.php?sort=aktion"><b>Aktion</b><?php echo $aktion_img; ?></a></th>
            <th><a href="protokoll.php?sort=data"><b>Data</b><?php echo $data_img; ?></a></th>
          </tr>
<?php

$where_txt = generate_where(array($where_aktion_txt, $where_pilot_nr_txt));

$query = "SELECT * FROM `status_meldungen` {$where_txt} {$order_by_txt};";

$res = $mysqli->query($query);

while ($obj = $res->fetch_object())
{
  $lokal_datum = mysql_stamp_to_ch($mysqli, $obj->timestamp);

  $aktion = $obj->aktion;
  $durch = $obj->durch;
  $data = $obj->data;

  //============================================================================
  // nach @@...@@ string gucken (die trackingnummer)
  // entsprechend dem resultat, ausgeben oder string ersetzen (UPDATE...)

  replace_sms_tracking ($mysqli, $obj);

  echo "\n<tr>
           <td style='text-align: left; background-color: transparent; color: #333333;'>{$lokal_datum}</td>
           <td>{$durch}</td>
           <td>{$aktion}</td>
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
