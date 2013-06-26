<?

require('header.php');

$browser = $_SERVER['HTTP_USER_AGENT'];
$referer = $_SERVER['HTTP_REFERER'];
$remote_addr = $_SERVER['REMOTE_ADDR'];
$feedback = isset($_REQUEST['feedback'])?$_REQUEST['feedback']:'';
$back = isset($_REQUEST['back'])?$_REQUEST['back']:'';

if ($feedback) { //process
  $user_name=$_SESSION['user_name'];
  $user_id=$_SESSION['user_id'];
  $email=$_SESSION['email'];
  echo "<p>Thank you so much for your valuable feedback, $user_name.  <a href=$back>Go Back</a>";
  email_letter('bruno.vernier@gmail.com','bruno.vernier@gmail.com',"OpenMoney FEEDBACK from $user_name",
               "Openmoney Feedback for $back<br>$user_name ($remote_addr) using $browser wrote: <p>$feedback <p>$email");
  $res = exec_sql("insert into logs (logdate,ip,feedback,user_id,referer,browser) values (?,?,?,?,?,?)",
		  array(date("Y-m-d H:i:s"),$remote_addr,$feedback,$user_id,$back,$browser),'',2 );
  exit;
  //header("Location: $back");
}
echo "<form>
<h3>Feedback</h3>
<p>Please report any bugs, suggestions or comments here:<br><br>
<textarea name=feedback id=feedback ></textarea>
<input type=hidden name=back value='$referer'><br>
<input type=submit>
</form>
";
//phpinfo();
require('footer.php');

?>