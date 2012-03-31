<?php
/**
 * EasySCP a Virtual Hosting Control Panel
 * Copyright (C) 2010-2012 by Easy Server Control Panel - http://www.easyscp.net
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * @link 		http://www.easyscp.net
 * @author 		EasySCP Team
 */

/**
 * Class for database updates
 *
 * @category	EasySCP
 * @package		EasySCP_Update
 */
class EasySCP_Update_Database extends EasySCP_Update {

	/**
	 * EasySCP_Update_Database instance
	 *
	 * @var EasySCP_Update_Database
	 */
	protected static $_instance = null;

	/**
	 * The database variable name for the update version
	 *
	 * @var string
	 */
	protected $_databaseVariableName = 'DATABASE_REVISION';

	/**
	 * The update functions prefix
	 *
	 * @var string
	 */
	protected $_functionName = '_databaseUpdate_';

	/**
	 * Default error message for updates that have failed
	 *
	 * @var string
	 */
	protected $_errorMessage = 'Database update %s failed';

	/**
	 * Get an EasySCP_Update_Database instance
	 *
	 * @return EasySCP_Update_Database An EasySCP_Update_Database instance
	 */
	public static function getInstance() {

		if (is_null(self::$_instance)) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/*
	 * Insert the update functions below this entry. The revision has to be
	 * ascending and unique. Each databaseUpdate function has to return a array,
	 * even if the array contains only one entry.
	 */

	/**
	 * Remove unused table 'suexec_props'
	 *
	 * @return array
	 */
	protected function _databaseUpdate_46() {

		$sqlUpd = array();

		$sqlUpd[] = "
			DROP TABLE IF EXISTS
				`suexec_props`
			;
		";

		return $sqlUpd;
	}

	/**
	 * Updated standard user quota to 100MB
	 *
	 * @return array
	 */
	protected function _databaseUpdate_47() {

		$sqlUpd = array();

		$sqlUpd[] = "
			ALTER TABLE
				`mail_users`
			CHANGE
				`quota` `quota` INT( 10 ) NULL DEFAULT '104857600'
		;";
		$sqlUpd[] = "
			UPDATE
				`mail_users`
			SET
				`quota` = '104857600'
			WHERE
				`quota` = '10485760';
		;";

		return $sqlUpd;
	}

	/**
	 * Adds needed field to ftp_users table to allow single sign on to net2ftp
	 *
	 * @return array
	 */
	protected function _databaseUpdate_48() {
		$sqlUpd = array();

		$sqlUpd[] = "
			ALTER TABLE
				`ftp_users`
			ADD
				`net2ftppasswd` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL
			AFTER
				`passwd`;
		";

		return $sqlUpd;
	}

	/**
	 * Remove unused column 'user_gui_props.logo'
	 *
	 * @author Markus Szywon <markus.szywon@easyscp.net>
	 * @return array
	 */
	protected function _databaseUpdate_49() {
		$sqlUpd = array();

		$sqlUpd[] = "
			ALTER TABLE
				`user_gui_props`
  			DROP
				`logo`;
		";

		return $sqlUpd;
	}

	/**
	 * Adds menu_icon field to custom_menus table to allow different icons on custom buttons
	 *
	 * @author Markus Szywon <markus.szywon@easyscp.net>
	 * @return array
	 */
	protected function _databaseUpdate_50() {
		$sqlUpd = array();

		$sqlUpd[] = "
			ALTER TABLE
				`custom_menus`
			ADD
				`menu_icon` VARCHAR( 200 ) NULL;
		";

		return $sqlUpd;
	}

	/**
	 * Adds field to enable/disable migration from GUI
	 *
	 * @author Markus Szywon <markus.szywon@easyscp.net>
	 * @return array
	 */
	protected function _databaseUpdate_51() {
		$sqlUpd = array();

		$sqlUpd[] = "
			INSERT INTO
				`config` (name, value)
			VALUES
				('MIGRATION_ENABLED', 0)
			;
		";

		return $sqlUpd;
	}

	/**
	 * Adds database fields for SSL configuration
	 *
	 * @author Tom Winterhalder <tom.winterhalder@easyscp.net>
	 * @return array
	 */
	protected function _databaseUpdate_52(){
		$sqlUpd = array();

		$sqlUpd[] = "
			INSERT INTO
				`config` (name, value)
			VALUES
				('SSL_KEY', ''),
				('SSL_CERT',''),
				('SSL_STATUS','0')
			;
		";

                $sqlUpd[] = "
                        ALTER TABLE 
                                `domain` 
                        ADD 
                                `domain_ssl` VARCHAR( 15 ) NOT NULL DEFAULT 'No' 
			;
		";

                $sqlUpd[] = "
                        ALTER TABLE 
                                `domain` 
                        ADD 
                                `SSL_KEY` VARCHAR( 5000 ) NULL DEFAULT NULL 
			;
		";

                $sqlUpd[] = "
                        ALTER TABLE 
                                `domain` 
                        ADD 
                                `SSL_CERT` VARCHAR( 5000 ) NULL DEFAULT NULL 
			;
		";

                $sqlUpd[] = "
                        ALTER TABLE 
                                `domain` 
                        ADD 
                                `SSL_STATUS` INT( 1 ) unsigned NOT NULL DEFAULT '0' 
			;
		";
                return $sqlUpd;
	}
		
	/*
	 * DO NOT CHANGE ANYTHING BELOW THIS LINE!
	 */
}
