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

$reseller_id = $_SESSION['user_id'];


if (isset($_GET['order_id']) && is_numeric($_GET['order_id'])) {
	$order_id = $_GET['order_id'];
} else {
	set_page_message(tr('Wrong order ID!'), 'error');
	user_goto('orders.php');
}

$query = "
	SELECT
		`id`
	FROM
		`orders`
	WHERE
		`id` = ?
	AND
		`user_id` = ?
";

$rs = exec_query($sql, $query, array($order_id, $reseller_id));

if ($rs->recordCount() == 0) {
	set_page_message(tr('Permission deny!'), 'error');
	user_goto('orders.php');
}

// delete all FTP Accounts
$query = "
	DELETE FROM
		`orders`
	WHERE
		`id` = ?
";
$rs = exec_query($sql, $query, $order_id);

set_page_message(tr('Customer order was removed successful!'), 'success');

write_log($_SESSION['user_logged'].": deletes customer order.");
user_goto('orders.php');
