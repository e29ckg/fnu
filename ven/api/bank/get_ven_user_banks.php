<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
// header("'Access-Control-Allow-Credentials', 'true'");
// header('Content-Type: application/javascript');
header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set("Asia/Bangkok");

include_once "../dbconfig.php";

// $data = json_decode(file_get_contents("php://input"));
// http_response_code(200);
//         echo json_encode(array('status' => false, 'massege' => 'ไม่พบข้อมูล', 'responseJSON' => $data->month));
// exit;

try{    
    $sql = "SELECT b.*, p.fname, p.name, p.sname FROM ven_user_bank b JOIN profile p ON (b.user_id = p.user_id) ORDER BY p.st;";
    // $st = $pdo->prepare('SELECT b.*, p.name FROM ven_user_bank b JOIN profile ON (b.user_id = p.user_id)');

    $query = $dbcon->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_OBJ);
 
    if (count($result) > 0) {
        http_response_code(200);
        echo json_encode(array(
            'status' => true, 
            'massege' => 'ok',
            'datas' => $result,
        ));
    }else {
        http_response_code(200);
        echo json_encode(array('status' => false, 'massege' => 'ไม่พบข้อมูล', 'datas' => null));
    }

}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'massege' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
}

