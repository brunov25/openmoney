<?php
require_once('config.php');
require_once('password.php');
require_once('rest/src/openmoney/rest_connect.php');

$email = isset($_GET['email'])?$_GET['email']:'';
$email = urldecode($email);
$email = str_replace(" ","+",$email);//plus sign gets replaced with a space

$username = '';
if ($email == '') {
	$username = isset($_GET['username'])?$_GET['username']:'';
	$username = urldecode($username);
	
	if ($username == '') {
		$error = "Username or Email is required!";
		?>
		<html>
		<head>
		</head>
		<body>
		<div style="text-align: center">
			<div style="display: inline-block;font-family:arial,sans-serif;color:#660000;">
				<?=$error?>, or <a href="mailto:<?=$CFG->maintainer?>">contact support</a><br/>
			</div>
		</div>
		</body>
		</html>
		<?
		exit();
	}
	$username = mysqli_real_escape_string($db, $username);
	$user_q = mysqli_query($db, $test = "SELECT * FROM users WHERE user_name='$username' limit 1") or die($test . mysqli_error($db));
	
} else {
	$email = mysqli_real_escape_string($db, $email);
	$user_q = mysqli_query($db, $test = "SELECT * FROM users WHERE email='$email' limit 1") or die($test . mysqli_error($db));
		
}

$user = mysqli_fetch_array($user_q);
$reset_key = (String)$user['password2'];
$reset_hash = urldecode($_GET['reset']);
$reset_hash = str_replace(" ","+",$reset_hash);//plus sign gets replaced with a space

if (password_verify( $reset_key, $reset_hash) ) {
	//hash match show password reset form.
	?>
	<html>
	<head>
		<script type="text/javascript">
			function validate(){
				var passwordFieldOne = document.getElementById('password');
				var passwordFieldTwo = document.getElementById('password2');
				if (passwordFieldOne.value.length > 0 && passwordFieldTwo.value.length > 0) {
					if (passwordFieldOne.value===passwordFieldTwo.value) {
						// they are equal 
					} else {
						alert(" Both Passwords must be the same! ");
						return false;
					}
				} else {
					alert(" Both Password Fields cannot be empty! ");
					return false;
				}
			}
		</script>
	</head>
	<body>	
	<div style="text-align: center">
	<div style="display: inline-block;">
		<img alt="open money omLETsytem" src="css/images/icon.png" style=""/>
	</div>
	</div>
	<form name="resetPassword" action="resetPasswordSave.php" method="POST" onsubmit="return validate();">
	<input type="hidden" name="reset" value="<?=$_GET['reset']?>" />
	<input type="hidden" name="email" value="<?=isset($_GET['email'])?$_GET['email']:''?>" />
	<input type="hidden" name="username" value="<?=isset($_GET['username'])?$_GET['username']:''?>" />
		<div style="display:table;vertical-align:middle;margin:0 auto 0 auto;">

			<div style="display:table-row;">
				<div style="display:table-cell;text-align:right;font-family:arial,sans-serif;">
					<label for="password">Password:</label>
				</div>
				<div style="display:table-cell;">
					<input id="password" type="password" name="password" />
				</div>
			</div>
			<div style="display:table-row;">
				<div style="display:table-cell;text-align:right;font-family:arial,sans-serif;">
					<label for="password2">Retype Password:</label>
				</div>
				<div style="display:table-cell;">
					<input id="password2" type="password" name="password2" />
				</div>
			</div>
			<div style="display:table-row;">
				<div style="display:table-cell;">
					
				</div>
				<div style="display:table-cell;">
					<input type="submit" name="submit" style="font-family:arial,sans-serif;font-size:16px" value="   Reset Password   " />
				</div>
			</div>
		</div>
	</form>
	<? if (isset($_GET['error'])) { ?>
	<div style="text-align: center">
		<div style="display: inline-block;font-family:arial,sans-serif;color:#660000;">
			<?=$_GET['error']?>
		</div>
	</div>
	<? } ?>
	</body>
	</html>
	<?
	
} else {
	function email_letter($to,$from,$subject='no subject',$msg='no msg') {
		
		$headers =  "From: $from\r\n";
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= 'X-Mailer: PHP/' . phpversion();
		return mail($to, $subject, $msg, $headers);
	}
	$debug = true;
	if($debug) {
		$subject = "Could Not Verify Link on $CFG->url";
		$msg =  "Supplied Username: $username ~ System username: " . $user['user_name'] . " <br/>";
		$msg .= "Supplied Email: $email ~ System Email: " . $user['email'] . " <br/>";
		$msg .= "Supplied Reset: $reset_hash ~ System Reset Key: " . $user['password2'] . " <br/>";
		email_letter($CFG->maintainer,$CFG->system_email,$subject,$msg);
	
	}

	?>
	<html>
	<head>
	</head>
	<body>
	<div style="text-align: center">
		<div style="display: inline-block;font-family:arial,sans-serif;color:#660000;">
			Could Not Verify Link! please try again, or <a href="mailto:<?=$CFG->maintainer?>">contact support</a><br/>
		</div>
	</div>
	</body>
	</html>
	<?

}
