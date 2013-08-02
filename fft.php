<?php
$parent=' ';
$VERSION="2.1";
// This program is copyright GPL by Bruno Vernier (c) 2004-5-7-8
// Fast Furious Transforms of Moodle Databases  version $VERSION
///////////////////////////////////////////////////////////////////////////
// This program is free software; you can redistribute it and/or modify  //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation; either version 2 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// This program is distributed in the hope that it will be useful,       //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details:                          //
//                                                                       //
//          http://www.gnu.org/copyleft/gpl.html                         //
///////////////////////////////////////////////////////////////////////////

$manual="";	  
require_once('connect.php');
require('header.php');
$sidebarshow = 1;
$origin = isset($_REQUEST['origin'])?$_REQUEST['origin'] : $_SERVER['HTTP_HOST'];
$fields = isset($_REQUEST['fields'])?$_REQUEST['fields'] : '';
$table = isset($_REQUEST['table'])?$_REQUEST['table']: '';
$select = isset($_REQUEST['select'])?$_REQUEST['select'] : '';
$admin = $_SESSION['admin'];

if (!$admin) {  header("Location: login.php");exit;};

#echo "<style>#region-fft {background-color:bisque;}#region-fft2 {background-color:cyan;}</style>";
$style= "input {background-color:#FFFFCC; width:auto} #region-fft2 {background-color:#FFFFCC; width:auto}";
// prepare the fields
$origin='';
#if (!array_key_exists('origin',$_REQUEST)) {$origin= array_key_exists('HTTP_REFERER',$_SERVER)?$_SERVER['HTTP_REFERER']:$CFG->wwwroot;}
if (array_key_exists('fields',$_REQUEST)) {$fields2=$fields;
}else { $fields2=array() ;}
if (!array_key_exists('fields',$_REQUEST)) {
    $fields=array();
    $fields[]='id';
}else{
    $fields = explode(",",$fields);
}
if (!array_key_exists('table',$_REQUEST)) {$table='';}
// prepare the sidemenu
$menu="<div id=region-fft2 style='$style'><table id=fft valign=top><tr><th colspan=2>
       Fast Furious Transforms for <font color=red>{$CFG->site_name}</font> (c)GPL Bruno Vernier 2004-13 </th></tr><tr>";
$sidebar="<th width=15% valign=top align=left>
<br><a title='spaces' href='fft.php?table=spaces&fields=id,space_name,specification,access_control,welcome,created'>spaces</a>
<br><a title='currencies' href='fft.php?table=currencies&fields=id,currency,currency_steward,access_control,specification,created'>currencies</a>
<br><a title='journal' href='fft.php?table=journal&fields=id,created,tid,buyer_id,seller_id,amount,description,source,posted'>journal</a>
<hhr>
<br><a title='users' href='fft.php?table=users&fields=id,user_name,lname,fname,email,privFlags,prefFlags,confirmed,created'>users</a>
<br><a title='users to confirm' href='fft.php?table=users&fields=id,user_name,confirmed,lname,fname,email,privFlags,prefFlags,created&searchconfirmed=[^1]'>confirm</a>
<br><a title='user_spaces' href='fft.php?table=user_spaces&fields=id,space_id,user_id,class'>user_spaces</a>
<br><a title='user_journal' href='fft.php?table=user_journal&fields=id,user_id,tid,trading_account,with_account,amount,currency,description,created,balance,trading,flags'>user_journal</a>
<br><a title='user_account_currencies' href='fft.php?table=user_account_currencies&fields=id,currency_id,trading_name,user_space_id'>user_acc_curr</a>
<br><a title='logs' href='fft.php?table=logs&fields=id,logdate,ip,feedback,user_id,referer,browser'>logs</a>
<br><a title='eventLog' href='fft.php?table=eventLog&fields=id,type,subType,account_id,related_id,content,date'>event log</a>
";

// notes about each table
$notes='';
switch ($table) {
    case 'account':  $notes = "use privFlags for permissions ";
    break;
}

$sidebar .= "<p><font color=red size=+0>$notes</font>
             <p><a href=http://www.zytrax.com/tech/web/regex.htm>RegExp</a>
<br> Use at own RISK</td>";
if ($sidebarshow) { $menu.=$sidebar."</div>"; }
$menu .= "<td valign=top>";
echo $menu;
$timefields=array();
#$timefields=array('timemodified','timestart','timeend','timecreated','lastaccess','lastlogin','currentlogin','timeaccess',
#		 'time','timeadded','startdate','added','lastcron','timedue','timemarked','modified','created','seenon',
#		 'assesstimestart','assesstimefinish','timeseen','available','deadline','timeopen','timeclose','timefinish',
#		 'laststarttime','lastendtime','nextstarttime','timemark','timenext','timecached','overridden','stop');
if (!$table) { echo 'No table selected '.$manual."</td></tr></table></div>"; return ;}
// process changes
if (array_key_exists('submit',$_REQUEST) and $_REQUEST['submit']=='save') {
    for ($i=0; $i<count($_REQUEST['id']);$i++) {
    	foreach ($fields as $field ) {
	    $field=explode(':',$field);
            $data->$field[0] = trim($_REQUEST[$field[0]][$i]);
	    if ((in_array($field[0],$timefields)) ) {  //deal with human-readable dates
		$dfield='date'.$field[0];
		$datef=(int)$data->$field[0];
		$dfdata= $_REQUEST[$dfield][$i];
		$dfdata2= strtotime($_REQUEST[$dfield][$i]);
		if ($dfdata2!=$datef) {
		    $data->$field[0] = $dfdata2;
		}
	    }
        }

        if ($_REQUEST['process'][$i]=='mod') {update_record($table,$data);}
        if ($_REQUEST['process'][$i]=='del') {delete_record($table,array($_REQUEST['id'][$i]));}
        if ($_REQUEST['process'][$i]=='ins') {
	    $data->id='';
	    insert_record($table,$data);
	}                 	    
    }
}
// deal with special cases
if (!array_key_exists('sort',$_REQUEST)) {$sort='id';}
else {$sort=$_REQUEST['sort'];}
if (!array_key_exists('limitnum',$_REQUEST)) {$limitnum=20;}
else {$limitnum=$_REQUEST['limitnum'];}
if (!array_key_exists('limitfrom',$_REQUEST)) {$limitfrom=0;}
else {$limitfrom=$_REQUEST['limitfrom'];}
//print_object($_REQUEST);
switch ($table) {
    case 'special':
        $tabledata = "";
    break;
    default:
        if (array_key_exists('sort2',$_REQUEST) and $_REQUEST['sort2']!=$_REQUEST['sort']) {$sort=$_REQUEST['sort2'];}
        if (array_key_exists('sort2',$_REQUEST) and $_REQUEST['sort2']==$_REQUEST['sort']) {$sort=$_REQUEST['sort2'].' desc';}
        $selection='';
        foreach ($fields as $field) {
	    $f=explode(':',$field);
	    $f=$f[0];
	    $REGEXP=' REGEXP ';
	    #if ($CFG->dbtype == 'postgres7') {$REGEXP=' ~ ';}
	    if (array_key_exists("search$field",$_REQUEST) and $_REQUEST["search$field"]=='NULL') {$REGEXP=' is NULL or $f REGEXP ';}
	    if (array_key_exists("search$field",$_REQUEST)) {echo "...";}
	    if (array_key_exists("search$field",$_REQUEST) and $_REQUEST["search$field"]) {$selection=$f."$REGEXP'".$_REQUEST["search$field"]."'";}
	}
        $tabledata = get_records_select($table, $selection, $sort, $limitfrom , $limitnum );
        $bad_tables=array('grade_grades','grade_grades_history');
        if (! in_array($table,$bad_tables) ) {
           $tabledata2 = get_records_select($table, $selection, $sort);
        }else {$tabledata2=$tabledata;};
    break;
}
// main form area
$count_table = count($tabledata);
$count_table2 = count($tabledata2);

if ($count_table>0){
    echo " <form method=post action=''>
	   <input type=hidden name=table value='$table'>
	   <input type=hidden name=sort value='$sort'>
	   <input type=hidden name=fields value='$fields2'>
	   <input type=hidden name=origin value='$origin'>
           <div id=region-fft >
	   <table valign=top border><tr><th><a href='fft.php'>menu</a> <a href='index.php?student=".(array_key_exists('searchuserid:user:firstname',$_REQUEST)?$_REQUEST['searchuserid:user:firstname']:'')."'>back</b>
	   </th><th colspan=".count($fields)."><font size=+3>$table</font>
	   limit:<input name=limitnum value='$limitnum' size=3 style='width:2em'>
	   offset:<input  name=limitfrom value='$limitfrom' size=3 style='width:2em'> <input type=submit name=submit value='go'>
  	   </th></tr><tr><th>search<br><a href=http://dev.mysql.com/doc/mysql/en/Regexp.html>regexp</a></th>";
    $furl=''; #query part about search fields
    foreach ($fields as $field) {
	$furl.=array_key_exists("search$field",$_REQUEST)?"&search$field=".$_REQUEST["search$field"]:'';
    }
    foreach ($fields as $field) { // show fields in table header
	$f=array_key_exists("search$field",$_REQUEST)?$_REQUEST["search$field"]:'';
	$fcount=strlen($field);
        $selection = explode(':',$field);
	$uri = preg_replace('/\&sort2=.*/','',$_SERVER['REQUEST_URI']);
	$urlsort=$uri."&sort2=".$selection[0]."&sort=$sort&limitnum=$limitnum&limitfrom=$limitfrom$furl";
        echo "<th><a href='$urlsort'>$field</a><br><input size=".$fcount." name=search$field value='$f' style='4em'></th>";
    }
    $process="<select name=process[] style='width:6em'>
	       <option value='mod' selected>mod</option>
	       <option value='ins'>ins</option>
	       <option value='del'>del</option>
	       </select>";
    echo "</tr>";
    if ($tabledata) {
      foreach ($tabledata as $ii) { // main loop
        $size=5;//print_object($ii);
        echo "<tr><th>$process</th>";
        foreach ($fields as $field) { 
	    $selection = explode(':',$field);
	    $field=$selection[0];
	    $keytable=array_key_exists(1,$selection)?$selection[1]:"";
	    $sortfield=array_key_exists(2,$selection)?$selection[2]:"";
	    $dbconcat="left(concat($sortfield,' (id:',id,')'),30)";
	    #if ($CFG->dbtype=='postgres7') {$dbconcat="ltrim($sortfield||' (id:'||id||')',30)";}
	    $fields3='*';
	    if ($sortfield) {$fields3="id,$dbconcat";}
            if (array_key_exists($field,$ii)) {$fieldcontent=$ii[$field];}else{$fieldcontent="$field";}
	    echo "<th>";
	    if ($keytable) {
	        #$options = get_records_select_menu($keytable, $select="$select", $params=array(),$sortfield,"DISTINCT ". $fields3);
		$options = get_records_select_menu($keytable, $sortfield,$fieldcontent, $field);
		//choose_from_menu($options, $selection[0].'[]', $fieldcontent, 
		#select(array $options, $name, $selected = '', $nothing = array(''=>'choosedots',array())
		#echo html_writer::select($options, $selection[0].'[]', $fieldcontent, 
		#  (array_key_exists($fieldcontent,$options)?$options[$fieldcontent]:'choose2') 
                #   or "No Match for $fieldcontent" or 'choose', array());
                echo $options;
            }
	    if (!$keytable) {
	       $size = strlen("$fieldcontent")+3;
	       if (in_array($field,$timefields))  {
		 echo "<font size=-2><input name='date".$field."[]' value='".
		   $fieldcontent."' size=12 ></font><br>";
		   #echo "<font size=-2><input name='date".$field."[]' value='".userdate($fieldcontent,
                   #     "%d %b %Y    %H hours %M minutes %S seconds")."' size=12 ></font><br>";
	       }
	       if ($field!='id') {
		   if ($size>30) {
		       $rows = floor($size/30)+1;
		       $tcontent = preg_replace('/<textarea/','&lt;textarea',$fieldcontent);
		       $tcontent = preg_replace('/<textarea/','&lt;textarea',$ii[$field]);
		       $tcontent = preg_replace('/<\/textarea/','&gt;/textarea',$tcontent);
		       
		       echo "<textarea name='".$field."[]' onClick='this.rows=$rows;this.cols=80' 
							onBlur='this.rows=2;this.cols=30' rows=2 cols=30>$tcontent</textarea>";
		   }
		   if ($size<=30) {
		       $size2=$size+5;

		       $fieldcontent = preg_replace('/\'/','&apos;',$fieldcontent);
		       echo "<input name='".$field."[]' onClick='this.size=$size2' onBlur='this.size=$size'
 			     value='$fieldcontent' size='".$size." style='width:3em'>";
		   }
	       }
	       if ($field=='id') {
		   echo "<input type=hidden name='".$field."[]' value='".$ii[$field]."'>".$ii[$field];
	       }
	    }
	    echo "</th>";
        }
        echo "</tr>";
      }
    }
    $nextbatch=min(count($tabledata)+(array_key_exists('limitfrom',$_REQUEST)?$_REQUEST['limitfrom']:0),$count_table2);
    $nextbatch2=min($count_table2,$nextbatch+$limitnum);
    $nowbatch=max(0,$nextbatch-$limitnum);
    $nowbatch2=$nowbatch+$limitnum;
    $prevbatch=max(0,$nowbatch-$limitnum);
    $prevbatch2=$prevbatch+$limitnum;
    $uri = preg_replace('/\&limitfrom=.*/','',$_SERVER['REQUEST_URI']);
    echo "<tr><th></th><th colspan=".count($fields)."><input name=submit type=submit value='save'> ".count($tabledata)." records
	 <a title='prev batch of records' href='$uri&limitfrom=".$prevbatch."&limitnum=$limitnum'> [$prevbatch - $prevbatch2]</a>
	 <!--a title='cuttent batch of records' href='$uri&limitfrom=".$nowbatch."&limitnum=$limitnum'> [$nowbatch - $nowbatch2]</a-->
	 <a title='next batch of records' href='$uri&limitfrom=".$nextbatch."&limitnum=$limitnum'> [$nextbatch - $nextbatch2]</a>
total: $count_table2									      
	 </th></tr></table></div></form>";
}
echo "</th></tr></table>";
#echo " </div>";
//echo $OUTPUT->footer();
function get_records_select ($table, $selection, $sort='id desc', $limitfrom=1, $limitnum=20) {
  global $db2;
  $data = array();
  $selection = $selection?$selection:'';

  $table = preg_match('/[a-z_0-9]/i', $table)?$table:'INVALID_TABLE_NAME';
  $sort = preg_match('/[a-z_0-9]/i',$sort)?$sort:'INVALID_SORT_FIELDNAME';
  $limitfrom = preg_match('/[0-9]/i',$limitfrom)?$limitfrom:'INVALID_OFFSET';
  $limitnum = preg_match('/[0-9]/i',$limitnum)?$limitnum:'INVALID_LIMIT';
  $selections = explode('REGEXP',$selection);
  $condition = '';
  $sep = 'WHERE';
  $key = $val = 'N/A';
  if (count($selections)>1) {
    $key = $selections[0];
    $val = $selections[1];
 #   echo "KEY=$key";
    $key = preg_match('/[a-z_0-9]/i',$key)?$key:'INVALID_FIELD_NAME';
    $data[$key] = $val;
#    $condition .= " $sep $key REGEXP :$key ";
    $condition .= " $sep $key REGEXP $val ";
    $sep = ' AND ';
  }
  $prepare = "select * from $table $condition order by $sort limit $limitnum offset $limitfrom";
#  echo "<br>($key:$val) $prepare<p>";print_r($data);
  $query = $db2->prepare($prepare);
  $query->execute(array($val));
#  $query->execute($data);
  return $query->fetchAll(PDO::FETCH_BOTH);
}

function get_records_select_menu ($table, $field, $selected, $field_orig) {
  global $db2;
  $table = preg_match('/[a-z_0-9]/i',$table)?$table:'INVALID_TABLE';
  $field = preg_match('/[a-z_0-9]/i',$field)?$field:'INVALID_FIELD_NAME';
  $selection = $db2->prepare("select id,$field from $table ORDER by $field");
  $selection->execute();
  $selected2 = "<option value='$selected' selected>choose $selected</option>";
  $options='';
  foreach ($selection as $key=>$val){
    $value = $val[$field];
    $id = $val['id'];
    $id = preg_match('/[a-z_0-9]/i',$id)?$id:'INVALID_ID';
    if ($id==$selected) {  $selected2 = "<option value=$id SELECTED>$value ($id)</option>";}
    $options.="<option value='$id'>$value ($id)</option>";
  }
  return   "<select id='${field_orig}[]' name='$field_orig'>$selected2 $options</select>";
}

function update_record ($table, $data) {
  global $db2;
  $data2 = array();
  $sets = 'set ';
  $sep = '';
  foreach ($data as $key=>$val) {
    $data2[$key]=$val;
    $sets .= "$sep $key=:$key ";
    $sep=',';
  }
  $table = preg_match('/[a-z_0-9]/i',$table)?$table:'INVALID_TABLE';
  $query = $db2->prepare("update $table  $sets  where id = :id");
  $query->execute($data2);
}
function delete_record ($table, $data) {
  global $db2;
  $table = preg_match('/[a-z_0-9]/i',$table)?$table:'INVALID_TABLE';
  foreach ($data as $key) {
    $query = $db2->prepare("delete from $table  where id = ?");
    echo "<br>deleted $key from $table";
    $query->execute(array($key));
  }
}
function insert_record ($table, $data) {
  global $db2;
  $table = preg_match('/[a-z_0-9]/i',$table)?$table:'INVALID_TABLE';
  $query = $db2->prepare("insert into $table () values ()");
  echo "<br>inserted blank record into $table";
  $query->execute(array($key));
}

require('footer.php');


?>
