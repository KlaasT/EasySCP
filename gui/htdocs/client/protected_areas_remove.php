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

$cfg = EasySCP_Registry::get('Config');

/**
 * @todo Do we have a proper cdir?
 */
if (!isset($_GET['cdir'])) {
	user_goto('protected_areas.php');
}
$domain_name = $_SESSION['user_logged'];
$cdir = $_GET['cdir'];

unlink($cfg->FTP_HOMEDIR . '/' . $domain_name . $cdir . '.htaccess');

set_page_message(tr('Protected area was deleted successful!'), 'success');

user_goto('protected_areas.php?cur_dir=' . $cdir);
?>