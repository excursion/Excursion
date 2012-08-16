DROP TABLE IF EXISTS `polls`;
CREATE TABLE IF NOT EXISTS `polls` (
  `id` mediumint(8) NOT NULL auto_increment,
  `type` varchar(10) collate utf8_unicode_ci NOT NULL default 'index',
  `code` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `creationdate` int(11) NOT NULL default '0',
  `text` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `creationdate` (`creationdate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `polls_options`;
CREATE TABLE IF NOT EXISTS `polls_options` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `pollid` mediumint(8) unsigned NOT NULL default '0',
  `text` varchar(128) collate utf8_unicode_ci NOT NULL default '',
  `count` mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `polls_voters`;
CREATE TABLE IF NOT EXISTS `polls_voters` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `pollid` mediumint(8) NOT NULL default '0',
  `userid` mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pollid` (`pollid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;