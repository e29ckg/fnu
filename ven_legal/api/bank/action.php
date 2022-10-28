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
// http_response_code(200);
//         echo json_encode(array('status' => false, 'massege' => 'ไม่พบข้อมูล', 'datas' => $data));
// exit;

try{    
    
    if($data->action == 'update'){        
        $sql = "UPDATE legal_c SET bank_account =:bank_account, bank_comment =:bank_comment, phone=:phone, sort=:sort WHERE id = :id"; 
        $query = $dbcon->prepare($sql);
        $query->bindParam(':bank_account',$data->bank_account, PDO::PARAM_STR);
        $query->bindParam(':bank_comment',$data->bank_comment, PDO::PARAM_STR);
        $query->bindParam(':phone',$data->phone, PDO::PARAM_STR);
        $query->bindParam(':sort',$data->sort, PDO::PARAM_STR);
        $query->bindParam(':id',$data->id, PDO::PARAM_INT);
        $query->execute();
        if($query->rowCount() > 0){
            // echo "เพิ่มข้อมูลเรียบร้อย ok";
            http_response_code(200);
            echo json_encode(array('status' => true, 'massege' => 'แก้ไขข้อมูลเรียบร้อย ok', 'datas' => $data));
            exit;
        }else{
            // echo "มีบางอย่างผิดพลาด";
            http_response_code(200);
            echo json_encode(array('status' => false, 'massege' => 'ไม่มีการปรับปรุง', 'datas' => $data));
            exit;
        }
        
    }
    
    http_response_code(200);
    echo json_encode(array('status' => false, 'massege' => 'NO ACTION', 'datas' => $data));

}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'massege' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
}

