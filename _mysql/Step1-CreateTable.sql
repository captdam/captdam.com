DROP TABLE IF EXISTS `BW_Config`;
CREATE TABLE `BW_Config` (
  `Key` varchar(255) NOT NULL,
  `Value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`Key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `BW_Config` VALUES ('Closed','');


DROP TABLE IF EXISTS `BW_Log`;
CREATE TABLE `BW_Log` (
  `TransactionID` int(11) NOT NULL AUTO_INCREMENT,
  `Time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Username` varchar(16) NOT NULL,
  `UserIP` varchar(15) NOT NULL,
  `RequestPage` varchar(255) NOT NULL,
  `PHPID` varchar(255) NOT NULL,
  `SessionID` varchar(255) NOT NULL,
  PRIMARY KEY (`TransactionID`)
) ENGINE=InnoDB AUTO_INCREMENT=85878 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `BW_Sitemap`;
CREATE TABLE `BW_Sitemap` (
  `URL` char(255) NOT NULL,
  `MIME` varchar(255) NOT NULL DEFAULT 'text/plain',
  `Title` varchar(255) NOT NULL DEFAULT 'Bearweb page',
  `Keywords` varchar(255) NOT NULL DEFAULT '',
  `Description` varchar(4096) NOT NULL DEFAULT '',
  `Category` varchar(255) NOT NULL DEFAULT '@Alone',
  `Author` varchar(255) NOT NULL DEFAULT '@Author',
  `TemplateMain` varchar(255) NOT NULL DEFAULT 'object',
  `TemplateSub` varchar(255) NOT NULL DEFAULT 'direct',
  `Data` longtext NOT NULL,
  `Binary` longblob NOT NULL,
  `JSON` longtext NOT NULL,
  `Copyright` varchar(255) NOT NULL DEFAULT 'All rights reserved',
  `CreateTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastModify` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Version` int(10) unsigned NOT NULL DEFAULT '0',
  `Status` char(1) NOT NULL DEFAULT 'O' COMMENT 'Open to all:\nO: OK\nC: Construction\nD: Deprecated\nS: Special (Hidden in sitemap and searching result)\n\nOpen to all, not a page:\nR: Redirected (Permanently 301)\nr: Redirected (Temp 302)\n\nPermission need:\nA: Auth need (Permission need, open to those who has permission, with a notice);\nP: Pending (Open to the author only, with a notice);',
  PRIMARY KEY (`URL`),
  UNIQUE KEY `URL_UNIQUE` (`URL`),
  KEY `MIME` (`MIME`),
  KEY `Category` (`Category`),
  KEY `Author` (`Author`),
  KEY `Status` (`Status`),
  KEY `LastModify` (`LastModify`),
  FULLTEXT KEY `Data` (`Title`,`Keywords`,`Description`,`Data`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TRIGGER `BW_Sitemap_BEFORE_INSERT` BEFORE INSERT ON `BW_Sitemap` FOR EACH ROW
BEGIN
    IF NEW.`Data` IS NULL THEN
		SET NEW.`Data` = "";
	END IF;
    IF NEW.`Binary` IS NULL THEN
		SET NEW.`Binary` = "";
	END IF;
    IF NEW.`JSON` IS NULL THEN
		SET NEW.`JSON` = "{}";
	END IF;
END;


DROP TABLE IF EXISTS `BW_User`;
CREATE TABLE `BW_User` (
  `Username` char(16) NOT NULL,
  `Nickname` char(16) NOT NULL,
  `Group` char(16) NOT NULL,
  `Password` char(32) NOT NULL,
  `LastActiveTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `RegisterIP` char(15) NOT NULL,
  `RegisterTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Email` varchar(128) DEFAULT NULL,
  `Data` longtext,
  `Photo` blob,
  PRIMARY KEY (`Username`),
  UNIQUE KEY `Username_UNIQUE` (`Username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;