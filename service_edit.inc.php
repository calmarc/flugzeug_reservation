
<?php
// von der uebersicht
if (isset($_GET['flieger_id']) && $_GET['flieger_id'] > 0)
{
  $flieger_id = $_GET['flieger_id'];

  $query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
  $res = $mysqli->query($query); 

  if ($res->num_rows != 1)
  {
    header('Location: /reservationen/index.php');
    exit;
  }
  $flieger_id = $_GET['flieger_id'];
  $flieger_name = $res->fetch_object()->flieger;

  if (isset($_GET['action'], $_GET['service_id']) && $_GET['action'] == "del" && $_GET['service_id'] > 0)
  {
    $query = "DELETE FROM `calmarws_test`.`service_eintraege` WHERE `service_eintraege`.`id` = ?;";
    mysqli_prepare_execute($mysqli, $query, 'i', array ($_GET['service_id']));
  }
}
else if (isset($_POST['submit']))
{
  // TODO ziemlich double maessig mit landungs-eintrag.. 
  $flieger_id = ""; if (isset($_POST['flieger_id'])) $flieger_id = $_POST['flieger_id'];
  $tag = ""; if (isset($_POST['tag'])) $tag = $_POST['tag'];
  $monat = ""; if (isset($_POST['monat'])) $monat = $_POST['monat'];
  $jahr = ""; if (isset($_POST['jahr'])) $jahr = $_POST['jahr'];
  $zaehlerstand = ""; if (isset($_POST['zaehlerstand'])) $zaehlerstand = $_POST['zaehlerstand'];
  $verantwortlich = ""; if (isset($_POST['verantwortlich'])) $verantwortlich = $_POST['verantwortlich'];
  $verantwortlich = intval($verantwortlich);

  $zaehler_minute = intval($zaehlerstand) * 60;
  $zaehler_minute += round($zaehlerstand * 100, 0, PHP_ROUND_HALF_UP) % 100;

  $_SESSION['flieger_id']  = $flieger_id;
  $_SESSION['tag']  = $tag;
  $_SESSION['monat']  = $monat;
  $_SESSION['jahr']  = $jahr;

  $tag = str_pad($tag, 2, "0", STR_PAD_LEFT);
  $monat = str_pad($monat, 2, "0", STR_PAD_LEFT);
  $date = "$jahr-$monat-$tag";

  $query = "SELECT * FROM `flieger` WHERE `id` = '$flieger_id' LIMIT 1;";
  $res = $mysqli->query($query); 
  $flieger_name = $res->fetch_object()->flieger;

  $error_msg = "";

  $z_max = -1;

  if ($stmt = $mysqli->prepare("INSERT INTO `calmarws_test`.`service_eintraege` (
	  `id` ,
	  `user_id` ,
	  `flieger_id` ,
	  `datum` ,
	  `zaehler_minute`
	  )
	  VALUES (
	  NULL , ?, ?, ?, ?
	  )"))
  {
	$stmt->bind_param('iisi', $verantwortlich, $flieger_id, $date, $zaehler_minute);
	if (!$stmt->execute()) 
	{
		header('Location: /reservationen/login/error.php?err=Registration failure: INSERT');
		exit;
	}
  }
}
else
{
  header('Location: /reservationen/index.php');
  exit;
}

?>
