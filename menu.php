<?// (c)2013 GPL by Bruno Vernier and Michael Linton
require_once('header.php');

if (!is_admin()) { header('main.php');}
$_SESSION['time'] = time();

if (isset($_REQUEST['batch']) AND $_REQUEST['batch']>'') { require('batch.php');}

$account = isset($_SESSION['account'])?$_SESSION['account']:'';
$user_id = isset($_SESSION['user_id'])?$_SESSION['user_id']:'';
$admin = isset($_SESSION['admin'])?$_SESSION['admin']:'';

// METRICS
$transactions = exec_sql("SELECT count(*) from journal",array(),"items in journal",1);
$user_ids = exec_sql("SELECT count(*) as total from user_journal where user_id=''",array(),"user_ids in user_journal",1);
$batch = exec_sql("SELECT count(*) as total from user_journal where flags=''",array(),"batch data",1);
$unconfirmed = exec_sql("SELECT count(*) as total from users where confirmed!='1'",array(),"unconfirmed users",1);
$most_recent = exec_sql("SELECT max(created) as total from user_journal order by created desc limit 1",array(),"most recent transaction",1);

echo"<h3>OpenMoney Beta $admin Menu</h3><table border>
<tr><td><a href=import_transactions.php>Import Transactions</a> </td><td>$transactions transactions</td></tr>
<tr><td><a href=check_user_ids.php>Check User_ids</a> </td><td>$user_ids unmatched user_ids</td></tr>
<tr><td><a href=menu.php?batch=1>batch </a> </td><td>$batch items still to process</td></tr>
<tr><td><a href=uac.php>uac</a> last:</td><td>$most_recent</td></tr>
<tr><td><a href='fft.php?table=users&fields=id,user_name,confirmed,lname,fname,email,privFlags,prefFlags,created&searchconfirmed=[^1]'>confirm</a></td><td>$unconfirmed unconfirmed users; <a href=pw.php?confirm=1>mail them passwords</a></td></tr>
</table>";

require('footer.php');
?>
