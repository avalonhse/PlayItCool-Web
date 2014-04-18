<?php
function Conection(){
   if (!($link=mysql_connect("localhost:3306","root","12345678")))  {
      exit();
   }
   if (!mysql_select_db("coolit",$link)){
      exit();
   }
   return $link;
}
?>

