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
    $sql = "SELECT id, fname, name, sname, phone, bank_account, bank_comment, sort FROM `legal_c` WHERE status=10 ORDER BY sort;";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_OBJ);

    $datas = array();
    foreach($result as $rs){
        array_push($datas,array(
            'id' => $rs->id,
            'name' => $rs->fname.$rs->name . ' '. $rs->sname,
            'phone' => $rs->phone,
            'bank_account' => $rs->bank_account,
            'bank_comment' => $rs->bank_comment,
            'sort' => $rs->sort,
        ));
    }
 
    if (count($result) > 0) {
        http_response_code(200);
        echo json_encode(array(
            'status' => true, 
            'massege' => 'ok',
            'datas' => $datas,
        ));
    }else {
        http_response_code(200);
        echo json_encode(array('status' => false, 'massege' => 'ไม่พบข้อมูล', 'datas' => $datas));
    }

}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'massege' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
}

