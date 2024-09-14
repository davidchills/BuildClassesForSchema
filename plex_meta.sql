/*
 Navicat Premium Dump SQL

 Source Server         : daveh@localhost
 Source Server Type    : MySQL
 Source Server Version : 80026 (8.0.26)
 Source Host           : localhost:3306
 Source Schema         : plex_meta

 Target Server Type    : MySQL
 Target Server Version : 80026 (8.0.26)
 File Encoding         : 65001

 Date: 14/09/2024 16:34:42
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for actor
-- ----------------------------
DROP TABLE IF EXISTS `actor`;
CREATE TABLE `actor` (
  `actor_id` int unsigned NOT NULL AUTO_INCREMENT,
  `actor_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `actor_score` smallint unsigned DEFAULT '0',
  `actor_url` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `actor_comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_active` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Y',
  `added_date` datetime DEFAULT NULL,
  `private_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`actor_id`),
  UNIQUE KEY `ACTOR_UIDX1` (`actor_name`(76)) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=898 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for actor_picture
-- ----------------------------
DROP TABLE IF EXISTS `actor_picture`;
CREATE TABLE `actor_picture` (
  `actor_id` int unsigned NOT NULL AUTO_INCREMENT,
  `picture_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `picture_width` smallint unsigned DEFAULT '0',
  `picture_height` smallint unsigned DEFAULT '0',
  PRIMARY KEY (`actor_id`),
  CONSTRAINT `ACTOR_PICTURE_FK1` FOREIGN KEY (`actor_id`) REFERENCES `actor` (`actor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=898 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for class_logs
-- ----------------------------
DROP TABLE IF EXISTS `class_logs`;
CREATE TABLE `class_logs` (
  `log_id` int unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `log_level` int unsigned NOT NULL DEFAULT '100',
  `user_id` int unsigned NOT NULL,
  `movie_id` int unsigned DEFAULT NULL,
  `log_desc` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `log_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `CLASS_LOGS_IDX1` (`log_name`) USING BTREE,
  KEY `CLASS_LOGS_IDX2` (`log_level`) USING BTREE,
  KEY `CLASS_LOGS_IDX3` (`user_id`) USING BTREE,
  KEY `CLASS_LOGS_IDX4` (`movie_id`),
  CONSTRAINT `CLASS_LOGS_FK1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4183 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for movie
-- ----------------------------
DROP TABLE IF EXISTS `movie`;
CREATE TABLE `movie` (
  `movie_id` int unsigned NOT NULL AUTO_INCREMENT,
  `title` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_title` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `studio` int unsigned DEFAULT '1',
  `collection` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rating` int unsigned DEFAULT '1',
  `format` int unsigned DEFAULT '1',
  `release_date` date DEFAULT NULL,
  `desc_short` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `desc_long` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `movie_score` tinyint unsigned DEFAULT '0',
  `original_url` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_active` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Y',
  `added_date` datetime DEFAULT NULL,
  `private_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`movie_id`),
  UNIQUE KEY `MOVIE_PK` (`movie_id`) USING BTREE,
  UNIQUE KEY `MOVIE_IDX1` (`title`(56)) USING BTREE,
  UNIQUE KEY `MOVIE_IDX2` (`sort_title`(56)) USING BTREE,
  KEY `MOVIE_FK1` (`studio`),
  KEY `MOVIE_FK2` (`rating`),
  KEY `MOVIE_FK3` (`format`),
  CONSTRAINT `MOVIE_FK1` FOREIGN KEY (`studio`) REFERENCES `movie_studio` (`studio_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `MOVIE_FK2` FOREIGN KEY (`rating`) REFERENCES `movie_rating` (`rating_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `MOVIE_FK3` FOREIGN KEY (`format`) REFERENCES `movie_format` (`format_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for movie_actor_xref
-- ----------------------------
DROP TABLE IF EXISTS `movie_actor_xref`;
CREATE TABLE `movie_actor_xref` (
  `movie_id` int unsigned NOT NULL,
  `actor_id` int unsigned NOT NULL,
  PRIMARY KEY (`movie_id`,`actor_id`),
  UNIQUE KEY `MOVIE_ACTOR_XREF_PK` (`movie_id`,`actor_id`) USING BTREE,
  KEY `MOVIE_ACTOR_XREF_IDX1` (`movie_id`) USING BTREE,
  KEY `MOVIE_ACTOR_XREF_IDX2` (`actor_id`) USING BTREE,
  CONSTRAINT `MOVIE_ACTOR_XREF_FK1` FOREIGN KEY (`movie_id`) REFERENCES `movie` (`movie_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `MOVIE_ACTOR_XREF_FK2` FOREIGN KEY (`actor_id`) REFERENCES `actor` (`actor_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for movie_chapters
-- ----------------------------
DROP TABLE IF EXISTS `movie_chapters`;
CREATE TABLE `movie_chapters` (
  `chapter_id` int unsigned NOT NULL AUTO_INCREMENT,
  `movie_id` int unsigned NOT NULL,
  `start_hour` int unsigned NOT NULL DEFAULT '0',
  `start_minute` int unsigned NOT NULL DEFAULT '0',
  `start_second` int unsigned NOT NULL DEFAULT '0',
  `start_micro` char(7) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0000000',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`chapter_id`),
  KEY `CHAPTERS_FK1` (`movie_id`) USING BTREE,
  CONSTRAINT `CHAPTER_FK1` FOREIGN KEY (`movie_id`) REFERENCES `movie` (`movie_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1411 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for movie_format
-- ----------------------------
DROP TABLE IF EXISTS `movie_format`;
CREATE TABLE `movie_format` (
  `format_id` int unsigned NOT NULL AUTO_INCREMENT,
  `format_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` smallint unsigned DEFAULT '0',
  `is_active` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Y',
  PRIMARY KEY (`format_id`),
  UNIQUE KEY `MOVIE_FORMAT_PK` (`format_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for movie_genre
-- ----------------------------
DROP TABLE IF EXISTS `movie_genre`;
CREATE TABLE `movie_genre` (
  `genre_id` int unsigned NOT NULL AUTO_INCREMENT,
  `genre_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Y',
  `opt_group` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Other',
  PRIMARY KEY (`genre_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for movie_genre_xref
-- ----------------------------
DROP TABLE IF EXISTS `movie_genre_xref`;
CREATE TABLE `movie_genre_xref` (
  `movie_id` int unsigned NOT NULL,
  `genre_id` int unsigned NOT NULL,
  PRIMARY KEY (`movie_id`,`genre_id`),
  UNIQUE KEY `MOVIE_GENRE_XREF_PK` (`movie_id`,`genre_id`) USING BTREE,
  KEY `MOVIE_GENRE_XREF_IDX1` (`movie_id`) USING BTREE,
  KEY `MOVIE_GENRE_XREF_IDX2` (`genre_id`) USING BTREE,
  CONSTRAINT `MOVIE_GENRE_XREF_FK1` FOREIGN KEY (`movie_id`) REFERENCES `movie` (`movie_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `MOVIE_GENRE_XREF_FK2` FOREIGN KEY (`genre_id`) REFERENCES `movie_genre` (`genre_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for movie_poster
-- ----------------------------
DROP TABLE IF EXISTS `movie_poster`;
CREATE TABLE `movie_poster` (
  `movie_id` int unsigned NOT NULL AUTO_INCREMENT,
  `poster_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `poster_width` smallint unsigned DEFAULT '0',
  `poster_height` smallint unsigned DEFAULT '0',
  PRIMARY KEY (`movie_id`),
  CONSTRAINT `MOVIE_POSTER_FK1` FOREIGN KEY (`movie_id`) REFERENCES `movie` (`movie_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1316 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for movie_rating
-- ----------------------------
DROP TABLE IF EXISTS `movie_rating`;
CREATE TABLE `movie_rating` (
  `rating_id` int unsigned NOT NULL AUTO_INCREMENT,
  `rating_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `is_active` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Y',
  PRIMARY KEY (`rating_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for movie_studio
-- ----------------------------
DROP TABLE IF EXISTS `movie_studio`;
CREATE TABLE `movie_studio` (
  `studio_id` int unsigned NOT NULL AUTO_INCREMENT,
  `studio_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `studio_short_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sort_order` smallint unsigned DEFAULT '0',
  `movie_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `directory_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Y',
  PRIMARY KEY (`studio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_status` enum('DISABLED','NORMAL') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'DISABLED',
  `password` blob,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- View structure for movie_chapters_v
-- ----------------------------
DROP VIEW IF EXISTS `movie_chapters_v`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `movie_chapters_v` AS select `movie_chapters`.`chapter_id` AS `chapter_id`,`movie_chapters`.`movie_id` AS `movie_id`,concat(lpad(`movie_chapters`.`start_hour`,2,'0'),':',lpad(`movie_chapters`.`start_minute`,2,'0'),':',lpad(`movie_chapters`.`start_second`,2,'0'),'.',lpad(`movie_chapters`.`start_micro`,7,'0')) AS `time_mark`,`movie_chapters`.`description` AS `description` from `movie_chapters` order by `movie_chapters`.`movie_id`,`movie_chapters`.`start_hour`,`movie_chapters`.`start_minute`,`movie_chapters`.`start_second`;

SET FOREIGN_KEY_CHECKS = 1;
