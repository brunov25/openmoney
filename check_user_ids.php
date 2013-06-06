<? //(c) GPL 2013 bruno vernier and Michael Linton
  require('header.php');
  echo "</center><h3>Check User Ids</h3>";
  $results = exec_sql("SELECT * FROM user_journal where user_id='' or user_id='0'",array(),"checking if there is anything in user_journal");
  $check = '';
  foreach($results as $row) {
    $tid =  $row['tid'];
    $currency =  $row['currency'];
    $userid =  $row['user_id'];
    $trading = $row['trading_account'];
    $report = exec_sql("select * from FULL_QUERY where user_name=? and currency=?",array($trading, $currency),"details of full query");
    $check = " <br><font color=red><b>No Match</b></font> for $trading using $currency in uac";
    foreach ($report as $row2) {
      $uid = $row2['user_id'];
      $account = $row2['user_name'];
      if ($uid == $userid AND $userid>'') {
        $check=" <b>.</b>";
        continue;  // all is good
      } elseif ($userid <='') {
        echo "<br>$currency $tid $trading $userid";
        $check = '';
        echo " (BLANK)<br>... UPDATE user_journal set user_id='$uid' where  trading_account='$trading' and currency='$currency'";
        $update = exec_sql("update user_journal set user_id=? where  trading_account=? and currency=?",array($uid, $trading, $currency),
			   "updating user_journal with missing user_id $uid",2);      
      } else {
        echo "<br>$currency $tid $trading $userid";
        $check = '';
        echo " <font color=red><b>(MISMATCH)</b></font>";
        echo "<br>... UPDATE user_journal set user_id='$uid' where  trading_account='$trading' and currency='$currency'";
        $update = exec_sql("update user_journal set user_id=? where  trading_account=? and currency=?",array($uid, $trading, $currency),
			   "updating user_journal with correct user_id $uid",2);      
      }
      if ($check) {echo "<br>$currency $tid $trading $userid - $check";};
      echo " the user ID is: $uid";
    }
    echo $check;
  }
  echo '<br>done.';
  require('footer.php');  
?>
