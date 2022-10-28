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

// $DN1_PRICE = 2500;  /** ผู้พิพากษากลางคืน   16:30:00    */
// $DN1_PRICE = 1200;  /** จนท กลางคืน      16:30:55    */
// $DN2_PRICE = 3000;  /** ผู้พิพากษากลางวัน   08:30:00    */
// $DN2_PRICE = 1500;  /** จนท กลางคืน      08:30:01, 08:30:11, 08:30:22    */

$DN_D_PRICE_DAY = 0;
$DN_N_PRICE_DAY = 0;

$_count = 0;
$price_dn1_all = 0;
$price_dn2_all = 0;
$error='';

// $ven_mounth = date("Y-m-d");
// $date_end = date('Y-m-d', strtotime('+7 days'));
// $ven_mounth = date_format($ven_mounth,"Y-m");
//action.php

$data = json_decode(file_get_contents("php://input"));

// $DATE_MONTH = date("2022-10");
$DATE_MONTH = date($data->month);
$users = array();
$vens = array();
$ven_users = array();
$ven_coms = array();

$datas = array();
// $DATE_MONTH = '2022-11';

$price_all = 0;
// http_response_code(200);
//         echo json_encode(array('status' => false, 'message' => 'ไม่พบข้อมูล', 'responseJSON' => $data->month));
// exit;
try{    

    $sql = "SELECT user_id,fname,name,sname FROM profile WHERE status=10 ORDER BY st";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $users = $query->fetchAll(PDO::FETCH_OBJ);
    
    $sql = "SELECT * FROM ven WHERE ven_month='$DATE_MONTH' AND (status=1 OR status=2) ORDER BY user_id";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $vens = $query->fetchAll(PDO::FETCH_OBJ);
    
    $sql = "SELECT * FROM ven_com WHERE ven_month='$DATE_MONTH' ORDER BY ven_com_num ASC";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $ven_coms = $query->fetchAll(PDO::FETCH_OBJ);
   
    // $datas = $users;

    foreach($users as $user){
        $ven_users = array();
        $D_c = 0;
        $N_c = 0;
        $D_price = 0;
        $N_price = 0;
        foreach($vens as $ven){
            if($ven->user_id == $user->user_id){
                if($ven->DN == 'กลางวัน'){
                    $D_price += $ven->price;
                    $D_c ++;
                }
                if($ven->DN == 'กลางคืน'){
                    $N_price += $ven->price;
                    $N_c ++ ;
                }
                $price_all += $ven->price;
                array_push($ven_users,array(
                    "ven_date" => $ven->ven_date,
                    "DN" => $ven->DN,
                    "price" => $ven->price,
                ));        
            }
        }
        
        if(count($ven_users) > 0){
            array_push($datas,array(
                "uid" => $user->user_id,
                "name" => $user->fname.$user->name.' '.$user->sname,
                "vens" => $ven_users,
                "D_c" => $D_c,
                "N_c" => $N_c,
                "D_price" => $D_price,
                "N_price" => $N_price    
            ));
        }
    }



    http_response_code(200);
    echo json_encode(array(
        'status' => true, 
        'message' => 'Ok.', 
        'datas' => $datas,
        "price_all" => $price_all,
        "ven_coms"=>$ven_coms,
        'month' => DateThai_ym($DATE_MONTH),
    ));

    // $sql = "SELECT price FROM `ven_com` WHERE ven_month ='$DATE_MONTH'" ;
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $price_day = $query->fetchAll(PDO::FETCH_OBJ);
    // foreach($price_day as $pd){
    //     $DN_D_PRICE_DAY += $pd->price;
    // }

    // $sql = "SELECT price FROM `ven_com` WHERE ven_month ='$DATE_MONTH'";
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $price_day = $query->fetchAll(PDO::FETCH_OBJ);
    // foreach($price_day as $pd){
    //     $DN_N_PRICE_DAY += $pd->price;
    // }

    // $sql = "SELECT ven_date FROM `ven` WHERE ven_month ='$DATE_MONTH' AND status = 1 GROUP BY ven_date;";
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $day = $query->fetchAll(PDO::FETCH_OBJ);
    // $day_num = count($day);

    // /** วันหยุด  $HLD */
    // $sql = "SELECT ven_date FROM `ven` WHERE ven_month = '$DATE_MONTH' GROUP BY `ven_date`;";
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $res_holiday = $query->fetchAll(PDO::FETCH_OBJ);
    // $HLD_count = count($res_holiday);

    // // $sql = "SELECT * FROM ven_user GROUP BY user_id ORDER BY 'order' ASC";
    // $sql = "SELECT * FROM profile WHERE status = 10 ORDER BY st ASC";
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $result = $query->fetchAll(PDO::FETCH_OBJ);

    // if (count($result) > 0) {
        

    //     foreach ($result as $rs){
            
    //         // $sql_ven = "SELECT * FROM ven WHERE user_id='$rs->user_id' AND ven_month = '$DATE_MONTH' AND status='1'";
    //         $sql_ven = "SELECT v.ven_date, v.ven_time, v.DN, vc.u_role, vc.ven_com_name, vc.price, v.user_id, v.ven_com_id
    //                     FROM ven_com as vc 
    //                     INNER JOIN ven as v 
    //                     ON vc.id = v.ven_com_id 
    //                     WHERE v.ven_month = '$DATE_MONTH' AND v.user_id = $rs->user_id AND v.`status` = 1 ORDER BY v.ven_date ASC, v.ven_time ASC;";
    //         $query_ven = $dbcon->prepare($sql_ven);
    //         $query_ven->execute();
    //         $result_ven = $query_ven->fetchAll(PDO::FETCH_OBJ);

    //         if (count($result_ven) > 0) {
    //             $dn_1_count = 0;
    //             $dn_1_price = 0;
    //             $dn_2_count = 0;
    //             $dn_2_price = 0;
    //             $price_total = 0;
                
    //             foreach ($result_ven as $rs_ven){
    //                 // $TIME['16:30:00']['PRICE'];
    //                 // if($TIME[$rs_ven->ven_time]){
    //                     // $time = $TIME[$rs_ven->ven_time]['PRICE'];
    //                     $price_total = $price_total + $rs_ven->price;
    //                 // }

                    
    //                 // if($TIME[$rs_ven->ven_time]){
    //                     if($rs_ven->DN === 'กลางคืน'){
    //                         $dn_1_count = $dn_1_count + 1;
    //                         $dn_1_price = $dn_1_price + $rs_ven->price;
    //                         $price_dn1_all = $price_dn1_all + $rs_ven->price;
    //                     }else {
    //                         $dn_2_count = $dn_2_count + 1;
    //                         $dn_2_price = $dn_2_price + $rs_ven->price;
    //                         $price_dn2_all = $price_dn2_all + $rs_ven->price;
    //                     }
    //                 // }else {
    //                 //     $error = 'Ven_Time error';
    //                 // }

    //             }
    //         }else{
    //             $dn_1_count = 0;
    //             $dn_1_price = 0;
    //             $dn_2_count = 0;
    //             $dn_2_price = 0;
    //             $price_total = 0;
    //         }

    //         if($price_total > 0){
    //             $_count++;
    //             $datas[] = [
    //                 // 'no'=> thainumDigit($_count),
    //                 'no'=> $_count,
    //                 'user_id' => $rs->user_id,
    //                 'user_name' => $rs->fname.$rs->name.' '.$rs->sname, 
    //                 'month' => $DATE_MONTH,
    //                 'DN_1' => [
    //                     'ven_name' => 'กลางคืน',
    //                     'ven_count' => $dn_1_count,
    //                     // 'price' => Num_f($dn_1_price)
    //                     'price' => $dn_1_price
    
    //                 ], 
    //                 'DN_2' => [
    //                     'ven_name' => 'กลางวัน',
    //                     'ven_count' => $dn_2_count,
    //                     // 'price' => Num_f($dn_2_price)
    //                     'price' => $dn_2_price
    //                 ], 
    //                 // 'price_total' =>  Num_f($price_total), 
    //                 'price_total' =>  $price_total, 
    //                 'price_total_thai' =>  Num_f($price_total), 
    //                 'error'=> $error
    //             ];
    //         }

    //     }
    // }

    // $doc_date = date("Y-m-d");
    // $doc_date  = DateThai_full($doc_date);
    // $doc_date_c  = thainumDigit($doc_date);
    // $month = thainumDigit(DateThai_ym($DATE_MONTH));
    // $count = thainumDigit($_count); 
    // $price_n_1 = Num_f($price_dn1_all);
    // $price_n_thai = thainumDigit($price_dn1_all);
    // $price_n_text = Convert($price_dn1_all);
    // $price_d = Num_f($price_dn2_all);
    // $price_d_thai = thainumDigit($price_dn2_all);
    // $price_d_text = Convert($price_dn2_all);
    // $price_all = $price_dn1_all + $price_dn2_all;
    // $price_all_thai = Num_f($price_dn1_all + $price_dn2_all);
    // $price_all_text = Convert($price_dn1_all + $price_dn2_all);
    // // $datas = $datas;


    // /**สร้างเอกสาร docx */
    // $templateProcessor = new TemplateProcessor('template_in.docx');//เลือกไฟล์ template ที่เราสร้างไว้
    // $templateProcessor->setValue('doc_date', $doc_date_c);//อัดตัวแปร รายตัว
    // $templateProcessor->setValue('month', $month);//อัดตัวแปร รายตัว
    // $templateProcessor->setValue('price_d', $price_d);
    // $templateProcessor->setValue('price_n', $price_n_1);
    // $templateProcessor->setValue('count', $count);
    // $templateProcessor->setValue('price_all', $price_all_thai);
    // $templateProcessor->setValue('price_all_text', $price_all_text);

    // for($x = 0; $x <= 22; $x++){
    //     $no = 'n' . $x;
    //     $name = 'name_n' . $x;
    //     $t3_n = 't3_n' . $x ;
    //     $price_n = 'price_n' . $x;
        
    //     if(isset($datas[$x]['user_name'])){
    //         $no_data = ($x+1) . '.';
    //         $username = $datas[$x]['user_name'];
    //         $t3_n_data = 'จำนวนเงิน';
    //         $price_total_thai = $datas[$x]['price_total_thai'].'.-บาท';
    //     }else{
    //         $no_data = '';
    //         $username = '';
    //         $t3_n_data = '';
    //         $price_total_thai = '';
    //     }


    //     $templateProcessor->setValue($no, $no_data);
    //     $templateProcessor->setValue($name, $username);
    //     $templateProcessor->setValue($t3_n, $t3_n_data);
    //     $templateProcessor->setValue($price_n,  $price_total_thai);
    // }
    // // $templateProcessor->setValue(
    // //     [
    // //         'doc_no',
    // //         'doc_date',
    // //     ],
    // //     [
    // //         'ทน1234/2345',
    // //         '18 กรกฏาคม 2560',
    // //     ]);//อัดตัวแปรแบบชุด
    // $templateProcessor->saveAs('ven.docx');//สั่งให้บันทึกข้อมูลลงไฟล์ใหม่


    // if (count($result) > 0) {
    //     http_response_code(200);
    //     echo json_encode(array(
    //         'doc_date'=>$doc_date, 
    //         'status' => true, 
    //         'message' => 'ok',
    //         'month' => DateThai_ym($DATE_MONTH),
    //         'DN_D_PRICE_DAY' => $DN_D_PRICE_DAY,
    //         'DN_N_PRICE_DAY' => $DN_N_PRICE_DAY,
    //         'day_num' => $day_num,
    //         'holiday_num' => $HLD_count,
    //         'count'=> $_count, 
    //         'price_n'       => $price_dn1_all,
    //         'price_n_thai'  => $price_n_thai,
    //         'price_n_text'  => $price_n_text,
    //         'price_d'       => $price_dn2_all,
    //         'price_d_thai'  => $price_d_thai,
    //         'price_d_text'  => $price_d_text,
    //         'price_all'     => $price_all,
    //         'price_all_thai'     => $price_all_thai,
    //         'price_all_text'=> $price_all_text,
    //         'link_download' => 'api/ven.docx',
    //         'datas'         => $datas,
    //         // 'responseJSON' => $result
    //     ));
    // }else {
    //     http_response_code(200);
    //     echo json_encode(array('status' => false, 'message' => 'ไม่พบข้อมูล', 'responseJSON' => null));
    // }

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
};