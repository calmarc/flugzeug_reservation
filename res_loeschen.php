<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors',1);
ini_set('html_errors', 1);

include_once ('includes/db_connect.php');
include_once ('includes/user_functions.php');
include_once ('includes/html_functions.php');
include_once ('includes/reservations_functions.php');
include_once ('includes/functions.php');
include_once ('includes/send_sms.php');

sec_session_start();

if (login_check($mysqli) == FALSE) { header("Location: /reservationen/login/index.php"); exit; }
if (check_gesperrt($mysqli) == TRUE) { header("Location: /reservationen/login/index.php"); exit; }

include_once ('res_loeschen.inc.php');

print_html_to_body('Reservierung loeschen', '');
include_once('includes/usermenu.php');

// html stuff

$required = 'required="required"';
$chars_java = "onsubmit=\"var text = document.getElementById('texta').value; if(text.length < 7) { alert('Ausführlichere Begruendung bitte!'); return false; } return true;\"";
$chars_java2 = "onsubmit=\"var text = document.getElementById('texta2').value; if(text.length < 7) { alert('Ausführlichere Begruendung bitte!'); return false; } return true;\"";
$optional = "";

// no begruendung required for admins.
if (check_admin($mysqli))
{
  $required = '';
  $optional = " (optional)";
  $chars_java = "";
  $chars_java2 = "";
}

// check if trimmer or delete for <header>
$query = "SELECT `von`, `bis` FROM `reservationen` WHERE `id` = {$reservierung} LIMIT 1;";
$res = $mysqli->query($query);

if ($res->num_rows < 1)
{
  header("Location: index.php?tag={$tag}&monat={$monat}&jahr={$jahr}");
  exit;
}
$obj = $res->fetch_object();

// Reservation ist am Rand - nur. - gegenteil: = liegt drinnen
// wieso einfach wenns auch kompliziert geht
if (!($obj->von >= $rounded_datetime || $obj->bis <= $rounded_datetime))
{
	$trimmen = TRUE;
	$h1 = "Reservation freigeben";
    $h3 = "";
    $button = "Ab ".date("H:i", strtotime($rounded_datetime))."h freigeben";
}
else
{
	$trimmen = FALSE;
	$h1 = "Reservation löschen";
    $h3 = "Begründung{$optional}";
    $button = "Reservation löschen";
}

echo " <main> ";
echo "<h1>$h1</h1>";
echo "    <div id='formular_innen'>";

$query = "SELECT * FROM `reservationen`
          LEFT OUTER JOIN `piloten` ON `piloten`.`id` = `reservationen`.`user_id`
          WHERE `reservationen`.`id` = $reservierung
          LIMIT 1";

$res = $mysqli->query($query);
$obj = $res->fetch_object();

$flugzeug = get_flugzeug_from_id($mysqli, $obj->flugzeug_id);

// infos ueber die buchung ausgeben:
?>
    <div class="center">
      <table class="vtable">
        <tr class="trblank">
          <td><b>Pilot:</b></td>
          <td><?php echo "[".str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT)."] ".$obj->name; ?></td>
        </tr>
        <?php if ($obj->telefon != "") {?>
        <tr class="trblank">
          <td><b>Telefon:</b></td>
          <td><?php echo $obj->telefon; ?></td>
        </tr>
        <?php } ?>
        <tr class="trblank">
          <td><b>Natel:</b></td>
          <td><?php echo $obj->natel; ?></td>
        </tr>
        <tr class="trblank">
          <td><b>Email:</b></td>
          <td><?php echo $obj->email; ?></td>
        </tr>
        <tr class="trblank">
          <td><b>Flugzeug:</b></td>
          <td><?php echo $flugzeug; ?></td>
        </tr>
        <tr class="trblank">
          <td><b>Buchungs-Zeit:</b></td>
          <td><?php echo mysql2chtimef($obj->von, $obj->bis, FALSE); ?></td>
        </tr>
      </table>
    </div>

<!-- <p>Hinweis: Es ist nicht möglich Reservierungen für bereits vergangene Tage zu löschen.</p> -->

<h3><?php echo $h3; ?></h3>
      <form <?php echo $chars_java; ?> action="res_loeschen.php" method="post">
        <input type="hidden" name="reservierung" value='<?php echo $reservierung; ?>' />
        <input type="hidden" name="backto" value='<?php echo $backto; ?>' />
        <input type="hidden" name="tag" value='<?php echo $tag; ?>' />
        <input type="hidden" name="monat" value='<?php echo $monat; ?>' />
        <input type="hidden" name="jahr" value='<?php echo $jahr; ?>' />
<?php

if (!$trimmen)
{ ?>
<textarea id="texta" title="3 characters minimum" style="width: 80%" <?php echo $required; ?> name="begruendung"></textarea>
<?php } ?>
<input class="submit_button" style="margin-top: 20px;" type='submit' name='submit' value='<?php echo $button; ?>' />
</form>

<br />
<hr />
<h2>Teillöschung</h2>

<?php

if (isset($error_msg) && $error_msg != "")
  echo "<p><b style='color: red;'>$error_msg</b></p>";

// get von bis von der Reservation
$res = $mysqli->query("SELECT * FROM `reservationen` WHERE `id` = {$reservierung};");
$obj = $res->fetch_object();
$von = $obj->von;
$bis = $obj->bis;

// gucken ob die tage gleich sind -> andere eingabefelder
list ($datum, $zeit) =  explode(" ", $obj->von, 2);
list ($von_jahr, $von_monat, $von_tag) = explode("-", $datum, 3);
list ($von_stunde, $von_minute) = explode(":", $zeit, 3);

$datum_v = $datum;

list ($datum, $zeit) =  explode(" ", $obj->bis, 2);
list ($bis_jahr, $bis_monat, $bis_tag) = explode("-", $datum, 3);
list ($bis_stunde, $bis_minute) = explode(":", $zeit, 3);

$show_2_datum = TRUE;
$summary_text = "Zeit";
if ($datum_v ==  $datum)
{
  $show_2_datum = FALSE;
  $summary_text = "Löschen";
}

?>

<form <?php echo $chars_java2; ?> action="res_loeschen.php" method="post">
        <input type="hidden" name="reservierung" value='<?php echo $reservierung; ?>' />
        <input type="hidden" name="backto" value='<?php echo $backto; ?>' />
        <input type="hidden" name="tag" value='<?php echo $tag; ?>' />
        <input type="hidden" name="monat" value='<?php echo $monat; ?>' />
        <input type="hidden" name="jahr" value='<?php echo $jahr; ?>' />
<?php

// 'blind' durchgeben diese daten.. da in der form nicht angezeigt
if (! $show_2_datum)
{ ?>
        <input type="hidden" name="von_tag" value='<?php echo $von_tag; ?>' />
        <input type="hidden" name="von_monat" value='<?php echo $von_monat; ?>' />
        <input type="hidden" name="von_jahr" value='<?php echo $von_jahr; ?>' />
        <input type="hidden" name="bis_tag" value='<?php echo $bis_tag; ?>' />
        <input type="hidden" name="bis_monat" value='<?php echo $bis_monat; ?>' />
        <input type="hidden" name="bis_jahr" value='<?php echo $bis_jahr; ?>' />

<?php  } ?>

<div class="center">
      <table class="vtable">
<?php
// datum zum editiere, oder fixes angeben
if ($show_2_datum)
{ ?>

        <tr class="trblank">
          <td style="text-align: center;" colspan="2"><b>Löschen von:</b></td>
        </tr>
        <tr>
          <td><b>Datum von:</b></td>
          <td>
            <select size="1" name="von_tag" style="width: 46px;">
              <?php combobox_tag($von_tag); ?>
            </select> <b>.</b>
            <select size="1" name="von_monat" style="width: 46px;">
              <?php combobox_monat($von_monat); ?>
            </select> <b>.</b>
            <select size="1" name="von_jahr" style="width: 86px;">
              <?php combobox_jahr($von_jahr); ?>
            </select>
          </td>
        </tr>
<?php
}
else
{ ?>
        <tr class="trblank">
          <td><b>Datum:</b></td>
          <td>
            <?php echo "$von_tag.$von_monat.$von_jahr"; ?>
          </td>
        </tr>
<?php
}
?>
        <tr>
          <td><b><?php echo $summary_text; ?> von:</b></td>
          <td>
            <select size="1" name="von_stunde" style="width: 46px;">
              <?php combobox_stunde($von_stunde); ?>
            </select> <b>:</b>
            <select size="1" name="von_minute" style="width: 46px;">
              <?php combobox_minute($von_minute); ?>
            </select> <b>Uhr</b>
          </td>
        </tr>
<?php
// datum zum editiere, oder nichts
if ($show_2_datum)
  { ?>
        <tr class="trblank">
          <td style="text-align: center;" colspan="2"><b>bis:</b></td>
        </tr>
        <tr>
          <td><b>Datum bis:</b></td>
          <td>
            <select size="1" name="bis_tag" style="width: 46px;">
              <?php combobox_tag($bis_tag); ?>
            </select> <b>.</b>
            <select size="1" name="bis_monat" style="width: 46px;">
              <?php combobox_monat($bis_monat); ?>
            </select> <b>.</b>
            <select size="1" name="bis_jahr" style="width: 86px;">
              <?php combobox_jahr($bis_jahr); ?>
            </select>
          </td>
        </tr>
<?php } ?>

        <tr>
          <td><b><?php echo $summary_text; ?> bis:</b></td>
          <td>
            <select size="1" name="bis_stunde" style="width: 46px;">
              <?php combobox_stunde($bis_stunde); ?>
            </select> <b>:</b>
            <select size="1" name="bis_minute" style="width: 46px;">
              <?php combobox_minute($bis_minute); ?>
            </select> <b>Uhr</b>
          </td>
        </tr>
      </table>
<?php

echo "<h3>{$h3}</h3>";

$texta = "texta2";

if (strtotime($rounded_datetime) >= strtotime($obj->bis) - (60*60*2))
  $texta = $required = "";

//echo strtotime($rounded_datetime);
//echo "<br />";
//echo date("Y-m-d H:i", strtotime($rounded_datetime));
//echo "<br />";
//echo strtotime($obj->bis);
//echo "<br />";
//echo date("Y-m-d H:i", strtotime($obj->bis) - (60*60*2));
//echo "<br />";
//echo $texta;

?>
<textarea id="<?php echo $texta; ?>" title="3 characters minimum" style="width: 80%" <?php echo $required; ?> name="begruendung"></textarea>
<input class="submit_button" style="margin-top: 20px;" type='submit' name='submit' value="Teillöschung"  />
</div>

</form>
</div>
  </main>
</body>
</html>
