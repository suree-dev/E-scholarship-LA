<?php
$db_host = "localhost"; 
$db_user = "root"; 
$db_password = ""; 
$db_name = "scholarship"; 

$connect1 = mysqli_connect($db_host, $db_user, $db_password, $db_name) or die('เกิดข้อผิดพลาด');
mysqli_query($connect1, "SET NAMES UTF8");

error_reporting(0);
mysqli_set_charset($connect1, "utf8mb4");