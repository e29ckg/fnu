<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
// header("'Access-Control-Allow-Credentials', 'true'");
// header('Content-Type: application/javascript');
header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set("Asia/Bangkok");



include 'vendor/autoload.php';
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

include_once "dbconfig.php";
include_once "function.php";

/** ----------------------------  a1 ใบขวาง กลางคืน ---------------------------*/

$DN_D_PRICE_DAY = 0;
$DN_N_PRICE_DAY = 0;

$_count = 0;
$price_total_all = 0;
$time='';
$error='';
$datas = array();

// $ven_mounth = date("Y-m-d");
// $date_end = date('Y-m-d', strtotime('+7 days'));
// $ven_mounth = date_format($ven_mounth,"Y-m");
//action.php

$data = json_decode(file_get_contents("php://input"));
// $DATE_MONTH = '2022-11';
// $DATE_MONTH = date('Y-m', strtotime('2022-10'));
$ven_com_id = date($data->ven_com_id);

$HOLIDAY=[];
// http_response_code(200);
//         echo json_encode(array('status' => false, 'message' => 'ไม่พบข้อมูล', 'responseJSON' => $data));
// exit;
try{    
    // $sql = "SELECT price FROM `ven_com` WHERE ven_month ='$DATE_MONTH' AND DN = 'กลางวัน';";
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $price_day = $query->fetchAll(PDO::FETCH_OBJ);
    // foreach($price_day as $pd){
    //     $DN_D_PRICE_DAY += $pd->price;
    // }

    // $sql = "SELECT price FROM `ven_com` WHERE ven_month ='$DATE_MONTH' AND DN = 'กลางคืน';";
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $price_day = $query->fetchAll(PDO::FETCH_OBJ);
    // foreach($price_day as $pd){
    //     $DN_N_PRICE_DAY += $pd->price;
    // }

    $sql = "SELECT id,ven_com_num, ven_com_name, ven_name, ven_month FROM `ven_com` WHERE id='$ven_com_id'";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $ven_com_num  = $query->fetch(PDO::FETCH_OBJ);
    $DATE_MONTH = $ven_com_num->ven_month;

    $sql = "SELECT ven_date FROM `ven` WHERE ven_month ='$DATE_MONTH' GROUP BY ven_date ORDER BY ven_date";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $days = $query->fetchAll(PDO::FETCH_OBJ);
    $day_a = array(); 
    foreach($days as $ds){
        array_push($day_a,$ds->ven_date);
    }

    $day_num = count($days);
    
    /** วันหยุด  $HLD */
    $sql = "SELECT ven_date FROM `ven` WHERE ven_month = '$DATE_MONTH' AND DN ='กลางวัน' GROUP BY `ven_date`;";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $res_holiday = $query->fetchAll(PDO::FETCH_OBJ);
    $HLD = array();     
    foreach($res_holiday as $RH){
        array_push($HLD,$RH->ven_date);
    }
    
    /** vens */
    $sql = "SELECT * FROM `ven` WHERE ven_com_idb = '$ven_com_id' AND (status =1 OR status=2)";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $vens = $query->fetchAll(PDO::FETCH_OBJ);

    /** user */    
    $sql = "SELECT * FROM profile WHERE status = 10 ORDER BY st ASC";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $users = $query->fetchAll(PDO::FETCH_OBJ);


    if (count($users) > 0) { 
        foreach ($users as $user){           
            $price  = 0;
            $work_day   = array();
            $price_one = 0;
            $weekdays = 0;
            $holiday = 0;
            $price_all = 0;

            foreach($vens as $ven){
                if($user->user_id == $ven->user_id){
                    $price_one = $ven->price;
                    $price += $ven->price;
                    
                    if(ck_holiday($ven->ven_date,$HLD )){
                        $holiday ++;
                    }else{
                        $weekdays ++;
                    }
                    if($ven->DN == 'กลางวัน'){
                        $time = ' เวลา 08.30 - 16.30 น.';
                    }
                    if($ven->DN == 'กลางคืน'){
                        $time = ' เวลา 16.30 - 08.30 น.';
                    }
                    array_push($work_day,$ven->ven_date);
                }


            }

           
            if($price > 0){
                
                $price_total_all += $price_one * ($weekdays + $holiday);
                array_push($datas,array(
                    'user_id'=>$user->user_id,
                    'name'  => $user->fname.$user->name.' '.$user->sname,
                    'bank_account'  => $user->bank_account,
                    'bank_comment'  => $user->bank_comment,
                    'phone'  => $user->phone,
                    'work_day'=>$work_day,
                    'price_one'=>$price_one,
                    'weekdays' => $weekdays,
                    'holiday' => $holiday,
                    'price_all' => $price_one * ($weekdays + $holiday),

                ));
            }


        }
    }

    

    http_response_code(200);
    echo json_encode(array(
        'status' => true, 
        'message' => 'ok', 
        'month'=>DateThai_ym($DATE_MONTH),
        'ven_com_num' => $ven_com_num->ven_com_num,
        'ven_com_name' => $ven_com_num->ven_name . $time,
        'price_all' => $price_total_all,
        'price_all_text' => ReadNumber($price_total_all).'บาทถ้วน',
        // 'error'=>$error,
        'day_num'=> count($days),
        'day'=> $day_a,
        'holiday'=> $HLD,
        'datas' => $datas
    ));
 

}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'message' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
}
