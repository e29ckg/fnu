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

/** ----------------------------  ---------------------------*/

$DN_D_PRICE_DAY = 0;
$DN_N_PRICE_DAY = 0;

$_count = 0;
$price_total_all = 0;
$time='';
$error='';
$datas = array();

$price_d_all = 0;
$price_n_all = 0;


$data = json_decode(file_get_contents("php://input"));
$DATE_MONTH = date($data->month);
// $DATE_MONTH = "2022-11";

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

    $sql = "SELECT id,ven_com_num, ven_com_name, ven_name, ven_month FROM `ven_com` WHERE ven_month='$DATE_MONTH'";
    $query = $dbcon->prepare($sql);
    $query->execute();
    $ven_com_nums  = $query->fetchAll(PDO::FETCH_OBJ);

    // $sql = "SELECT ven_date FROM `ven` WHERE ven_month ='$DATE_MONTH' GROUP BY ven_date ORDER BY ven_date";
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $days = $query->fetchAll(PDO::FETCH_OBJ);
    // $day_a = array(); 
    // foreach($days as $ds){
    //     array_push($day_a,$ds->ven_date);
    // }

    // $day_num = count($days);
    
    /** วันหยุด  $HLD */
    // $sql = "SELECT ven_date FROM `ven` WHERE ven_month = '$DATE_MONTH' AND DN ='กลางวัน' GROUP BY `ven_date`;";
    // $query = $dbcon->prepare($sql);
    // $query->execute();
    // $res_holiday = $query->fetchAll(PDO::FETCH_OBJ);
    // $HLD = array();     
    // foreach($res_holiday as $RH){
    //     array_push($HLD,$RH->ven_date);
    // }
    
    /** vens */
    $sql = "SELECT * FROM `ven` WHERE ven_month = '$DATE_MONTH' AND (status =1 OR status=2)";
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
            $price      = 0;
            $work_day   = array();
            $price_one  = 0;
            $weekdays   = 0;
            $holiday    = 0;
            $price_all  = 0;

            foreach($vens as $ven){
                
                if($user->user_id == $ven->user_id){
                    $price_one = $ven->price;
                    $price += $ven->price;
                    
                    // if(ck_holiday($ven->ven_date,$HLD )){
                    //     $holiday ++;
                    // }else{
                    //     $weekdays ++;
                    // }
                    if($ven->DN == 'กลางวัน'){
                        $time = ' เวลา 08.30 - 16.30 น.';
                        $price_d_all += $ven->price;
                    }
                    if($ven->DN == 'กลางคืน'){
                        $time = ' เวลา 16.30 - 08.30 น.';
                        $price_n_all += $ven->price;
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
                    'price_all' => $price,

                ));
            }

        }
    }


    $doc_date   = date("Y-m-d");
    $doc_date   = DateThai_full($doc_date);
    $doc_date_c  = thainumDigit($doc_date);
    $month      = thainumDigit(DateThai_ym($DATE_MONTH));
    $count      = thainumDigit(count($datas)); 
    $price_n_1  = Num_f($price_n_all);
    $price_n_thai = thainumDigit($price_n_all);
    $price_n_text = Convert($price_n_all);
    $price_d        = Num_f($price_d_all);
    $price_d_thai   = thainumDigit($price_d_all);
    $price_d_text   = Convert($price_d_all);
    $price_dn_all      = $price_d_all + $price_n_all;
    $price_all_thai = Num_f($price_dn_all);
    $price_all_text = Convert($price_dn_all);
    // $datas = $datas;


    /**สร้างเอกสาร docx */
    $templateProcessor = new TemplateProcessor('template_in.docx');//เลือกไฟล์ template ที่เราสร้างไว้
    $templateProcessor->setValue('doc_date', $doc_date_c);//อัดตัวแปร รายตัว
    $templateProcessor->setValue('month', $month);//อัดตัวแปร รายตัว
    $templateProcessor->setValue('price_d', $price_d);
    $templateProcessor->setValue('price_n', $price_n_1);
    $templateProcessor->setValue('count', $count);
    $templateProcessor->setValue('price_all', $price_all_thai);
    $templateProcessor->setValue('price_all_text', $price_all_text);

    for($x = 0; $x <= count($datas); $x++){
        $no = 'n' . $x;
        $name = 'name_n' . $x;
        $t3_n = 't3_n' . $x ;
        $price_n = 'price_n' . $x;
        
        if(isset($datas[$x]['name'])){
            $no_data = ($x+1) . '.';
            $username = $datas[$x]['name'];
            $t3_n_data = 'จำนวนเงิน';
            
            $price_total_thai = Num_f($datas[$x]['price_all']).'.-บาท';
            // $price_total_thai = $datas[$x]['price_all'].'.-บาท';
        }else{
            $no_data = '';
            $username = '';
            $t3_n_data = '';
            $price_total_thai = '';
        }

        $templateProcessor->setValue($no, $no_data);
        $templateProcessor->setValue($name, $username);
        $templateProcessor->setValue($t3_n, $t3_n_data);
        $templateProcessor->setValue($price_n,  $price_total_thai);
    }
    
    $templateProcessor->saveAs('ven.docx');//สั่งให้บันทึกข้อมูลลงไฟล์ใหม่

    http_response_code(200);
    echo json_encode(array(
        'status' => true, 
        'message' => 'ok', 
        'month'=>DateThai_ym($DATE_MONTH),
        // 'ven_com_num' => $ven_com_num->ven_com_num,
        // 'ven_com_name' => $ven_com_num->ven_name . $time,
        // 'price_all' => $price_total_all,
        // 'price_all_text' => ReadNumber($price_total_all).'บาทถ้วน',
        // // 'error'=>$error,
        // 'day_num'=> count($days),
        // 'day'=> $day_a,
        // 'holiday'=> $HLD,
        'datas' => $datas
    ));
 

}catch(PDOException $e){
    echo "Faild to connect to database" . $e->getMessage();
    http_response_code(400);
    echo json_encode(array('status' => false, 'message' => 'เกิดข้อผิดพลาด..' . $e->getMessage()));
}
