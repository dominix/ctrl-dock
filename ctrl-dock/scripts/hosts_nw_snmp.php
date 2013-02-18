<?php

include_once("../include/config.php");
include_once("../include/db.php");
include_once("../include/mail.php");
include_once("../include/mail_helper.php");
include_once("../include/ticket_post.php");

// Perform a periodic clean-up of Network Log Files
$cleanup_period=60;// 60 Days

$cleanup_period=$cleanup_period*86400;
$then=mktime()-$cleanup_period;

$sql="delete from hosts_nw_snmp_log where timestamp<$then";
$result = mysql_query($sql);



$sql="SELECT a.host_id,a.hostname,b.retry_count,b.timeout,b.community_string,a.platform,b.alarm_threshold,b.port FROM hosts_master a,hosts_nw_snmp b WHERE a.host_id=b.host_id AND a.status='1' AND b.enabled='1' ORDER BY a.hostname";
$result = mysql_query($sql);

$cpu_db_data = "";
$mem_db_data = ""; //same can be resued for dsk data too.
$cpu_user = -1;
$cpu_system = 0;
$cpu_idle = 0;
$dsk_utilization = "";
$mem_pct_used = 0;
$new_cpu_status = -1;
$new_dsk_status = -1;
$new_mem_status = -1;
$host_id="";
$atleast_one = 0;

while ($row = mysql_fetch_row($result)){
	$host_id		=	$row[0];
	$hostname		=	$row[1].":".$row[7];
	$count			=	$row[2];if($count==""){$count=4;}
	$timeout		=	$row[3];if($timeout==""){$timeout=5;}
	$community_string = $row[4];if($community_string==""){$community_string="public";}
	$platform		=	$row[5];if($platform==""){$platform="LINUX";}
	$alarm_threshold	= $row[6]-1;
	$limit				= $alarm_threshold+1;
	
	$sub_sql	="SELECT nw_snmp_cpu_status,nw_snmp_mem_status,nw_snmp_dsk_status FROM hosts_nw_snmp_log WHERE host_id='$host_id' ORDER BY record_id DESC LIMIT $limit";
	
	$th_sql = "select host_id, snmp_parameter, param_value, enabled from hosts_nw_snmp_thresholds where host_id='$host_id' order by snmp_parameter asc";
	$th_result = mysql_query($th_sql);
	
	while ($th_row = mysql_fetch_row($th_result)){
		if ($platform == "WINDOWS"){
			$param_enabled = $th_row[3];
			$param_threshold = $th_row[2];
			if (1 == $param_enabled){
				$atleast_one = 1;
				$param_name = $th_row[1];
				
				if ( "CPU Utilization" == $param_name){				
					$cpu_info = ".1.3.6.1.2.1.25.3.3.1.2";

					snmp_set_quick_print(1);
					$procInfo = @snmprealwalk($hostname, $community_string, $cpu_info, $timeout, $count);
					
					if ( count($procInfo) == 1 ){
						echo("Unable to query Windows server $hostname for CPU Utilization");
					}else{
						$counter = 0;
						$total = 0;
						foreach ($procInfo as $oid=>$val){
							$counter++;
							$total += $val;
							$cpu_db_data = $cpu_db_data . $oid . "," . $val . "::";
						}
						$cpu_user = round($total/$counter,2);
						if ($cpu_user < $param_threshold){
							$new_cpu_status = 1;						
						}else{					
							$new_cpu_status = 0;				
											
							// Check the last known state of the host
							$sub_result = mysql_query($sub_sql);
							$down_count=0;		
							while($sub_row=mysql_fetch_row($sub_result)){
								if($sub_row[0]==0){$down_count++;}
								$last_status=$sub_row[0];
							}
							
							// If the alarm threshold was breached, generate a ticket
							if($down_count==$alarm_threshold && $last_status==1){
								$timestamp = mktime();
								$timestamp_human=date("d-M-Y H:i:s",$timestamp);
								$message  = "ALERT $hostname - CPU Utilization Threshold breached at $timestamp_human";
								$subject=$message;
								ticket_post($smtp_email,$smtp_email,"28","$subject","$message",'1');
							}
							
							
						}
					}
				} else if ("Memory Utilization" == $param_name){ //end of else if CPU utilization
				
					$used_mem_info = ".1.3.6.1.2.1.25.5.1.1.2";
					$total_mem_info = ".1.3.6.1.2.1.25.2.2";

					snmp_set_quick_print(1);
					$snmpInfo = @snmprealwalk($hostname, $community_string, $used_mem_info, $timeout, $count);

					if ( count($snmpInfo) == 1 ){
						echo("Unable to query Windows server $hostname for Used Memory Info");
						//exit(1);
					}
					$total_mem = 0;
					$total_mem_used = 0;
					foreach ( $snmpInfo as $oid => $val ){
						$temp = array();
						$temp = explode(" ",$val);
						$factor = 1; //Mostly will be received in KBytes; In case we receive in MBytes or Bytes or GBytes then calculate for KByte only
						if ($temp[1] == "Bytes"){
							$factor = 1024;
						}elseif ($temp[1] == "MBytes"){
							$factor = (1/1024);
						}elseif ($temp[1] == "GBytes"){
							$factor = (1/(1024*1024));
						}
						$total_mem_used += $temp[0] * $factor;
						$mem_db_data = $mem_db_data . $oid . "," . $val . "::";
					}
					//Output is in KB; Convert to GB
					$total_mem_used = round($total_mem_used / (1024*1024), 2);				

					//get the total memory available
					snmp_set_quick_print(1);
					$snmpInfo = @snmprealwalk($hostname, $community_string, $total_mem_info, $timeout, $count);
					$mem_db_data .= "Total Mem: ";
					foreach ( $snmpInfo as $oid => $val ){
						$temp = array();
						$temp = explode(" ",$val);
						$factor = 1; //Mostly will be received in KBytes; In case we receive in MBytes or Bytes or GBytes then calculate for KByte only
						if ($temp[1] == "Bytes"){
							$factor = 1024;
						}elseif ($temp[1] == "MBytes"){
							$factor = (1/1024);
						}elseif ($temp[1] == "GBytes"){
							$factor = (1/(1024*1024));
						}
						$total_mem = $temp[0] * $factor;
						$mem_db_data = $mem_db_data . $oid . "," . $val . "::";
						break;
					}
					$total_mem = round($total_mem / (1024*1024), 2);

					$mem_pct_used = round (($total_mem_used * 100/ $total_mem), 2);
					
					if ($mem_pct_used < $param_threshold){
						$new_mem_status = 1;
					}else{					
						$new_mem_status = 0;				
										
						// Check the last known state of the host
						$sub_result = mysql_query($sub_sql);
						$down_count=0;		
						while($sub_row=mysql_fetch_row($sub_result)){
							if($sub_row[1]==0){$down_count++;}
							$last_status=$sub_row[1];
						}
						
						// If the host was up as known last, generate a ticket
						if($down_count==$alarm_threshold && $last_status==1){
							$timestamp = mktime();
							$timestamp_human=date("d-M-Y H:i:s",$timestamp);
							$message  = "ALERT $hostname - Memory Utilization Threshold breached at $timestamp_human";
							$subject=$message;
							ticket_post($smtp_email,$smtp_email,"28","$subject","$message",'1');
						}
					}				
				}elseif ("Disk Utilization" == $param_name){ //end of if mem utilization
					$disk_info = ".1.3.6.1.2.1.25.2.3.1";
					
					$mem_db_data = "";
					$dsk_utilization = "";
					$dsk_pct_used = 0;
					$new_dsk_status = 0;
					
					snmp_set_quick_print(1);
					$snmpInfo = @snmprealwalk($hostname, $community_string, $disk_info, $timeout, $count);
					foreach ( $snmpInfo as $oid => $val ){
						$mem_db_data = $mem_db_data . $oid . "," . $val . ",";
						
						$pos = strpos($val, "FixedDisk");
						if ($pos == true){
							$index = substr($oid, strrpos($oid, ".")+1);
							$diskLabel = @snmpget($hostname, $community_string, $disk_info.".3.$index", $timeout, $count);							
							$diskTotalSpace = @snmpget($hostname, $community_string, $disk_info.".5.$index", $timeout, $count);							
							$diskUsedSpace = @snmpget($hostname, $community_string, $disk_info.".6.$index", $timeout, $count);							
							$disk_pct_used = round(($diskUsedSpace / $diskTotalSpace)*100,2);
							
							$dsk_utilization = $dsk_utilization . substr($diskLabel,0,strpos($diskLabel," ")). " => " . $disk_pct_used . "%</br>";
							if ($dsk_pct_used > $param_threshold){
								$new_dsk_status++;
							}
						}						
					}
					if ($new_dsk_status == 0){
						$new_dsk_status = 1;
					}else if ($new_dsk_status > 0){					
						$new_dsk_status = 0;				
										
										
						// Check the last known state of the host
						$sub_result = mysql_query($sub_sql);
						$down_count=0;		
						while($sub_row=mysql_fetch_row($sub_result)){
							if($sub_row[2]==0){$down_count++;}
							$last_status=$sub_row[2];
						}
						
					
						// If the alarm threshold was breached, generate a ticket
						if($down_count==$alarm_threshold && $last_status==1){
							$timestamp = mktime();
							$timestamp_human=date("d-M-Y H:i:s",$timestamp);
							$message  = "ALERT $hostname - Disk Utilization Threshold breached at $timestamp_human";
							$subject=$message;
							ticket_post($smtp_email,$smtp_email,"28","$subject","$message",'1');
						}
										
					}
				}//end of if disk utilization
			}
		}elseif ($platform == "LINUX"){
			$param_enabled = $th_row[3];
			$param_threshold = $th_row[2];
			if (1 == $param_enabled){
				$atleast_one = 1;
				$param_name = $th_row[1];
				
				if ( "CPU Utilization" == $param_name){				
					snmp_set_quick_print(1);
					$procInfo = @snmprealwalk($hostname, $community_string, ".1.3.6.1.4.1.2021.11", $timeout, $count);
					
					if ( count($procInfo) == 1 ){
						echo("Unable to query server $hostname for CPU Utilization");
					}else{						
						$cpu_db_data = "";						
						foreach ( $procInfo as $oid => $val ){
							if ("UCD-SNMP-MIB::ssCpuUser.0" == $oid){
								$cpu_user = $val;
							}else if ("UCD-SNMP-MIB::ssCpuSystem.0" == $oid){
								$cpu_system = $val;
							}else if ("UCD-SNMP-MIB::ssCpuIdle.0" == $oid){
								$cpu_idle = $val;
							}
							$cpu_db_data = $cpu_db_data . $oid . "," . $val . ",";
						}
						if (($cpu_user < 0) || ($cpu_system < 0) || ($cpu_idle < 0)){
							$cpu_db_data = "Error in SNMP data collection:::" . $cpu_db_data;
						}
						if (($cpu_user + $cpu_system ) < $param_threshold){
							$new_cpu_status = 1;						
						}else{					
							$new_cpu_status = 0;				
											
							// Check the last known state of the host
							$sub_result = mysql_query($sub_sql);
							$down_count=0;		
							while($sub_row=mysql_fetch_row($sub_result)){
								if($sub_row[0]==0){$down_count++;}
								$last_status=$sub_row[0];
							}
							
							// If the alarm threshold was breached, generate a ticket
							if($down_count==$alarm_threshold && $last_status==1){
								$timestamp = mktime();
								$timestamp_human=date("d-M-Y H:i:s",$timestamp);
								$message  = "ALERT $hostname - CPU Utilization Threshold breached at $timestamp_human";
								$subject=$message;
								ticket_post($smtp_email,$smtp_email,"28","$subject","$message",'1');
							}
						}
					}
				} else if ("Disk Utilization" == $param_name){
					snmp_set_quick_print(1);
					$hrStorageDescr = @snmprealwalk($hostname, $community_string, "hrStorageDescr", $timeout, $count);

					if ( count($hrStorageDescr) == 1 )
					{
						echo("Unable to query server $hostname for Disk Utilization");
					}
					
					$mem_db_data = "";
					$dsk_utilization = "";
					$dsk_pct_used = 0;
					$new_dsk_status = 0;
					foreach ( $hrStorageDescr as $oid => $val )
					{
						$mem_db_data = $mem_db_data . $oid . "," . $val . ",";
						
						if ("/" == substr($val,0,1)){
							$index = substr($oid, strrpos($oid, ".")+1);

							$used = @snmpget($hostname, $community_string, "hrStorageUsed.$index", $timeout, $count);
							$total = @snmpget($hostname, $community_string, "hrStorageSize.$index", $timeout, $count);

							if ( ! ereg("^[0-9]+$", $used) ){
								echo("Unable to determine used space due to failed snmp query");
							}
							if ( ! ereg("^[0-9]+$", $total) ){
								echo("Unable to determine size due to failed snmp query");
							}
							$dsk_pct_used = round(($used / $total)*100, 0);
							$dsk_utilization = $dsk_utilization . $val. " => " . $dsk_pct_used . "%</br>";
							if ($dsk_pct_used > $param_threshold){
								$new_dsk_status++;
							}
						}					
					}
					
					if ($new_dsk_status == 0){
						$new_dsk_status = 1;
					}else if ($new_dsk_status > 0){					
						$new_dsk_status = 0;				
										
						// Check the last known state of the host
						$sub_result = mysql_query($sub_sql);
						$down_count=0;		
						while($sub_row=mysql_fetch_row($sub_result)){
							if($sub_row[2]==0){$down_count++;}
							$last_status=$sub_row[2];
						}
						
					
						// If the alarm threshold was breached, generate a ticket
						if($down_count==$alarm_threshold && $last_status==1){
							$timestamp = mktime();
							$timestamp_human=date("d-M-Y H:i:s",$timestamp);
							$message  = "ALERT $hostname - Disk Utilization Threshold breached at $timestamp_human";
							$subject=$message;
							ticket_post($smtp_email,$smtp_email,"28","$subject","$message",'1');
						}
					}
				}else if ("Memory Utilization" == $param_name){ //end of else if dsk utilization
				
					snmp_set_quick_print(1);
					$snmp_result = @snmpget($hostname, $community_string, ".1.3.6.1.4.1.2021.4.5.0", $timeout, $count);
				
					if (strrpos($snmp_result, ":") > 0){
						$total_mem = substr($snmp_result, strrpos($snmp_result, ":")+2);
					}else{
						$total_mem = $snmp_result;
					}
					
					$snmp_result = @snmpget($hostname, $community_string, ".1.3.6.1.4.1.2021.4.6.0", $timeout, $count);
					
					if (strpos($snmp_result, ":") > 0){
						$free_mem = substr($snmp_result, strrpos($snmp_result, ":")+2);
					}else{
						$free_mem = $snmp_result;
					}
					
					$snmp_result = @snmpget($hostname, $community_string, ".1.3.6.1.4.1.2021.4.15.0", $timeout, $count);
					
					if (strpos($snmp_result, ":") > 0){
						$cache_mem = substr($snmp_result, strrpos($snmp_result, ":")+2);
					}else{
						$cache_mem = $snmp_result;
					}
									
					if($total_mem > 0){
						$mem_pct_used = round((($total_mem - $free_mem - $cache_mem) / $total_mem)*100, 2);
					}else{
						echo "Unable to determine total memory for $hostname due to failed snmp query";
					}
					
					if ($mem_pct_used < $param_threshold){
						$new_mem_status = 1;
					}else{					
						$new_mem_status = 0;				
										
						// Check the last known state of the host
						$sub_result = mysql_query($sub_sql);
						$down_count=0;		
						while($sub_row=mysql_fetch_row($sub_result)){
							if($sub_row[1]==0){$down_count++;}
							$last_status=$sub_row[1];
						}

						// If the alarm threshold was breached, generate a ticket
						if($down_count==$alarm_threshold && $last_status==1){	
							$timestamp = mktime();
							$timestamp_human=date("d-M-Y H:i:s",$timestamp);
							$message  = "ALERT $hostname - Memory Utilization Threshold breached at $timestamp_human";
							$subject=$message;
							ticket_post($smtp_email,$smtp_email,"28","$subject","$message",'1');
						}
					}
				
				}		
			}
		}//end of elseif Linux
	}
	if (1 == $atleast_one){
		$timestamp = mktime();
		$sub_sql="insert into hosts_nw_snmp_log (host_id,nw_snmp_cpu_status,nw_snmp_mem_status,nw_snmp_dsk_status,cpu_user,cpu_system,cpu_idle,mem_utilization,cpu_snmp_result,mem_snmp_result,disk_utilization,timestamp) values ('$host_id','$new_cpu_status','$new_mem_status','$new_dsk_status','$cpu_user','$cpu_system','$cpu_idle','$mem_pct_used','$cpu_db_data','$mem_db_data','$dsk_utilization','$timestamp')";
		$sub_result = mysql_query($sub_sql);
	}
	
	// Reset Variables
	$host_id=0;$new_cpu_status=0;$new_mem_status=0;$new_dsk_status=0;$cpu_user=0;$cpu_system=0;$cpu_idle=0;$mem_pct_used=0;$cpu_db_data='';$mem_db_data='';$dsk_utilization=0;$timestamp=0;
}
	
?>
