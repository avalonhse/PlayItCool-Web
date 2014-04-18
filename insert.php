hello
<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

include("dbconnect.php");
$link=Conection();

date_default_timezone_set("Asia/Saigon");
$date = date('Y-m-d H:i:s');
echo "The current server timezone is: " . $date;

$Sql="insert into sensorRecords (Device,Humidity,Temperature,Timestamps) values ('".$_GET["device"]."', '".$_GET["humidity"]."', '".$_GET["temperature"]."', '".$date."')";

mysql_query($Sql,$link);

//header("Location: insertareg.php");
?>


