<?php

$DBhost = "localhost";
$DBuser = "root";
$DBpassword ="";
$DBname="vengg";
$port="3333";
// 
// $conn = mysqli_connect($DBhost, $DBuser, $DBpassword, $DBname); 
try{
    $dbcon = new PDO("mysql:host=$DBhost;dbname=$DBname;port=$port", $DBuser, $DBpassword);
    // set the PDO error mode to exception
    $dbcon->exec("set names utf8");
    $dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'massege' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
}

?> 