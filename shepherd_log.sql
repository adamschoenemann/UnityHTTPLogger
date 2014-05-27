-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 27, 2014 at 08:53 AM
-- Server version: 5.5.16
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `shepherd_log`
--

-- --------------------------------------------------------

--
-- Table structure for table `entries`
--

CREATE TABLE IF NOT EXISTS `entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scene_id` bigint(20) unsigned NOT NULL,
  `event` varchar(128) COLLATE latin1_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `loggable_id` bigint(20) unsigned NOT NULL,
  `time` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `scene_id` (`scene_id`),
  KEY `loggable_id` (`loggable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `float_data`
--

CREATE TABLE IF NOT EXISTS `float_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) unsigned NOT NULL,
  `key` varchar(128) NOT NULL,
  `value` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entry_id` (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `int_data`
--

CREATE TABLE IF NOT EXISTS `int_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) unsigned NOT NULL,
  `key` varchar(128) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entry_id` (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `loggables`
--

CREATE TABLE IF NOT EXISTS `loggables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `scene_id` bigint(20) unsigned NOT NULL,
  `name` varchar(56) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `scene_id` (`scene_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `quaternion`
--

CREATE TABLE IF NOT EXISTS `quaternion` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) unsigned NOT NULL,
  `key` varchar(128) NOT NULL,
  `x` float NOT NULL,
  `y` float NOT NULL,
  `z` float NOT NULL,
  `w` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entry_id` (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scenes`
--

CREATE TABLE IF NOT EXISTS `scenes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint(20) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `time` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `app_version` int(11) NOT NULL,
  `MAC` char(64) COLLATE latin1_general_ci NOT NULL,
  `ip` char(64) COLLATE latin1_general_ci NOT NULL,
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `string_data`
--

CREATE TABLE IF NOT EXISTS `string_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) unsigned NOT NULL,
  `key` varchar(512) NOT NULL,
  `value` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entry_id` (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `vector3`
--

CREATE TABLE IF NOT EXISTS `vector3` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entry_id` bigint(20) unsigned NOT NULL,
  `key` varchar(128) NOT NULL,
  `x` float NOT NULL,
  `y` float NOT NULL,
  `z` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `entry_id` (`entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `entries`
--
ALTER TABLE `entries`
  ADD CONSTRAINT `entries_ibfk_1` FOREIGN KEY (`loggable_id`) REFERENCES `loggables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `entries_ibfk_2` FOREIGN KEY (`scene_id`) REFERENCES `scenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `float_data`
--
ALTER TABLE `float_data`
  ADD CONSTRAINT `float_data_ibfk_1` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `int_data`
--
ALTER TABLE `int_data`
  ADD CONSTRAINT `int_data_ibfk_2` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `loggables`
--
ALTER TABLE `loggables`
  ADD CONSTRAINT `loggables_ibfk_1` FOREIGN KEY (`scene_id`) REFERENCES `scenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quaternion`
--
ALTER TABLE `quaternion`
  ADD CONSTRAINT `quaternion_ibfk_2` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scenes`
--
ALTER TABLE `scenes`
  ADD CONSTRAINT `scenes_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `string_data`
--
ALTER TABLE `string_data`
  ADD CONSTRAINT `string_data_ibfk_2` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vector3`
--
ALTER TABLE `vector3`
  ADD CONSTRAINT `vector3_ibfk_2` FOREIGN KEY (`entry_id`) REFERENCES `entries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
