<?php
include("config.php");
if (!check_feature(27)){feature_error();exit;} 


$SELECTED="ESCALATIONS";
include("header.php");
?>
<form method=POST action=escalations_2.php?ticket_type_id=3>
<table border=0 cellpadding=1 cellspacing=1 width=100% bgcolor=#F7F7F7>
<tr>	
	<td class="reportheader" width=150>&nbsp;CHANGE REQUEST</td>
	<td class="reportheader" width=200>Name</td>
	<td class="reportheader" width=100>EMRG</td>
	<td class="reportheader" width=100>HIGH</td>
	<td class="reportheader" width=100>NORMAL</td>
	<td class="reportheader" width=100>LOW</td>	
	<td class="reportheader" width=100>EXCP</td>	
	</tr>
<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='1' and ticket_type_id='3'";
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>
	
	<td align=center class=reportdata>&nbsp;<b>Escalation 1</td>
	<td align=center class=reportdata><select size=1 name=esc_1 class='formselect'>			
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
					echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center><input name="emg_1" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center><input name="hgh_1" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center><input name="med_1" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center><input name="low_1" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>
	<td align=center><input name="exc_1" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>
</tr>


<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='2'  and ticket_type_id='3'";
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>

	<td align=center class=reportdata>&nbsp;<b>Escalation 2</td>
	<td align=center class=reportdata><select size=1 name=esc_2 class='formselect'>			
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
		        	echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center><input name="emg_2" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center><input name="hgh_2" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center><input name="med_2" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center><input name="low_2" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>	
	<td align=center><input name="exc_2" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>
</tr>




<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='3'  and ticket_type_id='3'";
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>
	<td align=center class=reportdata style='background-color:#FF7D7D'>&nbsp;<b>Escalation 3</td>
	<td align=center class=reportdata style='background-color:#FF7D7D'>
			<select size=1 name=esc_3 class='formselect'>			
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
		        	echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center style='background-color:#FF7D7D'><input name="emg_3" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center style='background-color:#FF7D7D'><input name="hgh_3" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center style='background-color:#FF7D7D'><input name="med_3" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center style='background-color:#FF7D7D'><input name="low_3" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>	
	<td align=center style='background-color:#FF7D7D'><input name="exc_3" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>	
	</tr>

<td align=center colspan=7><input type=submit value="Save Escalation Settings" name="Submit" class='forminputbutton'></td>
</form>
</tr>
</table>



<br>




<form method=POST action=escalations_2.php?ticket_type_id=4>
<table border=0 cellpadding=1 cellspacing=1 width=100% bgcolor=#F7F7F7>
<tr>
	<td class="reportheader" width=150>&nbsp;SERVICE REQUEST</td>
	<td class="reportheader" width=200>Name</td>
	<td class="reportheader" width=100>EMRG</td>
	<td class="reportheader" width=100>HIGH</td>
	<td class="reportheader" width=100>NORMAL</td>
	<td class="reportheader" width=100>LOW</td>	
	<td class="reportheader" width=100>EXCP</td>	
</tr>
<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='1' and ticket_type_id='4'";
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>
	
	<td align=center class=reportdata>&nbsp;<b>Escalation 1</td>
	<td align=center class=reportdata><select size=1 name=esc_1 class='formselect'>
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
		        	echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center><input name="emg_1" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center><input name="hgh_1" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center><input name="med_1" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center><input name="low_1" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>
	<td align=center><input name="exc_1" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>
</tr>


<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='2'  and ticket_type_id='4'";
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>

	<td align=center class=reportdata>&nbsp;<b>Escalation 2</td>
	<td align=center class=reportdata><select size=1 name=esc_2 class='formselect'>			
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
		        	echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center><input name="emg_2" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center><input name="hgh_2" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center><input name="med_2" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center><input name="low_2" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>	
	<td align=center><input name="exc_2" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>

</tr>




<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='3'  and ticket_type_id='4'";
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>
	<td align=center class=reportdata style='background-color:#FF7D7D'>&nbsp;<b>Escalation 3</td>
	<td align=center class=reportdata style='background-color:#FF7D7D'>
		<select size=1 name=esc_3 class='formselect'>			
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
		        	echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center style='background-color:#FF7D7D'><input name="emg_3" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center style='background-color:#FF7D7D'><input name="hgh_3" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center style='background-color:#FF7D7D'><input name="med_3" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center style='background-color:#FF7D7D'><input name="low_3" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>	
	<td align=center style='background-color:#FF7D7D'><input name="exc_3" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>	

</tr>
<td align=center colspan=7><input type=submit value="Save Escalation Settings" name="Submit" class='forminputbutton'></td>
</form>
</tr>
</table>








<br>
<form method=POST action=escalations_2.php?ticket_type_id=1>
<table border=0 cellpadding=1 cellspacing=1 width=100% bgcolor=#F7F7F7>
<tr>
	<td class="reportheader" width=150>FAULTS / INCIDENTS</td>
	<td class="reportheader" width=200>Name</td>
	<td class="reportheader" width=100>EMRG</td>
	<td class="reportheader" width=100>HIGH</td>
	<td class="reportheader" width=100>NORMAL</td>
	<td class="reportheader" width=100>LOW</td>	
	<td class="reportheader" width=100>EXCP</td>	
</tr>
<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='1' and ticket_type_id='1'";	
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>
	
	<td align=center class=reportdata>&nbsp;<b>Escalation 1</td>
	<td align=center class=reportdata><select size=1 name=esc_1 class='formselect'>			
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
		        	echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center><input name="emg_1" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center><input name="hgh_1" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center><input name="med_1" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center><input name="low_1" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>
	<td align=center><input name="exc_1" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>

	</tr>


<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='2'  and ticket_type_id='1'";
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>

	<td align=center class=reportdata>&nbsp;<b>Escalation 2</td>
	<td align=center class=reportdata><select size=1 name=esc_2 class='formselect'>			
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
		        	echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center><input name="emg_2" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center><input name="hgh_2" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center><input name="med_2" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center><input name="low_2" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>
	<td align=center><input name="exc_2" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>

	</tr>




<tr>
<?
	$sql	= "select esc_username,emergency,high,medium,low,exception from escalations where esc_id='3'  and ticket_type_id='1'";
	$result = mysql_query($sql);
	$row 	= mysql_fetch_row($result);
?>
	<td class=reportdata style='background-color:#FF7D7D'>&nbsp;<b>Escalation 3</td>
	<td class=reportdata style='background-color:#FF7D7D'><select size=1 name=esc_3 class='formselect'>			
			<?php
				if($row[0]=='')
				{
					echo "<option value=''>Select Name</option>";
				}
			    $sub_sql = "select username,first_name,last_name from user_master where account_status='Active' order by first_name";
				$sub_result = mysql_query($sub_sql);
				while ($sub_row = mysql_fetch_row($sub_result)) {
					if($row[0]==$sub_row[0]){$selected="selected";}else{$selected="";}
		        	echo "<option value='$sub_row[0]' $selected>$sub_row[1] $sub_row[2]</option>";
				}
			?>
		</select>
	</td>
	<td align=center style='background-color:#FF7D7D'><input name="emg_3" size="3" class=forminputtext value='<?echo $row[1];?>'></td>
	<td align=center style='background-color:#FF7D7D'><input name="hgh_3" size="3" class=forminputtext value='<?echo $row[2];?>' ></td>
	<td align=center style='background-color:#FF7D7D'><input name="med_3" size="3" class=forminputtext value='<?echo $row[3];?>' ></td>
	<td align=center style='background-color:#FF7D7D'><input name="low_3" size="3" class=forminputtext value='<?echo $row[4];?>' ></td>
	<td align=center style='background-color:#FF7D7D'><input name="exc_3" size="3" class=forminputtext value='<?echo $row[5];?>' ></td>

	</tr>


<td align=center colspan=7><input type=submit value="Save Escalation Settings" name="Submit" class='forminputbutton'></td>
</form>
</tr>
</table>


<h4>Define the number of hours from the time of logging/opening the ticket, with-in which the issue should be resolved / closed.<br>After this time period, a notification will be sent out to the individuals defined in the list above.</h4>