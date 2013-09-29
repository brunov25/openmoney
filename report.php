<?// (c)2013 GPL by Bruno Vernier and Michael Linton
require_once('header.php');

if (!is_admin()) { header('main.php');}

$currency=isset($_REQUEST[ 'currency' ])? $_REQUEST['currency']:'';

$last_transactions = exec_sql("select *,currency, trading_account, trading from user_journal j where currency=? and trading = (select max(trading) from user_journal j2 where j2.trading_account = j.trading_account) order by trading_account,id desc; ",array($currency),"items in journal",0);

$data = '';
$total = 0.00;
$total2 = 0.00;
foreach ($last_transactions as $last) {
  $curr = $last['currency'];
  $user_id = $last['user_id'];
  $user_name = exec_sql("select user_name from users where id=?",array($user_id),'username',1);
  $trading_account = $last['trading_account'];
  $trading = $last['trading'];
  $date = $last['created'];
  $balance = $last['balance'];
  $total = $total + $balance;
  $total2 = $total2 + $trading;
  $amount = $last['amount'];
  $tid = $last['tid'];
  $description = $last['description'];
  $with_account = $last['with_account'];
  $data .="<tr><td>{$curr} {$date} {$user_name} ($user_id)</td><td> {$trading_account} -> {$with_account}</td>
           <td>$tid: $description </td>
           <td align=right>$amount</td>
           <td align=right bgcolor=#ffffe>$balance</td>
           <td align=right bgcolor=bisque>$trading</td>
  </tr>";
}

$all_currencies = exec_sql("select distinct currency from user_journal order by currency",array(),'currencies',0);
$currencies = "<select name=currency id=currency><option selected value=$currency>$currency</option>";
foreach ($all_currencies as $cur) {
  $curr = $cur['currency'];
  $currencies .= "<option value='{$curr}'>{$curr}</option>";
}

echo"<form><h3>{$CFG->site_name} OM Report for $currencies</select>
       <input type=submit value=change></h3><table border>$data
       </tr><th colspan=4> Total Balance = </th><th align=right>$total</th><th align=right bgcolor=bisque>$total2</th></tr></table></form>";

require('footer.php');
?>
