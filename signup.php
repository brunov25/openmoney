<? //(c)2013 GPL by Bruno Vernier and Michael Linton
require('header.php');

$username=isset($_POST['username'])?preg_replace('/[^a-zA-Z0-9\.\+\-\_\@\%\&\^\~\!\?\<\>\:\;\=\*\$\#]/','',$_POST['username']):'';
$firstname=isset($_POST['firstname'])?$_POST['firstname']:'';
$lastname=isset($_POST['lastname'])?$_POST['lastname']:'';
$email=isset($_POST['email'])?preg_replace('/[^a-zA-Z\_\-\@0-9\.\+\%\&]/','',$_POST['email']):'';
$newpw=isset($_REQUEST['newpw'])?$_REQUEST['newpw']:'';

if ($username) {
  $space_name = $_REQUEST['space_name'];
  $username = $space_name?$username .'.'. $space_name:$username;
  // check if proposed username is reserved:
  $reserved1 = exec_sql("select user_name from users where user_name=?",array($username),"check if username already exists",1);
  $reserved2 = exec_sql("select currency from currencies where currency=?",array($username),"check if username exists as currency",1);
  $reserved3 = exec_sql("select trading_name from user_account_currencies where trading_name=?",array($username),"check u_a_c",1);
  $reserved4 = exec_sql("select email from users where email=?",array($email),"check email address",1);
  if ($reserved1) {echo "<p><font size=+2 color=red><b>$username</b> is a reserved username</font>"; goto signup_form;}
  if ($reserved2) {echo "<p><font size=+2 color=red><b>$username</b> is a reserved currency</font>"; goto signup_form;}
  if ($reserved3) {echo "<p><font size=+2 color=red><b>$username</b> is a reserved trading name </font>"; goto signup_form;}
  if ($reserved4) {echo "<p><font size=+2 color=red><b>$email</b> is an existing email</font>"; goto signup_form;}
  echo "<br>creating username <b>$username</b> ";
  $insert1 = exec_sql("INSERT into users (user_name, lname, fname, email, phone, phone2, init_space, init_curr, confirmed) 
                      values (?,?,?,?,?,?,?,?,?)", array($username, $lastname, $firstname, $email, 
                      $_REQUEST['phone'], $_REQUEST['phone2'], $_REQUEST['space_name'], 
                      $_REQUEST['currency']?$_REQUEST['currency']:$CFG->default_currency, 
                      ($CFG->site_type!='Live')?'1':'0'), "creating new username $username (perhaps it already exists?)",2);
  if ($insert1>0) { 
    //echo "Thank you, $firstname! <p>You will be notified as soon as your account has been manually confirmed";
    echo "Thank you, %name! You will be notified as soon as your account %username has been manually confirmed.";
    $address = $CFG->admin_email;
    $address2 = $CFG->maintainer;
    $confirmed = ($CFG->site_type=='Live')?"needs <a href={$CFG->url}/menu?confirm=1>confirmation</a>":"was auto-confirmed";
    $msg = "$firstname $lastname created an account $username on {$CFG->site_name} which $confirmed.     <p>OpenMoney IT Team";
    $subject = "{$CFG->site_name}: new account REQUESTED for $username";
    email_letter($address,$email,$subject,$msg);  
    email_letter($address2,$email,$subject,$msg);  
    //echo "<br>confirmation request is in process - may take several hours"; 
    //echo "If you have any troubles creating a new password for your account, please contact {$CFG->admin_email} or {$CFG->maintainer}";
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
<p><table width=30%>
<tr><th colspan=2><font size=4><br />OpenMoney {$CFG->site_type} Signup Form<br /><br /></font></th></tr>
<tr><td> <b>Username</b>:</td>
<td><input type=text name=username required=required pattern='[A-Za-z0-9]{2}.*' 
     title='minimum 2 letters and numbers, no spaces nor punctuation' autofocus=autofocus placeholder='<preferred user name>'></td></tr>
<tr><td><b>Email address</b></td><td><input type=email required name=email placeholder='<email>'></td></tr>
<tr><td>First Name</td><td><input type=text pattern='[A-Za-z0-9]*' title='use only letters and numbers' name=firstname
        placeholder='<optional given name>'></td></tr>
<tr><td>Last Name </td><td><input type=text pattern='[A-Za-z0-9]*' title='use only letters and numbers' name=lastname 
        placeholder='<optional family name>'></td></tr>
<tr><td>Phone </td><td><input type=tel pattern='[A-Za-z0-9]*' title='use only letters and numbers' name=phone 
        placeholder='<optional phone number>'></td></tr>
<tr><td>Phone2 </td><td><input type=tel pattern='[A-Za-z0-9]*' title='use only letters and numbers' name=phone2 
        placeholder='<second phone number>'></td></tr>
<tr><td>Space</td><td><input type=text pattern='[A-Za-z0-9\.]*' title='use only letters and numbers'  name=space_name 
        placeholder='<if known>'></td></tr>
<tr><td>Currency</td><td><input type=text name=currency pattern='[A-Za-z0-9\.]*' title='use only letters and numbers'  
        placeholder='<if known>'></td></tr>
<tr><td colspan=2><input type=submit></td></tr></table></form>";
}
require('footer.php');
?>