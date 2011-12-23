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

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Databse: `easyscp`
--

-- --------------------------------------------------------

--
-- table structure for table `lang_EnglishBritain`
--

DROP TABLE IF EXISTS `lang_EnglishBritain`;
CREATE TABLE `lang_EnglishBritain` (
  `msgid` text collate utf8_unicode_ci,
  `msgstr` text collate utf8_unicode_ci,
  KEY `msgid` (msgid(25))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- data for table `lang_EnglishBritain`
--

INSERT INTO `lang_EnglishBritain` (`msgid`, `msgstr`) VALUES
('easyscp_languageSetlocaleValue', 'en_GB'),
('easyscp_table', 'EnglishBritain'),
('easyscp_language', 'English (GB)'),
('encoding', 'UTF-8');
