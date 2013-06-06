<?//(c)2013 bruno vernier and Michael Linton
require('header.php');
$report = exec_sql("select * from FULL_QUERY ORDER by currency, user_name, user_id",array(),"prepare u_a_c summary report ");
echo "<table><tr><th colspan=4><font size=+2 color=blue>User Member Currency Summary</font></th></tr>
         <tr><th>Account</th><th align=right>Balance</th><th align=right>Volume</th></tr>";
foreach($report as $row) {
  $currency = $row['currency'];
  $account = $row['user_name'];
  echo "<tr><th>$currency</th><td colspan=3>$account</td></tr>";
  $user_journal = exec_sql("select * from user_journal where currency=? and trading_account=?
         and trading >= (select max(trading) from user_journal where currency=? and trading_account=?) order by user_id,currency",
         array($currency,$account,$currency,$account),"preparing user_journal data");
  foreach($user_journal as $row2) {
    $uid = $row2['user_id'];
    $balance = $row2['balance'];
    $trading = $row2['trading'];
    echo "<tr><td></td>
           <td align=right>$currency $balance</td>
           <td align=right>$currency $trading</td>
           </tr>";
  }
  echo "</tr>";
}
echo "</table>";
require('footer.php');
?>
