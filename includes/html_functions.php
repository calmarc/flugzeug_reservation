<?php

function print_options($default, $t_array)
{
  foreach ($t_array as $item)
  {
    if (intval($default) == intval($item))
      echo "<option selected='selected'>{$item}</option>";
    else
      echo "<option>{$item}</option>";
  }
}

function combobox_flugzeug($mysqli, $default)
{
  $res = $mysqli->query("SELECT * FROM `flugzeug`;");
  while ($obj = $res->fetch_object())
  {
    if ($obj->id == $default)
      echo "<option selected='selected' value='{$obj->id}'>{$obj->flugzeug}</option>";
    else
      echo "<option value='{$obj->id}'>{$obj->flugzeug}</option>";
  }
}

function combobox_tag($default)
{
  $t_array = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31"];
  print_options($default, $t_array);
}

function combobox_monat($default)
{
  $t_array = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
  print_options($default, $t_array);
}
function combobox_jahr($default)
{
  date_default_timezone_set("Europe/Zurich");
  $jahr = date("Y", time());
  date_default_timezone_set("UTC");

  $t_array = array();
  for ($item = intval($jahr); $item < intval($jahr) + 2; $item++)
    array_push($t_array, "$item");
  print_options($default, $t_array);

}
function combobox_stunde($default)
{
  $t_array = ["07", "08", "09", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21"];
  print_options($default, $t_array);
}
function combobox_minute($default)
{
  $t_array = ["00", "30"];
  print_options($default, $t_array);
}

function combobox_piloten($mysqli, $default)
{
  $query = "SELECT * FROM `piloten` ORDER BY `pilot_nr` ASC;";
  $res = $mysqli->query($query);
  $t_array = array();
  
  while ($obj = $res->fetch_object())
  {
    $pilot_nr_pad = str_pad($obj->pilot_nr, 3, "0", STR_PAD_LEFT);
    if ($obj->pilot_nr == $default)
      echo "<option selected='selected' value='{$obj->id}'>[{$pilot_nr_pad}] {$obj->name}</option>";
    else
      echo "<option value='{$obj->id}'>[{$pilot_nr_pad}] {$obj->name}</option>";
  }
}

function legende_print($boxcol)
{ ?>
<div class="legende">
  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
        height="130px" style="background-color: transparent; width: 60%; min-width: 660px;" >
    <defs>
      <linearGradient id="gruen0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#66ee66" stop-opacity="1"/>
        <stop offset="100%" stop-color="#99ee99" stop-opacity="1"/>
      </linearGradient>
      <linearGradient id="gelblich0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
      <stop offset="0%"   stop-color="<?php echo $boxcol[1];?>" stop-opacity="1"/>
        <stop offset="100%" stop-color="<?php echo $boxcol[2];?>" stop-opacity="1"/>
      </linearGradient>
      <linearGradient id="grey0" x1="0" y1="0" x2="100%" y2="0" spreadMethod="pad">
        <stop offset="0%"   stop-color="#dddddd" stop-opacity="1"/>
        <stop offset="100%" stop-color="#eeeeee" stop-opacity="1"/>
      </linearGradient>
    </defs>
   <g transform="translate(0, 0)">
    <rect x="20%" y="0" width="20%" height="24" style="fill:url(#grey0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="30%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Vergangenheit</text>
    <rect x="40%" y="0" width="20%" height="24" style="fill:url(#gruen0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="50%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Frei</text>
    <rect x="60%" y="0" width="20%" height="24" style="fill: <?php echo $boxcol[0]; ?>; stroke: #666666; stroke-width: 1px;"></rect>
    <text x="70%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Gebucht</text>
    <rect x="80%" y="0" width="20%" height="24" style="fill: url(#gelblich0); stroke: #666666; stroke-width: 1px;"></rect>
    <text x="90%" y="18px" text-anchor="middle" style="fill: #000000; font-size: 100%; ">Standby</text>
  </g>
  </svg>
</div>
<?php
}

function tooltip_print()
{
?>
<div onclick="document.getElementById('tooltip_div').style.display = 'none';" id="tooltip_div" style="display: none; visibility: hidden;">
  <svg id="tooltip_svg" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
        height="100px" width="240px">
    <text id="tooltip_text1" x="3%" y="20px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text2" x="3%" y="45px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text3" x="3%" y="70px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
    <text id="tooltip_text4" x="3%" y="95px" text-anchor="start" style="fill: #000000; font-size: 100%; ">&nbsp;</text>
  </svg>
</div>
<?php
}

function print_html_to_body ($title, $special_meta)
{ ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content=
  "width=device-width, initial-scale=1.0" />
  <title><?php echo $title; ?></title>
  <meta name="generator" content="Calmar + Vim + Tidy" />
  <meta name="owner" content="MFGC.ch" />
  <meta name="author" content="candrian.org" />
  <meta name="robots" content="all" />
  <?php echo $special_meta; ?>
  <link rel="shortcut icon" href="http://www.mfgc.ch/flugschule/bilder/icon.png" />
  <link rel="stylesheet" href="/reservationen/css/reservationen.css" />

<?php
// fuer die madam
if ( isset($_SESSION['pilot_nr']) && ($_SESSION['pilot_nr'] == "107"))
{ 
  echo "<style>body { background-color: #ffebf8; }</style>"; 
}

?>

<!--[if IE]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

</head>
<body>
<?php
}

?>
