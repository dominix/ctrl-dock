<?
$sql="select email,name,userid,userpass,smtp_host,smtp_port,smtp_auth,smtp_secure from hrost_email where email_id=1";
$result = mysql_query($sql);
$row= mysql_fetch_row($result);

$smtp_server	=$row[4];
$smtp_port		=$row[5];
$smtp_email		=$row[0];
$smtp_username	=$row[2];
$smtp_name		=$row[1];
$smtp_password	=$row[3];
$smtp_secure    =$row[7];
$smtp_auth		=$row[6];
?>