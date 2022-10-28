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
    // if($data->action == 'insert'){
    //     $sql = "SELECT user_id FROM `ven_user_bank` WHERE user_id=:user_id LIMIT 1";
    //     $query = $dbcon->prepare($sql);
    //     $query->bindParam(':user_id',$data->user_id, PDO::PARAM_INT);
    //     $query->execute();
    //     $result = $query->fetchAll(PDO::FETCH_OBJ);
    //     if (count($result) > 0) {
    //         http_response_code(200);
    //         echo json_encode(array('status' => false, 'massege' => 'มีข้อมูลอยู่แล้ว กรุณาใช้เมนูแก้ไข', 'datas' => null));
    //     }else{
    //         $sql = "INSERT INTO ven_user_bank(user_id,bank_name,bank_branch,bank_account,phone,comment)
    //                     VALUE(:user_id, :bank_name, :bank_branch, :bank_account, :phone, :comment)";   
    //         $query = $dbcon->prepare($sql);
    //         $query->bindParam(':user_id',$data->user_id, PDO::PARAM_INT);
    //         $query->bindParam(':bank_name',$data->bank_name, PDO::PARAM_STR);
    //         $query->bindParam(':bank_branch',$data->bank_branch, PDO::PARAM_STR);
    //         $query->bindParam(':bank_account',$data->bank_account, PDO::PARAM_STR);
    //         $query->bindParam(':phone',$data->phone, PDO::PARAM_STR);
    //         $query->bindParam(':comment',$data->comment, PDO::PARAM_STR);
    //         $query->execute();
    //         if($query->rowCount() > 0){
    //             // echo "เพิ่มข้อมูลเรียบร้อย ok";
    //             http_response_code(200);
    //             echo json_encode(array('status' => true, 'massege' => 'เพิ่มข้อมูลเรียบร้อย ok', 'datas' => $data));
    //         }else{
    //             // echo "มีบางอย่างผิดพลาด";
    //             http_response_code(200);
    //             echo json_encode(array('status' => false, 'massege' => 'ไม่มีการปรับปรุง', 'datas' => $data));
    //         }
    //     }    
    // }
    if($data->action == 'update'){
        
        $sql = "UPDATE profile SET bank_account =:bank_account, bank_comment=:bank_comment, phone =:phone WHERE user_id = :user_id"; 
        $query = $dbcon->prepare($sql);
        $query->bindParam(':bank_account',$data->bank_account, PDO::PARAM_STR);
        $query->bindParam(':bank_comment',$data->bank_comment, PDO::PARAM_STR);
        $query->bindParam(':phone',$data->phone, PDO::PARAM_STR);
        $query->bindParam(':user_id',$data->user_id, PDO::PARAM_INT);
        $query->execute();
        if($query->rowCount() > 0){
            // echo "เพิ่มข้อมูลเรียบร้อย ok";
            http_response_code(200);
            echo json_encode(array('status' => true, 'massege' => 'แก้ไขข้อมูลเรียบร้อย ok', 'datas' => $data));
        }else{
            // echo "มีบางอย่างผิดพลาด";
            http_response_code(200);
            echo json_encode(array('status' => false, 'massege' => 'ไม่มีการปรับปรุง', 'datas' => $data));
        }
        
    }
    
    // http_response_code(200);
    // echo json_encode(array('status' => true, 'massege' => 'ลบข้อมูลเรียบร้อย ok', 'datas' => $data));

}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'massege' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
}

