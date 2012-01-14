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

require '../../include/easyscp-lib.php';

check_login(__FILE__);

/**
 * @var $cfg EasySCP_Config_Handler_File
 */
$cfg = EasySCP_Registry::get('Config');

// Test if we have a proper delete_id.
if (!isset($_GET['delete_lang'])) {
	user_goto('multilanguage.php');
}

$delete_lang = $_GET['delete_lang'];

// ERROR - we have domains that use this IP
if ($delete_lang == $cfg->USER_INITIAL_LANG) {
	set_page_message(
		tr("It is not possible to delete system default's language!"),
		'error'
	);
	user_goto('multilanguage.php');
}

// check if someone still uses that lang
$query = "
	SELECT
		*
	FROM
		`user_gui_props`
	WHERE
		`lang` = ?
";

$rs = exec_query($sql, $query, $delete_lang);

// ERROR - we have domains that use this IP
if ($rs->recordCount () > 0) {
	set_page_message(tr('There are users who use this language!'), 'error');

	user_goto('multilanguage.php');
}

$query = "DROP TABLE `$delete_lang`";

$rs = exec_query($sql, $query);

write_log(sprintf("%s removed language: %s", $_SESSION['user_logged'], $delete_lang));

set_page_message(tr('Language was removed!'), 'success');

user_goto('multilanguage.php');
?>