/*
MySQL Data Transfer
Source Host: localhost
Source Database: cfblog
Target Host: localhost
Target Database: cfblog
Date: 2010-3-6 0:49:49
*/

SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for cfblog_blogs
-- ----------------------------
DROP TABLE IF EXISTS `cfblog_blogs`;
CREATE TABLE `cfblog_blogs` (
  `eid` int(32) unsigned NOT NULL auto_increment,
  `title` text NOT NULL,
  `content` mediumtext NOT NULL,
  `createat` datetime NOT NULL,
  `updateat` datetime NOT NULL,
  `category` int(32) unsigned NOT NULL,
  `password` char(32) default NULL,
  `r_count` int(20) unsigned NOT NULL default '0',
  `c_count` int(20) unsigned NOT NULL default '0',
  `c_allow` tinyint(1) NOT NULL default '1',
  `visible` tinyint(1) NOT NULL default '1',
  `on_top` tinyint(1) NOT NULL default '0',
  `draft` tinyint(1) NOT NULL default '0',
  `status` int(32) unsigned NOT NULL default '0',
  PRIMARY KEY  (`eid`),
  KEY `category` (`category`),
  CONSTRAINT `cfblog_blogs_ibfk_1` FOREIGN KEY (`category`) REFERENCES `cfblog_categories` (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cfblog_categories
-- ----------------------------
DROP TABLE IF EXISTS `cfblog_categories`;
CREATE TABLE `cfblog_categories` (
  `eid` int(32) unsigned NOT NULL auto_increment,
  `name` text NOT NULL,
  `description` text,
  `seq` int(20) unsigned NOT NULL,
  PRIMARY KEY  (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cfblog_comments
-- ----------------------------
DROP TABLE IF EXISTS `cfblog_comments`;
CREATE TABLE `cfblog_comments` (
  `eid` int(32) unsigned NOT NULL auto_increment,
  `blogid` int(32) unsigned NOT NULL,
  `content` text NOT NULL,
  `reply` text,
  `nick_name` text NOT NULL,
  `homepage` text,
  `email` text,
  `pub_date` datetime NOT NULL,
  `IP` varchar(15) NOT NULL,
  `status` int(32) unsigned NOT NULL default '0',
  PRIMARY KEY  (`eid`),
  KEY `blogid` (`blogid`),
  CONSTRAINT `cfblog_comments_ibfk_1` FOREIGN KEY (`blogid`) REFERENCES `cfblog_blogs` (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cfblog_linkgroup
-- ----------------------------
DROP TABLE IF EXISTS `cfblog_linkgroup`;
CREATE TABLE `cfblog_linkgroup` (
  `eid` int(32) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cfblog_links
-- ----------------------------
DROP TABLE IF EXISTS `cfblog_links`;
CREATE TABLE `cfblog_links` (
  `eid` int(32) unsigned NOT NULL auto_increment,
  `url` text NOT NULL,
  `name` text NOT NULL,
  `logo` text,
  `groupid` int(32) unsigned NOT NULL,
  PRIMARY KEY  (`eid`),
  KEY `FK_cfblog_links_linkgroup` (`groupid`),
  CONSTRAINT `FK_cfblog_links_linkgroup` FOREIGN KEY (`groupid`) REFERENCES `cfblog_linkgroup` (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cfblog_photos
-- ----------------------------
DROP TABLE IF EXISTS `cfblog_photos`;
CREATE TABLE `cfblog_photos` (
  `eid` int(32) unsigned NOT NULL auto_increment,
  `originalname` varchar(255) NOT NULL,
  `storename` char(64) NOT NULL,
  `uploaddate` datetime NOT NULL,
  `album` int(32) unsigned NOT NULL default '0',
  PRIMARY KEY  (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for cfblog_users
-- ----------------------------
DROP TABLE IF EXISTS `cfblog_users`;
CREATE TABLE `cfblog_users` (
  `eid` int(32) unsigned NOT NULL auto_increment,
  `loginid` varchar(32) NOT NULL,
  `pwd` char(32) NOT NULL,
  `regdate` datetime NOT NULL,
  `nickname` varchar(255) default NULL,
  `fullname` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  `homepage` varchar(255) default NULL,
  `authority` int(32) NOT NULL default '0',
  `role` int(32) NOT NULL default '0',
  PRIMARY KEY  (`eid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records 
-- ----------------------------
INSERT INTO `cfblog_categories` VALUES ('1', '默认分类', '描述', '0');
INSERT INTO `cfblog_categories` VALUES ('2', '旧版文章', '导入的旧版文章', '0');
INSERT INTO `cfblog_linkgroup` VALUES ('1', '文字链接');
INSERT INTO `cfblog_linkgroup` VALUES ('2', '图片链接');
INSERT INTO `cfblog_linkgroup` VALUES ('3', '我的作品');
INSERT INTO `cfblog_linkgroup` VALUES ('4', '实用推荐');
INSERT INTO `cfblog_links` VALUES ('3', 'http://www.yyfc.com', 'www.yyfc.com', null, '3');
INSERT INTO `cfblog_links` VALUES ('4', 'http://www.damidai.com', 'www.damidai.com', null, '3');
INSERT INTO `cfblog_links` VALUES ('5', 'http://shop.damidai.com', 'shop.damidai.com', null, '3');
INSERT INTO `cfblog_links` VALUES ('6', 'http://www.cf-blog.net', 'www.cf-blog.net', null, '3');
INSERT INTO `cfblog_links` VALUES ('7', 'http://www.cf-blog.net', '阿君&阿肠', null, '1');
INSERT INTO `cfblog_links` VALUES ('8', 'http://cf-blog.net/', '图片连接测试', '/upload/logos/1218080370_0.gif', '2');
INSERT INTO `cfblog_links` VALUES ('9', 'http://www.yoho.cn', 'www.yoho.cn', null, '4');
INSERT INTO `cfblog_links` VALUES ('10', 'http://www.zcool.com.cn', 'www.zcool.com.cn', null, '4');
INSERT INTO `cfblog_links` VALUES ('11', 'http://pages.sccnn.com', 'pages.sccnn.com', null, '4');
INSERT INTO `cfblog_links` VALUES ('12', 'http://www.sj63.com', 'www.sj63.com', null, '4');
INSERT INTO `cfblog_links` VALUES ('13', 'http://jigsaw.w3.org/css-validator/#validate-by-uri', 'W3C CSS 验证', null, '4');
