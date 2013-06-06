<? #require('header.php');?>
<center>    
  <div class="container-fluid">
    <h1>Login</h1>

    <? if(isset($_SESSION['fail'])) { ?>
    <div class="fail">Login failed</div>
    <? } else if(isset($_SESSION['account'])) {
      header("Location: main.php"); 
    } ?>
    <form action="login.php" method="post">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" />

      <br />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" />

      <br />

      <input type="submit" value="Login" />
    </form>
    <a href="signup.php">Request a new account</a>
   <br> <a href="signup.php?newpw=1">Request a new password</a>
  </div>
</center>
<? require('footer.php'); ?>
