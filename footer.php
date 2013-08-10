<div class="modal-footer">
<? 
$user_name = isset($_SESSION['user_name'])?$_SESSION['user_name']:'';
$feedback=$user_name?"<a href=feedback.php><font size=-1>feedback</font></a>":'';
$copyleft="<br><font size=-2 style='color:#CCC;'>&copy; 2006-2013 GPL by Open Money Development Group</font>";

echo "<center><p>$feedback $copyleft</center></div></body></html>";
?>
</div>