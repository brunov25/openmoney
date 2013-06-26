<? //(c)GPL Bruno Vernier and Michael Linton
require('header.php');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
echo "<h3>Stewardship Stuff</h3>";
//process
$space_name = isset($_REQUEST['space'])?$_REQUEST['space']:'';
$steward = isset($_REQUEST['steward'])?$_REQUEST['steward']:'';
$class = isset($_REQUEST['class'])?$_REQUEST['class']:'';
$dupl = "ON DUPLICATE KEY UPDATE class=?"; //to deal with duplicates on unique keys

if ($space_name) {
  $space_id = exec_sql("select id from spaces where space_name = ?",array($space_name),"get space_id",1);
  //echo $space_id?"create subspace $space_name":goto steward_spaces_form ;
  if (!$space_id) { 
    $space_id  = exec_sql("insert into spaces (space_name) values (?)",array($space_name),'',2);  
    $result = exec_sql("insert into user_spaces (space_id,user_id,class) values (?,?,?)",array($space_id,$user_id,'steward'),'',2);  
    echo $result?"<br>created new subspace $space_name":"no such username as $steward";
    email_letter('bruno.vernier@gmail.com','michael.linton@gmail.com','OpenMoney SPACE creation',
		 "$user_name created SPACE $space_name on OpenMoney.  {$CFG->url}fft") ;
  }
  if ($steward) {
    $other_id = exec_sql("select id from users where user_name = ?",array($steward),"get user_id",1);
    $result  = exec_sql("insert into user_spaces (space_id,user_id,class) values (?,?,?) $dupl",
               array($space_id,$other_id,$class,$class),"inserting or updating membership for $other_id",2);  
    echo $result?"<br>made $steward a $class in $space_name":"no such username as $steward";    
    email_letter('bruno.vernier@gmail.com','michael.linton@gmail.com','OpenMoney new Steward assigned',
		 "$user_name assigned $steward to SPACE $space_name on OpenMoney.  {$CFG->url}/fft") ;
  }
  $removals = exec_sql("delete from user_spaces where class='remove'",array(),'removing members',2);
}

steward_spaces_form:
echo "<p><table><tr><th colspan=2>Spaces for which I am a steward:</th></tr>";
$my_spaces = exec_sql("select * from USER_SPACES_QUERY where user_id=? and class='steward'",array($user_id),"space steward");
foreach ($my_spaces as $row) {
  $space_id = $row['space_id'];
  $space_name = $row['space_name'];
  $space_name1 = $space_name?".$space_name":'';
  $space_name2 = $space_name?$space_name:'Root';
  $stewards = exec_sql("select * from USER_SPACES_QUERY where space_id=? order by class,user_name",array($space_id),"space steward");
  $users_data = "<datalist id=users_$space_name>";
  $stewards_data = '';
  $members = '';
  foreach ($stewards as $s) {
    $class = $s['class'];
    $user = $s['user_name'];
    $users_data .= "<option value='$user'>";
    //$stewards_data = ($class=='steward' AND $stewards_data)?"$stewards_data, $steward":"stewards(s): $steward";
    $user = ($class=='steward' )?"<b>$user</b>":"$user";
    $members = !$members?"$user":"$members, $user";
  }
  $users_data .= "</datalist>";
  $stewards_data .= "";
  $pattern=$space_name?"(pattern='$space_name|[\.a-zA-Z0-9]+\.$space_name)'":"pattern='[\.a-zA-Z0-9]+'";
  echo "<tr><form action='steward.php'>
        <td valign=top>make <input id=steward name=steward style='width:100px; color: #555555; display: inline-block; font-size: 14px; height: 20px; line-height: 20px; margin-bottom: 9px; padding: 4px 6px; ' pattern='[\.a-zA-Z0-9_-]+' title='alphanumeric usernames only'
              list='users_$space_name' placeholder='<user_name>'>$users_data 
             a <select id=class name=class style='width:100px;'>
               <option value='user'>user</option><option value='steward'>steward</option><option value='remove'>goner</option></select>
              <!--input type=submit value='new member'--> in</td><td>
        <input type=text id=space name=space $pattern value='$space_name' title='format: subspace.$space_name or just $space_name'
         onBlur='alert(\"are you really sure?\") required '>
        <input type=submit value='new member or subspace'><br><font size=-1>$members</font></td></form></tr>";
}
echo "</table><p><!--font size=-2>double click in steward box to see current stewards for each subspace</font-->";
require('footer.php');
?>