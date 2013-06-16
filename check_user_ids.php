<? //(c) GPL 2013 bruno vernier and Michael Linton
//  require('header.php');
  require_once('connect.php');
  echo "<p>Checking User Ids";
  $results = exec_sql("SELECT * FROM user_journal where user_id='' or user_id='0' or user_id is null",array(),"check ids in user_journal");
  $check = '';
  foreach($results as $row) {
    $tid =  $row['tid'];
    $currency =  $row['currency'];
    $userid =  $row['user_id'];
    $trading = $row['trading_account'];
    $with = $row['with_account'];
    $report = exec_sql("select * from FULL_QUERY where trading_name=? and currency=?",array($trading, $currency),"details of full query");
    //$report2 = exec_sql("select * from user_account_currencies where trading_name=? ",array($trading),"");
    $check = " <br><font color=red><b>No Match</b></font> for tid:$tid $trading ($with) using $currency in uac ".count($report);
    foreach ($report as $row2) {
      $uid = $row2['user_id'];
      $account = $row2['user_name'];
      if ($uid == $userid AND $userid>'') {
        $check=" <b>.</b>";
        continue;  // all is good
      } elseif ($userid <='') {
        //echo "<br>$currency $tid $trading $userid";
        $check = '';
        echo "<br>(BLANK)... UPDATE user_journal set user_id='$uid' where  trading_account='$trading' and currency='$currency'";
        $update = exec_sql("update user_journal set user_id=? where  trading_account=? and currency=?",array($uid, $trading, $currency),
			   "updating user_journal with missing user_id $uid",2);      
      } else {
        //echo "<br>$currency $tid $trading $userid";
        $check = '';
        echo " <br><font color=red><b>(MISMATCH)</b></font>";
        echo "... UPDATE user_journal set user_id='$uid' where  trading_account='$trading' and currency='$currency'";
        $update = exec_sql("update user_journal set user_id=? where  trading_account=? and currency=?",array($uid, $trading, $currency),
			   "updating user_journal with correct user_id $uid",2);      
      }
      if ($check) {echo "<br>$currency $tid $trading $userid - $check";};
      echo " the user ID is: $uid";
    }
    echo $check;
  }
echo '<br>done.<p>';
//require('footer.php');  
?>
