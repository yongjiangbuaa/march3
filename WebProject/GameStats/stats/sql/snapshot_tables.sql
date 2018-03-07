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

CREATE TABLE IF NOT EXISTS `stat_reg` (
  `date` int(8),
  `uid` varchar(40),
  `time` bigint(20),
  `pf` varchar(20),
  `pfId` varchar(40),
  `referrer` varchar(100),
  `country` varchar(10) COMMENT 'lookup',
  `type` int(5),
  `ip` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_reg` (
  `uid` varchar(40),
  `appVersion` varchar(40)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_login` (
  `date` int(8),
  `uid` varchar(40),
  `time` bigint(20),
  `disconnect` bigint(20),
  `ip` varchar(20),
  `level` int(10),
  `castlelevel` int(10)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_login_full` (
  `date` int(8),
  `regDate` int(8),
  `uid` varchar(40),
  `time` bigint(20),
  `disconnect` bigint(20),
  `ip` varchar(20),
  `level` int(10),
  `castlelevel` int(10),
  `payTotal` bigint(20),
  `deviceId` varchar(255),
  `regTime`  bigint(20),
  `pf` varchar(20),
  `country` varchar(10)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `userprofile` (
  `date` int(8),
  `uid` varchar(255),
  `regTime` bigint(20),
  `deviceId` varchar(255),
  `allianceId` varchar(255)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `userprofile_full` (
  `date` int(8),
  `uid` varchar(255),
  `level` int(10),
  `buildingLv` int(11),
  `exp` int(11),
  `gold` bigint(20),
  `paidGold` bigint(20),
  `payTotal` bigint(20),
  `pic` varchar(40),
  `picVer` int(11),
  `allianceId` varchar(255),
  `worldPoint` int(11),
  `deviceId` varchar(255),
  `gaid` varchar(40),
  `lang` varchar(40),
  `appVersion` varchar(40),
  `gmFlag` int(11),
  `regTime` bigint(20),
  `serverId` int(11),
  `banTime` bigint(20),
  `rewardForGoldDecr` int(11),
  `lastOnlineTime` bigint(20)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `paylog` (
  `date` int(8),
  `uid` varchar(40),
  `orderId` varchar(200),
  `pf` varchar(20) COMMENT 'lookup',
  `productId` varchar(10) COMMENT 'lookup',
  `orderInfo` varchar(100),
  `time` bigint(20),
  `currency` int(10),
  `spend` double(10,2),
  `paid` double(10,2),
  `status` int(4),
  `payLevel` int(10),
  `buildingLv` int(11)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- CREATE TABLE IF NOT EXISTS `gold_cost_record` (
--  `date` int(8),
--  `userId` varchar(255),
--  `goldType` int(11),
--  `type` int(11),
--  `param1` int(11),
--  `param2` int(11),
--  `originalGold` bigint(20),
--  `cost` int(11),
--  `remainGold` bigint(11),
--  `time` bigint(20)
-- ) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

 CREATE TABLE IF NOT EXISTS `goods_cost_record` (
  `time` bigint(20),
  `userId` varchar(255),
  `itemId` int(11),
  `type` int(11),
  `param1` int(11),
  `param2` int(11),
  `original` bigint(20),
  `cost` int(11),
  `remain` bigint(11)
 ) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `gold_cost_record_full` (
  `date` int(8),
  `userId` varchar(255),
  `goldType` int(11),
  `type` int(11),
  `param1` int(11),
  `param2` int(11),
  `originalGold` bigint(20),
  `cost` int(11),
  `remainGold` bigint(11),
  `time` bigint(20),
  `payTotal` bigint(20),
  `gmFlag` int(11),
  `level` int(10),
  `gold` bigint(20),
  `paidGold` bigint(20),
  `country` varchar(40) COMMENT 'lookup',
  `allianceId` varchar(40),
  `pf` varchar(40) COMMENT 'lookup',
  `lang` varchar(40) COMMENT 'lookup',
  `regTime` bigint(20),
  `deviceId` varchar(255),
  `serverId` int(11),
  `gmail` varchar(255)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 CREATE TABLE IF NOT EXISTS `account_new_full` (
  `time` bigint(20),
  `uid` varchar(100),
  `server` int(4),
  `deviceId` varchar(200),
  `facebookAccount` varchar(200),
  `pf` varchar(20) COMMENT 'lookup',
  `lastTime` bigint(20)
 ) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `hot_goods_cost_record` (
  `uuid` varchar(255),
  `uid` varchar(255),
  `goodsId` varchar(20),
  `priceType` int(10),
  `price` int(10),
  `buyTime` bigint(20)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_phone` (
  `uid` varchar(255),
  `model` varchar(255),
  `version` varchar(255),
  `width` int(11),
  `height` int(11)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `logrecord` (
  `date` int(8),
  `uid` varchar(50),
  `user` varchar(200),
  `timeStamp` bigint(20),
  `category` int(11),
  `type` int(11),
  `param1` int(11),
  `param2` int(11),
  `param3` int(11),
  `param4` int(11),
  `data1` varchar(255),
  `data2` varchar(255),
  `data3` varchar(255),
  `data4` varchar(255)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `logrecord_alliance` (
  `date` int(8),
  `uid` varchar(50),
  `user` varchar(200),
  `timeStamp` bigint(20),
  `category` int(11),
  `type` int(11),
  `param1` int(11),
  `param2` int(11),
  `param3` int(11),
  `param4` int(11),
  `data1` varchar(255),
  `data2` varchar(255),
  `data3` varchar(255),
  `data4` varchar(255)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `logrecord_war` (
  `date` int(8),
  `uid` varchar(50),
  `user` varchar(200),
  `timeStamp` bigint(20),
  `category` int(11),
  `type` int(11),
  `param1` int(11),
  `param2` int(11),
  `param3` int(11),
  `param4` int(11),
  `data1` varchar(255),
  `data2` varchar(255),
  `data3` varchar(255),
  `data4` varchar(255)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_14day_login` (
  `date` int(8),
  `uid` varchar(255),
  `type` int(4),
  `day` int(10),
  `lastRewardTime` bigint(20),
  `vipRewardTime` bigint(20),
  `reward` varchar(255)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `hot_info_before_refresh` (
  `date` int(8),
  `uid` varchar(255),
  `goodsId` varchar(20),
  `itemId` varchar(20),
  `priceType` int(10),
  `price` int(10),
  `num` int(10),
  `refreshTime` bigint(20),
  `gold` int(10)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `stat_tutorial` (
  `date` int(8),
  `uid` varchar(40),
  `tutorial` bigint(20),
  `time` bigint(20),
  `appVersion` varchar(40)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

