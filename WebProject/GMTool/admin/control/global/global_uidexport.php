<?php
!defined('IN_ADMIN') && exit('Access Denied');
//开发者debug
$developer = in_array($_COOKIE['u'],$privilegeArr);
//if(PHP_OS == 'WINNT' && $developer){
//    //action=search usernamelist=1,2,3,5 useruidlist=7,8,9
//    $_REQUEST['action'] = 'search';
//    $_REQUEST['username'] = '1,2,3,5';
//    $_REQUEST['useruid'] = '7,8,9';
//}


$type = $_REQUEST['action'];//
$_REQUEST['username'] = trim($_REQUEST['username'],' ,');
$_REQUEST['useruid'] = trim($_REQUEST['useruid'],' ,');
$_REQUEST['useruid'] = str_replace ( '，', ',', $_REQUEST['useruid'] );
$_REQUEST['useruid'] = str_replace(' ','',$_REQUEST['useruid']);

if(!empty($_REQUEST['username']))
    $usernamearr = explode(',',addslashes($_REQUEST['username']));
if(!empty($_REQUEST['useruid']))
    $useruidarr = explode(',',addslashes($_REQUEST['useruid']));
if($developer){
//    var_dump($usernamearr);
//    var_dump($useruidarr);
}
if(empty($usernamearr) && empty($useruidarr)) $type = false;

$return = array();
if ($type) {
    if($usernamearr){
        foreach ($usernamearr as $uname) {
            $account_list = cobar_getAllAccountList('name', $uname);
            //var_dump($account_list);
            $return = array_merge($return, $account_list);
        }
    }elseif($useruidarr){
        $arruids2 = array_chunk($useruidarr,180);
        $uidServerArray = array();
        foreach($arruids2 as $key=>$value){
            $value1 = array_values($value);

            $resulttmp = cobar_getAccountInfoByGameuids($value1);
            $return = array_merge($return,$resulttmp);
        }
//        $return = cobar_getAccountInfoByGameuids($useruidarr);
    }
}

$html .= "<table  class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
$html .= "<tr  class='listTr' onMouseOver=this.style.background='#ffff99' 
onMouseOut=this.style.background='#fff'><td>uid</td><td>用户名</td><td>大本等级</td><td>服务器</td><td>最后登陆时间</td><td>国家</td><td>金币</td><td>付费金币</td>
                    <td>水晶</td><td>铁</td><td>粮食</td><td>木材</td><td>付费总额</td><td>deviceId</td><td>玩家等级</td></tr>";
if(!empty($return)){
    foreach ($return as $row) {
        $sqlone = 'select u.lastOnlineTime,u.gold,u.paidgold,b.level,s.country,ur.stone,ur.iron,ur.food,ur.wood,sum(p.spend) as cost,u.deviceId,u.level
                    from userprofile u
                    left join user_building b on (u.uid = b.uid and b.itemid=400000)
                    left join stat_reg s on u.uid=s.uid
                    left join user_resource ur on u.uid=ur.uid
                    left join paylog p on u.uid=p.uid
                    where u.uid = "'.$row['gameUid'].'" limit 1';
        $server ='s'. $row['server'];
        $r = $page->executeServer($server,$sqlone,1);
        $lastonlinetime = date('Y-m-d H:i:s',intval($r['ret']['data'][0]['lastOnlineTime']/1000));

        $html .= "<tr><td>{$row['gameUid']}</td><td>{$row['gameUserName']}</td>
            <td>{$r['ret']['data'][0]['level']}</td>
            <td>{$row['server']}</td>
            <td>{$lastonlinetime}</td>
            <td>{$r['ret']['data'][0]['country']}</td>
            <td>{$r['ret']['data'][0]['gold']}</td>
            <td>{$r['ret']['data'][0]['paidgold']}</td>
            <td>{$r['ret']['data'][0]['stone']}</td>
            <td>{$r['ret']['data'][0]['iron']}</td>
            <td>{$r['ret']['data'][0]['food']}</td>
            <td>{$r['ret']['data'][0]['wood']}</td>
            <td>{$r['ret']['data'][0]['cost']}</td>
            <td>{$r['ret']['data'][0]['deviceId']}</td>
            <td>{$r['ret']['data'][0]['level']}</td>
            </tr>";
    }
}
$html .= '</table>';


include( renderTemplate("{$module}/{$module}_{$action}") );
?>