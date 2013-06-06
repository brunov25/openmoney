<? //(c)2013 GPL by Bruno Vernier and Michael Linton
require('header.php');

$username=isset($_POST['username'])?preg_replace('/[^a-zA-Z0-9\.\+\-\_\@\%\&\^\~\!\?\<\>\:\;\=\*\$\#]/','',$_POST['username']):'';
$firstname=isset($_POST['firstname'])?$_POST['firstname']:'';
$lastname=isset($_POST['lastname'])?$_POST['lastname']:'';
$email=isset($_POST['email'])?preg_replace('/[^a-zA-Z\_\-\@0-9\.\+\%\&]/','',$_POST['email']):'';
$newpw=isset($_REQUEST['newpw'])?$_REQUEST['newpw']:'';

if ($username) {
  $reserved1 = exec_sql("select user_name from users where user_name=?",array($username),"check if username already exists",1);
  $reserved2 = exec_sql("select currency from currencies where currency=?",array($username),"check if username exists as currency",1);
  $reserved3 = exec_sql("select trading_name from user_account_currencies where trading_name=?",array($username),"check u_a_c",1);
  $reserved4 = exec_sql("select email from users where email=?",array($email),"check email address",1);
  if ($reserved1) {echo "<p><font size=+2 color=red><b>$username</b> is a reserved username</font>"; goto signup_form;}
  if ($reserved2) {echo "<p><font size=+2 color=red><b>$username</b> is a reserved currency</font>"; goto signup_form;}
  if ($reserved3) {echo "<p><font size=+2 color=red><b>$username</b> is a reserved trading name </font>"; goto signup_form;}
  if ($reserved4) {echo "<p><font size=+2 color=red><b>$email</b> is an existing email</font>"; goto signup_form;}
  echo "<br>creating username <b>$username</b>";
  $insert1 = exec_sql("INSERT into users (user_name, lname, fname, email, confirmed) values (?,?,?,?,'1')",
                      array($username, $lastname, $firstname, $email),
		      "creating new username $username (perhaps it already exists?)",2);
  if ($insert1>0) { 
    echo "Thank you, $firstname! <p>You will be notified as soon as your account has been manually confirmed";
    $address = 'michael.linton@gmail.com';
    $fname = $firstname;
    $lname = $lastname;
    $msg = "$fname $lastname created an account $username on OpenMoney which was auto onfirmed. (authorized by mwl on may 20, 2013) 
    <p> please go to http://openmoney.ca/beta  and click on MENU then CONFIRM and then 'mail them pw'    <p>OpenMoney IT Team";
    $subject = "OpenMoney: new account REQUESTED for $username";
    if(email_letter($address,$address,$subject,$msg)) { echo "<br>confirmation request is in process - may take several hours"; }
    $insert2 = exec_sql("insert into spaces (space_name) values (?)",array($username),
			"creating unique space for $username",2);
    $insert3 = exec_sql("insert into user_spaces (space_id,user_id,class) values (?,?,'steward')",array($insert2,$insert1),
			"making $username a steward of his own space",2);
#    $insert4 = exec_sql("insert into currencies (currency,currency_steward) values (?,?)",array($username.'.cc',$insert1),
#			"making $username a steward of his own space",2);
#    $insert5 = exec_sql("insert into user_account_currencies (user_space_id,trading_name,currency_id) values (?,?,?)",
#			array($insert3,$username,$insert4),"inserting record into u_a_c",2);
#
#    echo "<br>admin debug: created user_id $insert1, space_id $insert2, user_space_id $insert3, u_a_c_id $insert4";
    echo "<br>preparing to email password to $email";
    $_REQUEST['confirm'] = '1';
    include('pw.php'); //send emails to new signups
  }
}else { goto signup_form;}
exit;
signup_form:
if ($newpw) {
  echo "<h5>request a new password</h5><form method=post action=pw.php>email: <input type=email name=email>
        <input type=submit></form> ";
}else {
  echo "<form method=post>
<p><table width=30% border>
<tr><th colspan=2>OpenMoney Signup Form</th></tr>
<tr><td><b>Username</b>:</td>
<td><input name=username required=required pattern='[A-Za-z0-9]{3}.*' 
     title='minimum 3 letters and numbers, no spaces nor punctuation' autofocus=autofocus></td></tr>
<tr><Td>First Name (optional)</td><td><input pattern='[A-Za-z0-9]*' title='use only letters and numbers' name=firstname></td></tr>
<tr><td>Last Name (optional)</td><td><input pattern='[A-Za-z0-9]*' title='use only letters and numbers' name=lastname></td></tr>
<tr><td><b>Email address</b></td><td><input type=email required name=email></td></tr>
<tr><td colspan=2><input type=submit></td></tr></form>";
}
require('footer.php');
?>