<?php 
include("config.php"); 

$hostname	=$_REQUEST["hostname"];
$records	=$_REQUEST["records"];
if(strlen($records)<=0){$records=10;}


$sql	="select host_id from hosts_master where hostname='$hostname'";
$result = mysql_query($sql);
$row	= mysql_fetch_row($result);
$host_id=$row[0];
?>
<title>Service Availability : <?=$hostname;?></title>
<table class="reporttable" width=450 border=0>
<tr><td class='reportdata' colspan=5><b>Service Availability : <?=$hostname;?></b></td></tr>
</table>
<table class="reporttable" width=450>
<td class='reportdata' style='text-align:center;' colspan=3>
<form method=POST action=svc_history.php?hostname=<?=$hostname;?> id=refresh>
	Display Last <input name="records" size="3" value=<?echo $records;?> class='forminputtext' onBlur=document.forms["refresh"].submit(); > Records
</form>
</td>
</tr>

<?
$sql = "select port,description from hosts_service where enabled=1 and host_id='$host_id'";
$result = mysql_query($sql);
while ($row = mysql_fetch_row($result)){
	$description	=$row[1];
	$port			=$row[0];
	
	$i=0;
	$graph_data	=array();
	$summary	=array();
	
	$sub_sql = "SELECT a.timestamp,a.svc_status FROM hosts_service_log a WHERE a.host_id='$host_id'AND a.port='$port' order by record_id desc LIMIT $records";
	$sub_result = mysql_query($sub_sql);
	$record_count=mysql_num_rows($sub_result);

	$i=$record_count-1;
	
	echo "<tr height=50><td class=reportdata colspan=3><b>$description Port : $port</b></td>";
	echo "<tr><td colspan=3>";
	
	while ($sub_row = mysql_fetch_row($sub_result)){
		$summary[$i][0]=date('d M Y H:i:s',$sub_row[0]);
		$summary[$i][1]=$sub_row[1];
		
		$graph_data_old[$i]=0;
		if($sub_row[1]==1){
			$graph_data_old[$i]=100;
		}
		$i--;
	}
	$graph_data = array_reverse($graph_data_old);
	$graph_data_export=implode(",",$graph_data);
	$x_axis_label="Last $records Records";
	$y_axis_label="Up / Down";
	$y_min=0;
	$y_max=100;
	$pt_label="Uptime %";
	$graph_name=$port;
	$graph_width="100%";
	$graph_height="200px";
	//include("host_graph.php");
	echo "</td></tr>";
	?>
	<tr>
		<td class="reportheader" width=125 colspan=2>Date & Time</td>	
		<td class="reportheader" width=60>Status</td>
	</tr>
	<?
	$sl_no=1;

	//for($i=count($graph_data)-1;$i>=0;$i--){
	for($i=0;$i<=count($graph_data)-1;$i++){
		if (($i%2)==1){$row_color="#EDEDE4";}else{$row_color="#FFFFFF";}
		$date	=$summary[$i][0];
		$status	=$summary[$i][1];
		if ($status==1){$bgcolor="#00CC00";$text="UP";}
		if ($status==0){$bgcolor="#FF0000";$text="DOWN";}
	
		
	?>
		<tr bgcolor=<?echo $row_color; ?>>
		<td class='reportdata' style='text-align:center;' width=40><?=$sl_no;?></td>
		<td class='reportdata' style='text-align:left;'><?=$date?></td>
		<td class='reportdata' style='text-align:center;background-color: <?echo $bgcolor;?>'><?=$text;?></td>
		</tr>
	<?
	$sl_no++;
	}
}
?>
</table>