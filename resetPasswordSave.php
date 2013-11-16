<?php

require_once('password.php');
require_once('rest/src/openmoney/rest_connect.php');

$email = isset($_POST['email'])?$_POST['email']:'';

if ($email == '') {
	$username = isset($_POST['username'])?$_POST['username']:'';
	
	if ($username == '') {
		$error = urlencode("Username or Email is required!");
		header("Location: resetPassword.php?email=" . $_POST['email'] . "&reset=" . $_POST['reset'] . "&error=" . $error);
		exit();
	}
	$username = mysqli_real_escape_string($db, $username);
	$user_q = mysqli_query($db, $test = "SELECT * FROM users WHERE user_name='$username' limit 1") or die($test . mysqli_error($db));
	
} else {
	$email = mysqli_real_escape_string($db, $email);
	$user_q = mysqli_query($db, $test = "SELECT * FROM users WHERE email='$email' limit 1") or die($test . mysqli_error($db));
		
}

if($user = mysqli_fetch_array($user_q)){
	$reset_key = (String)$user['password2'];
	
	if (password_verify( $reset_key, $_POST['reset']) ) {
	
		$username = $user['user_name'];
		$password = mysqli_real_escape_string($db, $_POST['password2']);
		$password_hash = password_hash($password, PASSWORD_BCRYPT);
		$userUpdate_q = mysqli_query($db,$test = "UPDATE users SET password='$password_hash', password2='$password_hash' WHERE user_name='$username' ") or die($test . mysqli_error($db));
	
		header("Location: ./webclient/index.html");
		exit();
	} else {
		$error = urlencode("Could not verify link!");
		header("Location: resetPassword.php?email=" . $user['email'] . "&reset=" . $_POST['reset'] . "&error=" . $error);
		exit();
	}
} else {
	$error = urlencode("Could find user!");
	header("Location: resetPassword.php?email=" . $user['email'] . "&reset=" . $_POST['reset'] . "&error=" . $error);
	exit();
}

