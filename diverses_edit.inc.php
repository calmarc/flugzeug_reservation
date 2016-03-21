<?php

//============================================================================
// Daten der Einstellung updaten

if (isset($_POST['updaten']))
{
  $id = intval($_POST['id']);
  $data1 = trim($_POST['data1']);
  // not always
  $data2 = ""; if (isset($_POST['data2'])) $data2 =  trim($_POST['data2']);

  // UPDATE USER DATA
  $query = "UPDATE `mfgcadmin_reservationen`.`diverses` SET `data1` = ?, `data2` = ? WHERE `diverses`.`id` = ?;";
  mysqli_prepare_execute($mysqli, $query, 'ssi', array ($data1, $data2, $id));

  header("Location: diverses.php");
  exit;
}
?>
