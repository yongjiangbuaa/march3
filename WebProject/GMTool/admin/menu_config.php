<?php

//定义菜单
$actions = array();
$permissionLink = array();

// GLOBAL管理
define('GLOBAL_MANAGE',9900);
define('GLOBAL_ACCOUT',9901);
define('GLOBAL_UIDEXPORT',9902);
define('GLOBAL_VERSION',9903);
define('GLOBAL_DAYREPORT',9904);
define('GLOBAL_REALTIME',9921);
define('GLOBAL_SUMMARY',9922);


define('GLOBAL_FIRSTPAYSTATISTICS',9905);
define('GLOBAL_ROI',9907);
define('GLOBAL_USERPAYANALYZE',9908);
define('GLOBAL_PAYRANK',9909);
define('GLOBAL_TOPPAY',9910);
define('GLOBAL_OPERATINGWEEKSTATISTICS',9911);
define('GLOBAL_GOODSCOST',9912);
define('GLOBAL_ITEM_UNUSUAL',9913);
define('GLOBAL_ITEMTOP',9914);
define('GLOBAL_ALLIANCE',9915);
define('GLOBAL_TRAVELBUSINESSMAN',9916);
define('GLOBAL_DAU',9917);
define('GLOBAL_REGREMAIN',9918);
define('GLOBAL_KPIWORKBENCH',9919);
define('GLOBAL_USERCITY',9920);
define('GLOBAL_DETAIL',9923);
define('GLOBAL_PAY_ANALYZE',9924);

// 系统管理
define('SYSTEM_MANAGE',100);
define('SYSTEM_APC',101);
define('SYSTEM_MEMCACHE',102);

//用户帐号管理
define('USER_MANAGE',200);
define('USER_USERINFO',201);
define('USER_GENERAL',202);
define('USER_BUILDING',203);
define('USER_ARMY',204);
define('USER_WALL',205);
define('USER_GOODS',206);
define('USER_CITY',207);
define('USER_SCIENCE',208);
define('USER_BIND',209);
define('USER_BADWORDS',210);
define('USER_BAN',211);
define('USER_SWITCHACCOUNT',212);
define('USER_FINDACCOUNT',213);
define('USER_GOLDCHANGE',214);
define('USER_USERDOACTION',215);
define('USER_USERARMACTION',216);
define('USER_MOD',217);
define('USER_BUILDINGQUEUE',218);
define('USER_VIPACTIVATIONTIME',219);
define('USER_FINDUIDANDNAME',220);
define('USER_BANWORD',221);
define('USER_GOODSCHANGE',222);
define('USER_SEARCHORDER',223);
define('USER_CHANGEALLIANCENAME',224);
define('USER_CHANGEINVITER',225);
define('USER_USEREQUIPMENT',226);
define('USER_PERSONALTRAVELRECORD',227);
define('USER_EQUIPMENTCHANGE',228);
define('USER_ROTARYTABLERECORD',229);
define('USER_ENVELOPE',230);
define('USER_RECEIVERECORD',231);
define('USER_SENDNOTICE',232);
define('USER_AUDITPICTURE',233);
define('USER_QUERYIP',234);
define('USER_UNDOAUDITPICTURE',235);
define('USER_ACTIVATIONUSEDTIMES',236);
define('USER_ADVERTISING',237);
define('USER_CHEATBAN',238);
define('USER_ORDERDEALWITH',239);
define('USER_BANRECORD',240);
define('USER_IPQUERY',241);
define('USER_REPORTPICTURE',242);
define('USER_MOVERECORD',243);
define('USER_AUDITUSERPICTURE',244);
define('USER_QUERYUID',245);
define('USER_TITAN',246);
define('USER_PRAY',247);
define('USER_ALLIANCEMSG',248);
define('USER_DEBRIS_CLEAN',249);
define('USER_MOD_BAN_RECORD',250);
define('USER_NAME_LOG',251);
define('USER_VIPCHANGE',252);
define('USER_ACTIVITYWEEK',253);
define('USER_ALLIANCE',254);
define('USER_TOUSERMAIL',255);
define('USER_KILLHUNTING',256);
define('USER_AUDITPICTURE_FRIENDMSG',257);
define('USER_AUDITUSERPICTURE_FRIENDMSG',258);
define('USER_GOODSCHANGE2',259);
define('USER_SPECIFICITEMSEARCH',260);
define('USER_SELLGIFT',261);
define('USER_SELLGIFT2',262);
define('USER_SCORE_ACTIVITY',263);
define('USER_BATTLE',264);
define('USER_ROSECROWN',265);
define('USER_BATTLE_PVP',266);
define('USER_ALLIANCE_SCORE_ACTIVITY',267);
define('USER_ALLIANCE_RANK_SCORE_ACTIVITY',268);
define('USER_BANBATCH',269);
define('USER_GLORY',270);
define('USER_GLORYSTAT',271);
define('USER_DISCUSS',272);
define('USER_DRAGON',273);
define('USER_DRAGONPRO',274);
define('USER_HERO_EMPLOY_RANK', 275);
define('USER_ARMY_SOUL', 276);
define('USER_GOD', 277);
define('USER_GOD_SKILL', 278);
define('USER_HERO', 279);
define('USER_NEWSHOP', 280);
define('USER_CITYSKIN', 281);
define('USER_BUSINESSMAN',282);
define('USER_POWER_CHANGE',283);
define('USER_STAR',284);
define('USER_GODACHIEVEMENT',285);
define('USER_GLAMOUR',286);
define('USER_PWD',287);
define('USER_PASSWD',288);
define('USER_LOGINCHECK',289);

// 运营支撑模块
define('OP_SUPPORT',300);
define('OP_PUSH',301);
define('OP_SUGGESTION',302);
define('OP_PUSH_EXECUTE',303);
define('OP_ANNOUNCE_MAIL',304);
define('OP_PUSH_ALL',305);
define('OP_USERMAIL',306);
define('OP_SERVER_INFO',307);
define('OP_OUTER_ACTIVITYLIST',308);
define('OP_OUTER_MAIL',309);
define('OP_OUTER_137LOGIN',310);
define('OP_OUTER_ACTIVATIONKEY',311);
define('OP_GROUPMAIL',312);
define('OP_REFUND',313);
define('OP_UPDATES',314);
define('OP_PAYPACKAGE',315);
define('OP_FILEMAIL',316);
define('OP_FUNCTIONCONFIG',317);
//define('OP_FBREFUND',318);
define('OP_ADJUST',319);
define('OP_UPLOADPACKAGEXML',320);
define('OP_MODIFYACTIVITY', 321);
define('OP_OPTIMIZECONFIG', 322);
define('OP_ADJUST_LOG',323);
//define('OP_XINGYUNTRANSLATION',324);
define('OP_BANIP',325);
define('OP_BANIP2',324);
//define('OP_REFUNDAMOUNT',326);
define('OP_SETRISK',327);
define('OP_GMPAY',328);
define('OP_UPDATEDEVICEMAPPING',329);
define('OP_BINDMI',330);
define('OP_ADDAPPVERSION',331);
define('OP_FRIENDMSGPUSH',332);
define('OP_PACKAGEID',333);
define('OP_GROUPMAIL2',334);
define('OP_GROUPMAILdev',335);
define('OP_BANIP3',336);
define('OP_PAYGOLD',337);
define('OP_UPDATEURL', 338);




// 统计数据管理
define('STAT_MANAGE',400);
define('STAT_REGISTER_DATA',401);
define('STAT_DISTRIBUTE',402);
define('STAT_REGREMAIN',403);
define('STAT_TUTORIAL',404);
define('STAT_REGLOST',405);
define('STAT_PVE',406);
define('STAT_USERACTION',407);
define('STAT_FIVEONLINEDATA',408);
define('STAT_ALLUSER',409);
// define('STAT_MIX',410);
define('STAT_ONLINE_DAILY_GRAPH', 411);
define('STAT_QUEST', 412);
define('STAT_DAU', 413);
define('STAT_STOREGOODS', 414);
define('STAT_VERSIONS', 415);
define('STAT_ALLIANCE',416);
//define('STAT_MONSTER',417);
//define('STAT_WORLD_RESOURCE',418);
define('STAT_COLLECTRESOURCE',419);
define('STAT_FACEBOOK',420);
define('STAT_VIP',421);
//define('STAT_PAY_PANEL',422);
define('STAT_USER_RESOURCE',423);
define('STAT_EXCHANGE',425);
//define('STAT_SAMPLE_USER',426);
//define('STAT_LOGINLOST',428);
define('STAT_TOP10',429);
define('STAT_BINDACCOUNT',430);
//define('STAT_TREASURE',431);
define('STAT_TRANSLATION',432);
define('STAT_FIGHTOFKING',433);
//define('STAT_HOTGOODS',434);
define('STAT_EQUIPMENTSTATISTICS2',434);
define('STAT_EQUIPMENTSTATISTICS',435);
//define('STAT_IOSPAY',436);
//define('STAT_IOSRETENTION',437);
define('STAT_HERO',437);
define('STAT_TRAVELBUSINESSMAN',438);
//define('STAT_SIGNSTATISTICS',439);
define('STAT_COINFEEDBACK',440);//  441
define('STAT_EGG',441);//  441
//define('STAT_ROTARYTABLESTATISTICS',440);
//define('STAT_FBADROI',441);
define('STAT_ALLPHONERETENTION',442);
define('STAT_ALLISCIENCE',443);
define('STAT_ACHIEVEMENTSTATISTICS',444);
define('STAT_NOTICESTATISTICS',445);
define('STAT_MONSTERSIEGESTATISTICS',446);
define('STAT_MONSTERSIEGEINFO',447);
define('STAT_PUSHSTATISTICS',448);
define('STAT_MOPUP_MONSTER',449);
define('STAT_OPERATINGDATASTATISTICS',450);
define('STAT_TUTORIALSTATISTICS',451);
define('STAT_USERSKILLSTATISTICS',452);
define('STAT_EXPLORESTATISTICS',453);
define('STAT_CROSSFIGHTSTATISTICS',454);
define('STAT_CASTLEDRESSUP',455);
define('STAT_FIGHTPK',456);
define('STAT_DRIFTBOTTLE',457);
define('STAT_KILLTITAN',458);
define('STAT_USERNOBILITY',459);
define('STAT_FRIENDGARRISON',461);
define('STAT_INVITES',462);
define('STAT_PRAYSERVICE',464);
define('STAT_DEBRIS',465);
define('STAT_NPCBUILD',466);
define('STAT_TERRITORYWIND',467);
define('STAT_MOVESERVERRECORD',468);
define('STAT_FRIENDMESSAGE',469);
define('STAT_DAILYACTIVE',470);
define('STAT_HUNTING',471);
define('STAT_LOSTPAYUSERS',472);
define('STAT_OPERATINGDATASTATISTICS2',473);

//define('STAT_GPINSTALLS',473);
define('STAT_LOTTERYPAY',474);
define('STAT_REGREMAINCURVE',474);
define('STAT_OPERATINGWEEKSTATISTICS',475);
define('STAT_REGCURVE',476);
define('STAT_RATEOFPAY',477);
define('STAT_MONTHDATA',478);
//define('STAT_ITEM_UNUSUAL',479);
define('STAT_ARMYENHANCE', 479);
define('STAT_QUICKDNF',480);
define('STAT_DAUCURVE', 481);
define('STAT_GOLDCURVE', 482);
define('STAT_CASINO', 483);
define('STAT_NEWDAYACTIVITIES', 484);
define('STAT_BLESS', 486);
define('STAT_DISTRIBUTE2',487);
define('STAT_DRAGON',488);
define('STAT_DISTRIBUTEDAYS',489);
define('STAT_DISTRIBUTEREFERRER',490);
define('STAT_MASTER',491);
define('STAT_DUNGEONS',492);
define('STAT_SMALLWAR',493);
define('STAT_ALCHEMY',494);
define('STAT_THRONE',495);
define('STAT_ARENA', 496);
define('STAT_PROMOTION', 497);
define('STAT_TERRITORY',498);

//数据修改
define('MODIFY_MANAGE',500);
define('MODIFY_PROFILE',501);
define('MODIFY_GENERAL',502);
define('MODIFY_BUILDING',503);
define('MODIFY_GOODS',504);
define('MODIFY_STORY',529);
define('MODIFY_CITY',505);
define('MODIFY_QUEUE',506);
define('MODIFY_LORD',507);
define('MODIFY_ARENA',508);
define('MODIFY_PVE',509);
define('MODIFY_ARMY',510);
define('MODIFY_SCIENCE',511);
define('MODIFY_WALL',512);
define('MODIFY_EQUIP',513);
define('MODIFY_TASK',514);
define('MODIFY_DRAGON',515);
define('MODIFY_GLOMOUR',516);
define('MODIFY_DRAGONPRO',517);
define('MODIFY_HERO',518);
define('MODIFY_EGG',519);
define('MODIFY_GOD',520);
define('MODIFY_GOD_SKILL',521);
define('MODIFY_EXCHANGE_GOODS',522);
define('MODIFY_ARMY_ENHANCE',523);
define('MODIFY_ARMY_ENHANCE_EFFECT',524);
define('MODIFY_STAR',525);
define('MODIFY_ENDLESS_EQUIP',526);
define('MODIFY_PWD',527);
define('MODIFY_PASSWD',528);

//MYSQL相关
define('MENU_MYSQL',600);
define('MENU_MYSQL_INDEX',601);
define('MENU_MYSQL_SEARCH',602);
define('MENU_MYSQL_STRUCT',603);
define('MENU_MYSQL_EXECUTE',604);
define('MENU_MYSQL_AEXECUTE',605);
define('MENU_MYSQL_COMPARE',606);
define('MENU_MYSQL_REDIS',607);
define('MENU_MYSQL_GLOBALREDIS',608);
define('MENU_MYSQL_TASK_FILE',609);

// 服务器相关管理
define('SERVER_MANAGE',700);
//define('SERVER_LANG',701);
define('SERVER_CONFIG',702);
define('SERVER_SHUTDOWN',703);
define('SERVER_DETECT',704);
define('SERVER_LOG',705);
define('SERVER_ONEKEY',706);
define('SERVER_DEPLOYRULE', 707);
//define('SERVER_DEPLOYXML',708);
define('SERVER_SERVERINFO',709);
define('SERVER_STRATEGY',710);
define('SERVER_COUNTRYAMOUNT',711);
define('SERVER_MONITOR',712);
define('SERVER_ONLINE_USERS',713);
define('SERVER_LUAVERSION',714);
define('SERVER_APPVERSION',715);
define('SERVER_VERSION',716);
define('SERVER_XML',717);
define('SERVER_MULTICITY',718);
define('SERVER_FRONTXML',719);
define('SERVER_PF_CONFIG', 720);


// 自定义管理模块
define('DIY_MANAGE',800);

// 管理员用户管理
define('ADMIN_MANAGE',900);
define('ADMIN_USER_LIST',901);
define('ADMIN_USER_EDITPASSWORD',902);
define('ADMIN_USER_LOG',903);

// 管理员用户管理
define('OUTPUT_MANAGE',1000);

//单服数据
define('PAY_MANAGE',1100);
define('PAY_RECENTPAY',1101);
define('PAY_PAY',1102);
define('PAY_GOLDLOG_ANALYZE',1104);
define('PAY_PAY_ANALYZE',1105);
define('PAY_DAILY_GRAPH',1106);
define('PAY_PAYRANK',1107);
//define('PAY_NEW_USER_DATA',1108);
define('PAY_RECENTPAY2',1108);

define('PAY_ROI',1109);
define('PAY_NEWROI',1110);
define('PAY_COUNTRYROI',1111);
define('PAY_GOLDSTATISTICS',1112);
define('PAY_USERPAYANALYZE',1113);
define('PAY_PAYUSERSSTATISTICS',1114);
define('PAY_FIRSTPAYSTATISTICS',1115);
define('PAY_TOPPAY',1116);
define('PAY_BASICDATAS',1117);
define('PAY_CNREGPAY',1118);
define('PAY_USERGOLDCOST',1119);
define('PAY_SYSTEM',1120);
define('PAY_SYSTEM_RELATE',1121);
define('PAY_TOTALPAY',1122);
define('PAY_PAYNEWROICURVE',1123);
define('PAY_MONEYORDERBYBLV',1124);
//define('PAY_SPECIFICITEMSEARCH',1125);
define('PAY_FIRSTPAYANALYSE',1127);
define('PAY_PAYREMAIN',1128);
define('PAY_RATE',1129);
define('PAY_LTV',1130);
define('PAY_GOLDLOG_ANALYZE2',1131);
define('PAY_EXCHANGE',1132);
define('PAY_CUMULATIVERECHARGE',1133);
define('PAY_PAYLEVELDATA',1134);
define('PAY_GOOGLE',1135);


//工作规范
define('STANDARD_MANAGE',1200);
define('STANDARD_ONLINEMAIL',1200);
define('STANDARD_CONFIRMVERSION',1201);
define('STANDARD_UPDATEPLAN',1202);
define('STANDARD_PLAYERDEMAND',1203);
define('STANDARD_CHANNELSPECIFICATION',1204);
define('STANDARD_CDNRESOURCE',1205);
//广告数据
define('AD_MANAGE',1400);//标题
define('AD_CPA',1401);
define('AD_CHANNELTRACKING',1402);
define('AD_EXPORTINSTALL',1403);
define('AD_FRAUD',1404);
define('AD_HISTORY',1405);
define('AD_DASHBOARD',1406);
define('AD_ROI',1407);


//主机名
$host = gethostbyname(gethostname());

$trunkMenu = array(
				'standard'=>array('permission'=>STANDARD_MANAGE,'lang'=>'menu_standard_manage'),
//				'global'=>array('permission'=>GLOBAL_MANAGE,'lang'=>'menu_global_manage'),
				'user'=>array('permission'=>USER_MANAGE,'lang'=>'menu_user_manage'),
				'modify'=>array('permission'=>MODIFY_MANAGE,'lang'=>'menu_modify_manage'),
// 				'op'=>array('permission'=>OP_SUPPORT,'lang'=>'menu_op_support'),
// 				'pay'=>array('permission'=>PAY_MANAGE,'lang'=>'menu_pay_manage'),
				'stat'=>array('permission'=>STAT_MANAGE,'lang'=>'menu_stat_manage'),
//				'ad'=>array('permission'=>AD_MANAGE,'lang'=>'menu_ad_manage'),
				'server'=>array('permission'=>SERVER_MANAGE,'lang'=>'menu_server_manage'),
				'mysql'=>array('permission'=>MENU_MYSQL,'lang'=>'menu_mysql'),
				'admin'=>array('permission'=>ADMIN_MANAGE,'lang'=>'menu_admin_manage'),

		);

$branchMenu = array(
		'global' => array(
				'account'=>array('permission'=>GLOBAL_ACCOUT,'lang'=>'menu_global_account'),
				'uidexport'=>array('permission'=>GLOBAL_UIDEXPORT,'lang'=>'menu_global_uidexport'),
				'version'=>array('permission'=>GLOBAL_VERSION,'lang'=>'menu_global_version'),

				'dayreport'=>array('permission'=>GLOBAL_DAYREPORT,'lang'=>'menu_global_dayreport'),
//				'realtime'=>array('permission'=>GLOBAL_REALTIME,'lang'=>'menu_global_realtime'),
				'summary'=>array('permission'=>GLOBAL_SUMMARY,'lang'=>'menu_global_summary'),

//				'userPayAnalyze'=>array('permission'=>GLOBAL_USERPAYANALYZE,'lang'=>'menu_global_userpayanalyze'),
//				'payrank'=>array('permission'=>GLOBAL_PAYRANK,'lang'=>'menu_global_payrank'),
//				'topPay'=>array('permission'=>GLOBAL_TOPPAY,'lang'=>'menu_global_toppay'),
//				'operatingWeekStatistics'=>array('permission'=>GLOBAL_OPERATINGWEEKSTATISTICS,'lang'=>'menu_global_operatingweekstatistics'),
//				'goodscost'=>array('permission'=>GLOBAL_GOODSCOST,'lang'=>'menu_global_goodscost'),
//				'item_unusual'=>array('permission'=>GLOBAL_ITEM_UNUSUAL,'lang'=>'menu_global_itemunusual'),
//				'itemTop'=>array('permission'=>GLOBAL_ITEMTOP,'lang'=>'menu_global_itemtop'),
//				'alliance'=>array('permission'=>GLOBAL_ALLIANCE,'lang'=>'menu_global_alliance'),
//				'travelBusinessman'=>array('permission'=>GLOBAL_TRAVELBUSINESSMAN,'lang'=>'menu_global_travelbusinessman'),
//				'dau'=>array('permission'=>GLOBAL_DAU,'lang'=>'menu_global_dau'),
//				'regremain'=>array('permission'=>GLOBAL_REGREMAIN,'lang'=>'menu_global_regremain'),
//				'kpiworkbench'=>array('permission'=>GLOBAL_KPIWORKBENCH,'lang'=>'menu_global_kpiworkbench'),
//				'userCity'=>array('permission'=>GLOBAL_USERCITY,'lang'=>'menu_global_usercity'),
		),
		'standard'=>array(
				'updatePlan'=>array('permission'=>STANDARD_UPDATEPLAN,'lang'=>'menu_standard_updatePlan'),
				'onlineMail'=>array('permission'=>STANDARD_ONLINEMAIL,'lang'=>'menu_standard_onlineMail'),
				'confirmVersion'=>array('permission'=>STANDARD_CONFIRMVERSION,'lang'=>'menu_standard_confirmVersion'),
				'playerDemand'=>array('permission'=>STANDARD_PLAYERDEMAND,'lang'=>'menu_standard_playerDemand'),
				'channelSpecification'=>array('permission'=>STANDARD_CHANNELSPECIFICATION,'lang'=>'menu_standard_channelSpecification'),
				'cdnResource'=>array('permission'=>STANDARD_CDNRESOURCE,'lang'=>'menu_standard_cdnResource'),
		),
		'user'=>array(
			'userinfo'=>array('permission'=>USER_USERINFO,'lang'=>'menu_user_userinfo'),
//			'queryUid'=>array('permission'=>USER_QUERYUID,'lang'=>'menu_user_queryUid'),
//            'building'=>array('permission'=>USER_BUILDING,'lang'=>'menu_user_building'),
//			'army'=>array('permission'=>USER_ARMY,'lang'=>'menu_user_army'),
			'goods'=>array('permission'=>USER_GOODS,'lang'=>'menu_user_goods'),
//			'science'=>array('permission'=>USER_SCIENCE,'lang'=>'menu_user_science'),
//			'userEquipment'=>array('permission'=>USER_USEREQUIPMENT,'lang'=>'menu_user_userEquipment'),
//			'equipmentChange'=>array('permission'=>USER_EQUIPMENTCHANGE,'lang'=>'menu_user_equipmentChange'),
//			'bind'=>array('permission'=>USER_BIND,'lang'=>'menu_user_bind'),
//			'banbatch'=>array('permission'=>USER_BANBATCH,'lang'=>'menu_user_banbatch'),
//			'businessman'=>array('permission'=>USER_BUSINESSMAN,'lang'=>'menu_user_businessman'),
//			'banRecord'=>array('permission'=>USER_BANRECORD,'lang'=>'menu_user_banRecord'),
//			'toUserMail'=>array('permission'=>USER_TOUSERMAIL,'lang'=>'menu_user_toUserMail'),
//			'findUidAndName'=>array('permission'=>USER_FINDUIDANDNAME,'lang'=>'menu_user_findUidAndName'),
//			'name_log'=>array('permission'=>USER_NAME_LOG,'lang'=>'menu_user_name_log'),
//			'goodsChange'=>array('permission'=>USER_GOODSCHANGE,'lang'=>'menu_user_goodsChange'),
//			'goldchange'=>array('permission'=>USER_GOLDCHANGE,'lang'=>'menu_user_goldchange'),
//			'searchOrder'=>array('permission'=>USER_SEARCHORDER,'lang'=>'menu_user_searchOrder'),
//			'orderDealWith'=>array('permission'=>USER_ORDERDEALWITH,'lang'=>'menu_user_orderDealWith'),
//			'battle'=>array('permission'=>USER_BATTLE,'lang'=>'menu_user_battle'),
//			'auditUserPicture'=>array('permission'=>USER_AUDITUSERPICTURE,'lang'=>'menu_user_auditUserPicture'),
//			'moveRecord'=>array('permission'=>USER_MOVERECORD,'lang'=>'menu_user_moveRecord'),
//			'queryIp'=>array('permission'=>USER_QUERYIP,'lang'=>'menu_user_queryIp'),
//			'hero'=>array('permission'=>USER_HERO,'lang'=>'menu_user_hero'),
//			'dragon'=>array('permission'=>USER_DRAGON,'lang'=>'menu_user_dragon'),
//			'dragonpro'=>array('permission'=>USER_DRAGONPRO,'lang'=>'menu_user_dragonpro'),
//			'glory'=>array('permission'=>USER_GLORY,'lang'=>'menu_user_glory'),
//			'glorystat'=>array('permission'=>USER_GLORYSTAT,'lang'=>'menu_user_glorystat'),
//			'ban'=>array('permission'=>USER_BAN,'lang'=>'menu_user_ban'),
//			'banWord'=>array('permission'=>USER_BANWORD,'lang'=>'menu_user_banWord'),
//			'mod'=>array('permission'=>USER_MOD,'lang'=>'menu_user_mod'),
//			'modBanRecord'=>array('permission'=>USER_MOD_BAN_RECORD,'lang'=>'menu_user_modBanRecord'),
//			'god'=>array('permission'=>USER_GOD,'lang'=>'menu_user_god'),
//			'god_skill'=>array('permission'=>USER_GOD_SKILL,'lang'=>'menu_user_god_skill'),
//			'armySoul'=>array('permission'=>USER_ARMY_SOUL,'lang'=>'menu_user_armySoul'),
//			'alliance'=>array('permission'=>USER_ALLIANCE,'lang'=>'menu_user_alliance'),
//			'changeAllianceName'=>array('permission'=>USER_CHANGEALLIANCENAME,'lang'=>'menu_user_changeAllianceName'),
//			'score_activity'=>array('permission'=>USER_SCORE_ACTIVITY,'lang'=>'menu_user_score_activity'),
//			'alliance_score_activity'=>array('permission'=>USER_ALLIANCE_SCORE_ACTIVITY,'lang'=>'menu_user_alliance_score_activity'),
//			'alliance_rank_score_activity'=>array('permission'=>USER_ALLIANCE_RANK_SCORE_ACTIVITY,'lang'=>'menu_user_alliance_rank_score_activity'),
//			'city'=>array('permission'=>USER_CITY,'lang'=>'menu_user_city'),
//			'wall'=>array('permission'=>USER_WALL,'lang'=>'menu_user_wall'),
//			'heroEmployRank'=>array('permission'=>USER_HERO_EMPLOY_RANK,'lang'=>'menu_user_heroEmployRank'),
//			'personalTravelRecord'=>array('permission'=>USER_PERSONALTRAVELRECORD,'lang'=>'menu_user_personalTravelRecord'),
//			'sendNotice'=>array('permission'=>USER_SENDNOTICE,'lang'=>'menu_user_sendNotice'),
//			'cheatBan'=>array('permission'=>USER_CHEATBAN,'lang'=>'menu_user_cheatBan'),
//			'specificItemSearch'=>array('permission'=>USER_SPECIFICITEMSEARCH,'lang'=>'menu_user_specificItemSearch'),
//			'goodsChange2'=>array('permission'=>USER_GOODSCHANGE2,'lang'=>'menu_user_goodsChange2'),
//			'activationUsedTimes'=>array('permission'=>USER_ACTIVATIONUSEDTIMES,'lang'=>'menu_user_activationUsedTimes'),
//			'auditPicture'=>array('permission'=>USER_AUDITPICTURE,'lang'=>'menu_user_auditPicture'),
//			'reportPicture'=>array('permission'=>USER_REPORTPICTURE,'lang'=>'menu_user_reportPicture'),
//			'advertising'=>array('permission'=>USER_ADVERTISING,'lang'=>'menu_user_advertising'),
//			'titan'=>array('permission'=>USER_TITAN,'lang'=>'menu_user_titan'),
//			'pray'=>array('permission'=>USER_PRAY,'lang'=>'menu_user_pray'),
//			'allianceMsg'=>array('permission'=>USER_ALLIANCEMSG,'lang'=>'menu_user_allianceMsg'),
//			'debris_clean'=>array('permission'=>USER_DEBRIS_CLEAN,'lang'=>'menu_user_debris_clean'),
//			'vipChange'=>array('permission'=>USER_VIPCHANGE,'lang'=>'menu_user_vipChange'),
//			'activityWeek'=>array('permission'=>USER_ACTIVITYWEEK,'lang'=>'menu_user_activityWeek'),
//			'killHunting'=>array('permission'=>USER_KILLHUNTING,'lang'=>'menu_user_killHunting'),
//			'auditPicture_friendMsg'=>array('permission'=>USER_AUDITPICTURE_FRIENDMSG,'lang'=>'menu_user_auditPicture_friendMsg'),
//			'auditUserPicture_friendMsg'=>array('permission'=>USER_AUDITUSERPICTURE_FRIENDMSG,'lang'=>'menu_user_auditUserPicture_friendMsg'),
//			'sellgift'=>array('permission'=>USER_SELLGIFT,'lang'=>'menu_user_sellgift'),
//			'sellgift2'=>array('permission'=>USER_SELLGIFT2,'lang'=>'menu_user_sellgift2'),
//			'roseCrown'=>array('permission'=>USER_ROSECROWN,'lang'=>'menu_user_roseCrown'),
//			'battle_pvp'=>array('permission'=>USER_BATTLE_PVP,'lang'=>'menu_user_battle_pvp'),
//			'discuss'=>array('permission'=>USER_DISCUSS,'lang'=>'menu_user_discuss'),
//			'newshop'=>array('permission'=>USER_NEWSHOP,'lang'=>'menu_user_newshop'),
//			'citySkin'=>array('permission'=>USER_CITYSKIN,'lang'=>'menu_user_cityskin'),
//			'undoAuditPicture'=>array('permission'=>USER_UNDOAUDITPICTURE,'lang'=>'menu_user_undoAuditPicture'),
//			'buildingQueue'=>array('permission'=>USER_BUILDINGQUEUE,'lang'=>'menu_user_buildingQueue'),
//			'rotaryTableRecord'=>array('permission'=>USER_ROTARYTABLERECORD,'lang'=>'menu_user_rotaryTableRecord'),
//			'envelope'=>array('permission'=>USER_ENVELOPE,'lang'=>'menu_user_envelope'),
//			'receiveRecord'=>array('permission'=>USER_RECEIVERECORD,'lang'=>'menu_user_receiveRecord'),
//			'powerChange'=>array('permission'=>USER_POWER_CHANGE,'lang'=>'menu_user_powerChange'),
//			'star'=>array('permission'=>USER_STAR,'lang'=>'menu_user_star'),
//			'godAchievement'=>array('permission'=>USER_GODACHIEVEMENT,'lang'=>'menu_user_godAchievement'),
//			'glamour'=>array('permission'=>USER_GLAMOUR,'lang'=>'menu_user_glamour'),
//			'pwd'=>array('permission'=>USER_PWD,'lang'=>'menu_user_pwd'),
//			'passwd'=>array('permission'=>USER_PASSWD,'lang'=>'menu_user_passwd'),
//			'loginCheck'=>array('permission'=>USER_LOGINCHECK,'lang'=>'menu_user_loginCheck'),
		),
		'op'=>array(
				'push'=>array('permission'=>OP_PUSH,'lang'=>'menu_op_push'),
				'announce_mail'=>array('permission'=>OP_ANNOUNCE_MAIL,'lang'=>'menu_op_announce_mail'),
 				'push_execute'=>array('permission'=>OP_PUSH_EXECUTE,'lang'=>'menu_op_push_execute'),
 				'push_all'=>array('permission'=>OP_PUSH_ALL,'lang'=>'menu_op_push_all'),
				// 'general'=>array('permission'=>OP_GENERAL,'lang'=>'menu_op_general'),
				'usermail'=>array('permission'=>OP_USERMAIL,'lang'=>'menu_op_usermail'),
				'fileMail'=>array('permission'=>OP_FILEMAIL,'lang'=>'menu_op_fileMail'),
				'groupMail'=>array('permission'=>OP_GROUPMAIL,'lang'=>'menu_op_groupMail'),
				'groupMail2'=>array('permission'=>OP_GROUPMAIL2,'lang'=>'menu_op_groupMail2'),
				'groupMaildev'=>array('permission'=>OP_GROUPMAILdev,'lang'=>'menu_op_groupMaildev'),
				'suggestion'=>array('permission'=>OP_SUGGESTION,'lang'=>'menu_stat_suggestion'),
				'banIP'=>array('permission'=>OP_BANIP,'lang'=>'menu_op_banIP'),
				'banIP2'=>array('permission'=>OP_BANIP2,'lang'=>'menu_op_banIP2'),
				'banIP3'=>array('permission'=>OP_BANIP3,'lang'=>'menu_op_banIP3'),
				'server_info'=>array('permission'=>OP_SERVER_INFO,'lang'=>'menu_op_server_info'),
				'functionConfig'=>array('permission'=>OP_FUNCTIONCONFIG,'lang'=>'menu_op_functionConfig'),
				'optimizeConfig'=>array('permission'=>OP_OPTIMIZECONFIG,'lang'=>'menu_op_optimizeConfig'),
//				'xingyunTranslation'=>array('permission'=>OP_XINGYUNTRANSLATION,'lang'=>'menu_op_xingyunTranslation'),
				'activityList'=>array('permission'=>OP_OUTER_ACTIVITYLIST,'lang'=>'menu_op_activityList'),
// 				'outer_mail'=>array('permission'=>OP_OUTER_MAIL,'lang'=>'menu_op_outer_mail'),
				'137login'=>array('permission'=>OP_OUTER_137LOGIN,'lang'=>'menu_op_137login'),
				'activationKey'=>array('permission'=>OP_OUTER_ACTIVATIONKEY,'lang'=>'menu_op_activationKey'),
				
//				'refundAmount'=>array('permission'=>OP_REFUNDAMOUNT,'lang'=>'menu_op_refundAmount'),
				'refund'=>array('permission'=>OP_REFUND,'lang'=>'menu_op_refund'),
//				'fbRefund'=>array('permission'=>OP_FBREFUND,'lang'=>'menu_op_fbRefund'),
				'payPackage'=>array('permission'=>OP_PAYPACKAGE,'lang'=>'menu_op_payPackage'),
				'gmPay'=>array('permission'=>OP_GMPAY,'lang'=>'menu_op_gmPay'),
				'setRisk'=>array('permission'=>OP_SETRISK,'lang'=>'menu_op_setRisk'),
				'updates'=>array('permission'=>OP_UPDATES,'lang'=>'menu_op_updates'),
				'uploadPackageXML'=>array('permission'=>OP_UPLOADPACKAGEXML,'lang'=>'menu_op_uploadPackageXML'),
		        'modifyActivity'=>array('permission'=>OP_MODIFYACTIVITY,'lang'=>'menu_op_modifyActivity'),
				'updateDeviceMapping'=>array('permission'=>OP_UPDATEDEVICEMAPPING,'lang'=>'menu_op_updateDeviceMapping'),
				'bindMi'=>array('permission'=>OP_BINDMI,'lang'=>'menu_op_bindMi'),
				'addAppVersion'=>array('permission'=>OP_ADDAPPVERSION,'lang'=>'menu_op_addAppVersion'),
				'friendMsgPush'=>array('permission'=>OP_FRIENDMSGPUSH,'lang'=>'menu_op_friendMsgPush'),
				'packageId'=>array('permission'=>OP_PACKAGEID,'lang'=>'menu_op_packageId'),
				'paygold'=>array('permission'=>OP_PAYGOLD,'lang'=>'menu_op_paygold'),
				'updateDownloadUrl'=>array('permission'=>OP_UPDATEURL, 'lang'=>'menu_op_updateUrl')
		),
		'pay'=>array(
				'cnRegPay'=>array('permission'=>PAY_CNREGPAY,'lang'=>'menu_pay_cnRegPay'),
				'payremain'=>array('permission'=>PAY_PAYREMAIN,'lang'=>'menu_pay_payremain'),
				'basicdatas'=>array('permission'=>PAY_BASICDATAS,'lang'=>'menu_pay_basicdatas'),
				'recentpay'=>array('permission'=>PAY_RECENTPAY,'lang'=>'menu_pay_recentpay'),

				'totalPay'=>array('permission'=>PAY_TOTALPAY,'lang'=>'menu_pay_totalPay'),
				'detail'=>array('permission'=>PAY_PAY,'lang'=>'menu_pay_detail'),
				'pay_analyze'=>array('permission'=>PAY_PAY_ANALYZE,'lang'=>'menu_pay_pay_analyze'),
				'payUsersStatistics'=>array('permission'=>PAY_PAYUSERSSTATISTICS,'lang'=>'menu_pay_payUsersStatistics'),
				'firstPayStatistics'=>array('permission'=>PAY_FIRSTPAYSTATISTICS,'lang'=>'menu_pay_firstPayStatistics'),
				'roi'=>array('permission'=>PAY_ROI,'lang'=>'menu_pay_roi'),
				'userPayAnalyze'=>array('permission'=>PAY_USERPAYANALYZE,'lang'=>'menu_pay_userPayAnalyze'),
				'payNewRoiCurve'=>array('permission'=>PAY_PAYNEWROICURVE,'lang'=>'menu_pay_payNewRoiCurve'),
				'newroi'=>array('permission'=>PAY_NEWROI,'lang'=>'menu_pay_newroi'),
				'ltv'=>array('permission'=>PAY_LTV,'lang'=>'menu_pay_ltv'),
                //'countryroi'=>array('permission'=>PAY_COUNTRYROI,'lang'=>'menu_pay_countryroi'),
				//'regpay'=>array('permission'=>PAY_REGPAY,'lang'=>'menu_pay_regpay'),
				//'new_user_data'=>array('permission'=>PAY_NEW_USER_DATA,'lang'=>'menu_pay_new_user_data'),
				'payrank'=>array('permission'=>PAY_PAYRANK,'lang'=>'menu_pay_payrank'),
				'topPay'=>array('permission'=>PAY_TOPPAY,'lang'=>'menu_pay_topPay'),
				//'goldlog_analyze'=>array('permission'=>PAY_GOLDLOG_ANALYZE,'lang'=>'menu_pay_goldlog_analyze'),
				'goldlog_analyze2'=>array('permission'=>PAY_GOLDLOG_ANALYZE2,'lang'=>'menu_pay_goldlog_analyze2'),
				'daily_graph'=>array('permission'=>PAY_DAILY_GRAPH,'lang'=>'menu_pay_daily_graph'),
				//'goldStatistics'=>array('permission'=>PAY_GOLDSTATISTICS,'lang'=>'menu_pay_goldStatistics'),
				'userGoldCost'=>array('permission'=>PAY_USERGOLDCOST,'lang'=>'menu_pay_userGoldCost'),
				'system'=>array('permission'=>PAY_SYSTEM,'lang'=>'menu_pay_system'),
		        'moneyOrderByBlv'=>array('permission'=>PAY_MONEYORDERBYBLV,'lang'=>'menu_pay_moneyOrderByBlv'),
				'firstPayAnalyse'=>array('permission'=>PAY_FIRSTPAYANALYSE,'lang'=>'menu_pay_firstPayAnalyse'),
				'rate'=>array('permission'=>PAY_RATE,'lang'=>'menu_pay_rate'),
				'exchange'=>array('permission'=>PAY_EXCHANGE,'lang'=>'menu_pay_exchange'),
				'cumulativeRecharge'=>array('permission'=>PAY_CUMULATIVERECHARGE,'lang'=>'menu_pay_cumulativeRecharge'),
				'payleveldata'=>array('permission'=>PAY_PAYLEVELDATA,'lang'=>'menu_pay_payleveldata'),
				'recentpay2'=>array('permission'=>PAY_RECENTPAY2,'lang'=>'menu_pay_recentpay2'),
				'google'=>array('permission'=>PAY_GOOGLE,'lang'=>'menu_pay_google'),
		),
		'stat'=>array(
//				'fiveonlinedata'=>array('permission'=>STAT_FIVEONLINEDATA,'lang'=>'menu_stat_fiveonlinedata'),
				'dau'=>array('permission'=>STAT_DAU,'lang'=>'menu_stat_dau'),
				'dauCurve'=>array('permission'=>STAT_DAUCURVE, 'lang'=>'menu_stat_dauCurve'),
				'registerdata'=>array('permission'=>STAT_REGISTER_DATA,'lang'=>'menu_stat_register'),
				'regremain'=>array('permission'=>STAT_REGREMAIN,'lang'=>'menu_stat_regremain'),
				'regCurve'=>array('permission'=>STAT_REGCURVE,'lang'=>'menu_stat_regCurve'),
				'regremainCurve'=>array('permission'=>STAT_REGREMAINCURVE,'lang'=>'menu_stat_regremainCurve'),
//				'gpInstalls'=>array('permission'=>STAT_GPINSTALLS,'lang'=>'menu_stat_gpInstalls'),
				
//				'operatingDataStatistics'=>array('permission'=>STAT_OPERATINGDATASTATISTICS,'lang'=>'menu_stat_operatingDataStatistics'),
//				'operatingDataStatistics2'=>array('permission'=>STAT_OPERATINGDATASTATISTICS2,'lang'=>'menu_stat_operatingDataStatistics2'),
//				'operatingWeekStatistics'=>array('permission'=>STAT_OPERATINGWEEKSTATISTICS,'lang'=>'menu_stat_operatingWeekStatistics'),
//				'alluser'=>array('permission'=>STAT_ALLUSER,'lang'=>'menu_stat_alluser'),
//				'fbAdRoi'=>array('permission'=>STAT_FBADROI,'lang'=>'menu_stat_fbAdRoi'),
// 				'reglost'=>array('permission'=>STAT_REGLOST,'lang'=>'menu_stat_reglost'),
//				'distribute'=>array('permission'=>STAT_DISTRIBUTE,'lang'=>'menu_stat_distribute'),
//				'distribute2'=>array('permission'=>STAT_DISTRIBUTE2,'lang'=>'menu_stat_distribute2'),
//				'distributeDays'=>array('permission'=>STAT_DISTRIBUTEDAYS,'lang'=>'menu_stat_distributeDays'),
//				'distributeReferrer'=>array('permission'=>STAT_DISTRIBUTEREFERRER,'lang'=>'menu_stat_distributeReferrer'),
				'tutorial'=>array('permission'=>STAT_TUTORIAL,'lang'=>'menu_stat_tutorial'),
				'tutorialStatistics'=>array('permission'=>STAT_TUTORIALSTATISTICS,'lang'=>'menu_stat_tutorialStatistics'),
//		        'loginlost'=>array('permission'=>STAT_LOGINLOST,'lang'=>'menu_stat_loginlost'),
//				'quest'=>array('permission'=>STAT_QUEST,'lang'=>'menu_stat_quest'),
// 				'mix'=>array('permission'=>STAT_MIX,'lang'=>'menu_stat_mix'),
// 				'useraction'=>array('permission'=>STAT_USERACTION,'lang'=>'menu_stat_useraction'),
//				'world_resource'=>array('permission'=>STAT_WORLD_RESOURCE,'lang'=>'menu_stat_world_resource'),
				'online_daily_graph'=>array('permission'=>STAT_ONLINE_DAILY_GRAPH,'lang'=>'menu_stat_online_daily_graph'),
//				'storegoods'=>array('permission'=>STAT_STOREGOODS,'lang'=>'menu_stat_storegoods'),
//				'versions'=>array('permission'=>STAT_VERSIONS,'lang'=>'menu_stat_versions'),
//				'alliance'=>array('permission'=>STAT_ALLIANCE,'lang'=>'menu_stat_alliance'),
//				'monster'=>array('permission'=>STAT_MONSTER,'lang'=>'menu_stat_monster'),
//				'signStatistics'=>array('permission'=>STAT_SIGNSTATISTICS,'lang'=>'menu_stat_signStatistics'),
//				'collectresource'=>array('permission'=>STAT_COLLECTRESOURCE,'lang'=>'menu_stat_collectresource'),
				//'facebook'=>array('permission'=>STAT_FACEBOOK,'lang'=>'menu_stat_facebook'),
//				'iosPay'=>array('permission'=>STAT_IOSPAY,'lang'=>'menu_stat_iosPay'),
//				'iosRetention'=>array('permission'=>STAT_IOSRETENTION,'lang'=>'menu_stat_iosRetention'),
//				'allPhoneRetention'=>array('permission'=>STAT_ALLPHONERETENTION,'lang'=>'menu_stat_allPhoneRetention'),
//				'pay_panel'=>array('permission'=>STAT_PAY_PANEL,'lang'=>'menu_stat_pay_panel'),
// 				'user_resource'=>array('permission'=>STAT_USER_RESOURCE,'lang'=>'menu_stat_user_resource'),
//				'exchange'=>array('permission'=>STAT_EXCHANGE,'lang'=>'menu_stat_exchange'),
//				'sample_user'=>array('permission'=>STAT_SAMPLE_USER,'lang'=>'menu_stat_sample_user'),
//				'top10'=>array('permission'=>STAT_TOP10,'lang'=>'menu_stat_top10'),
//				'bindAccount'=>array('permission'=>STAT_BINDACCOUNT,'lang'=>'menu_stat_bindAccount'),
//				'treasure'=>array('permission'=>STAT_TREASURE,'lang'=>'menu_stat_treasure'),
//				'translation'=>array('permission'=>STAT_TRANSLATION,'lang'=>'menu_stat_translation'),
				//'hotgoods'=>array('permission'=>STAT_HOTGOODS,'lang'=>'menu_stat_hotgoods'),
//				'vip'=>array('permission'=>STAT_VIP,'lang'=>'menu_stat_vip'),
//				'fightofking'=>array('permission'=>STAT_FIGHTOFKING,'lang'=>'menu_stat_fightofking'),
//				'equipmentStatistics'=>array('permission'=>STAT_EQUIPMENTSTATISTICS,'lang'=>'menu_stat_equipmentStatistics'),
//				'equipmentStatistics2'=>array('permission'=>STAT_EQUIPMENTSTATISTICS2,'lang'=>'menu_stat_equipmentStatistics2'),
//				'travelBusinessman'=>array('permission'=>STAT_TRAVELBUSINESSMAN,'lang'=>'menu_stat_travelBusinessman'),
//				'dauAndGoldCost'=>array('permission'=>STAT_DAUANDGOLDCOST,'lang'=>'menu_stat_dauAndGoldCost'),
//				'rotaryTableStatistics'=>array('permission'=>STAT_ROTARYTABLESTATISTICS,'lang'=>'menu_stat_rotaryTableStatistics'),
//				'monsterSiegeStatistics'=>array('permission'=>STAT_MONSTERSIEGESTATISTICS,'lang'=>'menu_stat_monsterSiegeStatistics'),
//				'monsterSiegeInfo'=>array('permission'=>STAT_MONSTERSIEGEINFO,'lang'=>'menu_stat_monsterSiegeInfo'),
//				'achievementStatistics'=>array('permission'=>STAT_ACHIEVEMENTSTATISTICS,'lang'=>'menu_stat_achievementStatistics'),
//				'userSkillStatistics'=>array('permission'=>STAT_USERSKILLSTATISTICS,'lang'=>'menu_stat_userSkillStatistics'),
//				'exploreStatistics'=>array('permission'=>STAT_EXPLORESTATISTICS,'lang'=>'menu_stat_exploreStatistics'),
//				'crossFightStatistics'=>array('permission'=>STAT_CROSSFIGHTSTATISTICS,'lang'=>'menu_stat_crossFightStatistics'),
//				'noticeStatistics'=>array('permission'=>STAT_NOTICESTATISTICS,'lang'=>'menu_stat_noticeStatistics'),
//				'pushStatistics'=>array('permission'=>STAT_PUSHSTATISTICS,'lang'=>'menu_stat_pushStatistics'),
//				'castleDressUp'=>array('permission'=>STAT_CASTLEDRESSUP,'lang'=>'menu_stat_castleDressUp'),
//				'fightPk'=>array('permission'=>STAT_FIGHTPK,'lang'=>'menu_stat_fightPk'),
//				'driftBottle'=>array('permission'=>STAT_DRIFTBOTTLE,'lang'=>'menu_stat_driftBottle'),
//				'killTitan'=>array('permission'=>STAT_KILLTITAN,'lang'=>'menu_stat_killTitan'),
//				'userNobility'=>array('permission'=>STAT_USERNOBILITY,'lang'=>'menu_stat_userNobility'),
//				'friendGarrison'=>array('permission'=>STAT_FRIENDGARRISON,'lang'=>'menu_stat_friendGarrison'),
//				'invites'=>array('permission'=>STAT_INVITES,'lang'=>'menu_stat_invites'),
//				'prayService'=>array('permission'=>STAT_PRAYSERVICE,'lang'=>'menu_stat_prayService'),
//				'debris'=>array('permission'=>STAT_DEBRIS,'lang'=>'menu_stat_debris'),
//				'npcBuild'=>array('permission'=>STAT_NPCBUILD,'lang'=>'menu_stat_npcBuild'),
//				'territoryWind'=>array('permission'=>STAT_TERRITORYWIND,'lang'=>'menu_stat_territoryWind'),
//				'moveServerRecord'=>array('permission'=>STAT_MOVESERVERRECORD,'lang'=>'menu_stat_moveServerRecord'),
//				'friendMessage'=>array('permission'=>STAT_FRIENDMESSAGE,'lang'=>'menu_stat_friendMessage'),
//				'dragon'=>array('permission'=>STAT_DRAGON,'lang'=>'menu_stat_dragon'),
//				'dailyActive'=>array('permission'=>STAT_DAILYACTIVE,'lang'=>'menu_stat_dailyActive'),
//				'hunting'=>array('permission'=>STAT_HUNTING,'lang'=>'menu_stat_hunting'),
//				'lostPayUsers'=>array('permission'=>STAT_LOSTPAYUSERS,'lang'=>'menu_stat_lostPayUsers'),
//				'rateOfPay'=>array('permission'=>STAT_RATEOFPAY,'lang'=>'menu_stat_rateOfPay'),
				// 'pve'=>array('permission'=>STAT_PVE,'lang'=>'menu_stat_pve'),
//				'item_unusual'=>array('permission'=>STAT_ITEM_UNUSUAL,'lang'=>'menu_stat_itemUnusual'),
//				'quickdnf'=>array('permission'=>STAT_QUICKDNF,'lang'=>'menu_stat_quickdnf'),
//				'goldCurve'=>array('permission'=>STAT_GOLDCURVE,'lang'=>'menu_stat_goldCurve'),
//				'casino'=>array('permission'=>STAT_CASINO,'lang'=>'menu_stat_casino'),
//				'newDayActivities'=>array('permission'=>STAT_NEWDAYACTIVITIES,'lang'=>'menu_stat_newDayActivities'),
//				'bless'=>array('permission'=>STAT_BLESS,'lang'=>'menu_stat_bless'),
//				'master'=>array('permission'=>STAT_MASTER,'lang'=>'menu_stat_master'),
//				'dungeons'=>array('permission'=>STAT_DUNGEONS,'lang'=>'menu_stat_dungeons'),
//				'smallwar'=>array('permission'=>STAT_SMALLWAR,'lang'=>'menu_stat_smallwar'),
//				'MOPUP_MONSTER'=>array('permission'=>STAT_MOPUP_MONSTER,'lang'=>'menu_stat_MOPUP_MONSTER'),
//				'alchemy'=>array('permission'=>STAT_ALCHEMY,'lang'=>'menu_stat_alchemy'),
//				'throne'=>array('permission'=>STAT_THRONE,'lang'=>'menu_stat_throne'),
//				'hero'=>array('permission'=>STAT_HERO,'lang'=>'menu_stat_hero'),
//				'coinfeedback'=>array('permission'=>STAT_COINFEEDBACK,'lang'=>'menu_stat_coinfeedback'),
//				'egg'=>array('permission'=>STAT_EGG,'lang'=>'menu_stat_egg'),
//				'arena'=>array('permission'=>STAT_ARENA,'lang'=>'menu_stat_arena'),
//				'armyEnhance'=>array('permission'=>STAT_ARMYENHANCE,'lang'=>'menu_stat_armyEnhance'),
//				'alliscience'=>array('permission'=>STAT_ALLISCIENCE,'lang'=>'menu_stat_alliscience'),
//				'lotterypay'=>array('permission'=>STAT_LOTTERYPAY,'lang'=>'menu_stat_lotterypay'),
//				'promotion'=>array('permission'=>STAT_PROMOTION, 'lang'=>'menu_stat_promotion'),
//				'territory'=>array('permission'=>STAT_TERRITORY,'lang'=>'menu_stat_territory'),
		),
		'ad'=>array(
			'dashboard'=>array('permission'=>AD_DASHBOARD,'lang'=>'menu_ad_dashboard'),
			'roi'=>array('permission'=>AD_ROI,'lang'=>'menu_ad_roi'),
			'history'=>array('permission'=>AD_HISTORY,'lang'=>'menu_ad_history'),
			'fraud'=>array('permission'=>AD_FRAUD,'lang'=>'menu_ad_fraud'),
			'cpa'=>array('permission'=>AD_CPA,'lang'=>'menu_ad_cpa'),
//			'channelTracking'=>array('permission'=>AD_CHANNELTRACKING,'lang'=>'menu_ad_channelTracking'),
			'exportinstall'=>array('permission'=>AD_EXPORTINSTALL,'lang'=>'menu_ad_exportinstall'),
		),
		'repair'=>array(
				'goldlog'=>array('permission'=>REPAIR_GOLDLOG,'lang'=>'menu_repair_goldlog'),
		),
		'modify'=>array(
				'profile'=>array('permission'=>MODIFY_PROFILE,'lang'=>'menu_modify_profile'),
				// 'general'=>array('permission'=>MODIFY_GENERAL,'lang'=>'menu_modify_general'),
//				'building'=>array('permission'=>MODIFY_BUILDING,'lang'=>'menu_modify_building'),
				'goods'=>array('permission'=>MODIFY_GOODS,'lang'=>'menu_modify_goods'),
                'story'=>array('permission'=>MODIFY_STORY,'lang'=>'menu_modify_story'),
//				'hero'=>array('permission'=>MODIFY_HERO,'lang'=>'menu_modify_hero'),
//				'glomour'=>array('permission'=>MODIFY_GLOMOUR,'lang'=>'menu_modify_glomour'),
//				'egg'=>array('permission'=>MODIFY_EGG,'lang'=>'menu_modify_egg'),
//				'equip'=>array('permission'=>MODIFY_EQUIP,'lang'=>'menu_modify_equip'),
////				'task'=>array('permission'=>MODIFY_TASK,'lang'=>'menu_modify_task'),
//				'city'=>array('permission'=>MODIFY_CITY,'lang'=>'menu_modify_city'),
//				'army'=>array('permission'=>MODIFY_ARMY,'lang'=>'menu_modify_army'),
//				'science'=>array('permission'=>MODIFY_SCIENCE,'lang'=>'menu_modify_science'),
//				'wall'=>array('permission'=>MODIFY_WALL,'lang'=>'menu_modify_wall'),
//				'dragon'=>array('permission'=>MODIFY_DRAGON,'lang'=>'menu_modify_dragon'),
//				'dragonpro'=>array('permission'=>MODIFY_DRAGONPRO,'lang'=>'menu_modify_dragonpro'),
//				'god'=>array('permission'=>MODIFY_GOD,'lang'=>'menu_modify_god'),
//				'god_skill'=>array('permission'=>MODIFY_GOD_SKILL,'lang'=>'menu_modify_god_skill'),
//				// 'lord'=>array('permission'=>MODIFY_LORD,'lang'=>'menu_modify_lord'),
//				// 'arena'=>array('permission'=>MODIFY_ARENA,'lang'=>'menu_modify_arena'),
//				// 'pve'=>array('permission'=>MODIFY_PVE,'lang'=>'menu_modify_pve'),
//				// 'queue'=>array('permission'=>MODIFY_QUEUE,'lang'=>'menu_modify_queue'),
//				'exchange_goods'=>array('permission'=>MODIFY_EXCHANGE_GOODS,'lang'=>'menu_modify_exchange_goods'),
//				'army_enhance'=>array('permission'=>MODIFY_ARMY_ENHANCE,'lang'=>'menu_modify_army_enhance'),
//				'army_enhance_effect'=>array('permission'=>MODIFY_ARMY_ENHANCE_EFFECT,'lang'=>'menu_modify_army_enhance_effect'),
//                'star'=>array('permission'=>MODIFY_STAR,'lang'=>'menu_modify_star'),
//				'endless_equip'=>array('permission'=>MODIFY_ENDLESS_EQUIP,'lang'=>'menu_modify_endless_equip'),
//				'pwd'=>array('permission'=>MODIFY_PWD,'lang'=>'menu_modify_pwd'),
//				'passwd'=>array('permission'=>MODIFY_PASSWD,'lang'=>'menu_modify_passwd'),


		),
		'server'=>array(
				'serverInfo'=>array('permission'=>SERVER_SERVERINFO,'lang'=>'menu_server_serverInfo'),
				'countryAmount'=>array('permission'=>SERVER_COUNTRYAMOUNT,'lang'=>'menu_server_countryAmount'),
				'strategy'=>array('permission'=>SERVER_STRATEGY,'lang'=>'menu_server_strategy'),
				'config'=>array('permission'=>SERVER_CONFIG,'lang'=>'menu_server_config'),
				'pf_config'=>array('permission'=>SERVER_PF_CONFIG,'lang'=>'menu_server_pf_config'),
		        'deployrule'=>array('permission'=>SERVER_DEPLOYRULE,'lang'=>'menu_server_deployrule'),
//				'deployclientconfig'=>array('permission'=>SERVER_LANG,'lang'=>'menu_server_lang'),
//				'deployresourcexml'=>array('permission'=>SERVER_DEPLOYXML,'lang'=>'menu_server_deployresourcexml'),
				'xml'=>array('permission'=>SERVER_XML,'lang'=>'menu_server_xml'),
				'shutdown'=>array('permission'=>SERVER_SHUTDOWN,'lang'=>'menu_server_shutdown'),
		        'detect'=>array('permission'=>SERVER_DETECT,'lang'=>'menu_server_detect'),
		        'onlineUsers'=>array('permission'=>SERVER_ONLINE_USERS,'lang'=>'menu_server_onlineUsers'),
		        'monitor'=>array('permission'=>SERVER_MONITOR,'lang'=>'menu_server_monitor'),
		        'luaVersion'=>array('permission'=>SERVER_LUAVERSION,'lang'=>'menu_server_luaVersion'),
		        'appVersion'=>array('permission'=>SERVER_APPVERSION,'lang'=>'menu_server_appVersion'),
		        'version'=>array('permission'=>SERVER_VERSION,'lang'=>'menu_server_version'),
		        //'onekey'=>array('permission'=>SERVER_ONEKEY,'lang'=>'menu_server_onekey'),
		        'multiCity'=>array('permission'=>SERVER_MULTICITY,'lang'=>'menu_server_multiCity'),
			'frontxml'=>array('permission'=>SERVER_FRONTXML,'lang'=>'menu_server_frontxml'),

		),
		'mysql'=>array(
							//'index'=>array('permission'=>MENU_MYSQL_INDEX,'lang'=>'menu_mysql_index'),
				//'redis'=>array('permission'=>MENU_MYSQL_REDIS,'lang'=>'menu_mysql_redis'),
				//'globalRedis'=>array('permission'=>MENU_MYSQL_GLOBALREDIS,'lang'=>'menu_mysql_globalRedis'),
					//'struct'=>array('permission'=>MENU_MYSQL_STRUCT,'lang'=>'menu_mysql_struct'),
					//'search'=>array('permission'=>MENU_MYSQL_SEARCH,'lang'=>'menu_mysql_search'),
				'execute'=>array('permission'=>MENU_MYSQL_EXECUTE,'lang'=>'menu_mysql_execute'),
			'task_file'=>array('permission'=>MENU_MYSQL_TASK_FILE,'lang'=>'menu_mysql_task_file'),
							//'aexecute'=>array('permission'=>MENU_MYSQL_AEXECUTE,'lang'=>'menu_mysql_aexecute'),
							//'compare'=>array('permission'=>MENU_MYSQL_COMPARE,'lang'=>'menu_mysql_compare'),
		),
		'admin'=>array(
				'list'=>array('permission'=>ADMIN_USER_LIST,'lang'=>'menu_admin_list'),
				'log'=>array('permission'=>ADMIN_USER_LOG,'lang'=>'menu_admin_log'),
				'editpassword'=>array('permission'=>ADMIN_USER_EDITPASSWORD,'lang'=>'menu_admin_edit_password'),
		),
);

if ($host == '127.0.0.1' || $host == '10.1.16.211' || $host == 'URLIP') {
// 	unset($branchMenu['standard']['onlineMail']);
// 	unset($branchMenu['standard']['confirmVersion']);
// 	unset($branchMenu['standard']['updatePlan']);
// 	unset($branchMenu['standard']['channelSpecification']);
	
	unset($trunkMenu['standard']);
	
	unset($branchMenu['user']['orderDealWith']);
	unset($branchMenu['user']['banRecord']);
	unset($branchMenu['user']['ipQuery']);
	
	unset($branchMenu['op']['fbRefund']);
	unset($branchMenu['op']['uploadPackageXML']);
	unset($branchMenu['op']['refundAmount']);
//	unset($branchMenu['op']['updateDeviceMapping']);
	
//	unset($branchMenu['pay']['recentpay']);
	unset($branchMenu['pay']['pay_analyze']);
	unset($branchMenu['pay']['userPayAnalyze']);
	unset($branchMenu['pay']['goldStatistics']);
	unset($branchMenu['pay']['topPay']);
//	unset($branchMenu['pay']['basicdatas']);
	
	unset($branchMenu['stat']['dau']);
	unset($branchMenu['stat']['regremain']);
//	unset($branchMenu['stat']['alliance']);
	unset($branchMenu['stat']['signStatistics']);
	unset($branchMenu['stat']['iosPay']);
	unset($branchMenu['stat']['iosRetention']);
	unset($branchMenu['stat']['allPhoneRetention']);
	unset($branchMenu['stat']['vip']);
	unset($branchMenu['stat']['travelBusinessman']);
	unset($branchMenu['stat']['dauAndGoldCost']);
	unset($branchMenu['stat']['rotaryTableStatistics']);
	unset($branchMenu['stat']['achievementStatistics']);
	unset($branchMenu['stat']['noticeStatistics']);
	unset($branchMenu['stat']['pushStatistics']);
	unset($branchMenu['stat']['cumulativeRecharge']);
//	unset($branchMenu['stat']['operatingDataStatistics']);
	unset($branchMenu['stat']['tutorialStatistics']);
	unset($branchMenu['stat']['userSkillStatistics']);
	unset($branchMenu['stat']['exploreStatistics']);
	unset($branchMenu['stat']['crossFightStatistics']);
	unset($branchMenu['stat']['castleDressUp']);
	
}elseif ($host == 'IPIPIP'){
	unset($branchMenu['standard']['updatePlan']);
	unset($branchMenu['standard']['channelSpecification']);
	unset($branchMenu['standard']['cdnResource']);
	
	unset($branchMenu['op']['fbRefund']);
	unset($branchMenu['op']['uploadPackageXML']);
	unset($branchMenu['op']['refundAmount']);
	
	unset($branchMenu['user']['orderDealWith']);
	unset($branchMenu['user']['banRecord']);
	unset($branchMenu['user']['ipQuery']);
	
	unset($branchMenu['pay']['recentpay']);
	unset($branchMenu['pay']['pay_analyze']);
	unset($branchMenu['pay']['userPayAnalyze']);
	unset($branchMenu['pay']['newroi']);
	unset($branchMenu['pay']['goldStatistics']);
	unset($branchMenu['pay']['topPay']);
//	unset($branchMenu['pay']['basicdatas']);
	
	unset($branchMenu['stat']['dau']);
	unset($branchMenu['stat']['regremain']);
	unset($branchMenu['stat']['alliance']);
	unset($branchMenu['stat']['signStatistics']);
	unset($branchMenu['stat']['iosPay']);
	unset($branchMenu['stat']['iosRetention']);
	unset($branchMenu['stat']['allPhoneRetention']);
	unset($branchMenu['stat']['vip']);
	unset($branchMenu['stat']['travelBusinessman']);
	unset($branchMenu['stat']['dauAndGoldCost']);
	unset($branchMenu['stat']['rotaryTableStatistics']);
	unset($branchMenu['stat']['achievementStatistics']);
	unset($branchMenu['stat']['noticeStatistics']);
	unset($branchMenu['stat']['pushStatistics']);
	unset($branchMenu['stat']['cumulativeRecharge']);
	unset($branchMenu['stat']['operatingDataStatistics']);
	unset($branchMenu['stat']['tutorialStatistics']);
	unset($branchMenu['stat']['userSkillStatistics']);
	unset($branchMenu['stat']['exploreStatistics']);
	unset($branchMenu['stat']['crossFightStatistics']);
	unset($branchMenu['stat']['castleDressUp']);
	
}else {
	
}

if (in_array($_COOKIE['u'],$privilegeArr)) {
	$branchMenu['stat']['facebook']=array('permission'=>STAT_FACEBOOK,'lang'=>'menu_stat_facebook');
	$branchMenu['user']['switchAccount']=array('permission'=>USER_SWITCHACCOUNT,'lang'=>'menu_user_switchAccount');
}
//if ($_COOKIE ['u'] == 'dongyue' || $_COOKIE ['u'] == 'zhangyuanyuan' || $_COOKIE ['u'] == 'wangzhongyuan' || $_COOKIE ['u'] == 'chengxiwang' || $_COOKIE ['u'] == 'dinghanchen' || $_COOKIE ['u'] == 'litaikui' || $_COOKIE ['u'] == 'kangyongbin2' || $_COOKIE ['u'] == 'zhengze') {
//	$branchMenu['user']['switchAccount']=array('permission'=>USER_SWITCHACCOUNT,'lang'=>'menu_user_switchAccount');
//}

if ($_COOKIE['u']=='specialAccounts'){
	unset($trunkMenu['standard']);
	unset($trunkMenu['global']);
	unset($trunkMenu['user']);
	unset($trunkMenu['modify']);
	unset($trunkMenu['op']);
	unset($trunkMenu['pay']);
	//unset($trunkMenu['stat']);
	unset($trunkMenu['server']);
	unset($trunkMenu['mysql']);
	unset($trunkMenu['admin']);
	unset($branchMenu['stat']);
	$branchMenu['stat']=array('dau'=>array('permission'=>STAT_DAU,'lang'=>'menu_stat_dau'));
}

if ($_COOKIE['u'] != 'xiaomi'){
	unset($branchMenu['pay']['cnRegPay']);
}

if ($_COOKIE['u'] == 'xiaomi'){
//	unset($branchMenu['pay']['basicdatas']);
	unset($branchMenu['stat']['operatingDataStatistics']);
	unset($branchMenu['pay']['newroi']);
	unset($branchMenu['pay']['topPay']);
	unset($branchMenu['pay']['payrank']);
	unset($branchMenu['pay']['userPayAnalyze']);
	unset($branchMenu['pay']['goldlog_analyze']);
	unset($branchMenu['pay']['goldStatistics']);
	
}

$authList = array();
foreach ($trunkMenu as $tab=>$value){//大分类标题
	$authList[] = $value['permission'];
	$permissionLink[$value['permission']] = $tab;
	foreach ($branchMenu[$tab] as $subTab=>$subValue){//$branchMenu 每一项
		$authList[] = $subValue['permission'];
		$actions[$tab][$subTab] = $subValue['permission'];
		$permissionLink[$subValue['permission']] = $tab;
	}
}

/**
 * @see 获取指定mod和act的链接
 */
function getActionHref($mod, $act) {
	return "admincp.php?mod=$mod&act=$act";
}

/**
 * @see 生成导航菜单
 * 
 * @param array $groupid
 * 
 * @param array $permission
 * 
 * @return array
 */
function initMenu($groupid, $permission = null) {
	global $MALANG;
	global $trunkMenu;
	global $branchMenu;
	
	$super = intval($groupid/10) == 1;
	$hidePermission = intval($groupid/10) == 2;
	
	if (!$super&&empty($permission))
		return array();
	
	$menu = array();
	
	foreach ($trunkMenu as $tab=>$value){
		//0724改动：旧版：当有trunk的时候brunch才有效		新版：当有trunk的时候branch权限全开，只有branch权限的可以点击trunk分页
		if($super || $permission [$value['permission']]){
			$trunkPermission = true;
			$branchPermission = true;
		}else{
			$branchPermission = false;
			$trunkPermission = false;
			foreach ($branchMenu[$tab] as $subTab=>$subValue){
				if($permission[$subValue['permission']]){
					$trunkPermission = true;
				}
			}
		}
		if ($trunkPermission) {
			$menu[$tab] = array ("name" => $MALANG[$value['lang']],"permit" => $value['permission']);
			$menu_user = &$menu[$tab];
			foreach ($branchMenu[$tab] as $subTab=>$subValue){
				if($subValue['hide'] && !$hidePermission && !$super)
					continue;
				if($branchPermission || $permission[$subValue['permission']])
					$menu_user["sub_menu"][$subValue['permission']] = array (
					"name" => $MALANG[$subValue['lang']],
					"permit" => $subValue['permission'],
					"href" => getActionHref($tab,$subTab),
					"action"=> $subTab,
					"hide" => $subValue['hide'],
				);
			}
			//只有隐藏分页权限的时候特殊处理
			if(!$menu_user["sub_menu"])
				unset($menu[$tab]);
		}
	}

	return $menu;
}

