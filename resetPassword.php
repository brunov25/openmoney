<?php

require_once('password.php');
require_once('rest/src/openmoney/rest_connect.php');

$email = isset($_GET['email'])?$_GET['email']:'';

if ($email == '') {
	$username = isset($_GET['username'])?$_GET['username']:'';
	
	if ($username == '') {
		$error = "Username or Email is required!";
	}
	$username = mysqli_real_escape_string($db, $username);
	$user_q = mysqli_query($db, $test = "SELECT * FROM users WHERE user_name='$username' limit 1") or die($test . mysqli_error($db));
	
} else {
	$email = mysqli_real_escape_string($db, $email);
	$user_q = mysqli_query($db, $test = "SELECT * FROM users WHERE email='$email' limit 1") or die($test . mysqli_error($db));
		
}

$user = mysqli_fetch_array($user_q);
$reset_key = (String)$user['password2'];

if (password_verify( $reset_key, $_GET['reset']) ) {
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
	<input type="hidden" name="email" value="<?=$_GET['email']?>" />
	<input type="hidden" name="username" value="<?=$_GET['username']?>" />
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


	?>
	<html>
	<head>
	</head>
	<body>
	<div style="text-align: center">
		<div style="display: inline-block;font-family:arial,sans-serif;color:#660000;">
			Could Not Verify Link!
		</div>
	</div>
	</body>
	</html>
	<?

}
