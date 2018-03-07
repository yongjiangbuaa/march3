
<?php
defined('IB_ROOT') || define('IB_ROOT', __DIR__);
require_once IB_ROOT.'/ib.inc.php';
ini_set('memory_limit', '512M');
$type = 1;//1是slave,2是stats
if($type==1) {
	$db = COK_DB_NAME;
	$dump_db = "slave_db"; //master_db
}else if($type==2){
	$db = IB_DB_NAME_SNAPSHOT;
	$dump_db = "stats_db";
}
$num = substr($db,5);


//$page = new BasePage();

//Fatal error: Class 'BasePage' not found in /data/htdocs/stats/infobright/test_referrer.php on line 14

//$sql = "select floor(date/100), count(distinct uid) c from $db.stat_login group by floor(date/100);";

//时间限制
//$start = strtotime("-1 days")*1000;

//480*800
//480*854
//540*960
//$sql = "select s.width,s.height ,s.model,count(s.uid) from $db.stat_phone s INNER JOIN  $db.stat_reg r on r.uid=s.uid WHERE ((s.width=480 and s.height=800)or(s.width=480 and s.height=854)or(s.width=540 and s.height=960)) and r.time > $start group by s.width,s.height,s.model ; ";

//这15天内,购买哪些礼包,购买时间; 花多少钱, 近15天 首冲玩家, uids,//注册时间,
//$sql="select  $num sid,p.uid,p.productId,p.spend,date_format(from_unixtime(min(p.time)/1000),'%Y-%m-%d') as buytime ,date_format(from_unixtime(r.time/1000),'%Y-%m-%d') as regtime from $db.paylog p inner join $db.stat_reg r on r.uid=p.uid group by uid having min(p.time)>1461081600000 and min(p.time)<1462377600000 order by p.productId";

//$sql="select b.uid,b.gaid,from_unixtime(a.time/1000,'%Y%m%d') as time ,from_unixtime(b.regTime/1000,'%Y%m%d') as regTime ,a.spend from $db.paylog a ,$db.userprofile b where a.uid=b.uid and  from_unixtime(a.time/1000,'%Y%m%d')=20160427;";
//$sql = "select ii.itemId,sum(ii.count) cnt from $db.user_item ii inner join $db.userprofile u on u.uid=ii.ownerId where u.bantime != 9223372036854775807 group by ii.itemId";
//$num = substr($db,5);
//$sql = "select $num sid ,u.pic,u.payTotal,sp.model,sp.width,sp.height,r.ip from $db.stat_reg r INNER join $db.stat_phone sp on r.uid=sp.uid INNER JOIN $db.userprofile u on u.uid=r.uid where r.country='CN'  ";
//$sql = "select s.width,s.height ,count(s.uid) ,r.country from $db.stat_phone s INNER JOIN  $db.stat_reg r on r.uid=s.uid WHERE r.time > 1461513600000 group by s.width,s.height,r.country ; ";
//默认头像谁用了
//$sql = "select u.pic , b.level ,count(*) from $db.userprofile u INNER JOIN $db.user_building b on u.uid = b.uid where b.itemId = 400000 and (u.picVer=0 or u.picVer = 1000001) and (lastOnlineTime - $start) >0 GROUP BY u.pic,b.level ;";

//$sql = "select buildingLv ,productId ,count(1) from $db.paylog group by buildingLv ,productId ";
//cokdb11

//$sql = "select $num sid,curMission ,count(1) from $db.kill_Titan k INNER JOIN $db.userprofile u on u.uid=k.uid WHERE u.lastOnlineTime>=$start group by curMission ;";

//select u.pic,count(distinct l.uid) from stat_login l inner join userprofile_full u  on u.uid=l.uid where l.time>1462982400000 and l.castlelevel>10 group by u.pic;

//$ret = $page->executeServer($server, $sql, 3);
//
//$result = $ret ['ret'] ['data'];


//select tp.addr,case when nn.pic in(100008,100015,100050,100053,100054,'g008','g015','g024','g044','g050','g053','g054') then 1 when nn.pic in('g026','g032','g038','g041','g045','g046','g052','g007','g012',100052,100026,100032,100038,100041) then 2 end as sex ,nn.model,sum(paytotal),count(1) from nannv nn  inner join t_ip tp on nn.ip=tp.ip group by tp.addr,sex,nn.model;
//必须group不用
//$sql = "select case when nn.pic in(100008,100015,100050,100053,100054,'g008','g015','g024','g044','g050','g053','g054') then 1 when nn.pic in('g026','g032','g038','g041','g045','g046','g052','g007','g012',100052,100026,100032,100038,100041) then 2 end as sex ,count(1) from $db.userprofile nn  inner join $db.stat_login_2016_7 l on nn.uid=l.uid group by nn.pic;";

//$sql = "select $num,uid,productid,orderparam from $db.paylog where time>=1470355200000 and time<=1470621600000 and orderParam REGEXP '7010[0-6]';";
//$sql = "select $num,r.pf,ub.level ,count(1) from $db.userprofile u
//inner join $db.user_building ub on u.uid=ub.uid
//inner join $db.stat_reg r on r.uid=u.uid
//where ub.itemid = 400000 and r.time>=1468281600000 and u.lastOnlineTime<=1470528000000
//group by r.pf,ub.level;";


//$sql = "select $num,u.uid, u.level as user_level, u.paidGold, u.payTotal, vip.level as vip_level, vip.score as vip_score, FROM_UNIXTIME(u.regTime/1000, '%Y-%m-%d-%H-%i-%S') as regTime, FROM_UNIXTIME(u.lastOnlineTime/1000, '%Y-%m-%d-%H-%i-%S') as lastOnlineTime, sum(pay.spend), count(pay.uid), reg.country, reg.pf, building.level as building_lv
//from $db.userprofile u
//left join $db.paylog pay on u.uid=pay.uid
//inner join $db.stat_reg reg on u.uid=reg.uid
//inner join $db.user_building building on u.uid=building.uid
//inner join $db.user_vip vip on u.uid=vip.uid
//where  building.itemId='400000' and u.regTime>1468281600000 and u.regTime<1471478400000 group by u.uid;";

//$sql = "select $num, from_unixtime(time/1000,'%Y%m%d') date , count(distinct uid) cnt from $db.paylog where time>=1459468800000 and time <=1464652800000 group by date";
//$sql = " select $num,from_unixtime(l.time/1000,'%Y%m%d') date ,count(distinct l.uid) cnt from $db.stat_login_2016_3 l group by date;";
//$sql = " select $num,from_unixtime(l2.time/1000,'%Y%m%d') date ,count(distinct l2.uid) cnt from $db.stat_login_2016_4 l2 group by date;";

//executesql($sql,'month3__');

//$sql = "select $num,uid,productid,count(1) cnt from $db.paylog where productid not in('85001','85002','85003','9006','9010','9011','85004','85005') and time>=1473241200000 and time<=1473372000000 group by uid ,productid;";
//$sql = "select u.uid,case
//when u.payTotal=0 then 0
//when u.payTotal>0 and u.payTotal<=1000 then 1
//when u.payTotal>1000 and u.payTotal<=10000 then 2
//when u.payTotal>10000 and u.payTotal<=40000 then 3
//when u.payTotal>40000 and u.payTotal<=100000 then 4
//when u.payTotal>100000 and  u.payTotal<=200000 then 5
//when u.payTotal>200000 and  u.payTotal<=1000000 then 6
//when u.payTotal>1000000 and  u.payTotal<=2000000 then 7
//when u.payTotal>2000000 then 8 end as payLevel,ub.itemid,ub.level
//from $db.userprofile u
//inner join $db.user_building ub on u.uid=ub.uid
//where u.banTime != 9223372036854775807 and u.lastOnlineTime >1475625600000 and ub.itemid in(400000,484000,485000,486000,487000,489000) and ub.level>1 order by u.uid,ub.itemid;";

//$sql = "select $num,case
//when p.allpay<=0 or p.allpay is null then 0
//when p.allpay>0 and p.allpay<=5 then 1
//when p.allpay>5 and p.allpay<=500 then 2
//when p.allpay>500 and p.allpay<=1000 then 3
//when p.allpay>1000 and p.allpay<=5000 then 4
//when p.allpay>5000 and  p.allpay<=10000 then 5
//when p.allpay>10000 and  p.allpay<=20000 then 6
//when p.allpay>20000 and  p.allpay<=30000 then 7
//when p.allpay>30000 then 8 end as payLevel,ub.itemid,ub.on,count(1) cnt
//from $db.userprofile u
//inner join $db.user_equip ub on u.uid=ub.uid
//left join (select uid,sum(spend) allpay from $db.paylog where pf!='iostest' group by uid) p on p.uid=u.uid
//where u.banTime<2422569600000 and u.gmFlag != 1 and u.lastOnlineTime >1479484800000
//group by payLevel,ub.itemId,ub.on
//order by payLevel,ub.itemId,ub.on";

//$sql = "select r.pf,gaid,from_unixtime(regtime/1000,'%Y%m%d') reg ,from_unixtime(lastOnlineTime/1000,'%Y%m%d') lastlogin,from_unixtime(AA.paytime/1000,'%Y%m%d'),AA.spend from $db.userprofile u
//inner join $db.stat_reg r on r.uid=u.uid
//left join (select uid ,min(time) as paytime,sum(spend) spend from $db.paylog group by uid) AA on AA.uid=u.uid
//where r.country='JP' ";




//近7天活跃用户，区分不同付费档位，麻烦统计一下信息；
//1.大本等级和各分城建筑的等级（1级的不用统计了）；
//2.三种龙的等级和属性对应的等级（0级的不要）；
//3.英雄的等级、星级、品质；
//4.装备等级、套装数据（还要区分是不是穿上了）；
//5.所有科技的状况，高级军事和荣誉科技的也要
//$req_date_end = date('Ymd',time());
//$span = 7;
//
//$req_date_end = str_replace('-', '', $req_date_end);
//$req_date_start = strtotime("-$span day",strtotime($req_date_end)) *1000;

//
//$sql = "SELECT $num,FROM_UNIXTIME(timeStamp/1000, '%Y-%m-%d') as date,COUNT(data1) battle_count,COUNT(DISTINCT data1) totle_user FROM $db.logrecord l WHERE l.category=6 and l.timeStamp >= 1467331200000 and l.timeStamp <= 1469836800000 and (l.type=1 or l.type=0) GROUP BY FROM_UNIXTIME(timeStamp/1000, '%Y-%m-%d') ";

//读取从库 充值金币变化明细  按月分 (这里不分迁服bantime)
//$sql = "select from_unixtime(g.time/1000,'%Y%m') as month,case when g.cost>=0 then 0  when g.cost<0 then 1 end as consumeType,sum(g.cost) sumc
//from $db.gold_cost_record  g
//inner join $db.userprofile u on g.userid=u.uid
//where g.goldType=1 and u.gmflag=0
//group by month,consumeType ";


//直接查看身上 付费金币总剩余量(应该与上边差不多)
$sql  = "select sum(payTotal) as allpaygold,sum(paidGold) as leftgold from $db.userprofile where banTime < 2422569600000 and gmflag=0 ";

executesql($sql,'userprofile_paygold_0208_');


function executesql ($sql ,$file)
{
	$dump_file = "/home/qinbinbin/$file".SERVER_ID;
	if(file_exists($dump_file)){
		unlink($dump_file);
	}
	touch($dump_file);
	$dump_file = realpath($dump_file);
	$dump_file = str_replace('\\', '/', $dump_file);
	global $dump_db;
	$cmd = build_mysql_cmd(
		$dump_db,
		$sql,
		$dump_file
	);
//	echo $cmd.PHP_EOL;
	$re = system($cmd, $retval);
}
