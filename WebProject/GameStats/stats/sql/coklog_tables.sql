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

CREATE TABLE if not exists `logaction` (
  `uid` bigint(255) NOT NULL,
  `name` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `action` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'lookup',
  `time` bigint(20) DEFAULT '0',
  `costtime` int(10) NOT NULL DEFAULT '0',
  `param` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `result` char(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `wood` int(11) NOT NULL DEFAULT '0',
  `food` int(11) NOT NULL DEFAULT '0',
  `stone` int(11) NOT NULL DEFAULT '0',
  `iron` int(11) NOT NULL DEFAULT '0'
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE if not exists `logaction_v3` (
  `uid` bigint(255) NOT NULL,
  `name` char(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `action` char(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'lookup',
  `time` bigint(20) DEFAULT '0',
  `costtime` int(10) NOT NULL DEFAULT '0',
  `param` text,
  `result` text,
  `resource` text
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE if not exists `arm` (
  `datec` char(16),
  `timec` char(16),
  `uid` bigint(255),
  `armid` int(10),
  `type` char(16) COMMENT 'lookup',
  `before` int(11),
  `after` int(11),
  `act` char(16) COMMENT 'lookup',
  `time` bigint(20)
) ENGINE=BRIGHTHOUSE DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
