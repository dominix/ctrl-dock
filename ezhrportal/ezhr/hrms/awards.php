<?php 
include("config.php"); 
if (!check_feature(9)){feature_error();exit;}

$account=$_REQUEST["account"];


$sql = "select first_name,last_name,account_status from user_master where username='$account'";
$result = mysql_query($sql);
$row = mysql_fetch_row($result);

$first_name		=$row[0];
$last_name		=$row[1];
$account_status	=$row[2];
?>

<center>
<table border=0 cellpadding=3 cellspacing=0 width=100%>
<tr>
	<td align=left>
		<b><font face="Arial" color="#CC0000" size="2">Awards / Recognitions for : <?=$first_name?> <?=$last_name;?>
	</td>
	<td align=right>
		<a href="user_home.php?account=<?=$account?>"><font face="Arial" color="#336699" size="1"><b>BACK</b></font></a>
	</td>
</tr>
</table>
<br>
<table border=0 width=100% cellspacing=0 cellpadding=3 style="border-collapse: collapse" bordercolor="#E5E5E5">
<tr>
		<td class=reportheader>Organization</td>
		<td class=reportheader>Award</td>
		<td class=reportheader>Delete</td>
</tr>
<?php

$sql = "select * from user_awards where username='$account'";
$result = mysql_query($sql);
$i=1;
$row_color="#FFFFFF";
while ($row = mysql_fetch_row($result)){
	if (($i%2)==1){$row_color="#EDEDE4";}else{$row_color="#FFFFFF";}
?>
	<tr bgcolor=<?echo $row_color; ?>>
		<td class=reportdata><? echo $row[2]; ?></font></td>
		<td class=reportdata><? echo $row[3]; ?></font></td>
 		<td align=center><a href="delete_award.php?index=<? echo $row[0]; ?>&account=<? echo $account; ?>"><img border=0 src="images/delete.gif"></a></font></td>
	</tr>
	<?	
	$i++;
}
?>
</table>
<br>
<?
include("add_award_1.php"); 
?>