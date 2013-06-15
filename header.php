<? //(c)2013 GPL by Bruno Vernier and Michael Linton
require_once('connect.php'); ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=480, height=752, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <title>open money beta</title>
    <link href="css/bootstrap.min.css" rel="stylesheet" />
    <NOTlink href="css/bootstrap-responsive.min.css" rel="stylesheet" />
    <NOTlink href="css/application.css" rel="stylesheet" />
<!-- <script src="js/jquery-1.8.2.min.js"></script>
    <script src="js/moment.min.js"></script>
    <script src="js/qr.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/application.js"></script> 
-->
  </head><body><div class="container-fluid"><center>
<?

$user_name = isset($_SESSION['user_name'])?$_SESSION['user_name']:'';
$account = isset($_SESSION['account'])?$_SESSION['account']:'';
$index = basename($_SERVER['REQUEST_URI'])=='index.php'?TRUE:FALSE;
//$signup = preg_match('signup.php',basename($_SERVER['REQUEST_URI'])=='signup.php'?TRUE:FALSE;
$signup = preg_match('/signup.php/',basename($_SERVER['REQUEST_URI']))?TRUE:FALSE;
$admin = isset($_SESSION['admin'])?$_SESSION['admin']:'user';
$menu="$admin: $account 
 | <a href='settings.php'>pw</a> 
 | <a href='new.php'>New</a>
 | <a href='steward.php'>Stew</a>
 | <a href='logout.php'>Logout</a>
 | <a href='main.php'>Main</a>";
if (isset($_SESSION['admin']) AND $_SESSION['admin']) {$menu .= "
 | <a href='fft.php'>fft</a> 
 | <a href='menu.php'>Menu</a> ";
}
if ($user_name) {echo "<center>$menu</center>";};

if (!($account OR $signup OR $user_name)) {
  echo " you are not allowed here!";
  header("Location: index.php");
  exit;
} else {
  if( !isset($_SESSION)) {session_start(); echo 'start a session';} 
}
?>
