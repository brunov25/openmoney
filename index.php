<? //(c)GPL 2013 by Bruno Vernier and Mochael Linton
if(isset($_SESSION['fail'])) {echo "<div class='fail'>Login failed</div>";}
else if(isset($_SESSION['account'])) {header("Location: main.php"); }
//NOTE: edit customized frontpage in index_local.php
if (file_exists('index_local.php')) { header("Location: index_local.php");}
require('header.php');
?>

<br>   
  
    <!--h1>Login</h1-->
    <form action="login.php" method="post">
    <table class="front">
  <tr>
    <td><label for="username">Username</label></td>
    <td><input type="text" id="username" name="username" autofocus placeholder='<email address>' /></td>
  </tr>
  <tr>
    <td><label for="password">Password</label></td>
    <td><input type="password" id="password" name="password" required placeholder='<password>'/></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" value="Login" /></td>
    </tr>
</table>
    <br />
    <a href="signup.php">Request a new account</a>
   <br> <a href="signup.php?newpw=1">Request a new password</a>
   <br>
   <br> <a href="http://tinyurl.com/mjz4wbm" target="_blank">user guide to open money</a>
  </form>
 </div>
</div>
</center>


<? require('footer.php'); ?>
