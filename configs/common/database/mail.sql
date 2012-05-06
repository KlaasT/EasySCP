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
-- Datenbank: `mail`
--

-- --------------------------------------------------------
CREATE DATABASE `mail` CHARACTER SET utf8 COLLATE utf8_unicode_ci;
use `mail`;

--
-- Table structure for table `domains`
--
CREATE TABLE `domains` (
  `domain` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`domain`)
);
-- --------------------------------------------------------

--
-- Table structure for table `forwardings`
--
CREATE TABLE `forwardings` (
  `source` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `destination` TEXT COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`source`)
);
-- --------------------------------------------------------

--
-- Table structure for table `transport`
--
CREATE TABLE `transport` (
  `domain` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `transport` VARCHAR(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  UNIQUE KEY `domain` (`domain`)
);
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--
CREATE TABLE `users` (
  `email` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`email`)
);
-- --------------------------------------------------------