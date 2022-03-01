CREATE DATABASE IF NOT EXISTS `{$NAMESPACE}_vacation` /*!40100 DEFAULT CHARACTER SET {$CHARSET} COLLATE {$COLLATE_TEXT} */;

USE `{$NAMESPACE}_vacation`;

 SET NAMES utf8 ;

 SET character_set_client = {$CHARSET} ;

CREATE TABLE IF NOT EXISTS `vacation_spenthours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `spentHours` int(10) NOT NULL,
  `dateWhenUsed` int(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={$CHARSET} COLLATE={$COLLATE_TEXT};

CREATE TABLE IF NOT EXISTS `vacation_earnedhours` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `earnedHours` float(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET={$CHARSET} COLLATE={$COLLATE_TEXT};

CREATE TABLE IF NOT EXISTS `vacation_vacationrules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `yearsOfSeniority` int(10) NOT NULL,
  `coefficient` float(5) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET={$CHARSET} COLLATE={$COLLATE_TEXT};
