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
      $db_pw2 = $row['password2'] or die("$user is not allowed here");

      if (password_verify($old, $db_pw2)) {
	echo "SUCCESS";
        $update = exec_sql("update users set password2=? WHERE id = ?",array($new_pw,$user_id),"updating new password",2);
      }else {echo "<h3>FAILURE - original password incorrect</h3>";}
    }
} 
//main settings menu:
echo "<p><table border><tr><th><h3>$account - $admin</h3></th></tr>
<tr><td><form method=POST name=chpw>old password: <input type=password name=old>
  <br>new password: <input type=password name=new>
  <input type=submit name=chpw value=change></form></td></tr>
<tr><td><a href=main.php>back</a></td></tr></table>";

require_once('footer.php');
?>
