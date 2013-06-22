<? //(c)GPL 2013 Bruno Vernier and Michael Linton for OpenMoney 
require_once('header.php') ;
if (!isset($_SESSION['account'])) {header("Location: index.php");}
$account = $_SESSION['account'];
$currency = $_SESSION['currency'];
$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];
$order = isset($_REQUEST['order'])?$_REQUEST['order']:'currency';

process_submitted_transaction:
if (isset($_REQUEST['amount']) AND $_REQUEST['amount']>0) {
  $tid=time();
  $created = $_REQUEST['created'];
  $description = $_REQUEST['description'];
  $trading_account = $_REQUEST['trading_account'];
  $with_account = $_REQUEST['with_account'];
  $amount = $_REQUEST['amount'];
  $currency = $_REQUEST['currency'];
  $space_name = explode('.',$trading_account);
  $space_name= array_key_exists(1,$space_name)?$space_name[1]:'';
  if ($trading_account == $with_account) {
     echo "<h3>You cannot trade with yourself</h3>"; 
     goto main_transaction_form;
  }
  $with_partner = exec_sql("select trading_name from user_account_currencies where trading_name=? ",array($with_account),
			   "does my ($trading_account) trading partner, $with_account, really exist ?",1);
  if (!$with_partner) {
     echo "<h3><font color=red>You cannot trade with non-existing partner $with_account</font></h3>"; 
     goto main_transaction_form;
  }
  $with_id = exec_sql("select user_id from FULL_QUERY where trading_name=? and currency=?", 
                           array($with_account,$currency),"user_id in uac $with_account",1);
  if ((!$with_id) OR ($with_id==0)) {
     echo "<h3><font color=red>Your partner is not a participant in $currency </font></h3>"; 
     goto main_transaction_form;
  }
  $insert1 = exec_sql("insert into user_journal (user_id, tid, created, description, trading_account, with_account, currency, amount)
     values ( ?,?,?,?,?,?,?,? )",array($user_id,$tid,$created,$description,$trading_account,$with_account,$currency,-1*$amount),
		      "inserting new transaction in user_journal",2);
  $insert2 = exec_sql("insert into user_journal (user_id, tid, created, description, with_account, trading_account, currency, amount)
     values (?,?,?,?,?,?,?,?)",array($with_id,$tid,$created,$description, $trading_account, $with_account, $currency, $amount),
		      "inserting transaction to user_journal",2);
  $buyer_id = $seller_id = 0;
  $insert3 = exec_sql("insert into journal (tid, created, description, buyer_id, seller_id, amount) values (?,?,?,?,?,?)",
		      array($tid,$created,$description, $buyer_id, $seller_id, $amount),
		      "inserting transaction into journal",2);
  require_once('batch.php');
}

main_transaction_form:
$currencies = "<option selected value='cc'>cc</option>";
$trading_accounts = "<option selected value='$account'>$account</option>";
$trading_accounts_db = exec_sql("select distinct trading_name from users join user_spaces on user_id=users.id
                         join user_account_currencies on user_space_id = user_spaces.id where user_name=?",array($user_name),
				"creating a list of $user_name trading names");                        
foreach ($trading_accounts_db as $a) {$trading_accounts .="<option value='".$a['trading_name']."'>".$a['trading_name']."</option>";}
$currencies_db = exec_sql("select distinct currency  from user_account_currencies join currencies on currency_id=currencies.id
                          JOIN user_spaces on user_spaces.id = user_space_id where user_id=?",array($user_id),
			  "creating a list of appropriate currencies");
foreach($currencies_db as $c) {$currency = $c['currency'];$currencies .= "<option value='$currency'>$currency</option>";}
$date = date("Y-m-d H:i:s"); 
$date2 = date("M d"); 
$onfocus = "onfocus=this.value=''";

echo "<h2>{$CFG->site_name}</h2>
      <form action='' method='post'>
      <input type='hidden' id='current_account' value='$account' />
      <input type='hidden' id='user_name' value='$user_name' />
      <input type='hidden' id='current_currency' value='$currency' />
      <input type='hidden' name='created' id='created' value='$date'/>

      <table width=50% border=0 class='ttable transactions'>
        <tr>
            <td colspan=2>Transaction:<br><label>$date2</label><br>
               <input type='text' id='description' name='description' $onfocus placeholder='<description>'></td>
            <td>Account:<br><select id='trading_account' name='trading_account'> $trading_accounts </select><br>
            <input type=text id='with_account' name='with_account' placeholder='<trading partner>' required=required
                 pattern='[a-zA-Z0-9\.]+' $onfocus title='only letters, numbers and dots, no spaces' autofocus=autofocus> </td>
            <td>Currency:<br><select id='currency' name='currency'> $currencies     </select>  <br>
                 <input type='number' id='amount' name='amount' placeholder='0.00' min='0.01' max='9999.99' step='0.01' 
                 title='more than zero!' required=required /></td>
            <td><input type='submit' value='send' /></td>
        </tr>
          </table><table border ><tr> <th align=left colspan=2><a href=main.php?order=date>Date</a> </th> 
            <th align=left><a href=main.php?order=trading>Trading</a> &rarr; <a href=main.php?order=with>With</a></th>
           <th align=right>Amount</th>
           <th align=right>Balance</th><th><a href=main.php?order=currency>cc</a></th> </tr>";

//creating historical journal
$currency = isset($_REQUEST['currency'])?$_REQUEST['currency']:'';
$account = $account?$account:$user_name;
switch ($order) {
  case 'currency': $orderby = "currency, id desc";break;
  case 'date': $orderby = "created asc";break;
  case 'trading': $orderby = "trading_account, created desc";break;
  case 'with': $orderby = "with_account, created desc";break;
}
$history_db = exec_sql("select * from user_journal where user_id=? order by $orderby",array($user_id),
		       "reading historic transaction data");
$new_currency='';
$change_color=0;
$ynow = date('Y');
foreach($history_db as $h) {
  $date = $h['created'];
  $ynow2 = date('Y', strtotime($date));
  $date2 = ($ynow!=$ynow2)?date('Y M d', strtotime($date)):date('M d', strtotime($date));
  $description = $h['description'];
  $from = $h['trading_account'];
  $with = $h['with_account'];
  $amount = $h['amount'];
  $currency = $h['currency'];
  $edge = $new_currency==$currency?'':"<tr bgcolor='lightyellow'><th colspan=4></th></tr>";
  $change_color = $edge?$change_color+1:$change_color;
  $color = $change_color%2?'#ffffdd':'';  
  $new_currency = $currency;
  $flags = $h['flags'];
  $balance = ($orderby=='currency, id desc')?$h['balance']:'';
  $direction = $amount<0?'&rarr;':'&larr;';
  //if ($balance=='0.00') {$balance ='TBC';}
  $tid = $h['tid'];
  $rid = $h['id'];
  $currency_table = "cc is for this";
  echo "$edge <tr bgcolor=$color><td colspan=2>$date2:  $description</td><td>$from $direction $with</td>
        <td align=right><b>$amount</b></td><td align=right> $balance</td><td onclick=document.getElementById('here').innerHTML='currency'>$currency</td></tr>";
}

echo "</table><span id=here name=here></span></form>";

require_once('footer.php');
?>
