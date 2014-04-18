
<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

include("dbconnect.php");
$link=Conection();

$Sql="insert into sensorRecords (Device,Humidity,Temperature,Timestamps) values ('".$_GET["device"]."', '".$_GET["humidity"]."', '".$_GET["temperature"]."', '".$_GET["timestamps"]."')";

mysql_query($Sql,$link);

//header("Location: insertareg.php");
?>

