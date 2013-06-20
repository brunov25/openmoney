<? 
require_once('connect.php');
$batch = exec_sql(" SELECT id FROM user_journal WHERE id > (SELECT coalesce(max(id),0) FROM user_journal WHERE flags = 'm') 
                    ORDER BY id",
		  array(),"extracting data from user_journal");
foreach($batch as $row) {
  $id = $row['id'];
  // start of multi statement SQL (from Adam)
  $query2 = $db2->prepare("
  SELECT trading_account, currency, amount, tid  FROM user_journal WHERE id = :id
    INTO @trading_account, @currency, @amount, @tid;

  SET @balance = (SELECT COALESCE((                                                                                                        
    SELECT balance  FROM user_journal
    WHERE trading_account = @trading_account
    AND currency = @currency  AND id <  :id
    ORDER BY id DESC LIMIT 1)
  ,0)) + @amount;

  SET @trading = (SELECT COALESCE((
    SELECT trading FROM user_journal
    WHERE trading_account = @trading_account
    AND currency = @currency                
    AND id <  :id
    ORDER BY id DESC LIMIT 1)
  ,0)) + (SELECT IF(@tid LIKE '%-r', -abs(@amount), abs(@amount)));    

  UPDATE user_journal SET
    balance = @balance,
    trading = @trading,
    flags = 'm'
   WHERE id = :id ");
  $query2->bindParam(':id', $id);
  $query2->execute();
  $query2->closeCursor(); //must close cursor after multi-statement sql query
}
?>
