-- ==============================================================================
-- Pikachu-Enhanced Automated Database Schema & Seed Data
-- ==============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `pikachu` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pikachu`;

-- 1. Users authentication table
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(66) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

INSERT INTO `users` (`id`, `username`, `password`, `level`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 1),
(2, 'pikachu', '670b14728ad9902aecba32e22fa4f6bd', 2),
(3, 'test', 'e10adc3949ba59abbe56e057f20f883e', 3);

-- 2. Member data table
DROP TABLE IF EXISTS `member`;
CREATE TABLE `member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(66) NOT NULL,
  `pw` varchar(128) NOT NULL,
  `sex` char(10) NOT NULL,
  `phonenum` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

INSERT INTO `member` (`id`, `username`, `pw`, `sex`, `phonenum`, `address`, `email`) VALUES
(1, 'vince', 'e10adc3949ba59abbe56e057f20f883e', 'boy', '18626545453', 'chain', 'vince@pikachu.com'),
(2, 'allen', 'e10adc3949ba59abbe56e057f20f883e', 'boy', '13676767767', 'nba 76', 'allen@pikachu.com'),
(3, 'kobe', 'e10adc3949ba59abbe56e057f20f883e', 'boy', '15988767673', 'nba lakes', 'kobe@pikachu.com'),
(4, 'grady', 'e10adc3949ba59abbe56e057f20f883e', 'boy', '13676765545', 'nba hs', 'grady@pikachu.com'),
(5, 'kevin', 'e10adc3949ba59abbe56e057f20f883e', 'boy', '13677676754', 'Oklahoma City Thunder', 'kevin@pikachu.com'),
(6, 'lucy', 'e10adc3949ba59abbe56e057f20f883e', 'girl', '12345678922', 'usa', 'lucy@pikachu.com'),
(7, 'lili', 'e10adc3949ba59abbe56e057f20f883e', 'girl', '18656565545', 'usa', 'lili@pikachu.com');

-- 3. Stored XSS message table
DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='stored_xss_board' AUTO_INCREMENT=1;

INSERT INTO `message` (`id`, `content`, `time`) VALUES
(1, '欢迎来到 Pikachu-Enhanced 靶场交流留言板！', NOW()),
(2, '安全测试请严格遵守靶场测试规程。', NOW());

-- 4. Blind XSS feedback table
DROP TABLE IF EXISTS `xssblind`;
CREATE TABLE `xssblind` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `content` text NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

INSERT INTO `xssblind` (`id`, `time`, `content`, `name`) VALUES
(1, NOW(), '系统管理员将定期在此审核用户反馈。', '系统管理员');

-- 5. HTTP Header injection logs table
DROP TABLE IF EXISTS `httpinfo`;
CREATE TABLE `httpinfo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) NOT NULL,
  `ipaddress` varchar(255) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  `httpaccept` varchar(255) NOT NULL,
  `remoteport` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- 6. CTF Flag vault table
DROP TABLE IF EXISTS `flag_vault`;
CREATE TABLE `flag_vault` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `flag_name` varchar(66) NOT NULL,
  `flag_val` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

INSERT INTO `flag_vault` (`id`, `flag_name`, `flag_val`) VALUES
(1, 'sqli_flag', 'flag{Pikachu_SQLi_Database_Vault_Extracted}'),
(2, 'xxe_flag', 'flag{Pikachu_XXE_Local_Entity_Disclosure}'),
(3, 'deser_flag', 'flag{Pikachu_Deserialization_POP_Chain_Executed}'),
(4, 'idor_flag', 'flag{Pikachu_IDOR_Privilege_Escalation_Master}'),
(5, 'jwt_flag', 'flag{Pikachu_JWT_Modern_Auth_Token_Bypassed}');

-- 7. Anti-replay request logs table (Frontend Encryption Lab)
DROP TABLE IF EXISTS `requests`;
CREATE TABLE `requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `requestID` varchar(255) NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `requestID` (`requestID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

SET FOREIGN_KEY_CHECKS = 1;
