<?php

include_once ('login/includes/db_connect.php');
include_once ('login/includes/functions.php');

sec_session_start();

if (login_check($mysqli) == true):

// check if admin rights
$query = "SELECT `admin` from `members` where `id` = ".$_SESSION['user_id']." LIMIT 1;";
$res = $mysqli->query($query); 
$obj = $res->fetch_object();
if ($obj->admin == FALSE)
  header("Location: /reservationen/index.php");

?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content=
    "width=device-width, initial-scale=1.0">
    <title>Benutzer Administration</title>
    <meta name="title" content="Benutzer Administration">
    <meta name="keywords" content="Benutzer,Administration">
    <meta name="description" content="Benutzer Administration">
    <meta name="generator" content="Calmar + Vim + Tidy">
    <meta name="owner" content="calmar.ws">
    <meta name="author" content="candrian.org">
    <meta name="robots" content="all">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/reservationen/reservationen.css">
  </head>

  <!--[if IE]>
  <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->

  <body>
    <?php include_once('includes/usermenu.php'); ?>
    <main>
  

          <div id="formular_innen">

    <div class="center">
    <h1>Benutzer Admin</h1>

  <?php

  $query = "SELECT * FROM `members` ORDER BY `members`.`id`;";

  $res = $mysqli->query($query); 

    
  echo "<table id='user_admin'>";
  echo "<tr><th><th><b>User-ID</b></th><th><b>Nick-Name</b></th><th><b>Name</b></th><th><b>Natel</b></th><th><b>Telefon</b></th><th><b>Email</b></th><th><b>Admin</b></th></tr>";
  while ($obj = $res->fetch_object())
  {
    if ($obj->admin == 1)
      $admin_txt = "ja";
    else
      $admin_txt = "nein";
      
    echo "\n<tr>";
    echo "<td><a href='user_edit.php?id=".$obj->id."'><small>[edit]</small></a></td>";
    echo "<td style='text-align: center;'>".str_pad($obj->id, 3, "0", STR_PAD_LEFT)."</td><td>".$obj->username."</td><td>".$obj->name."</td><td>".$obj->natel."</td><td>".$obj->telefon."</td><td>".$obj->email."</td><td>".$admin_txt."</td>";
    echo "</tr>";
  }
  echo "</table>";

  ?>
   
  </div>
  </div>
</main>
</body>
</html>

<?php else :
header("Location: /reservationen/login/index.php");
exit;
endif; 
?>
