CREATE database IF NOT EXISTS `snapshot_global`;

CREATE TABLE IF NOT EXISTS `snapshot_global`.`account_new` (
  `gameUid` bigint(20),
  `gameUserLevel` int(11)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `snapshot_global`.`helpinfo` (
  `sid` smallint(5),
  `gameUid` bigint(20),
  `fgameUid` bigint(20)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



CREATE TABLE IF NOT EXISTS `snapshot_global`.`account_new_sid` (
  `sid` smallint(5),
  `gameUid` bigint(20),
  `gameUserLevel` int(11)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
CREATE TABLE IF NOT EXISTS `snapshot_global`.`account_new_fb` (
  `gameUid` bigint(20),
  `facebookAccount` varchar(200)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `snapshot_global`.`userprofile` (
  `uid` bigint(20),
  `level` int(10),
  `payTotal` bigint(20),
  `regTime` bigint(20),
  `serverId` smallint(5)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `snapshot_global`.`invitee_move_20150101_0114` (
  `uid` bigint(20),
  `currSid` smallint(5),
  `regSid` smallint(5),
  `userLevel` int(10),
  `payTotal` int(10),
  `mbLevel` int(10)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `snapshot_global`.`sign_in_feed` (
  `date` int(8),
  `uid` varchar(255),
  `type` int(40),
  `feed` varchar(40),
  `time` bigint(20)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
