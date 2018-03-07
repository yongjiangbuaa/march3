CREATE TABLE IF NOT EXISTS `process_offset` (
  `process_id` char(32),
  `name` char(64),
  `offset` bigint(20),
  PRIMARY KEY  (`process_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `process_filemtime` (
  `process_id` char(32) NOT NULL DEFAULT '',
  `name` char(64) DEFAULT NULL,
  `offset` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`process_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `stat_retention_daily_pf_country` (
  `sid` int(11),
  `date` int(8),
  `pf` varchar(20),
  `country` char(10) default 'Unknown' COMMENT 'lookup',
  `dau` int(10),
  `reg_all` int(10),
  `reg_valid` int(10),
  `r1` int(10),
  `r2` int(10),
  `r3` int(10),
  `r4` int(10),
  `r5` int(10),
  `r6` int(10),
  `r7` int(10),
  `r8` int(10),
  `r9` int(10),
  `r10` int(10),
  `r11` int(10),
  `r12` int(10),
  `r13` int(10),
  `r14` int(10),
  `r15` int(10),
  `r16` int(10),
  `r17` int(10),
  `r18` int(10),
  `r19` int(10),
  `r20` int(10),
  `r21` int(10),
  `r22` int(10),
  `r23` int(10),
  `r24` int(10),
  `r25` int(10),
  `r26` int(10),
  `r27` int(10),
  `r28` int(10),
  `r29` int(10),
  `r30` int(10),
  PRIMARY KEY (`sid`, `date`, `pf`, `country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `stat_retention_daily_pf_country_referrer` (
  `sid` int(11),
  `date` int(8),
  `pf` varchar(20),
  `country` char(10) default 'Unknown' COMMENT 'lookup',
  `referrer` varchar(40),
  `dau` int(10),
  `reg_all` int(10),
  `reg_valid` int(10),
  `replay` int(10),
  `relocation` int(10),
  `r1` int(10),
  `r2` int(10),
  `r3` int(10),
  `r4` int(10),
  `r5` int(10),
  `r6` int(10),
  `r7` int(10),
  `r8` int(10),
  `r9` int(10),
  `r10` int(10),
  `r11` int(10),
  `r12` int(10),
  `r13` int(10),
  `r14` int(10),
  `r15` int(10),
  `r16` int(10),
  `r17` int(10),
  `r18` int(10),
  `r19` int(10),
  `r20` int(10),
  `r21` int(10),
  `r22` int(10),
  `r23` int(10),
  `r24` int(10),
  `r25` int(10),
  `r26` int(10),
  `r27` int(10),
  `r28` int(10),
  `r29` int(10),
  `r30` int(10),
  PRIMARY KEY (`sid`, `date`, `pf`, `country`,`referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# noinspection SqlNoDataSourceInspection
CREATE TABLE `stat_retention_daily_pf_country_referrer_appVersion` (
  `sid` int(11) NOT NULL DEFAULT '0',
  `date` int(8) NOT NULL DEFAULT '0',
  `pf` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` char(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unknown' COMMENT 'lookup',
  `referrer` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `appVersion` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dau` int(10) DEFAULT NULL,
  `reg_all` int(10) DEFAULT NULL,
  `reg_valid` int(10) DEFAULT NULL,
  `replay` int(10) DEFAULT NULL,
  `relocation` int(10) DEFAULT NULL,
  `pay_all` int(10) DEFAULT NULL,
  `r1` int(10) DEFAULT NULL,
  `r2` int(10) DEFAULT NULL,
  `r3` int(10) DEFAULT NULL,
  `r4` int(10) DEFAULT NULL,
  `r5` int(10) DEFAULT NULL,
  `r6` int(10) DEFAULT NULL,
  `r7` int(10) DEFAULT NULL,
  `r8` int(10) DEFAULT NULL,
  `r9` int(10) DEFAULT NULL,
  `r10` int(10) DEFAULT NULL,
  `r11` int(10) DEFAULT NULL,
  `r12` int(10) DEFAULT NULL,
  `r13` int(10) DEFAULT NULL,
  `r14` int(10) DEFAULT NULL,
  `r15` int(10) DEFAULT NULL,
  `r16` int(10) DEFAULT NULL,
  `r17` int(10) DEFAULT NULL,
  `r18` int(10) DEFAULT NULL,
  `r19` int(10) DEFAULT NULL,
  `r20` int(10) DEFAULT NULL,
  `r21` int(10) DEFAULT NULL,
  `r22` int(10) DEFAULT NULL,
  `r23` int(10) DEFAULT NULL,
  `r24` int(10) DEFAULT NULL,
  `r25` int(10) DEFAULT NULL,
  `r26` int(10) DEFAULT NULL,
  `r27` int(10) DEFAULT NULL,
  `r28` int(10) DEFAULT NULL,
  `r29` int(10) DEFAULT NULL,
  `r30` int(10) DEFAULT NULL,
  `rr1` int(10) DEFAULT NULL,
  `p1` int(10) DEFAULT NULL,
  `p2` int(10) DEFAULT NULL,
  `p3` int(10) DEFAULT NULL,
  `p4` int(10) DEFAULT NULL,
  `p5` int(10) DEFAULT NULL,
  `p6` int(10) DEFAULT NULL,
  `p7` int(10) DEFAULT NULL,
  `p8` int(10) DEFAULT NULL,
  `p9` int(10) DEFAULT NULL,
  `p10` int(10) DEFAULT NULL,
  `p11` int(10) DEFAULT NULL,
  `p12` int(10) DEFAULT NULL,
  `p13` int(10) DEFAULT NULL,
  `p14` int(10) DEFAULT NULL,
  `p15` int(10) DEFAULT NULL,
  `p16` int(10) DEFAULT NULL,
  `p17` int(10) DEFAULT NULL,
  `p18` int(10) DEFAULT NULL,
  `p19` int(10) DEFAULT NULL,
  `p20` int(10) DEFAULT NULL,
  `p21` int(10) DEFAULT NULL,
  `p22` int(10) DEFAULT NULL,
  `p23` int(10) DEFAULT NULL,
  `p24` int(10) DEFAULT NULL,
  `p25` int(10) DEFAULT NULL,
  `p26` int(10) DEFAULT NULL,
  `p27` int(10) DEFAULT NULL,
  `p28` int(10) DEFAULT NULL,
  `p29` int(10) DEFAULT NULL,
  `p30` int(10) DEFAULT NULL,
  PRIMARY KEY (`sid`,`date`,`pf`,`country`,`referrer`,`appVersion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_retention_daily_pf_country_new` (
  `sid` int(11),
  `date` int(8),
  `pf` varchar(20),
  `country` char(10) default 'Unknown' COMMENT 'lookup',
  `dau` int(10),
  `reg_all` int(10),
  `reg_valid` int(10),
  `replay` int(10),
  `relocation` int(10),
  `r1` int(10),
  `r2` int(10),
  `r3` int(10),
  `r4` int(10),
  `r5` int(10),
  `r6` int(10),
  `r7` int(10),
  `r8` int(10),
  `r9` int(10),
  `r10` int(10),
  `r11` int(10),
  `r12` int(10),
  `r13` int(10),
  `r14` int(10),
  `r15` int(10),
  `r16` int(10),
  `r17` int(10),
  `r18` int(10),
  `r19` int(10),
  `r20` int(10),
  `r21` int(10),
  `r22` int(10),
  `r23` int(10),
  `r24` int(10),
  `r25` int(10),
  `r26` int(10),
  `r27` int(10),
  `r28` int(10),
  `r29` int(10),
  `r30` int(10),
  PRIMARY KEY (`sid`, `date`, `pf`, `country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_retention_daily_pf_country_version` (
  `sid` int(11),
  `date` int(8),
  `pf` varchar(20),
  `country` char(10) default 'Unknown' COMMENT 'lookup',
  `version` varchar(20),
  `dau` int(10),
  `reg_all` int(10),
  `reg_valid` int(10),
  `replay` int(10),
  `relocation` int(10),
  `r1` int(10),
  `r2` int(10),
  `r3` int(10),
  `r4` int(10),
  `r5` int(10),
  `r6` int(10),
  `r7` int(10),
  `r8` int(10),
  `r9` int(10),
  `r10` int(10),
  `r11` int(10),
  `r12` int(10),
  `r13` int(10),
  `r14` int(10),
  `r15` int(10),
  `r16` int(10),
  `r17` int(10),
  `r18` int(10),
  `r19` int(10),
  `r20` int(10),
  `r21` int(10),
  `r22` int(10),
  `r23` int(10),
  `r24` int(10),
  `r25` int(10),
  `r26` int(10),
  `r27` int(10),
  `r28` int(10),
  `r29` int(10),
  `r30` int(10),
  PRIMARY KEY (`sid`, `date`, `pf`, `country`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `pay_goldStatistics_daily` (
  `sid` int(11),
  `date` int(8),
  `costType` int(4),
  `paidFlag` TINYINT(1),
  `users` int(10),
  `times` int(10),
  `sumc` int(10),
   PRIMARY KEY (`sid`, `date`, `costType`, `paidFlag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `pay_goldStatistics_daily_groupByType` (
  `sid` int(11),
  `date` int(8),
  `paidFlag` TINYINT(1),
  `type` int(4),
  `users` int(10),
  `times` int(10),
  `sumc` int(10),
   PRIMARY KEY (`sid`, `date`, `type`, `paidFlag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `pay_goldStatistics_daily_groupByGoodsAndResource` (
  `sid` int(11),
  `date` int(8),
  `paidFlag` TINYINT(1),
  `type` int(4),
  `param1` int(4),
  `users` int(10),
  `times` int(10),
  `sumc` int(10),
   PRIMARY KEY (`sid`, `date`, `paidFlag`, `type`, `param1`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `stat_dau_daily_pf_country` (
--  `sid` int(11),
--  `date` int(8),
--  `pf` varchar(20),
--  `country` varchar(40),
--  `reg` int(11),
--  `dau` int(11),
--   PRIMARY KEY (`sid`, `date`, `pf`, `country`)
-- ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_payFirst_daily` (
  `sid` int(11),
  `date` int(8),
  `pf` varchar(20),
  `uniquePay` int(11),
  `totalPay` int(11),
  `total` int(11),
  `cnt` int(11),
   PRIMARY KEY (`sid`, `date`, `pf`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `pay_payAnalyze_7day` (
  `sid` int(11),
  `date` int(8),
  `totalUsers` int(11),
  `loseUsers` int(11),
  `silenceUsers` int(11),
  `repeatUsers` int(11),
  `firsetUsers` int(11),
  `r1` int(10),
  `r2` int(10),
  `r3` int(10),
  `r4` int(10),
  `r5` int(10),
  `r6` int(10),
  `r7` int(10),
  PRIMARY KEY (`sid`, `date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `pay_analyze_pf_country` (
  `sid` int(11),
  `date` int(8),
  `pf` varchar(20),
  `country` varchar(10),
  `payTotle` double(11,2),
  `payUsers` int(11),
  `payTimes` int(11),
  `dau` int(11),
  `firstPay` int(11),
   PRIMARY KEY (`sid`, `date`, `pf`,`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `pay_payTotle_pf_country` (
  `sid` int(11),
  `date` int(8),
  `payChanel` varchar(20),
  `pf` varchar(20),
  `country` varchar(10),
  `payCount` double(11,2),
   PRIMARY KEY (`sid`, `date`, `payChanel`, `pf`,`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_dau_daily_pf_country_v2` (
  `sid` int(11),
  `date` int(8),
  `pf` varchar(20),
  `country` varchar(40),
  `reg` int(11),
  `dau` int(11),
  `paid_dau` int(11),
  `deviceDau` int(11),
   PRIMARY KEY (`sid`, `date`, `pf`, `country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_dau_daily_pf_country_new` (
  `sid` int(11),
  `date` int(8),
  `pf` varchar(20),
  `country` varchar(40),
  `reg` int(11),
  `replay` int(11),
  `relocation` int(11),
  `dau` int(11),
  `paid_dau` int(11),
  `deviceDau` int(11),
   PRIMARY KEY (`sid`, `date`, `pf`, `country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_roi_pf_country_v2` (
  `country` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `pf` varchar(20),
  `payDate` int(8),
  `regDate` int(8),
  `spendSum` double(11,2),
PRIMARY KEY (`country`,`pf`,`payDate`,`regDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_roi_pf_country_reg` (
 `sid` int(11),
 `regDate` int(8),
 `country` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
 `pf` varchar(20),
 `reg` int(11),
 PRIMARY KEY (`sid`,`regDate`,`country`,`pf`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_retention_ios` (
  `sid` int(11),
  `date` int(8),
  `dau` int(10),
  `model` varchar(64),
  `version` varchar(64),
  `reg_all` int(10),
  `reg_valid` int(10),
  `r1` int(10),
  `r2` int(10),
  `r3` int(10),
  `r4` int(10),
  `r5` int(10),
  `r6` int(10),
  `r7` int(10),
  `r8` int(10),
  `r9` int(10),
  `r10` int(10),
  `r11` int(10),
  `r12` int(10),
  `r13` int(10),
  `r14` int(10),
  `r15` int(10),
  `r16` int(10),
  `r17` int(10),
  `r18` int(10),
  `r19` int(10),
  `r20` int(10),
  `r21` int(10),
  `r22` int(10),
  `r23` int(10),
  `r24` int(10),
  `r25` int(10),
  `r26` int(10),
  `r27` int(10),
  `r28` int(10),
  `r29` int(10),
  `r30` int(10),
  PRIMARY KEY (`sid`, `date`,`model`,`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_retention_allPhone` (
  `sid` int(11),
  `date` int(8),
  `dau` int(10),
  `model` varchar(64),
  `reg_all` int(10),
  `reg_valid` int(10),
  `r1` int(10),
  `r2` int(10),
  `r3` int(10),
  `r4` int(10),
  `r5` int(10),
  `r6` int(10),
  `r7` int(10),
  `r8` int(10),
  `r9` int(10),
  `r10` int(10),
  `r11` int(10),
  `r12` int(10),
  `r13` int(10),
  `r14` int(10),
  `r15` int(10),
  `r16` int(10),
  `r17` int(10),
  `r18` int(10),
  `r19` int(10),
  `r20` int(10),
  `r21` int(10),
  `r22` int(10),
  `r23` int(10),
  `r24` int(10),
  `r25` int(10),
  `r26` int(10),
  `r27` int(10),
  `r28` int(10),
  `r29` int(10),
  `r30` int(10),
  PRIMARY KEY (`sid`, `date`,`model`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_hot_goods_cost_record2` (
  `sid` int(11),
  `buyTime` int(8),
  `goodsId` varchar(20),
  `price` int(10),
  `priceType` int(10),
  `num` int(11),
  `people` int(11),
  `reTimes` int(11),
   PRIMARY KEY (`sid`, `buyTime`, `goodsId`,`price`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_sign` (
  `sid` int(11),
  `date` int(8),
  `day` int(4),
  `request` int(11),
  `signCount` int(11),
  `dau` int(11),
   PRIMARY KEY (`sid`, `date`, `day`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_exchange_pf_country_send` (
  `sid` int(11),
  `date` int(8),
  `productId` varchar(64),
  `pf` varchar(20),
  `country` varchar(40),
  `num` int(11),
  `sendNum` int(11),
   PRIMARY KEY (`sid`, `date`, `productId`, `pf`, `country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_vip_record` (
  `sid` int(11),
  `date` int(8),
  `vipLevel` int(4),
  `untlogin` int(11),
  `untActive` int(11),
   PRIMARY KEY (`sid`, `date`, `vipLevel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_rotaryTable_out` (
  `sid` int(11),
  `date` int(8),
  `countUsers` int(11),
  `sumCost` int(11),
   PRIMARY KEY (`sid`, `date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_rotaryTable_in` (
  `sid` int(11),
  `date` int(8),
  `lotteryId` varchar(20),
  `position` int(4),
  `pcounts` int(11),
  `sumCost` int(11),
   PRIMARY KEY (`sid`, `date`, `lotteryId`,`position`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_noticeUsersAndTimes` (
  `sid` int(11),
  `date` int(8),
  `users` int(11),
  `times` int(11),
  PRIMARY KEY (`sid`, `date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_equipmentForgingTimes_v2` (
 `sid` int(11),
 `date` int(8),
 `paidFlag` int(8),
 `blevel` int(11),
 `dau` int(11),
 `forgingUsers` int(11),
 `forgingTimes` int(11),
 `steelCost` int(11),
 `cdCost` int(11),
 `materialCost` int(11),
  PRIMARY KEY (`sid`, `date`, `paidFlag`, `blevel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_achievement` (
 `sid` int(11),
 `date` int(8),
 `achieveId` varchar(255),
 `users` int(11),
  PRIMARY KEY (`sid`, `date`, `achieveId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_pushInfo` (
 `sid` int(11),
 `date` int(8),
 `type` int(4),
 `pushCount` int(11),
 `entryUsers` int(11),
 `entryTimes` int(11),
 `10entryUsers` int(11),
 `10entryTimes` int(11),
 PRIMARY KEY (`sid`, `date`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_fbRoi_retention` (
  `sid` int(11),
  `adsrc` varchar(40),
  `regDate` int(8),
  `reg_all` int(10),
  `r1` int(10),
  `r2` int(10),
  `r3` int(10),
  `r4` int(10),
  `r5` int(10),
  `r6` int(10),
  `r7` int(10),
  `r8` int(10),
  `r9` int(10),
  `r10` int(10),
  `r11` int(10),
  `r12` int(10),
  `r13` int(10),
  `r14` int(10),
  `r15` int(10),
  `r16` int(10),
  `r17` int(10),
  `r18` int(10),
  `r19` int(10),
  `r20` int(10),
  `r21` int(10),
  `r22` int(10),
  `r23` int(10),
  `r24` int(10),
  `r25` int(10),
  `r26` int(10),
  `r27` int(10),
  `r28` int(10),
  `r29` int(10),
  `r30` int(10),
  PRIMARY KEY (`sid`, `adsrc`, `regDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_fbRoi_pay` (
  `sid` int(11),
  `adsrc` varchar(40),
  `regDate` int(8),
  `payDate` int(8),
  `payNum` double(11,2),
  PRIMARY KEY (`sid`, `adsrc`, `regDate`,`payDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_alliance_territory` (
 `sid` int(11),
 `territoryNum` int(11),
 `allianceNum` int(11),
 `attackTimes` int(11),
 `callBackTimes` int(11),
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_recharge_cumulative` (
 `sid` int(11),
 `date` int(8),
 `level` int(4),
 `users` int(11),
 PRIMARY KEY (`sid`, `date`, `level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_tutorial_pf_country_appVersion` (
  `date` int(8),
  `country` varchar(40),
  `pf` varchar(20),
  `appVersion` varchar(40),
  `tutorial` bigint(20),
  `regCount` int(11),
  `perTutCount` int(11),
   PRIMARY KEY (`date`,`country`, `pf`, `appVersion`,`tutorial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_tutorial_pf_country_appVersion_new` (
  `date` int(8),
  `country` varchar(40),
  `pf` varchar(20),
  `appVersion` varchar(40),
  `tutorial` bigint(20),
  `regCount` int(11),
  `perTutCount` int(11),
   PRIMARY KEY (`date`,`country`, `pf`, `appVersion`,`tutorial`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_server_info` (
  `sid` int(11),
  `days` int(11),
  `users` int(11),
  `paySum` double(11,2),
  `newUsers` int(11),
  `replay` int(11),
  `relocation` int(11),
  `daoliangDays` int(11),
  `daoliangPeriod` varchar(40),
   PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_cross_fight_users` (
  `sid` int(11),
  `startTime` bigint(20),
  `round` int(11),
  `partUsers` int(11),
  `permissionUsers` int(11),
   PRIMARY KEY (`sid`,`startTime`,`round`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_equipUsedTimes_daily` (
  `sid` int(11),
  `date` int(8),
  `ublevel` int(4),
  `paidFlag` int(4),
  `totalUsers` int(11),
  `u0` int(11),
  `u1` int(11),
  `u2` int(11),
  `u3` int(11),
  `u4` int(11),
  `u5` int(11),
  `s0` int(11),
  `s1` int(11),
  `s2` int(11),
  `s3` int(11),
  `s4` int(11),
  `s5` int(11),
  PRIMARY KEY (`sid`,`date`,`ublevel`,`paidFlag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_dressUp` (
  `sid` int(11),
  `date` int(8),
  `gType` int(11),
  `userCount` int(11),
  PRIMARY KEY (`sid`,`date`,`gType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_lost_payUsers` (
  `sid` int(11),
  `date` int(8),
  `blevel` int(11),
  `payLevel` int(11),
  `cnt` int(11),
  PRIMARY KEY (`sid`,`date`,`blevel`,`payLevel`)
 ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `stat_log_rbi_dailyActive` (
  `sid` int(11) NOT NULL DEFAULT '0',
  `date` int(8) NOT NULL DEFAULT '0',
  `activeId` int(8) NOT NULL DEFAULT '0',
  `part` int(8) NOT NULL DEFAULT '0',
  `complete` int(8) NOT NULL DEFAULT '0',
  `reward` int(8) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`date`,`activeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `stat_log_rbi_dailygoodscost` (
  `sid` int(11) NOT NULL DEFAULT '0',
  `date` int(8) NOT NULL DEFAULT '0',
  `type` int(4) NOT NULL DEFAULT '0' COMMENT '0增加1消耗',
  `itemId` int(11) NOT NULL DEFAULT '0',
  `param1` int(11) NOT NULL DEFAULT '0' COMMENT '物品来源类型',
  `cost` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`,`date`,`type`,`itemId`,`param1`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `stat_dau_daily_pf_country_referrer` (
  `sid` int(11) NOT NULL DEFAULT '0',
  `date` int(8) NOT NULL DEFAULT '0',
  `pf` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `referrer` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reg` int(11) DEFAULT NULL,
  `replay` int(11) DEFAULT NULL,
  `relocation` int(11) DEFAULT NULL,
  `dau` int(11) DEFAULT NULL,
  `paid_dau` int(11) DEFAULT NULL,
  `pdau_relocation` int(11) DEFAULT NULL,
  `deviceDau` int(11) DEFAULT NULL,
  `totalDeviceDau` int(11) DEFAULT NULL,
  `gold` bigint(20) DEFAULT NULL,
  `paidGold` bigint(20) DEFAULT NULL,
  `userNum` int(11) DEFAULT NULL,
  PRIMARY KEY (`sid`,`date`,`pf`,`country`,`referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
# 不同付费等级数据
CREATE TABLE `pay_userdata_dau` (
  `date` int(8) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `pf` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `paylevel` int(8) DEFAULT NULL,
  `dau` int(11) DEFAULT NULL,
  `money` bigint(20) DEFAULT NULL,
  `users` int(11) DEFAULT NULL,
  PRIMARY KEY (`date`,`sid`,`pf`,`country`,`paylevel`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
# 不同付费等级装备信息
CREATE TABLE `pay_equip_level` (
  `date` int(8) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `level` int(8) NOT NULL DEFAULT '0' COMMENT '领主等级',
  `paylevel` int(8) DEFAULT '0' COMMENT '付费等级',
  `itemId` int(11) NOT NULL DEFAULT '0' COMMENT '装备itemid',
  `cnt` int(11) DEFAULT NULL COMMENT '装备个数',
  `users` int(11) DEFAULT NULL COMMENT 'itemid 人数',
  `allpeople` int(11) DEFAULT NULL COMMENT '对应当时level人数 跟itemid无关',
  PRIMARY KEY (`date`,`sid`,`level`,`paylevel`,`itemId`),
  KEY `idx_date_itemid` (`date`,`itemId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# 版本统计
CREATE TABLE `stats_version` (
  `date` int(8) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `pf` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `appversion` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `cnt` int(11) DEFAULT NULL COMMENT '版本个数',
  PRIMARY KEY (`date`,`sid`,`pf`,`country`,`appversion`),
  KEY `idx_appversion` (`date`,`pf`,`appversion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `stat_basic` (
  `date` int(8) NOT NULL DEFAULT '0',
  `sid` int(11) NOT NULL DEFAULT '0',
  `country` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `platform` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `referrer` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dau` int(10) DEFAULT NULL,
  `dau_device` int(10) DEFAULT NULL,
  `totalDeviceDau` int(11) DEFAULT NULL,
  `dau_paid` int(10) DEFAULT NULL,
  `olduser` int(10) DEFAULT NULL,
  `newuser` int(10) DEFAULT NULL,
  `replay` int(10) DEFAULT '0',
  `relocation` int(10) DEFAULT '0',
  `pay_amount` decimal(10,2) DEFAULT NULL,
  `pay_usernum` int(10) DEFAULT NULL,
  `pay_times` int(10) DEFAULT NULL,
  `pay_firstusernum` int(10) DEFAULT NULL,
  `pay_rate` decimal(10,2) DEFAULT NULL,
  `arpu` decimal(10,2) DEFAULT NULL,
  `r1` decimal(10,2) DEFAULT NULL,
  `r3` decimal(10,2) DEFAULT NULL,
  `r7` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`date`,`sid`,`country`,`platform`,`referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

# 没有sid 信息
CREATE TABLE `basic_operation` (
  `date` int(8) NOT NULL DEFAULT '0',
  `pf` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `reg` int(10) DEFAULT 0 COMMENT '新注册',
  `dau` int(10) DEFAULT 0 COMMENT '日活跃',
  `pdau` int(10) DEFAULT 0 COMMENT '付费dau',
  `pdau_move` int(11) DEFAULT 0 COMMENT '迁服付费dau',
  `paytotal` double(11,2) DEFAULT 0 COMMENT '付费总值',
  `payusers` int(10) DEFAULT 0 COMMENT '付费用户',
  `firstpayusers` int(10) DEFAULT 0 COMMENT '首冲用户',
  `newTotalPay` double(11,2) DEFAULT 0 COMMENT '首冲付费金额',
  `r1` int(10) DEFAULT 0 COMMENT '次日留存',
  `r3` int(10) DEFAULT 0,
  `r7` int(10) DEFAULT 0,
  `r15` int(10) DEFAULT 0,
  `r30` int(10) DEFAULT 0,
  PRIMARY KEY (`date`,`pf`,`country`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT '运营数据统计日的数据';

CREATE TABLE `pay_ublevel_rate_pf_country_referrer_appVersion` (
  `sid` int(11) NOT NULL DEFAULT '0',
  `date` int(8) NOT NULL DEFAULT '0',
	`buildingLv` int(11) NOT NULL DEFAULT '0',
  `pf` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `referrer` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `appVersion` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `buildAll` bigint(20) NOT NULL DEFAULT '0',
  `sum` int(11) DEFAULT NULL,
  `count` int(11) DEFAULT NULL,
  PRIMARY KEY (`sid`,`date`,`buildingLv`,`country`,`pf`,`appVersion`,`referrer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pay_analyze_pf_country_referrer_new` (
  `sid` int(11) NOT NULL DEFAULT '0',
  `date` int(8) NOT NULL DEFAULT '0',
  `pf` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `referrer` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` int(5) NOT NULL DEFAULT '0',
  `payTotle` double(11,2) DEFAULT NULL,
  `payUsers` int(11) DEFAULT NULL,
  `payTimes` int(11) DEFAULT NULL,
  `dau` int(11) DEFAULT NULL,
  `firstPay` int(11) DEFAULT NULL,
  PRIMARY KEY (`sid`,`date`,`pf`,`country`,`referrer`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `pay_ratio_analyze_pf_country_referrer_appVersion` (
  `sid` int(11) NOT NULL,
  `date` int(8) NOT NULL DEFAULT '0',
  `pf` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `referrer` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `appVersion` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `dau` int(11) DEFAULT NULL,
  `newTotalPay` double(11,2) DEFAULT NULL,
  `firstDayPay` int(11) DEFAULT NULL COMMENT '人数',
  `regDevice` int(11) DEFAULT NULL,
  `oldPayDAU` int(11) DEFAULT NULL,
  PRIMARY KEY (`sid`,`date`,`pf`,`country`,`referrer`,`appVersion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci