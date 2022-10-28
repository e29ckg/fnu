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

/** ----------------------------  a1 ใบขวาง กลางคืน ---------------------------*/

// $DN1_PRICE = 2500;  /** ผู้พิพากษากลางคืน   16:30:00    */
// $DN1_PRICE = 1200;  /** จนท กลางคืน      16:30:55    */
// $DN2_PRICE = 3000;  /** ผู้พิพากษากลางวัน   08:30:00    */
// $DN2_PRICE = 1500;  /** จนท กลางคืน      08:30:01, 08:30:11, 08:30:22    */
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
    $sql = "SELECT * FROM `ven` WHERE ven_com_idb = '$ven_com_id'";
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

           
           
           
            // $sql_ven = "SELECT * FROM ven WHERE user_id='$rs->user_id' AND ven_month = '$DATE_MONTH' AND status='1'";
            // $sql_ven = "SELECT v.ven_date, v.ven_time, v.DN, vc.u_role, vc.ven_com_name, vc.price, v.user_id, v.ven_com_id
            //             FROM ven_com as vc 
            //             INNER JOIN ven as v 
            //             ON vc.id = v.ven_com_id 
            //             WHERE v.ven_com_idb ='$ven_com_id' AND v.ven_month = '$DATE_MONTH' AND v.user_id = $rs->user_id AND (v.`status` = 1 OR v.`status` = 2) ORDER BY v.ven_date ASC";
            // $query_ven = $dbcon->prepare($sql_ven);
            // $query_ven->execute();
            // $result_ven = $query_ven->fetchAll(PDO::FETCH_OBJ);

            // $dn_1_price_day = 0;
            // $dn_2_price_day = 0;
            // if (count($result_ven) > 0) {
            //     $ven=[];  
            //     $ven['DN1'] =[];  
            //     $ven['DN2'] =[];  
            //     $dn_1_count = 0;
            //     $dn_1_price = 0;
            //     $dn_1_holiday = 0;
            //     $dn_1_weekdays = 0;

            //     $dn_2_count = 0;
            //     $dn_2_price = 0;
            //     $price_total = 0;       
            //     $dn_2_holiday = 0;
            //     $dn_2_weekdays = 0;       
            //     foreach ($result_ven as $rs_ven){     
            //         if($rs_ven->DN === 'กลางคืน'){
            //             $ven['DN1'][]   = $rs_ven->ven_date;
            //             $dn_1_count     = $dn_1_count + 1;
            //             $dn_1_price_day = $rs_ven->price;
            //             $dn_1_price     = $dn_1_price + $rs_ven->price;
            //             $price_dn1_all  = $price_dn1_all + $rs_ven->price;
            //             ck_holiday($rs_ven->ven_date, $HLD) ? $dn_1_holiday = $dn_1_holiday + 1 : $dn_1_weekdays = $dn_1_weekdays + 1 ;
                        
            //         }else {
            //             $ven['DN2'][]   = $rs_ven->ven_date;
            //             $dn_2_count     = $dn_2_count + 1;
            //             $dn_2_price     = $dn_2_price + $rs_ven->price;
            //             $dn_2_price_day = $rs_ven->price;
            //             $price_dn2_all  = $price_dn2_all + $rs_ven->price;
            //             ck_holiday($rs_ven->ven_date, $HLD) ? $dn_2_holiday = $dn_2_holiday + 1 : $dn_2_weekdays = $dn_2_weekdays + 1 ;                        
            //         }
            //     }
            // }else{
            //     $dn_1_count = 0;
            //     $dn_1_price = 0;
            //     $dn_2_count = 0;
            //     $dn_2_price = 0;
            //     $price_total = 0;   
            // }
            if($price > 0){
                
                $price_total_all += $price_one * ($weekdays + $holiday);
                array_push($datas,array(
                    'user_id'=>$user->user_id,
                    'name'  => $user->fname.$user->name.' '.$user->sname,
                    'work_day'=>$work_day,
                    'price_one'=>$price_one,
                    'weekdays' => $weekdays,
                    'holiday' => $holiday,
                    'price_all' => $price_one * ($weekdays + $holiday),

                    // 'DN_1' => [
                    //     'ven_name' => 'กลางคืน',
                    //     'ven_count' => $dn_1_count,
                    //     'price_day' => $dn_1_price_day,
                    //     'price' => $dn_1_price,
                    //     'work_day' => $ven['DN1'],
                    //     'weekdays' => $dn_1_weekdays,
                    // ], 
                    // 'DN_2' => [
                    //     'ven_name' => 'กลางวัน',
                    //     'ven_count' => $dn_2_count,
                    //     'price_day' => $dn_2_price_day,
                    //     'price' => $dn_2_price,
                    //     'work_day' => $ven['DN2'],
                    //     'holiday' => $dn_2_holiday,
                    //     'weekdays' => $dn_2_weekdays,
                    // ], 
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
        // 'DN_D_PRICE_DAY' => $DN_D_PRICE_DAY,
        // 'DN_N_PRICE_DAY' => $DN_N_PRICE_DAY,
        'price_all' => $price_total_all,
        'price_all_text' => ReadNumber($price_total_all).'บาทถ้วน',
        // 'price_dn2_all' => $price_dn2_all,
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


function DateThai_full($strDate)
{
    if($strDate == ''){
        return "-";
    }
    $strYear = date("Y",strtotime($strDate))+543;
    $strMonth= date("n",strtotime($strDate));
    $strDay= date("j",strtotime($strDate));
    $strHour= date("H",strtotime($strDate));
    $strMinute= date("i",strtotime($strDate));
    $strSeconds= date("s",strtotime($strDate));
    $strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม",
                        "สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
    $strMonthThai=$strMonthCut[$strMonth];
    return "$strDay $strMonthThai $strYear";
}

function DateThai_ym($strDate)
{
    if($strDate == ''){
        return "-";
    }
    $strYear = date("Y",strtotime($strDate))+543;
    $strMonth= date("n",strtotime($strDate));
    $strDay= date("j",strtotime($strDate));
    $strHour= date("H",strtotime($strDate));
    $strMinute= date("i",strtotime($strDate));
    $strSeconds= date("s",strtotime($strDate));
    $strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม",
                        "สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
    $strMonthThai=$strMonthCut[$strMonth];
    return "$strMonthThai $strYear";
}
function DateThai_D($strDate)
{
    if($strDate == ''){
        return "-";
    }
    $strYear = date("Y",strtotime($strDate))+543;
    $strMonth= date("n",strtotime($strDate));
    $strDay= date("j",strtotime($strDate));
    $strHour= date("H",strtotime($strDate));
    $strMinute= date("i",strtotime($strDate));
    $strSeconds= date("s",strtotime($strDate));
    $strMonthCut = Array("","มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม",
                        "สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
    $strMonthThai=$strMonthCut[$strMonth];
    return "$strDay";
}

function Num_f($num){
    return thainumDigit(number_format($num));
}
function Convert($amount_number)
{
    $amount_number = number_format($amount_number, 2, ".", "");
    $pt = strpos($amount_number, ".");
    $number = $fraction = "";
    if ($pt === false) {
        $number = $amount_number;
    } else {
        $number = substr($amount_number, 0, $pt);
        $fraction = substr($amount_number, $pt + 1);
    }

    $ret = "";
    $baht = ReadNumber($number);
    if ($baht != "") {
        $ret .= $baht . "บาท";
    }

    $satang = ReadNumber($fraction);
    if ($satang != "") {
        $ret .= $satang . "สตางค์";
    } else {
        $ret .= "ถ้วน";
    }

    return $ret;
}

function ReadNumber($number)
{
    $position_call = array("แสน", "หมื่น", "พัน", "ร้อย", "สิบ", "");
    $number_call = array("", "หนึ่ง", "สอง", "สาม", "สี่", "ห้า", "หก", "เจ็ด", "แปด", "เก้า");
    $number = $number + 0;
    $ret = "";
    if ($number == 0) {
        return $ret;
    }

    if ($number > 1000000) {
        $ret .= ReadNumber(intval($number / 1000000)) . "ล้าน";
        $number = intval(fmod($number, 1000000));
    }

    $divider = 100000;
    $pos = 0;
    while ($number > 0) {
        $d = intval($number / $divider);
        $ret .= (($divider == 10) && ($d == 2)) ? "ยี่" :
        ((($divider == 10) && ($d == 1)) ? "" :
            ((($divider == 1) && ($d == 1) && ($ret != "")) ? "เอ็ด" : $number_call[$d]));
        $ret .= ($d ? $position_call[$pos] : "");
        $number = $number % $divider;
        $divider = $divider / 10;
        $pos++;
    }
    return $ret;
}

function thainumDigit($num){
    return str_replace(array( '0' , '1' , '2' , '3' , '4' , '5' , '6' ,'7' , '8' , '9' ),
    array( "๐" , "๑" , "๒" , "๓" , "๔" , "๕" , "๖" , "๗" , "๘" , "๙" ),$num);
}

function ck_holiday($value, $array){
    return in_array($value, $array, true) ? true : false ;
}