<?
require_once('header.php');
echo "<center><h3>Trading Names</h3>";

$file = isset($_FILES['file']) ? $_FILES['file']:'';
$rawdata = isset($_POST[ 'csv' ])? $_REQUEST['csv']:'';
$csv_data = isset($_POST[ 'save' ])? $_REQUEST['save']:'';
$new = isset($_POST[ 'new' ])? $_REQUEST['new']:'';
$order = isset($_REQUEST[ 'order' ])? $_REQUEST['order']:'currency';
$admin = is_admin();

// process csv data for new accounts
if ($csv_data AND $file['name'] AND $file["error"]>0) {
  echo "Error:  " . $file["error"] . "<br>";
} elseif ($csv_data AND $file['name']) {
  echo "Upload: " . $file["name"] . "<br>Type: " . $file["type"] . "<br>Size: " . ($file["size"] / 1024) . " kB<br>";
  $rawdata .= file_get_contents($file['tmp_name_csv']);
  echo "<pre>";print_r($rawdata);echo "</pre>";
}

if ($csv_data and $rawdata and is_admin()) { 
  $user_name = $_SESSION['user_name'];
  $query = "select * from users JOIN user_spaces on user_id=user_spaces.id
                     JOIN spaces on space_id=spaces.id where user_name=? and class='steward'";
  $result = exec_sql($query,array($user_name),"check if $user_name is a steward");
  $legit_spaces = array();
  foreach ($result as $r){$legit_spaces[] = $r['space_name'];}
  $data = preg_split( "/[\r\n\;]+/", $rawdata );
  foreach ($data as $line) {
    $skip = 0;
    if (!$line) {continue;}
    $datum = str_getcsv($line,',');
    $datum = array_slice($datum,0,8);
    $data_user_name = $datum[0];
    $data_fname = $datum[1];
    $data_lname = $datum[2];
    $data_email = $datum[3];
    $data_currency = $datum[4];
    $data_space_name = $datum[5];
    echo "<li>$data_user_name, $data_fname, $data_lname, $data_email, $data_space_name, $data_currency";
    if (!in_array($data_space_name,$legit_spaces)) {echo "<br>ERROR: $user_name not a steward for $data_space_name"; $skip=1;} ;
    if (!$skip) {echo "<br>processing ...";}
  }
}

// process self-created spaces, currencies and trading_names
if ($new and isset($_REQUEST['currency'])) {
    $space_name     = trim($_REQUEST['space_name']);
    $space_name     = preg_replace('/[^a-zA-Z0-9\.\+\-\_\@\%\&\^\~\!\?\<\>\:\;\=\*\$\#\s]/','',$space_name);
    $space_id   = exec_sql("select id from spaces where space_name=?",array($space_name),"space_id from space_name",1);
    //echo "SPACE NAME is $space_name and spaceid is $space_id";exit;
    //if (!$space_name) {echo "<br>no such space_name $space_id"; goto new_form;} //allow blank name
    //$space_name   = $_REQUEST['space_name'];
    $user_id      = $_REQUEST['user_id'];
    $user_name    = $_SESSION['user_name'];
    $currency     = trim($_REQUEST['currency']);
    //$currency     = preg_replace('/\s*(.*?\s*/','\1',$currency);
    $trading_name = trim($_REQUEST['trading_name']);
    $class        = $_REQUEST['class'];
    // restrictions of trading names, currencies and spaces
    $currency_prefix = preg_split( "/[\.]/", $currency );
    //$space_id = exec_sql("select id from spaces where space_name = ?",array($space_name),"getting space_id",1);
    //$space_id = $space_id?$space_id:1;
    echo "<table><tr><td>";
    if (!$space_id) { 
      $space_id = $admin?exec_sql("insert into spaces (space_name) values (?)",array($space_name),"create a new space",2):
	email_letter('bruno.vernier@gmail.com','michael.linton@gmail.com','OpenMoney SPACE request',
        "$user_name is requesting stewardship of SPACE $space_name on OpenMoney. {$CFG->url}/fft") ;
      echo $admin?"<br>NEW: created space <b>$space_name</b> ":'';
    } //else {echo "<br>using existing space $space_name";}
    $user_space_id = exec_sql("select id from user_spaces where space_id=? and user_id=?",
                                   array($space_id,$user_id),"consulting user_spaces",1);
    if (!$user_space_id) { 
      $user_space_id = exec_sql("insert into user_spaces (space_id,user_id,class) values (?,?,?)",
   				   array($space_id,$user_id,$class),"insert into user_spaces",2);
      //echo "<br>NEW: created user_space </b>";
    } //else {echo "<br>using existing user_space $space_name:$user_name";}

    // check that new currency name is not somebody else's trading account 
    $reserved = exec_sql("select user_name from FULL_QUERY where trading_name=? and user_id != ? limit 1",
			 array($currency_prefix[0],$user_id),"consulting currencies $currency",1);
    if ($reserved) {echo "Sorry, but currency $currency is reserved for user $reserved ";goto new_form;}

    // then check if the currency already exists
    if ($currency) {
      if ($trading_name=='' AND preg_match('/\./',$currency)) {echo "Dots not allowed in currency name in this context";goto new_form;}
      $currency_array = explode('.',$currency);
      //$currency = $space_name?$currency:$currency_array[0];
      $currency_new = $space_name?$currency.'.'.$space_name:$currency;
      $currency_id = exec_sql("select id from currencies where currency=? or currency=? ",array($currency,$currency_new),
                    "consulting currencies $currency",1);
      if (!$trading_name AND !$currency_id) { //add constraint and $space_name to prevent creating currencies in default BLANK space
        $currency_id = exec_sql("insert into currencies (currency,currency_steward) values (?,?)",
			      array($currency_new,$user_id),"currencies insert",2);
        echo $admin?"<br>NEW: currency <b>$currency</b> created ":'';
	email_letter('bruno.vernier@gmail.com','michael.linton@gmail.com','OpenMoney CURRENCY was created',
        "$user_name is creating currency $currency on OpenMoney.  {$CFG->url}/fft") ;
        goto new_form;
      }elseif (!$currency_id) {echo "<br>currency $currency does not already exists";  }
    }else{
      $currency_id = 1;
    }
    
    // user_account_currencies
    $trading_name = $trading_name?$trading_name:$user_name;
    $trading_name2 = $space_name?"$trading_name.$space_name":$trading_name;
    $trading_name2 = preg_replace('/^[\.]/', '', $trading_name2);
    //    echo "tradingname2 is $trading_name2 spacename is :${space_name}:";
    $uac_id = exec_sql("select id from user_account_currencies where user_space_id=? and currency_id=? and trading_name=?",
        array($user_space_id,$currency_id,$trading_name2),"looking up u_a_c id",1);
    if (!$uac_id AND $trading_name2 AND $currency_id AND $user_space_id) { //  prevent creating currencies in default BLANK space       
      $uac_id= exec_sql("insert into user_account_currencies (user_space_id,currency_id,trading_name) values (?,?,?)",
               array($user_space_id,$currency_id,$trading_name2),"inserting user_spaces_currency",2);
      echo $admin?"<br>NEW: user_account_currency <b>$trading_name:$space_name:$currency</b> created ":'';
      email_letter('bruno.vernier@gmail.com','michael.linton@gmail.com','OpenMoney new U_A_C record created',
        "$user_name added u_a_c record for trading_name=$trading_name, space=$space_name and currency=$currency on OpenMoney.
         <p>consult {$CFG->url}/fft if necessary") ;
    }
    echo "</td></tr></table>";
}

new_form:

$user_id = isset($_SESSION[ 'user_id' ])? $_SESSION['user_id']:'';
$account = isset($_SESSION['account'])?$_SESSION['account']:'';
$user_name = $_SESSION['user_name'];
$admin = $_SESSION['admin'];
$spaces ='';// "<option value=''>blank</option>";
$spaces2 = array();
$my_spaces = exec_sql("select * from USER_SPACES_QUERY where user_id=?",array($user_id),"full query records");
foreach($my_spaces as $row){ 
  $space_name = $row['space_name'];
  $space_id = $row['space_id'];
#  $spaces .= "<option value=$space_name>$space_name</option>";
  if (!in_array($space_name, $spaces2)) {
    $spaces .= "<option value=$space_name>$space_name</option>";
    $spaces2[]=$space_name;
  }
}
$onclick = "onClick=this.value='' ";
echo "<p><form method=POST  enctype='multipart/form-data' onsubmit=\"return confirm('Are you sure you want to do this?')\" >
<style>table.bruno input {size:7;} input[type=text] { color:blue;background-color: #FFFFCC;}
        NOTtr:nth-child(2n) {background-color: lightyellow}</style>
<table class=bruno><tr><th align=left>
      <input style='width:80px;' type=text name=trading_name id=trading_name $onclick placeholder='<trading name>' $onclick
      pattern='[A-Za-z0-9_]+' title='only letters and numbers, no spaces, no punctuation'>
                  <input type=hidden name=user_id id=user_id value='$user_id'>
            <input id=class name=class value=user type=hidden>
      <input style='width:80px;' type=text name=currency id=currency placeholder='<currency>' $onclick
         pattern='[^\s]+' title='no spaces' required=required >
      <br><select style='width:94px;margin-top:10px;' name=space_name id=space_name>$spaces</select>
      <input type=submit value='request new' name=new onBlur='alert(\"are you really sure?\")'></th></tr>

     </table><p>&nbsp;<p>&nbsp;<table><tr><th align=left><a href=new.php?order=trading>Trading Names</a>:</th>
       <th></th><th align=left><a href='new.php?order=currency'>Currencies</a>:</th></tr>";
//      <input style='width:80px;' type=text  name=space_name id=space_name $onclick placeholder='<space>' $onclick>

switch ($order) {
  case 'currency': $orderby = "space_name,currency"; break;
  case 'trading': $orderby = "trading_name asc"; break;
}
$records = exec_sql("select * from FULL_QUERY where user_name=? order by $orderby",array($user_name),"full query records");
foreach($records as $row) {
  $currency = $row['currency'];
  $trading_name = $row['trading_name'];
  $user_id = $row['user_id'];
  echo "<tr><td>$trading_name</td><td></td><td>$currency</td></tr>";
}

echo "</table><p>
";

#echo "<p>&nbsp;<p><form method=POST><table><tr><td colspan=2>Create a New Currency</a>";

if ($admin) {
  echo "<!--<p>&nbsp;<p><table border><tr><th colspan=2>Steward's Mass Creation of Accounts</th></tr>
      <tr><td>format (1 record per line):<br>&nbsp; 
      <p>user_name, <br>first_name, <br>last_name, <br>email, 
      <br>currency_name, <br>space_name, <br>class (user, steward)
      <br><p><br><input type='file' name='file' id='file'> <p><input type=submit id=save name=save value=upload></td>
      <td><textarea style='background-color:bisque;' rows=14 cols=100% name=csv id=csv></textarea></td><td></td></tr>-->";
}
echo "</form></table></center>";
require('footer.php');


?>