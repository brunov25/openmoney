<? //(c)GPL Bruno Vernier and Michael Linton
require_once('connect.php'); 
//require('header.php');
$form_pw = isset($_POST['password'])?$_POST['password']: 'nothing';
$form_pw = isset($_POST['pw'])?$_POST['pw']:$form_pw;
$user = isset($_POST['username'])?$_POST['username']:'nobody';
$user_id = '0';
require('password.php');
//$hash = password_hash($password, PASSWORD_DEFAULT, ["cost" => 10]); //strongest algorithm known 
$record = exec_sql("SELECT * FROM users JOIN user_spaces ON user_id=users.id
         JOIN user_account_currencies ON  user_space_id=user_spaces.id  where user_name = ? and confirmed !=''",
		   array($user),"checking if $user can play here");
$debug='';
if (!$record) {
    $record = exec_sql("SELECT * FROM users JOIN user_spaces ON user_id=users.id
          where user_name = ? and confirmed !=''", array($user),"checking if $user can play here");
    $debug='missing uac record '.count($record);
}
foreach($record as $row) {
   $db_pw1 = $row['password'];
   $db_pw2 = $row['password2'];
   $privflags = $row['privFlags'];
   $user_id = $row['user_id'];
   $user_name = $row['user_name'];
   $account = $row['user_name'];
   $email = $row['email'];
   $currency = isset($row['currency'])?$row['currency']:'';
};

if (isset($db_pw2) AND (password_verify($form_pw, $db_pw2) OR (password_verify($form_pw, $db_pw1)))) {
   /* Valid */
   $_SESSION["user_id"] = $user_id;
   $_SESSION["user_name"] = $user_name;
   $_SESSION["email"] = $email;
   $_SESSION["account"] = $user_name;
   $_SESSION["currency"] = $currency;
   $_SESSION['admin'] = strstr($privflags,'admin')?'admin':'';
   $res = exec_sql("insert into eventLog (type,subtype,account_id,content,date) values ('login','success',?,?,?)",
                   array($user_id,"$user_name from ".$_SERVER['REMOTE_ADDR'],date("Y-m-d H:i:s")),'log',2);
   header("location: main.php");
} else {
   /* Invalid */
   echo "<h1>...Incorrect Credentials for $user </h1><a href=logout.php>login again</a>";
   $res = exec_sql("insert into eventLog (type,subtype,account_id,content,date) values ('login','failure',?,?,?)",
                   array($user_id,"$user attempted from ".$_SERVER['REMOTE_ADDR'],date("Y-m-d H:i:s")),'log',2);
   exit;
   //header("location: index.php");
}
#=================================== REHASH password if needed
#$hash = password_hash($form_pw, PASSWORD_BCRYPT);
#//$hash = password_hash($password, PASSWORD_BCRYPT, ["cost" => 10]);
#//$hash = password_hash($password, PASSWORD_DEFAULT, ["cost" => 10]); //strongest algorithm known
#$db_pw = exec_sql("SELECT password2 FROM users WHERE user_name = ?",array($username),"user record",1);
#if (password_verify($password, $hash)) {
#    if (password_needs_rehash($hash, $algorithm, $options)) {
#        $hash = password_hash($password, $algorithm, $options);
#        /* Store new hash in db */
#    }
#}
#=====================================
echo "<p><a href=signup.php>Request a New Account</a>";
require('footer.php');
?>
