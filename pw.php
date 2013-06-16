<? //(c)GPL bruno and michael, based on https://raw.github.com/ircmaxell/password_compat/

require_once('password.php');
require_once('connect.php');

$user_email = isset($_REQUEST['email'])?$_REQUEST['email']:'';
$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
$length = 8;
$new_pw = substr(str_shuffle($chars),0,$length);
$new_pw_hash = password_hash($new_pw, PASSWORD_BCRYPT);
$postmaster = 'michael.linton@gmail.com'; 

if ($user_email) {
  $subject = "OpenMoney: new password";
  $msg = "you apparently requested a new password for your OpenMoney account.  Here it is: <b>$new_pw</b> . 
          <br>You can still use your old one.  <a href=http://openmoney.ca/beta>Open Money beta</a>
          <br>We recommend you click on Settings and change your password to something secret and memorable for you";
  if(email_letter($user_email, $postmaster, $subject, $msg)) { echo "<br>new password sent to $user_email<p><a href=index.php>back</a>"; }
  $update = exec_sql("update users set password= ? where email = ?",array($new_pw_hash, $user_email),"creating password",2);
}
$confirm = isset($_REQUEST['confirm'])?$_REQUEST['confirm']:'';
if ($confirm) {
  $unconfirmed_users = exec_sql("select * from users where confirmed>'' and password2=''",array(),"new password-less members");
  foreach($unconfirmed_users as $row) {
    $user_name = $row['user_name'];
    $userid = $row['id'];
    $dupl = "ON DUPLICATE KEY UPDATE id=id"; //to deal with duplicates on unique keys
    $insert1 = exec_sql("insert into user_spaces (space_id,user_id,class) values ('1',?,'user') ON DUPLICATE KEY UPDATE id=id",
 			array($userid),"inserting $user_name into user_spaces",2);
    $insert2 = exec_sql("insert into user_account_currencies (user_space_id,trading_name,currency_id) values (?,?,'1') $dupl",
 			array($insert1,$user_name),"inserting $user_name into user_account_currencies",2);
    $address = $row['email'];
    $new_pw = substr(str_shuffle($chars),0,$length);
    $new_pw_hash = password_hash($new_pw, PASSWORD_BCRYPT);
    $fname = $row['fname'];
    $msg = "Hello $fname <p>Your account on OpenMoney has been confirmed. <p> please go to http://openmoney.ca/beta 
     <p>your username is $user_name <br>and your password is $new_pw <p> Please change it right away by clicking on 
       settings in the top menu <p>( http://openmoney.ca/beta/settings.php )<p> Welcome to OpenMoney - Michael Linton"; 
    $msg2 = "$fname signed up for an account on OpenMoney http://openmoney.ca/beta ";
    $subject = "OpenMoney: new account for $user_name";
    if(email_letter($address, $postmaster, $subject, $msg)) { echo "<br>sending confirmation email to $address"; }
    $id = $row['id'];
    $update = exec_sql("update users set password2= ? where id = ?",array($new_pw_hash, $id),"creating password",2);
    email_letter($postmaster,$address,$subject,$msg2);
  }
  echo "<br>done";
  exit;
}

// check if all passwords are hashed
$blank_passwords = exec_sql("SELECT * FROM users WHERE password2=''",array(),"blank passwords");
foreach($blank_passwords as $row){
  $old_pw = $row['password'];
  $id = $row['id'];
  $new_pw = password_hash($old_pw, PASSWORD_BCRYPT);
  $update1 = exec_sql("update users set password2=? WHERE id=?",array($new_pw,$id),"updating passwords",2);
  echo "<br>$id: new_pw";
}
?>
