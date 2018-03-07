<?php
! defined ( 'IN_ADMIN' ) && exit ( 'Access Denied' );
$start = date('Y-m-d',time()-86400*7);
$end = date('Y-m-d',time());
$lang = loadLanguage();
$exchageXml = loadXml('exchange','exchange');
$databaseXml = loadXml('goods','goods');
$exchangeName = require ADMIN_ROOT . '/etc/packageArray.php';

if ($_REQUEST ['analyze'] == 'platform') {
    $lang = loadLanguage();
    $packageId = $_REQUEST ['packageId'];
    $zh_CN = $exchageXml[$packageId]['item'];

    $isreward = false;
    if(!isset($zh_CN) || empty($zh_CN)){
        $reward = (int)$exchageXml[$packageId]['reward'];
        $rewardXml = loadXml('reward',false);
        $zh_CN = (string)$rewardXml[$reward]['item'];
        $zh_CN_num = (string)$rewardXml[$reward]['num'];
        $isreward = true;
//        $html .= $zh_CN;
//        $html .= "--".$zh_CN_num;
    }
    $goods_name = array();

    if($isreward){
        if(stripos($zh_CN,'|') !== false){
            $a = explode(';', $zh_CN);
            $a_num = explode(';', $zh_CN_num);
        }else{
            $a = explode(';', $zh_CN);
            $a_num = explode(';', $zh_CN_num);
        }

        for($i=0;$i<count($a) ;++$i){
            $goods_name[] = $a[$i].';'.$a_num[$i];
        }
    }else{
        if(stripos($zh_CN,'|') !== false){
            $goods_name = explode('|',$zh_CN);
        }
    }

    foreach($goods_name as $goods){
        $goods = explode(';',$goods);
        $name = $goods[0];
        $nums = $goods[1];
        $html = $databaseXml[$name];
        $items[] = $lang[(int)$databaseXml[$name]['name']].";".$nums;
        if($goods_all) {
            $goods_all = $goods_all . "|".$name;
        }else{
            $goods_all = $name;
        }
        if($num_all) {
            $num_all = $num_all . "|".$nums;
        }else{
            $num_all = $nums;
        }
    }
    $result_sum =array(
        '0'=>$packageId,
        '1'=>$exchangeName[$packageId][0],
        '2'=>$exchangeName[$packageId][1],
        '3'=>$exchageXml[$packageId]['gold_doller'],
    );

    $title = array(
        'Id',
        '礼包内容',
        '金额',
        '金币数'
    );
    $html .= "<div style='float:left;width:90%;height:300px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    foreach ($title as $key=>$value){
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        $html .= "<td>" . $result_sum[0] . "</td>";
        $html .= "<td>" . $result_sum[1] . "</td>";
        $html .= "<td>" . $result_sum[2] . "</td>";
        $html .= "<td>" . $result_sum[3] . "</td>";
        $html .= "</tr>";
        $html .= "<td>" . '物品Id+个数' . "</td>";
        $html .= "</tr>";
        foreach($goods_name as $_value){
            if($_value==null){
                $html .= "<td>" . '此礼包不包含物品 '. "</td>";
            }else {
                $html .= "<td>" . $_value . "</td>";
            }
        }
        $html .= "</tr>";
        $html .= "<td>" . '中文名称+个数' . "</td>";
        $html .= "</tr>";
        foreach($items as $value) {
            if($value==';'){
                $html .= "<td>" . '此礼包不包含物品 '. "</td>";
            }else {
                $html .= "<td>" . $value . "</td>";
            }
        }
        $html .= "</table></div><br/>";
    $html .= "<div style='float:left;width:90%;height:300px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
    $html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    $html .= "<tr class='listTr'>";
    $html .= "</tr>";
    $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
    $html .= "<td>道具列</td><td>" . $goods_all . "</td>";
    $html .= "</tr>";
    $html .= "<td>数量列</td><td>" . $num_all . "</td>";
    $html .= "</tr>";
    $html .= "</table></div><br/>";
    echo $html;
    exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>