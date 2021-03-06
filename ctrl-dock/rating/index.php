<?
// User settings
$rater_ip_voting_restriction = false; // restrict ip address voting (true or false)
$rater_ip_vote_qty=1; // how many times an ip address can vote
$rater_already_rated_msg="You have already rated this item. You were allowed ".$rater_ip_vote_qty." vote(s).";
$rater_not_selected_msg="You have not selected a rating value or did not provide supporting comments.";
$rater_thankyou_msg="Thank you for your rating.";
$rater_generic_text="this item"; // generic item text
$rater_end_of_line_char="\n"; // may want to change for different operating systems

// DO NOT MODIFY BELOW THIS LINE
include_once("config.php");

$ticket_id		=$_REQUEST["ticket_id"];
$rated_staff	=$_REQUEST["rated_staff"];
$rated_by		=$_REQUEST["rated_by"];
$source			=$_REQUEST["source"];
$subject		=$_REQUEST["subject"];
$ticket_status	=$_REQUEST["ticket_status"];


if ($ticket_status=="open"){
	$sql="update ticket_rating set closed_rating=0 where ticket_id='$ticket_id'";
	$result = mysql_query($sql);
}

// Check if the ticket has been rated after it has been closed.

$sql="select * from ticket_rating where ticket_id='$ticket_id'";
$result = mysql_query($sql);
$rating_count=mysql_num_rows($result);

if ($rating_count>0){
	$row = mysql_fetch_row($result);
	$closed_rating=$row[6];
}

?>
<table border=0 width=100% cellspacing=1 cellpadding=3>
<tr>
	<td bgcolor=#DDDDDD><b><font face=Arial size=2>Ticket ID</b></td>	
	<td bgcolor=#DDDDDD><font face=Arial size=2><?=$ticket_id;?></td>	
</tr>
<tr>
	<td bgcolor=#DDDDDD><font face=Arial size=2><b>Subject</b></td>	
	<td bgcolor=#DDDDDD><font face=Arial size=2><?=$subject;?></td>	
</tr>
<tr>
	<td bgcolor=#DDDDDD><font face=Arial size=2><b>Support Staff </b></td>	
	<td bgcolor=#DDDDDD><font face=Arial size=2><?=$rated_staff;?></td>
</tr>
</table>
<hr>
<?
echo '<font face=Arial size=2>';


if(!isset($rater_id)) $rater_id=1;
if(!isset($rater_item_name)) $rater_item_name=$rater_generic_text;

$rater_filename='item_'.$rater_id.".rating";
$rater_rating=0;
$rater_stars="";
$rater_stars_txt="";
$rater_rating=0;
$rater_votes=0;
$rater_msg="";

// Rating action
if(isset($_REQUEST["rate".$rater_id])){
if(isset($_REQUEST["rating_".$rater_id]) && strlen($_REQUEST["comments"])>0){
//if(isset($_REQUEST["rating_".$rater_id])){
  while(list($key,$val)=each($_REQUEST["rating_".$rater_id])){
   $rater_rating=$val;
  }
  $comments=$_REQUEST["comments"];
  
  // Rating available here
  if($rater_rating==5){$rating_text="Excellent";}
  if($rater_rating==4){$rating_text="Very Good";}
  if($rater_rating==3){$rating_text="Good";}
  if($rater_rating==2){$rating_text="Fair";}
  if($rater_rating==1){$rating_text="Poor";}
  echo "<b>You gave a rating of $rating_text ($rater_rating) with the following comments</b>";
  echo '<br>';
  echo "$comments";
  echo '<br>';
  

  $now=mktime();
  if ($closed_rating==0){
	  $sql ="delete from ticket_rating where ticket_id='$ticket_id'";
	  $result = mysql_query($sql);
	  
	  if($ticket_status=="closed"){ $closed_rating=1;}
	  $sql ="insert into ticket_rating";
	  $sql.=" values ('$ticket_id','$rated_staff','$rated_by','$now','$rater_rating','$comments',$closed_rating)";
	  $result = mysql_query($sql);
  }
  
  
  
		  // Assign star image
		if ($rater_rating <= 0  ){$rater_stars = "./img/00star.gif";$rater_stars_txt="Not Rated";}
		if ($rater_rating >= 0.5){$rater_stars = "./img/05star.gif";$rater_stars_txt="0.5";}
		if ($rater_rating >= 1  ){$rater_stars = "./img/1star.gif";$rater_stars_txt="1";}
		if ($rater_rating >= 1.5){$rater_stars = "./img/15star.gif";$rater_stars_txt="1.5";}
		if ($rater_rating >= 2  ){$rater_stars = "./img/2star.gif";$rater_stars_txt="2";}
		if ($rater_rating >= 2.5){$rater_stars = "./img/25star.gif";$rater_stars_txt="2.5";}
		if ($rater_rating >= 3  ){$rater_stars = "./img/3star.gif";$rater_stars_txt="3";}
		if ($rater_rating >= 3.5){$rater_stars = "./img/35star.gif";$rater_stars_txt="3.5";}
		if ($rater_rating >= 4  ){$rater_stars = "./img/4star.gif";$rater_stars_txt="4";}
		if ($rater_rating >= 4.5){$rater_stars = "./img/45star.gif";$rater_stars_txt="4.5";}
		if ($rater_rating >= 5  ){$rater_stars = "./img/5star.gif";$rater_stars_txt="5";}
		echo '<div>';
		echo '<span  class="rating"><img src="'.$rater_stars.'?x='.uniqid((double)microtime()*1000000,1).'" alt="'.$rater_stars_txt.' stars" /></span>';
		echo '</div>';
		echo '<hr>';
		  
  
  
  
  $rater_ip = getenv("REMOTE_ADDR"); 
  $rater_file=fopen($rater_filename,"a+");
  $rater_str="";
  $rater_str = rtrim(fread($rater_file, 1024*8),$rater_end_of_line_char);
  fwrite($rater_file,$rater_rating."|".$rater_ip.$rater_end_of_line_char);
  $rater_msg=$rater_thankyou_msg;
  fclose($rater_file);
 }else{
  $rater_msg=$rater_not_selected_msg;
 }
}

// Get current rating
if(is_file($rater_filename)){
 $rater_file=fopen($rater_filename,"r");
 $rater_str="";
 $rater_str = fread($rater_file, 1024*8);
 if($rater_str!=""){
  $rater_data=explode($rater_end_of_line_char,$rater_str);
  $rater_votes=count($rater_data)-1;
  $rater_sum=0;
  foreach($rater_data as $d){
   $d=explode("|",$d);
   $rater_sum+=$d[0];
  }
  $rater_rating=number_format(($rater_sum/$rater_votes), 2, '.', '');
 }
 fclose($rater_file);
}else{
 $rater_file=fopen($rater_filename,"w");
 fclose($rater_file);
}

//Show rating form only if the the user logged in is the one who is the source of the ticket
if ($rated_by==$source && $closed_rating==0){
	// Show Form
	echo '<div class="hreview">';
	echo '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
	echo '<br><br>';
	echo '<div>';
	echo '<b>Rate the quality of support that your received for this support request.</b>';
	echo '<br><br>';
	echo '<label for="rate1_'.$rater_id.'"><input tabindex=5 type="radio" value="1" name="rating_'.$rater_id.'[]" id="rate1_'.$rater_id.'" />Poor</label>';
	echo '<label for="rate2_'.$rater_id.'"><input tabindex=4 type="radio" value="2" name="rating_'.$rater_id.'[]" id="rate2_'.$rater_id.'" />Fair</label>';
	echo '<label for="rate3_'.$rater_id.'"><input tabindex=3 type="radio" value="3" name="rating_'.$rater_id.'[]" id="rate3_'.$rater_id.'" />Good</label>';
	echo '<label for="rate4_'.$rater_id.'"><input tabindex=2 type="radio" value="4" name="rating_'.$rater_id.'[]" id="rate4_'.$rater_id.'" />Very Good</label>';
	echo '<label for="rate5_'.$rater_id.'"><input tabindex=1 type="radio" value="5" name="rating_'.$rater_id.'[]" id="rate5_'.$rater_id.'" />Excellent</label>';
	echo '<input type="hidden" name="rs_id" value="'.$rater_id.'" />';
	echo '<br><br>';
	echo '<b>Provide your feedback / comments for your rating.</b>';
	echo '<br><br>';
	echo '<textarea tabindex=6 rows=5 cols=40 name=comments></textarea>';
	echo '<br><br>';
	echo '<input tabindex=7 type="submit" name="rate'.$rater_id.'" value="Rate" />';

	echo "<input type=hidden name=rated_staff value=$rated_staff>";
	echo "<input type=hidden name=rated_by value=$rated_by>";
	echo "<input type=hidden name=ticket_id value=$ticket_id>";
	echo "<input type=hidden name=subject value='$subject'>";
	echo "<input type=hidden name=ticket_status value='$ticket_status'>";

	echo '<input type=button value="Close this window" onclick="javascript:window.close()">';
	echo '</div>';
	if($rater_msg!="") echo "<div>".$rater_msg."</div>";
	echo '</form>';
	echo '</div>';

}

// Check if ticket has already been rated
$sql="select * from ticket_rating where ticket_id='$ticket_id' order by rated_date desc limit 1";
$result = mysql_query($sql);
$rating_count=mysql_num_rows($result);

// Display last rating information
if ($rating_count>0){
	echo "<hr>";
	$row = mysql_fetch_row($result);
	
	$rated_date		=date("d M Y H:i",$row[3]);
	$rating			=$row[4];
	$comments		=$row[5];
	
	// Rating available here
	if($rating==5){$rating_text="Excellent";}
	if($rating==4){$rating_text="Very Good";}
	if($rating==3){$rating_text="Good";}
	if($rating==2){$rating_text="Fair";}
	if($rating==1){$rating_text="Poor";}
    echo "<font face=Arial size=2><b>Support to this request was rated as $rating_text ($rating) on $rated_date with the following comments</b>";
    echo '<br><br>';
    echo "$comments";
    echo '<br><hr>';
}else{
	echo "<hr>";
    echo "<font face=Arial size=2 color=#DDDDDD><b>Support to this request has not been rated yet.</b>";
	echo '<br><hr>';
}

?>

