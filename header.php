<? //(c)2013 GPL by Bruno Vernier and Michael Linton
require_once('connect.php'); 
echo"<!DOCTYPE html>
<html>
  <head>
    <meta name='viewport' content='width=device-width'>
    <meta charset='utf-8' />
    <title>{$CFG->site_name}</title>
    <link href='css/bootstrap.min.css' rel='stylesheet' />
  <link rel='stylesheet' href='http://domain.tld/screen.css' type='text/css' media='Screen' />
  <link rel='stylesheet' href='http://domain.tld/mobile.css' type='text/css' media='handheld' />
  <link href='css/om.css' rel='stylesheet' />
  </head><body>
  <center>
   <div class='container-fluid' style='background-color: #fbfcf6;'>
  
  
  <div class='container-fluid-header' style='background-color: #f3f6e2; padding:5px;'>
  <img src='css/images/Open-Money-logo1.png' width='261' height='60' alt='logo'><br><br>
";

$do_not_use = "<NOTlink href='css/bootstrap-responsive.min.css' rel='stylesheet' />
    <NOTlink href='css/application.css' rel='stylesheet' />
    <script src='js/jquery-1.8.2.min.js'></script>
    <script src='js/moment.min.js'></script>
    <script src='js/qr.js'></script>
    <script src='js/bootstrap.min.js'></script>
    <script src='js/application.js'></script> ";


$user_name = isset($_SESSION['user_name'])?$_SESSION['user_name']:'';
if (!$user_name) {goto end;}
$account = isset($_SESSION['account'])?$_SESSION['account']:'';
$index = basename($_SERVER['REQUEST_URI'])=='index.php'?TRUE:FALSE;
//$signup = preg_match('signup.php',basename($_SERVER['REQUEST_URI'])=='signup.php'?TRUE:FALSE;
$signup = preg_match('/signup.php/',basename($_SERVER['REQUEST_URI']))?TRUE:FALSE;
$admin = isset($_SESSION['admin'])?$_SESSION['admin']:'user';
$menu="$admin: $account 
  <a class='top' href='main.php'>&nbsp; Main &nbsp;</a> 
  <a class='top' href='settings.php'>&nbsp; pw &nbsp;</a> 
  <a class='top' href='new.php'>&nbsp; New &nbsp;</a> 
  <a class='top' href='steward.php'>&nbsp; Stew &nbsp;</a> 
  <a class='top' href='logout.php'>&nbsp; Logout &nbsp;</a>";
if (isset($_SESSION['admin']) AND $_SESSION['admin']) {$menu .= "
  <a class='top' href='fft.php'>&nbsp; fft &nbsp;</a> 
  <a class='top' href='menu.php'>&nbsp; Menu &nbsp;</a> ";
}
if ($user_name) {echo "<center>$menu</center>";};

if (!($account OR $signup OR $user_name)) {
  echo " you are not allowed here!";
  header("Location: index.php");
  exit;
} else {
  if( !isset($_SESSION)) {session_start(); echo 'start a session';} 
}
end:
?>

</div>