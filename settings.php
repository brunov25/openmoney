<?// (c)2013 GPL by Bruno Vernier and Michael Linton
require_once('header.php');
$account = isset($_SESSION['account'])?$_SESSION['account']:'';
$user_id = isset($_SESSION['user_id'])?$_SESSION['user_id']:'';
$admin = isset($_SESSION['admin'])?$_SESSION['admin']:'';
require_once('password.php');

if (isset($_POST['old'])) {
    $old = $_POST['old'];
    $new = $_POST['new'];
    #echo "you have attempted to change a password $old to $new ";
    $user_records = exec_sql("SELECT * FROM users WHERE id=?",array($user_id),"extracting user's record");
    foreach($user_records as $row) {
      $old_pw = password_hash($old, PASSWORD_BCRYPT);
      $new_pw = password_hash($new, PASSWORD_BCRYPT);
      $db_pw2 = $row['password2'] or die("user is not allowed here");

      if (password_verify($old, $db_pw2)) {
	echo "SUCCESS";
        $update = exec_sql("update users set password2=? WHERE id = ?",array($new_pw,$user_id),"updating new password",2);
      }else {echo "<h3>FAILURE - original password incorrect</h3>";}
    }
} 
//change password:
echo "<p><table border><tr><th><h3>$account - $admin</h3></th></tr>
<tr><td><form method=POST name=chpw>old password: <input type=password name=old>
  <br>new password: <input type=password name=new>
  <input type=submit name=chpw value=change></form></td></tr>
<tr><td><a href=main.php>back</a></td></tr></table>";

if (isset($_POST['email'])) {
  echo "<p>&nbsp;<p>Contact Info is now updated";
  $res = exec_sql("update users set fname=?, lname=?, email=?, phone=?, phone2=?, address1=?, address2=?,
                  city=?, state=?, zip=?, country=?  where id = ?",
		  array($_POST['fname'],$_POST['fname'],$_POST['email'],$_POST['phone'],$_POST['phone2'],$_POST['address1'],
			$_POST['address2'],$_POST['city'],$_POST['state'],$_POST['zip'],$_POST['country'],$user_id),"updating contact info",2);
}

$info = exec_sql("SELECT * FROM users WHERE id=?",array($user_id),"extracting user's record");
$fname = $info[0]['fname'];
$lname = $info[0]['lname'];
$email = $info[0]['email'];
$phone = $info[0]['phone'];
$phone2 = $info[0]['phone2'];
$address1 = $info[0]['address1'];
$address2 = $info[0]['address2'];
$city = $info[0]['city'];
$state = $info[0]['state'];
$zip = $info[0]['zip'];
$country = $info[0]['country'];


echo "<p><FORM action='settings.php' method='post' name='contact'><p>&nbsp;<p>
<table><tr><th colspan=2>Update Contact Information:<p></th></tr>
<tr><td align='left'>Given Name: </td><td><input name='fname' type=text value='$fname' placeholder='{{First name:}}' ></td></tr>
<tr><td align='left'>Family Name: </td><td><input name='lname' type=text value='$lname' placeholder='{{Last name:}}' ></td></tr>
<tr><td align='left'>Email: </td><td><input name='email' type=email value='$email' placeholder='{{Email:}}' ></td></tr>
<tr><td align='left'>Phone: </td><td><input name='phone' type=text value='$phone' placeholder='{{Phone:}}' ></td></tr>
<tr><td align='left'>Phone2: </td><td><input name='phone2' type=text value='$phone2' placeholder='{{Phone2:}}' ></td></tr>
<tr><td align='left'>Address1: </td><td><input name='address1' type=text value='$address1' placeholder='{{Address1:}}' ></td></tr>
<tr><td align='left'>Address2: </td><td><input name='address2' type=text value='$address2' placeholder='{{Address2:}}' ></td></tr>
<tr><td align='left'>City: </td><td><input name='city' type=text value='$city' placeholder='{{City:}}' ></td></tr>
<tr><td align='left'>Province: </td><td><input name='state' type=text value='$state' placeholder='{{State:}}' ></td></tr>
<tr><td align='left'>Postal Code: </td><td><input name='zip' type=text value='$zip' placeholder='{{Zip:}}' ></td></tr>
<tr><td align='left'>Country: </td><td><input name='country' type=text value='$country' placeholder='{{Country:}}' ></td></tr>

</tr>
</table>
<P>
<INPUT TYPE=Submit Value='update contact info'>
<INPUT TYPE=HIDDEN name='id' value=''>
</FORM>
";

require_once('footer.php');
?>
