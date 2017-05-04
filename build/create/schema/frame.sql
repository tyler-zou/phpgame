DROP TABLE IF EXISTS `profile`;
CREATE TABLE `profile` (
  `userId` int(20) unsigned NOT NULL,
  `level` int(10) unsigned NOT NULL DEFAULT 1,
  `exp` int(10) unsigned NOT NULL DEFAULT 0,
  `money` int(10) unsigned NOT NULL DEFAULT 0,
  `energy` int(10) unsigned NOT NULL DEFAULT 0,
  `energyLimit` int(10) unsigned NOT NULL DEFAULT 0,
  `lastEnergyChargedTime` int(10) unsigned NOT NULL,
  `lastLoginTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` (
  `itemId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(20) unsigned NOT NULL,
  `itemDefId` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `count` tinyint(10) unsigned NOT NULL,
  `expireTime` int(10) unsigned NOT NULL,
  `updateTime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`itemId`),
  INDEX idxUserId USING BTREE (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;