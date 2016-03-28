<?php

function select_aktion_status_meldungen($mysqli, $where)
{
  $select = "<form style='display: inline-block;' action='protokoll.php' method='get'>
                    <select size='1' onchange='this.form.submit()' style='width: 12em;' name='aktion'>";

  $res = $mysqli->query("SELECT DISTINCT `aktion` FROM `status_meldungen` ORDER BY `aktion` ASC;");

  $select .= "<option value=''>alle Aktionen</option>";

  $selected = ""; if ($where == "ohne_ea") $selected = "selected='selected'";
  $select .= "<option {$selected} value='ohne_ea'>Ohne Ein/Aus</option>";

  while ($obj = $res->fetch_object())
  {
    $selected = "";
    if ($obj->aktion == $where)
      $selected = "selected='selected'";
    $select .= "<option {$selected} value='{$obj->aktion}'>{$obj->aktion}</option>";
  }
  $select .= "</select></form>";

  return $select;
}

function select_pilot_nr_geloescht($mysqli, $where, $backto)
{
  $select = "<form style='display: inline-block;' action='{$backto}' method='get'>
                    <select size='1' onchange='this.form.submit()' style='width: 18em;' name='pilot_nr'>";

  $res = $mysqli->query("SELECT DISTINCT `pilot_nr`, `id`, `name` FROM `piloten` ORDER BY `pilot_nr` ASC;");

  // erster und 2ter spezielle dings...
  $select .= "<option value=''>alle Piloten</option>";
  $selected = "";

  while ($obj = $res->fetch_object())
  {
    list($pilot_nr_pad, $name) = get_pilot_from_user_id($mysqli, $obj->id);
    $name = substr($name, 0, 20);
    $selected = "";
    if ($obj->pilot_nr == $where)
      $selected = "selected='selected'";
    $select .= "<option {$selected} value='{$obj->pilot_nr}'>[{$pilot_nr_pad}] {$name}</option>";
  }
  $select .= "</select></form>";

  return $select;
}

function select_pilot_nr_momentan($mysqli, $where, $backto)
{
  $select = "<form style='display: inline-block;' action='{$backto}' method='get'>
                    <select size='1' onchange='this.form.submit()' style='width: 18em;' name='pilot_nr'>";

  $res = $mysqli->query("SELECT DISTINCT `pilot_nr`, `id`, `name` FROM `piloten` ORDER BY `pilot_nr` ASC;");

  // erster und 2ter spezielle dings...
  $select .= "<option value=''>alle Piloten</option>";
  $selected = "";
  $flag = FALSE;

  if ($_SESSION['pilot_nr'] == $where)
  {
    $selected = "selected='selected'";
    $flag = TRUE;
  }
  $select .= "<option {$selected} value='{$_SESSION['pilot_nr']}'>Eigene Reservationen</option>";

  $selected = "";
  while ($obj = $res->fetch_object())
  {
    list($pilot_nr_pad, $name) = get_pilot_from_user_id($mysqli, $obj->id);
    $name = substr($name, 0, 20);
    $selected = "";
    if (!$flag)
    {
      if ($obj->pilot_nr == $where)
        $selected = "selected='selected'";
    }
    $select .= "\n<option {$selected} value='{$obj->pilot_nr}'>[{$pilot_nr_pad}] {$name}</option>";
  }
  $select .= "</select></form>";

  return $select;
}

function select_pilot_nr_status_meldungen($mysqli, $where)
{
  $select = "<form style='display: inline-block;' action='protokoll.php' method='get'>
                    <select size='1' onchange='this.form.submit()' style='width: 18em;' name='pilot_nr'>";

  $res = $mysqli->query("SELECT DISTINCT `pilot_nr`, `id`, `name` FROM `piloten` ORDER BY `pilot_nr` ASC;");

  // 1-3 spezielle dings...
  $select .= "<option value=''>alle</option>";

  $selected = ""; if ($where == "System") $selected = "selected='selected'";
  $select .= "<option {$selected} value='System'>System</option>";

  while ($obj = $res->fetch_object())
  {
    list($pilot_nr_pad, $name) = get_pilot_from_user_id($mysqli, $obj->id);
    $name = substr($name, 0, 20);
    $selected = "";
    if ($obj->pilot_nr == $where)
      $selected = "selected='selected'";
    $select .= "<option {$selected} value='{$obj->pilot_nr}'>[{$pilot_nr_pad}] {$name}</option>";
  }
  $select .= "</select></form>";

  return $select;
}

function generate_where($arr)
{
  $w_arr = array();
  foreach ($arr as $i)
  {
    if ($i != "")
      array_push($w_arr, $i);
  }

  $where_txt = "";
  if (count($w_arr) > 0)
    $where_txt = "WHERE  ".implode(" AND ", $w_arr);
  return $where_txt;
}

?>
