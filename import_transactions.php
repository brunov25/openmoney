<? //(c)2013 GPL by Bruno Vernier and Michael Linton - import transactions into om_repo
  require_once('header.php');
  if (isset($_SESSION) AND isset($_SESSION['admin']) AND $_SESSION['admin']) {echo '';} else {echo 'not authorized';exit;}
  $rawdata = isset($_POST[ 'transactions' ])? $_REQUEST['transactions']:'';
  $pw = isset($_REQUEST['pw']) ? $_REQUEST['pw']:'';
  $file = isset($_FILES['file']) ? $_FILES['file']:'';
  if ($file AND $file["error"]>0) {
    echo "Error: " . $file["error"] . "<br>";
  } elseif ($file) {
    echo "Upload: " . $file["name"] . "<br>Type: " . $file["type"] . "<br>Size: " . ($file["size"] / 1024) . " kB<br>";
    $rawdata .= file_get_contents($file['tmp_name']);
    echo "<pre>";print_r($rawdata);echo "</pre>";
  }
  if ($rawdata ) { //password check prior to processing
    $data = preg_split( "/[\r\n\;]+/", $rawdata );
    foreach ($data as $line) {
      if (!$line) {continue;}
      $created = date("Y-m-d H:i:s"); 
      $transactions = str_getcsv($line,',');
      $transactions = array_slice($transactions,0,8);
      if (count($transactions)!=8) {echo "<br><b>ERROR: missing data</b> in <pre>";print_r($transactions);echo "</pre>";}
      list($tid,$date,$trading_account,$with_account,$currency,$amount,$description,$tax) = $transactions;
      $date = date("Y-m-d H:i:s", strtotime($date));
      //ADD incorrect data checks here
      //echo "<li>INSERT into user_journal (tid,created,trading_account,with_account,currency,amount,description)                     
      //                   VALUES ($tid,$date,$trading_account,$with_account,$currency,-1*$amount,$description)<p>";
      $line1 = exec_sql("INSERT into user_journal (tid,created,trading_account,with_account,currency,amount,description) 
                         VALUES (?,?,?,?,?,?,?)",array($tid,$date,$trading_account,$with_account,$currency,-1*$amount,$description),
			"insert into user_journal first line",2);
      $line2 = exec_sql("INSERT into user_journal (tid,created,trading_account,with_account,currency,amount,description) 
                         VALUES (?,?,?,?,?,?,?)",array($tid,$date,$with_account,$trading_account,$currency,$amount,$description),
			"insert into user_journal second line",2);
      $buyer_id = exec_sql("select user_id from FULL_QUERY where trading_name=? and currency = ?",
                         array($trading_account,$currency),'getting buyer_id');
      $seller_id = exec_sql("select user_id from FULL_QUERY where trading_name=? and currency = ?",
                         array($with_account,$currency),'getting seller_id');
      $journal = exec_sql("INSERT into journal (tid,created,buyer_id,seller_id,amount,description,tax) VALUES (?,?,?,?,?,?,?)",
			  array($tid,$date,$buyer_id,$seller_id,$amount,$description,$tax),"insert into journal",2);
      if (!$journal) {echo "nothing to process";exit;}
      echo "<br>INSERT into journal (tid, date, buyer_id,seller_id,amount,description,tax) 
             VALUES ($tid,$date,$seller_id,$buyer_id,$currency,$amount,$description,$tax)";
      echo "<br>... INSERT into user_journal (tid, created, trading_account,currency,amount,description) 
             VALUES ($tid,$date,$trading_account,$with_account,$currency,-$amount,$description)";
      echo "<br>... INSERT into user_journal (tid, created,  trading_account,currency,amount,description) 
             VALUES ($tid,$date,$with_account,$trading_account,$currency,$amount,$description)";
    }
  }
require('check_user_ids.php');
// the actual form starts here:?>
<center><h3>OpenMoney</h3>
<form action='' method='post' enctype="multipart/form-data">
Import a set of transactions into journal and user_journal
<p><b>CSV Input Format</b>:<br>tid, date, buyer, seller, currency, amount, description, flags
    <br>and the date needs to be in <b>yyyy-mm-dd hh:mm:ss</b> format
<br><textarea rows=24 cols=120 name=transactions></textarea>
<br><input type="file" name="file" id="file">
<br><input type=submit>
<br><a href=menu.php>back to menu</a>
</form>
</center>
<?require('footer.php');?>
