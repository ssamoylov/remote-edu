-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Янв 30 2010 г., 13:22
-- Версия сервера: 5.1.41
-- Версия PHP: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `remote-edu`
--

-- --------------------------------------------------------

--
-- Структура таблицы `applications`
--

CREATE TABLE IF NOT EXISTS `applications` (
  `app_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `object_id` int(10) unsigned NOT NULL,
  `type` enum('program','discipline') NOT NULL,
  `status` enum('applied','declined','accepted','signed') DEFAULT NULL,
  `contract_filename` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Структура таблицы `apps_history`
--

CREATE TABLE IF NOT EXISTS `apps_history` (
  `app_id` int(10) unsigned NOT NULL,
  `status` enum('applied','declined','accepted','signed') DEFAULT NULL,
  `modifed` datetime NOT NULL,
  PRIMARY KEY (`app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `disciplines`
--

CREATE TABLE IF NOT EXISTS `disciplines` (
  `discipline_id` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` int(11) DEFAULT NULL,
  `serial_number` int(11) NOT NULL,
  `title` varchar(256) DEFAULT NULL,
  `coef` tinyint(4) DEFAULT NULL,
  `labour_intensive` smallint(6) DEFAULT NULL,
  `responsible_teacher` int(11) DEFAULT NULL,
  PRIMARY KEY (`discipline_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- Структура таблицы `edu_docs`
--

CREATE TABLE IF NOT EXISTS `edu_docs` (
  `edu_doc_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `type` enum('diploma-high','diploma-medium','custom') NOT NULL,
  `custom_type` varchar(256) DEFAULT NULL,
  `number` varchar(128) NOT NULL,
  `exit_year` year(4) NOT NULL,
  `speciality` varchar(256) NOT NULL,
  `qualification` varchar(256) NOT NULL,
  PRIMARY KEY (`edu_doc_id`),
  KEY `fk_edu_docs_users` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `localities`
--

CREATE TABLE IF NOT EXISTS `localities` (
  `locality_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `region_id` int(10) unsigned NOT NULL,
  `code` smallint(3) unsigned zerofill NOT NULL,
  `name` char(40) NOT NULL,
  `type` enum('аал','арбан','аул','волость','высел','г','городок','д','дп','ж/д_будка','ж/д_казарм','ж/д_оп','ж/д_платф','ж/д_пост','ж/д_рзд','ж/д_ст','заимка','казарма','кв-л','кордон','кп','м','мкр','нп','остров','п','п/о','п/р','п/ст','пгт','погост','починок','промзона','рзд','рп','с','с/а','с/о','с/п','с/с','сл','снт','ст','ст-ца','тер','у','х') NOT NULL,
  PRIMARY KEY (`locality_id`),
  KEY `fk_localities_regions` (`region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `materials`
--

CREATE TABLE IF NOT EXISTS `materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `section` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `passports`
--

CREATE TABLE IF NOT EXISTS `passports` (
  `passport_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `series` smallint(4) unsigned zerofill NOT NULL,
  `number` mediumint(6) unsigned zerofill NOT NULL,
  `birthday` date NOT NULL,
  `given_by` varchar(256) NOT NULL,
  `given_date` date NOT NULL,
  `region_id` int(10) unsigned NOT NULL,
  `city_id` int(10) unsigned NOT NULL,
  `street` varchar(64) NOT NULL,
  `house` varchar(8) NOT NULL,
  `flat` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`passport_id`),
  KEY `fk_passports_users` (`user_id`),
  KEY `fk_passports_regions` (`region_id`),
  KEY `fk_passports_localities` (`city_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(10) unsigned NOT NULL,
  `amount` decimal(9,2) unsigned DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `fk_payments_applications` (`app_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `phones`
--

CREATE TABLE IF NOT EXISTS `phones` (
  `phones_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `stationary` varchar(16) DEFAULT NULL,
  `mobile` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`phones_id`),
  KEY `fk_phones_users` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `programs`
--

CREATE TABLE IF NOT EXISTS `programs` (
  `program_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(256) DEFAULT NULL,
  `labour_intensive` smallint(6) DEFAULT NULL,
  `edu_type` enum('direction','course') DEFAULT NULL,
  `paid_type` enum('free','paid') DEFAULT NULL,
  `responsible_teacher` int(11) DEFAULT NULL,
  `cost` decimal(9,2) unsigned DEFAULT NULL,
  PRIMARY KEY (`program_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Структура таблицы `regions`
--

CREATE TABLE IF NOT EXISTS `regions` (
  `region_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` tinyint(2) unsigned zerofill NOT NULL,
  `name` char(40) NOT NULL,
  PRIMARY KEY (`region_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `section_id` int(11) NOT NULL AUTO_INCREMENT,
  `discipline_id` int(11) DEFAULT NULL,
  `title` varchar(256) DEFAULT NULL,
  `number` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`section_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(64) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `passwd` varchar(32) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `email` varchar(256) NOT NULL,
  `surname` varchar(64) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `patronymic` varchar(64) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `curator` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
