<div class="modal-footer">
<? 
$user_name = isset($_SESSION['user_name'])?$_SESSION['user_name']:'';
$feedback=$user_name?"<a href=feedback.php><font size=-1>feedback</font></a>":'';
echo "<p>$feedback</center></div></body></html>";
?>
</div>