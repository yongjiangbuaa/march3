<?php

include 'common.func.php';
include 'class.phpmailer.php';
include 'class.smtp.php';

$mailAdd = array('huangyuanqiang');
$currentTime = time();
$h=date("H",$currentTime);
$day = date('Y-m-d',$currentTime);
$yestaday = date('Y-m-d',$currentTime - 3600*24);

//开始处理报警邮件

//连接cokdb_pay数据库
$link = mysqli_connect('10.82.60.173','gow','ZPV48MZH6q9V8oVNtu','cokdb_pay',3306);

//查询当天这个小时之前的总付费数额
//$sql = "select *,sum(paysum) total from payfive p where date >= '$day 00 0' and date < '$day $h 0' and pf !='iostest' group by date";
$sql = "select sum(paysum) as total from payfive p where date >= '$day 00 0' and date < '$day $h 0' and pf !='iostest'";
if ($h == 0){
    $day = $day - 1;
    $sql = "select sum(paysum) as total from payfive p where date >= '$day 00 0' and date < '$day 24 59' and pf !='iostest'";
}
echo $sql."\n";
$results = mysqli_query($link,$sql);
$now = 0;
while ($result = mysqli_fetch_assoc($results)){
    $value = $result['total'];
    //echo "now test".$value."\n";
    $now += $value;
}
echo "\n";
//查询昨天这个小时之前的总付费数额
$y_sql = "select sum(paysum) as total from payfive p where date >= '$yestaday 00 0' and date < '$yestaday $h 0' and pf !='iostest'";
if ($h == 0){
    $yestaday = $yestaday - 1;
    $y_sql = "select sum(paysum) as total from payfive p where date >= '$yestaday 00 0' and date < '$yestaday 24 59' and pf !='iostest'";
}
echo $y_sql."\n";
$results2 = mysqli_query($link,$y_sql);
$before = 0;
while ($result2 = mysqli_fetch_assoc($results2)){
    $value2 = $result2['total'];
    //echo "before test".$value2."\n";
    $before += $value2;
}

//关闭mysql客户端连接
mysql_close($link);


//根据规则发送报警通知
/*
$smsMsg = $h.'时,今['.$now.'$],'.'昨['.$before.'$]';
$info = sendSMSForReport('COQ','COQ付费预警',$smsMsg);
echo json_encode($info);
echo "\n";

$content = '全球收入:';
//echo "11";
$content .= "<br/>".$day.": $now\$<br/>";
//echo "22";
$content .= "昨天:"." $before\$<br/>";
//echo "33";
sendMailForReport("Hourly pay warning",$content,"COQ",$mailAdd);
echo $content;
echo "\n";
*/
if($before > 1000){
    if(($now / $before) <= 0.8){
        //发送短信通知
        $smsMsg = $h.'时,今['.$now.'$],'.'昨['.$before.'$]';
        $info = sendSMSForReport('COQ','COQ付费预警',$smsMsg);
        //echo json_encode($info);
    }

    if(($now / $before) <= 0.9){
        $content = '全球收入:';
        $content .= "<br/>".$day.": $now\$<br/>";
        $content .= "昨天:"." $before\$<br/>";
        sendMailForReport("Hourly pay warning",$content,"COQ",$mailAdd);
    }
}


