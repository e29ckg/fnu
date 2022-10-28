<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
// header("'Access-Control-Allow-Credentials', 'true'");
// header('Content-Type: application/javascript');
header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set("Asia/Bangkok");

include_once "../dbconfig.php";

$data = json_decode(file_get_contents("php://input"));
if($data->user_id == ''){
    http_response_code(200);
    echo json_encode(array('status' => false, 'massege' => 'ไม่พบข้อมูล uid'));
    exit;
}

try{    
    $sql = "SELECT user_id, fname, name, sname, phone, bank_account, bank_comment FROM `profile` WHERE user_id=:user_id LIMIT 1";
    
    $query = $dbcon->prepare($sql);
    $query->bindParam(':user_id',$data->user_id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_OBJ);
    if (count($result) > 0) {
        http_response_code(200);
        echo json_encode(array('status' => true, 'massege' => 'ok', 'datas' => $result));
    }else {
        http_response_code(200);
        echo json_encode(array('status' => false, 'massege' => 'ไม่พบข้อมูล', 'datas' => null));
    }

}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'massege' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
}

