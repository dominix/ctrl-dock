<?php 

include("config.php");
if (!check_feature(13)){feature_error();exit;}

include("calendar.php");
include("date_to_int.php");

$from_date=$_REQUEST['from_date'];
$from_date=search_date_to_int($from_date,0,0);

$to_date=$_REQUEST['to_date'];
$to_date=search_date_to_int($to_date,23,59);

$agency_index=$_REQUEST['agency_index'];

$search_end_date=date('d-m-Y');
$search_from_date=date('d-m-Y',mktime()-(86400*7));

$account=$_REQUEST['account'];
$project_index=$_REQUEST['project_index'];

if (strlen($account)==0){
	$header_name="ALL";
}else{
	$sql="select first_name,last_name from user_master where username='$account'";
	$result = mysql_query($sql);														
	$row = mysql_fetch_row($result);
	$header_name=$row[0]." ".$row[1];
}
?>
<center>
<table border=0 width=100% cellspacing=0 cellpadding=2>
<form method=POST action="staff_time_sheet.php?account=<?=$account;?>">
<tr>
	<td colspan=9 align=left bgcolor=#A0A0A0><font color=#FFFFFF face=Arial size=2><b>Timesheet : <?=$header_name;?></font></a></b></td>
	</td>
</tr>
<tr bgcolor="#CCCCCC">
	<td colspan=9><font face=Arial size=1><b>&nbsp;LIST TIME SHEETS</td>
</tr>
<tr bgcolor="#CCCCCC">
	<td align=right><font face=Arial size=1><b>AGENCY</td>
	<td>
	             <select size=1 name=agency_index style="font-size: 8pt; font-family: Arial">
                        <?php
								echo "<option value=''>ALL</option>";
								echo "<option value='1'>INTERNAL</option>";
                        		$sub_sql = "select a.agency_index,a.name from agency a,agency_status b where a.agency_index!='1' and a.agency_index=b.agency_index and b.status='Active' order by name";
								$sub_result = mysql_query($sub_sql);														
								while ($sub_row = mysql_fetch_row($sub_result)){
										if(strlen($sub_row[1])==0){
											$label=$sub_row[2];
										}else{
											$label=$sub_row[1];
										}
										echo "<option value='$sub_row[0]'>$label</option>";
								}
                         ?>
                </select>
	</td>
	<td align=right><font face=Arial size=1><b>PROJECT</td>
	        <td align=right>
                <select size=1 name=project_index style="font-size: 8pt; font-family: Arial">
				
                        <?php
								echo "<option value=''>ALL</option>";
                        		$sub_sql = "select project_index,project_description from timesheet_project where project_status='Active'";
								$sub_result = mysql_query($sub_sql);														
								while ($sub_row = mysql_fetch_row($sub_result)){
										echo "<option value='$sub_row[0]'>$sub_row[1]</option>";
								}
                         ?>
                </select>
        </td>
	
	<td align=right><font face=Arial size=1><b>FROM DATE : </td>
	<td><input style="font-size: 8pt; font-family: Arial; width: 75px;" name=from_date id="from_date" value='<?=$search_from_date;?>' readonly onclick="fPopCalendar('from_date')"></td>
	
	<td align=right><font face=Arial size=1><b>TO DATE : </td>
	<td><input style="font-size: 8pt; font-family: Arial; width: 75px;" name=to_date id="to_date" value='<?=$search_end_date;?>' readonly onclick="fPopCalendar('to_date')"></td>
	
	<td align="center">
	<input type=submit value="GO >>" name="Submit" style="font-size: 8pt; font-family: Arial">
	</td>
</tr>
</form>
</table>

<?
	$sql="select c.name,b.description,a.start_date,a.end_date,a.activity,d.first_name,d.last_name,e.project_description from timesheet a,timesheet_context b,agency c,user_master d,timesheet_project e";
	$sql.=" where a.context_id=b.context_id and a.agency_index=c.agency_index and a.username=d.username and a.project_index=e.project_index";
	$sql.=" and a.start_date>='$from_date' and a.end_date<='$to_date' and a.agency_index like '$agency_index%' and a.project_index like '$project_index%'";
	if($header_name=="ALL"){
		$sql.=" and a.username in (select username from user_organization where direct_report_to='$employee' or dot_report_to='$employee')";
	}else {
		$sql.=" and a.username='$account'";
	}
	$sql.=" order by a.start_date";
	$result = mysql_query($sql);	
	$record_count=mysql_num_rows($result);
	if ($record_count>0){
?>
		<table border=0 width=100% cellspacing=1 cellpadding=3 class=reporttable>
		<tr>
			<td colspan=7 class=reportdata style='text-align:right;'><a href='staff_time_sheet_export.php?from_date=<?=$from_date?>&to_date=<?=$to_date?>&agency_index=<?=$agency_index;?>&account=<?=$account;?>&header_name=<?=$header_name;?>&project_index=<?=$project_index;?>'>Export to Excel</a>
			</td>
		</tr>
		<tr>
			<td class=reportheader width=180>Staff</font></b></td>
			<td class=reportheader width=90>Start Date / Time</font></b></td>
			<td class=reportheader width=90>End Date / Time</font></b></td>
			<td class=reportheader width=50>Hours</font></b></td>
			<td class=reportheader width=90>Context</font></b></td>
			<td class=reportheader width=200>Client</font></b></td>
			<td class=reportheader>Activity </font></b></td>
		</tr>
<? 
		$row_color="#FFFFFF";
		$total_hours=0;
		while ($row=mysql_fetch_row($result)){
			$agency		=$row[0];
			$context	=$row[1];
			$start_date	=date("d-m-Y H:i","$row[2]");
			$end_date	=date("d-m-Y H:i","$row[3]");
			$hours=round((($row[3]-$row[2])/3600),1);
			$total_hours=$total_hours+$hours;
			$activity	=nl2br($row[4]);
			$name		=$row[5]. " " .$row[6];
			$project_description=$row[7];
?>		
			<tr>
			<td class=reportdata><?=$name;?></td>
			<td class=reportdata style='text-align:center;'><?=$start_date;?></td>
			<td class=reportdata style='text-align:center;'><?=$end_date;?></td>
			<td class=reportdata style='text-align:center;'><?=$hours;?></td>
			<td class=reportdata style='text-align:center;'><?=$context;?></td>
			<td class=reportdata><?=$agency;?><br><?=$project_description;?></td>
			<td class=reportdata><?=$activity;?></td>
<?
		}
?>
		<tr>
			<td class=reportheader colspan=3 style='text-align:right;'>TOTAL&nbsp;</font></b></td>
			<td class=reportheader style='text-align:center;'><?=$total_hours;?>&nbsp;</font></b></td>
			<td class=reportheader colspan=3>&nbsp;</td>
		</tr>
</table>
<?
	}
?>