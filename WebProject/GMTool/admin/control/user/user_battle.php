<?php
!defined('IN_ADMIN') && exit('Access Denied');
$start = date('Y-m-d', time() - 86400 * 7);
$end = date('Y-m-d');
$battleType = array('玩家战斗', '地宫',);
foreach ($battleType as $key => $value) {
    $options .= "<option value='$key'>$value</option>";
}
$options2 = "<option value='0'>总数</option><option value='1'>每人详细战斗</option>";

if ($_REQUEST['dotype'] == 'getPageData') {
    $start = $_REQUEST['start'] ? strtotime($_REQUEST['start']) * 1000 : strtotime($start) * 1000;
    $end = $_REQUEST['end'] ? strtotime($_REQUEST['end']) * 1000 : strtotime($end) * 1000;
    $type = $_REQUEST['battleType'];
    $searchtype = $_REQUEST['type'];

    $wheretime = " and l.timeStamp >= $start and l.timeStamp <= $end ";
    if ($type == 1) {
        $typeWhere = '  and l.type=2 ';
    } else {
        $typeWhere = '  and (l.type=1 or l.type=0) ';
    }
    //条件
    if ($_REQUEST['useruid']) {
        $strUid = trim($_REQUEST['useruid'], ',');
        $where = " and u.uid in ('' ";
        if (strpos($strUid, ',')) {
            $UidArr = explode(',', $strUid);
            foreach ($UidArr as $Uid) {
                $where .= ",'$Uid'";
            }
        } else {
            $where .= ",'$strUid'";
        }
        $where .= ') ';
        $insql = "SELECT l.user FROM logrecord l 	LEFT JOIN userprofile u on u.uid=l.data1
			LEFT JOIN alliance a on a.uid=l.data2
			WHERE category = 6 $typeWhere $where ";
        $whereInfo = " and l.user in ($insql)";
    } else if ($_REQUEST['allName']) {
        $strUid = trim($_REQUEST['allName'], ',');
        $where = " and a.alliancename in ('' ";
        if (strpos($strUid, ',')) {
            $UidArr = explode(',', $strUid);
            foreach ($UidArr as $Uid) {
                $where .= ",'$Uid'";
            }
        } else {
            $where .= ",'$strUid'";
        }
        $where .= ') ';
        $insql = "SELECT l.user FROM logrecord l 	LEFT JOIN userprofile u on u.uid=l.data1
			LEFT JOIN alliance a on a.uid=l.data2
			WHERE category = 6 $typeWhere $where ";
        $whereInfo = " and l.user in ($insql)";
    }
    if($searchtype == 1) {//详细
        $limit = 100;
        $sql = "select count(1) sum from logrecord l LEFT JOIN userprofile u on u.uid=l.data1
				LEFT JOIN alliance a on a.uid=l.data2 where l.category=6  $wheretime $typeWhere $whereInfo";
        if (in_array($_COOKIE['u'],$privilegeArr)) {
            echo $sql . PHP_EOL;
        }
        //上边所有都只是为了一个总数......
        $result = $page->execute($sql, 3);
        $count = $result['ret']['data'][0]['sum'];
        if ($count < 1) {
            exit($sql . '<h3>无数据！</h3>');
        }
        $pager = page($count, $_REQUEST['page'], $limit);
        $index = $pager['offset'];
    }

    if($searchtype == 0){
        $sql = "SELECT  FROM_UNIXTIME(l.`timeStamp`/1000, '%Y-%m-%d') `date`,COUNT(l.`data1`) `battle_count`,COUNT(DISTINCT l.`data1`) `totle_user` FROM logrecord l  WHERE l.category=6   $wheretime $typeWhere $whereInfo
				GROUP BY FROM_UNIXTIME(l.`timeStamp`/1000, '%Y-%m-%d')";
    }elseif($searchtype == 1){
        $height = "height:200px";
        $sql = "SELECT  FROM_UNIXTIME(l.`timeStamp`/1000, '%Y-%m-%d') `date`,COUNT(l.`data1`) `battle_count`,COUNT(DISTINCT l.`data1`) `totle_user` FROM logrecord l LEFT JOIN userprofile u ON u.uid=l.data1
				LEFT JOIN alliance a ON a.uid=l.data2 WHERE l.category=6   $wheretime $typeWhere $whereInfo
				GROUP BY FROM_UNIXTIME(l.`timeStamp`/1000, '%Y-%m-%d')";
    }

    $result_stats = $page->execute($sql, 3);
    if (in_array($_COOKIE['u'],$privilegeArr)) {
        $html .= $sql.PHP_EOL;
    }

    $html .= "<div style='float:left;width:100%;{$height};text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
    $_index = array('date' => 'date', 'battle_count' => 'battle_count', 'totle_user' => 'totle_user', 'average' => 'average');
    $html .= "<tr class='listTr'>";
    foreach ($_index as $key => $value) {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    foreach ($result_stats['ret']['data'] as $no => $sqlData) {
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        foreach ($_index as $key => $title) {
            $value = $sqlData[$key];
            switch ($key) {
                case 'average':
                    $html .= "<td>" . floor($sqlData['battle_count'] / $sqlData['totle_user']) . "</td>";
                    break;
                default:
                    $html .= "<td>" . $value . "</td>";
            }
        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    //查看类型
    if($searchtype == 0){//总数
        echo $html;
        exit();
    }else{//

    }
    $sql = "SELECT l.*,u.name ,a.alliancename FROM logrecord l
				LEFT JOIN userprofile u on u.uid=l.data1 
				LEFT JOIN alliance a on a.uid=l.data2 
				WHERE category = 6 $wheretime $typeWhere $whereInfo order by timeStamp desc,user limit $index,$limit ";
    $result = $page->execute($sql, 3);
    $html .= "<div style='float:left;width:100%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'><table class='listTable' cellspacing=1 padding=0 style='TABLE-LAYOUT:fixed;WORD-WRAP:break-word;word-break:break-all;width: 100%; text-align: center'>";
    $index = array('num' => '编号',
        'timeStamp' => '时间',
        'type' => '战斗类型',
        'data4' => '目标点类型',
        'name' => '玩家名称',
        'data1' => '玩家Uid',
        'alliancename' => '联盟名称',
// 			'data2'=>'联盟uid',
        'param1' => '攻击方',
        'param2' => '战斗结果',
        'param3' => '死亡兵力',
        'param4' => '受伤兵力',
    );

    //PointType枚举参见见com.elex.cok.gameengine.world.core,WorldPoint.PointType
    $pointTypeArray = array(
        '空地',   // 0 空地
        '玩家城市', // 1 玩家城市
        '扎营地',  // 2 扎营地
        '资源', // 3 资源
        '遗迹', // 4 遗迹
        '塔',//5 塔
        '地宫', // 6 地宫
        '地宫周边', // 7 地宫周边
        '玩家周边', // 8 玩家周边
        '野怪', // 9 野怪
        '王座', // 10 王座
        '王座周边', // 11 王座周边
        '投石机', // 12 投石机
        '投石机周边', // 13 投石机周边
        '联盟堡垒', // 14 联盟哨塔
        '世界BOSS', // 15 世界BOSS
        '联盟哨塔周边', // 16 联盟哨塔周边
        '世界BOSS周边', // 17 世界BOSS周边
        '联盟领域超级矿', // 18 联盟领域超级矿
        '联盟领域超级矿周边', // /19 联盟领域超级矿周边
        '联盟箭塔', // 20 联盟箭塔
        '联盟仓库', // 21 联盟仓库
        '联盟仓库周边', // 22 联盟仓库周边
        '联盟领域旗帜', // 23 联盟领域旗帜
        '巨龙战场之水晶建筑', // 24 巨龙战场之水晶建筑
        '巨龙战场之水晶建筑周边', // 25 巨龙战场之水晶建筑周边
        '巨龙战场之军械库', // 26 巨龙战场之军械库
        '巨龙战场之军械库周边',   // 27 巨龙战场之军械库周边
        '巨龙战场之训练场', // 28 巨龙战场之训练场
        '巨龙战场之训练场周边',  // 29 巨龙战场之训练场周边
        '巨龙战场之补给点', // 30 巨龙战场之补给点
        '巨龙战场之祝福塔', // 31 巨龙战场之祝福塔
        '巨龙战场之治疗塔', // 32 巨龙战场之治疗塔
        '巨龙战场之龙塔', // 33 巨龙战场之龙塔
        '巨龙战场之兵营', // 34 巨龙战场之兵营
        '巨龙战场之兵营周边', // 35 巨龙战场之兵营周边
        '巨龙战场之传送点', // 36 巨龙战场之传送点
        '联盟风车', // 37 联盟风车
        '联盟风车周边', // 38 联盟风车周边
        '半兽人', // 39 半兽人
        '半兽人周边', // 40 半兽人周边
        '假田', // 41 假田
        '保护怪', // 42 保护怪
        '超级要塞',   // 43 联盟要塞
        '超级要塞周边', // 44 联盟要塞周边
        '资源城',  // 45 资源城
        '资源城周边', // 46 资源城周边
        '远征胜利塔', // 47 远征胜利塔
        '联盟哨塔', // 48 联盟哨塔
        '联盟大炮', // 49 联盟大炮
        '墓地', // 50 墓地
        '墓地周边', // 51 墓地周边
        '联盟Boss', // 52 联盟Boss
        '联盟Boss周边', // 53 联盟Boss周边
        '长城怪', // 54 长城怪
        '超级BOSS', // 55 超级BOSS
        '超级BOSS残骸', // 56 超级BOSS残骸
        '超级BOSS周边', // 57 超级BOSS周边
        '联盟战场积分建筑', //58 联盟战场buff(积分)建筑
        '联盟战场积分建筑周边',   //59 联盟战场buff(积分)建筑周边
        '联盟战场传送点',   //60 联盟战场传送点(出生点)
        '联盟战场传送点周边',    //61 联盟战场传送点周边
    );
    $realBattleType = array('普通战斗', '组队战', '攻打地宫',);
    $fightResult = array('胜利', '失败', '平局',);
    $html .= "<tr class='listTr'>";
    foreach ($index as $key => $value) {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    $i = 1;
    $tempBattleId = "0";
    foreach ($result['ret']['data'] as $no => $sqlData) {
        if ($type != 2 && $sqlData['user'] != $tempBattleId) {
            $tempBattleId = $sqlData['user'];
            if ($i != 1) {
                $html .= "<tr style='background: #bce8f1;'><td colspan=11 ></td></tr>";
            }
        }
        $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>";
        //判断是否是主攻击方或主防守方
        $chiefPlayerText = "";
        if (!empty($sqlData['data3']) && $sqlData['data3'] == '1') {
            $chiefPlayerText = " (主)";
        }
        foreach ($index as $key => $title) {
            $value = $sqlData[$key];
            switch ($key) {
                case 'num':
                    $html .= "<td>" . $i++ . "</td>";
                    break;
                case 'timeStamp':
                    $html .= "<td>" . date('Y-m-d H:i:s', $value / 1000) . "</td>";
                    break;
                case 'type':
                    $html .= "<td>" . $realBattleType[$value] . "</td>";
                    break;
                case 'param1':
                    $html .= "<td>" . ($value == 1 ? '攻击方' : '防守方') . $chiefPlayerText . "</td>";
                    break;
                case 'param2':
                    $html .= "<td>" . ($sqlData['param1'] == 1 ? $fightResult[$value] : $fightResult[abs($value - 1)]) . "</td>";
                    break;
                case 'data4':
                    $html .= "<td>" . (!empty($value) ? $pointTypeArray[(int) $value] : "") . "</td>";
                    break;
                default:
                    $html .= "<td>" . $value . "</td>";
            }

        }
        $html .= "</tr>";
    }
    $html .= "</table></div><br/>";
    if ($pager['pager'])
        $html .= "<div class='span11' style='TEXT-ALIGN:center;'>" . $pager['pager'] . "</div>";
    echo $html;
    exit();
}

include(renderTemplate("{$module}/{$module}_{$action}"));
?>