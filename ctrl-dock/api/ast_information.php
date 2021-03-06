<?php

header('Content-Type:text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";

// This API is used to list all relevant information about an asset
// ast_information.php?key=abcd&hostname=lptp-john


function invalid(){
	echo "<node>";
		echo "<count>invalid</count>";
	echo "</node>";
	die(0);
}

function success($count){
	echo "<node>";
		echo "<count>".$count."</count>";
	echo "</node>";
	die(0);
}

function showxml($result, $num_rows){
if($num_rows>0){
			echo "<node>";
			while($row = mysql_fetch_array($result)){		
				echo "<asset>";
					echo "<tag>".$row[0]."</tag>";
					echo "<category>".$row[1]."</category>";
					echo "<model>".$row[2]."</model>";
					echo "<serialno>".$row[3]."</serialno>";
					echo "<assignedto>".$row[4]." ".$row[5]."</assignedto>";
					echo "<status>".$row[6]."</status>";
				echo "</asset>";
			}
			echo "</node>";
		}else{
			$nodata = 0;
			success($nodata);
		}
}
// include config file, also contains the API KEY
require_once('../include/config.php');
require_once('../include/db.php');

$api_key		= strip_tags($_REQUEST['key']);
$hostname		= strip_tags($_REQUEST['hostname']);

$num_rows		= '';
// validate api key
if($api_key!=$API_KEY || $api_key==''){
	invalid();
}else{
		$sql = "SELECT a.assetid,b.assetcategory,a.model,a.serialno,d.first_name,d.last_name,c.status FROM asset a,assetcategory b,assetstatus c, user_master d WHERE a.assetcategoryid=b.assetcategoryid AND a.statusid=c.statusid AND a.employee=d.username and a.hostname='$hostname'";
		$result = mysql_query($sql);	

		$num_rows = mysql_num_rows($result);
		showxml($result, $num_rows);		
}
?>
