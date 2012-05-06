--
-- EasySCP a Virtual Hosting Control Panel
-- Copyright (C) 2010-2012 by Easy Server Control Panel - http://www.easyscp.net
--
-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; either version 2
-- of the License, or (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
--
-- @link 		http://www.easyscp.net
-- @author 		EasySCP Team
-- --------------------------------------------------------

--
-- Datenbank: `powerdns`
--

-- --------------------------------------------------------
create database `powerdns` CHARACTER SET utf8 COLLATE utf8_unicode_ci;

use `powerdns`;

--
-- Table structure for table `domains`
--
CREATE TABLE IF NOT EXISTS `domains` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `easyscp_domain_id` INT DEFAULT NULL,
  `easyscp_domain_alias_id` INT DEFAULT NULL,
  `name` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `master` VARCHAR(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_check` INT DEFAULT NULL,
  `type` VARCHAR(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notified_serial` INT DEFAULT NULL,
  `account` VARCHAR(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_index` (`name`)
);
-- --------------------------------------------------------

--
-- Table structure for table `records`
--
CREATE TABLE `records` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `domain_id` INT DEFAULT NULL,
  `name` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` VARCHAR(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ttl` INT DEFAULT NULL,
  `prio` INT DEFAULT NULL,
  `change_date` INT DEFAULT NULL,
  `protected` TINYINT(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rec_name_index` (`name`),
  KEY `nametype_index` (`name`, `type`),
  KEY `domain_id` (`domain_id`)
);
-- --------------------------------------------------------

--
-- Table structure for table `supermasters`
--
CREATE TABLE `supermasters` (
  `ip` VARCHAR(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `nameserver` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account` VARCHAR(40) COLLATE utf8_unicode_ci DEFAULT NULL
);
-- --------------------------------------------------------