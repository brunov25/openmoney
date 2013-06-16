<? //(c)2012-13 by Adam, Bruno and Michael
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_id() == '') {session_start();}
//if (!defined('PDO::ATTR_DRIVER_NAME')) { echo 'PDO unavailable'; } elseif (defined('PDO::ATTR_DRIVER_NAME')) {echo 'PDO available'; }    
error_reporting(0);
require_once('config.php');
$db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
error_reporting(E_ALL);
#$db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//$db2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); //PDO::ERRMODE_WARNING
//$db2->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

function exec_sql($sql, $parms=array(), $comment="", $one=0, $debug=0) {
  global $db2, $_SESSION;
  $admin = isset($_SESSION['admin'])?$_SESSION['admin']:'';
  $sql = preg_replace('/USER_SPACES_QUERY/',"users join user_spaces on user_id=users.id
                        join spaces on spaces.id = space_id ",$sql);
  $sql = preg_replace('/USER_ACCOUNT_CURRENCIES_QUERY/',"users join user_spaces on user_id=users.id
                        join user_account_currencies on user_space_id = user_spaces.id",$sql);
  $sql = preg_replace('/USER_JOURNAL_QUERY/',"users join user_spaces on user_id=users.id
                        join user_account_currencies on user_space_id = user_spaces.id
                        join user_journal uj using (user_id)",$sql);
  $sql = preg_replace('/FULL_QUERY/',"users JOIN user_spaces on user_id=users.id
                        join spaces on spaces.id = space_id
                        join user_account_currencies on user_space_id = user_spaces.id
                        join currencies on currency_id = currencies.id",$sql);
  $query = $db2->prepare($sql);
  if (!$query->execute($parms)) {
    echo "<br><b><font color=red>ERROR: $comment</font></b> <br>";
  };
  if ($debug and $admin) {
    echo "<br><font color=blue><b>DEBUG:</b> $comment</font> <br>SQL = $sql <br>";
    echo $query->debugDumpParams();
    echo '<br>'. var_export($query->errorInfo());
  }
  if ($one==1) { //extract first field only
    $result = $query->fetchColumn();
    $query->closeCursor(); 
    return $result;
  }elseif ($one==2) { //show id of inserted record
    return $db2->lastInsertId();
  }else { // create an associative array
    return $query->fetchAll(PDO::FETCH_ASSOC); //or _BOTH
  }
}

function email_letter($to='bruno.vernier@gmail.com',$from='michael.linton@gmail.com',$subject='no subject',$msg='no msg') {
    $headers  = "From: $from,\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "Return-Path: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    return mail($to, $subject, $msg, $headers);
}

function is_admin() {
  global $_SESSION;
  $user_id = isset($_SESSION['user_id'])?$_SESSION['user_id']:header('login.php');
  $admin = exec_sql("SELECT privflags from users where id=? and privflags like '%admin%'",array($user_id),"checking if admin privs",1);
  if ($admin) {return 'admin';} else {return '';} 
}  


function pdo_sql_debug($sql,$placeholders){ //show actual SQL being processed
  foreach($placeholders as $k => $v){
    $sql = preg_replace('/:'.$k.'/',"'".$v."'",$sql);
  }
  return $sql;
}
?>
