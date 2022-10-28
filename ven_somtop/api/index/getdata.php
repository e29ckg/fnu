<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json; charset=utf-8");
date_default_timezone_set("Asia/Bangkok");

include_once "../dbconfig.php";

$data = json_decode(file_get_contents("php://input"));

// $DATE_MONTH = date("2022-04");
$DATE_MONTH = date($data->month);
$MOUNT_NUM = (int)date_format(date_create($DATE_MONTH), "t");
$DAY_PRICE = 1250;
$PRICE_ALL = 0;
$VEN_NUM_ALL = 0;
$DAY = array();

for ($x = 1; $x <= $MOUNT_NUM; $x++) {  
    $dy = date("Y-m-d",strtotime($DATE_MONTH.'-'.$x));
    array_push($DAY,$dy);
  }


try{    
    /** วันหยุด  $HLD */
    $sql = "SELECT ven_date FROM `ven` WHERE ven_month = '$DATE_MONTH' AND ven_time='08:30:00' ORDER BY `ven_date` ASC;";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $res_holiday = $query->fetchAll(PDO::FETCH_OBJ);
    $HLD = array();     
    foreach($res_holiday as $RH){
        array_push($HLD,$RH->ven_date);
    }
    
    $sql = "SELECT * FROM somtop WHERE st=10 ORDER BY `sort` ASC;";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_OBJ);
    
    

    $data = array();
    foreach($result as $rs){      

        $sql = "SELECT * FROM `somtop_v` WHERE somtop_id='$rs->id' AND ven_date LIKE '$DATE_MONTH%' ORDER BY ven_date ASC;";
        $query = $dbcon->prepare($sql);
        $query->execute();
        $res_ven_days = $query->fetchAll(PDO::FETCH_OBJ);

        if($res_ven_days){        
            $ven_days = array();
            foreach($res_ven_days as $rvd){
                array_push($ven_days,$rvd->ven_date);
            }
            $VEN_NUM_ALL = $VEN_NUM_ALL + count($ven_days);
            $PRICE_ALL = $PRICE_ALL + (count($ven_days) * $DAY_PRICE);
            array_push($data,array(
                'id'=>$rs->id,
                'name'=>$rs->name,
                'phone'=>$rs->phone,
                'bank_account'=>$rs->bank_account,
                'bank_comment'=>$rs->bank_comment,
                'ven_count'=>count($ven_days),
                'price'=> count($ven_days) * $DAY_PRICE,
                'ven_days' => $ven_days
            ));
        }
    }

    http_response_code(200);
    echo json_encode(array( 
        'status' => true, 
        'massege' => 'ok',
        'month' => DateThai_ym($DATE_MONTH),
        'doc_date'  => DateThai_full(date("Y-m-d")),
        'month_num' => $MOUNT_NUM,
        'ven_num_all' => $VEN_NUM_ALL,
        'day' => $DAY,
        'day_price' => $DAY_PRICE,
        'price_all' => $PRICE_ALL,
        'price_all_text' => Convert($PRICE_ALL),
        'holiday'=> $HLD,
        'datas' =>  $data,
    ));
    

}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'massege' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
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