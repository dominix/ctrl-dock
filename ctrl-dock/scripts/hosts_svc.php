<?
include_once("../include/config.php");
include_once("../include/db.php");
include_once("../include/mail.php");
include_once("../include/mail_helper.php");
include_once("../include/ticket_post.php");
include_once("../include/simple_html_dom.php");


// Perform a periodic clean-up of Network Log Files
$cleanup_period=60;// 60 Days

$cleanup_period=$cleanup_period*86400;
$then=mktime()-$cleanup_period;

$sql="delete from hosts_service_log where timestamp<$then";
$result = mysql_query($sql);



$sql="SELECT a.host_id,a.hostname,b.port,b.alarm_threshold,b.url,b.pattern,b.timeout,a.alert_status FROM hosts_master a,hosts_service b WHERE a.host_id=b.host_id AND a.status='1' AND b.enabled='1' ORDER BY a.hostname";
$result = mysql_query($sql);

while ($row = mysql_fetch_row($result)){
	$host_id		=	$row[0];
	$hostname		=	$row[1];
	$port			=	$row[2];
	$alarm_threshold=	$row[3];
	$timestamp		=	mktime();
	
	$url				=$row[4];
	$pattern			=$row[5];
	$url_timeout		=$row[6];
	$alert_status		=$row[7];
	
	$sub_sql	="SELECT description FROM hosts_service WHERE host_id='$host_id' and port='$port' and url='$url'";
	$sub_result = mysql_query($sub_sql);
	$sub_row	= mysql_fetch_row($sub_result);
	$svc		= $sub_row[0];

	if($port>0){
		$connection=@fsockopen($hostname,$port,$errno, $errstr,3);
			
		if ($connection) {
			$svc_status=1;
			fclose($connection);
		}else{
			$svc_status=0;
		}
		
		$sub_sql="insert into hosts_service_log (host_id,port,svc_status,timestamp) values ('$host_id','$port','$svc_status','$timestamp')";
		$sub_result = mysql_query($sub_sql);
		
		if ($svc_status==0){
			// Check the last known state of the host and the service
			$limit=$alarm_threshold+1;
			$sub_sql	="SELECT svc_status FROM hosts_service_log WHERE host_id='$host_id' and port='$port' ORDER BY record_id DESC LIMIT $limit";
			$sub_result = mysql_query($sub_sql);
			$down_count=0;		
			while($sub_row=mysql_fetch_row($sub_result)){
				if($sub_row[0]==0){$down_count++;}
				$last_status=$sub_row[0];
			}
			
			// If the service was down and breached the alarm threshold, generate a ticket
			if($down_count==$alarm_threshold && $last_status==1 && $alert_status==1){	
				$timestamp_human=date("d-M-Y H:i:s",$timestamp);
				$message  = "ALERT $hostname $svc($port) DOWN $timestamp_human";
				$subject=$message;
				ticket_post($smtp_email,$smtp_email,"28","$subject","$message",'1');		
			}
			
			// Get all the emails who have subscribed to the service going down and 
			// intend to receive continuous alerts while the service is down

			$select_email_query = "SELECT email_id FROM sys_uptime_email WHERE status = 'active' AND host_id = '$host_id' and continuos_alerts=1";
			$email_result = mysql_query($select_email_query);
			if (mysql_num_rows($email_result)>0 && $down_count>$alarm_threshold && $alert_status==1){
				while($email_row = mysql_fetch_assoc($email_result)){
					$timestamp_human=date("d-M-Y H:i:s",$timestamp);
					$message  = "ALERT $hostname $svc($port) DOWN $timestamp_human";
					$subject=$message;
					$to_email = $email_row['email_id'];
					ezmail($to_email,$to_email,$subject,$message,$attachment);
				}
			}
			
		}
		
		//This section is used to send a notification mail to the respective people to say the service is UP again
		if ($svc_status==1){
			$limit=$alarm_threshold+1;
			$select_up_query	="SELECT svc_status FROM hosts_service_log WHERE host_id='$host_id' and port='$port' ORDER BY record_id DESC LIMIT $limit";
			$up_result = mysql_query($select_up_query);
			
			$up_count 	= 0;
			$down_count	= 0;
			while($up_row = mysql_fetch_row($up_result)){
				if($up_row[0] == 1){
					$up_count++;
				}
				if($up_row[0] == 0){
					$down_count++;
				}
			}
			
			if($down_count>=$alarm_threshold && $alert_status==1){
				$timestamp_human=date("d-M-Y H:i:s",$timestamp);
				$body = "$hostname $svc($port) UP on $timestamp_human";
			
				$attachment ="";
				// GET ALL THE EMAILS WHO ARE ASSOCIATED WITH THAT HOST '
				$select_email_query = sprintf("SELECT email_id
						       FROM sys_uptime_email
						       WHERE status = 'active' AND host_id = '%d'",$host_id);
				$email_result = mysql_query($select_email_query);
				while($email_row = mysql_fetch_assoc($email_result)){
					$to_email = $email_row['email_id']; 
					ezmail($to_email,$to_email,"Service $svc UP",$body,$attachment);
				}
			}
		}
	}
	
	if($port==0){
		$t1 = mktime();
		$html = file_get_html($url)->plaintext;
		$t2 = mktime();
		
		$diff=$t2 - $t1;

		if (!strstr($html,$pattern)){
			$svc_status=0;
			$subject="CHECKSITE ALERT - ".$url . " is DOWN";
			$message="CHECKSITE ALERT - ".$url . " is DOWN";
		}else{
			if ($diff > $url_timeout){
				$svc_status=0;
				$subject="CHECKSITE ALERT - ".$url . " is SLOW";
				$message="CHECKSITE ALERT - ".$url . " load time (".$diff." secs) has exceeded threshold time";
			}else{
				$svc_status=1;
			}
		}
		
		$sub_sql="insert into hosts_service_log (host_id,port,svc_status,timestamp,url,loadtime) values ('$host_id','$port','$svc_status','$timestamp','$url','$diff')";
		$sub_result = mysql_query($sub_sql);
		
		if ($svc_status==0){
			// Check the last known state of the host and the service
			$limit=$alarm_threshold+1;
			$sub_sql	="SELECT svc_status FROM hosts_service_log WHERE host_id='$host_id' and url='$url' ORDER BY record_id DESC LIMIT $limit";			
			$sub_result = mysql_query($sub_sql);
			$down_count=0;		
			while($sub_row=mysql_fetch_row($sub_result)){
				if($sub_row[0]==0){$down_count++;}
				$last_status=$sub_row[0];
			}
			
			// If the service was down and breached the alarm threshold, generate a ticket
			if($down_count==$alarm_threshold && $last_status==1 && $alert_status==1){
				$timestamp_human=date("d-M-Y H:i:s",$timestamp);
				$message  = $message. " $timestamp_human";		
				ticket_post($smtp_email,$smtp_email,"28","$subject","$message",'1');		
			}
			
			
			// Get all the emails who have subscribed to the service going down and 
			// intend to receive continuous alerts while the service is down
			
			$select_email_query = "SELECT email_id FROM sys_uptime_email WHERE status = 'active' AND host_id = '$host_id' and continuos_alerts=1";
			$email_result = mysql_query($select_email_query);
			if (mysql_num_rows($email_result)>0 && $down_count>$alarm_threshold && $alert_status==1){
				while($email_row = mysql_fetch_assoc($email_result)){
					$to_email = $email_row['email_id'];
					ezmail($to_email,$to_email,$subject,$message,$attachment);
				}
			}
		}
		
		
		//This section is used to send a notification mail to the respective people to say the service is UP again
		if ($svc_status==1){
			$limit=$alarm_threshold+1;
			$select_up_query	="SELECT svc_status FROM hosts_service_log WHERE host_id='$host_id' and url='$url'  ORDER BY record_id DESC LIMIT $limit";
			$up_result = mysql_query($select_up_query);
			
			$up_count 	= 0;
			$down_count	= 0;
			while($up_row = mysql_fetch_row($up_result)){
				if($up_row[0] == 1){
					$up_count++;
				}
				if($up_row[0] == 0){
					$down_count++;
				}
			}
			
			if($down_count>=$alarm_threshold && $alert_status==1){
				$timestamp_human=date("d-M-Y H:i:s",$timestamp);
				$body = "$svc($url) UP on $timestamp_human";
			
				$attachment ="";
				// GET ALL THE EMAILS WHO ARE ASSOCIATED WITH THAT HOST '
				$select_email_query = sprintf("SELECT email_id
						       FROM sys_uptime_email
						       WHERE status = 'active' AND host_id = '%d'",$host_id);
				$email_result = mysql_query($select_email_query);
				while($email_row = mysql_fetch_assoc($email_result)){
					$to_email = $email_row['email_id'];
					ezmail($to_email,$to_email,"URL $url UP",$body,$attachment);
				}
			}
		}	
	}
}