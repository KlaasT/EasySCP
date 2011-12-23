<?php
/**
 * EasySCP a Virtual Hosting Control Panel
 * Copyright (C) 2010-2011 by Easy Server Control Panel - http://www.easyscp.net
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
 * @todo check queries if any of them use db prepared statements
 */

if (isset($_GET['id']) && $_GET['id'] !== '') {

	$id = $_GET['id'];
	$delete_status = $cfg->ITEM_DELETE_STATUS;
	$dmn_id = get_user_domain_id($sql, $_SESSION['user_id']);

	// let's see the status of this thing
	$query = "
		SELECT
			`status`
		FROM
			`htaccess`
		WHERE
			`id` = ?
		AND
			`dmn_id` = ?
	";

	$rs = exec_query($sql, $query, array($id, $dmn_id));
	$status = $rs->fields['status'];
	$ok_status = $cfg->ITEM_OK_STATUS;

	if ($status !== $ok_status) {
		set_page_message(
			tr('Protected area status should be OK if you want to delete it!'),
			'error'
		);
		user_goto('protected_areas.php');
	}

	// TODO use prepared statement for $delete_status
	$query = <<<SQL_QUERY
		UPDATE
			`htaccess`
		SET
			`status` = '$delete_status'
		WHERE
			`id` = ?
		AND
			`dmn_id` = ?
SQL_QUERY;

	$rs = exec_query($sql, $query, array($id, $dmn_id));
	send_request();

	write_log($_SESSION['user_logged'].": deletes protected area with ID: ".$_GET['id']);
	set_page_message(tr('Protected area deleted successfully!'), 'success');
	user_goto('protected_areas.php');
} else {
	set_page_message(tr('Permission deny!'), 'error');
	user_goto('protected_areas.php');
}
?>