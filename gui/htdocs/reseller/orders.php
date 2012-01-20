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

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'reseller/orders.tpl';

// static page messages
gen_logged_from($tpl);

gen_order_page($tpl, $sql, $_SESSION['user_id']);

$tpl->assign(
	array(
		'TR_PAGE_TITLE'				=> tr('EasySCP - Reseller/Order management'),
		'TR_MANAGE_ORDERS'			=> tr('Manage Orders'),
		'TR_ID'						=> tr('ID'),
		'TR_DOMAIN'					=> tr('Domain'),
		'TR_USER'					=> tr('Customer data'),
		'TR_ACTION'					=> tr('Action'),
		'TR_STATUS'					=> tr('Order'),
		'TR_EDIT'					=> tr('Edit'),
		'TR_DELETE'					=> tr('Delete'),
		'TR_DETAILS'				=> tr('Details'),
		'TR_HP'						=> tr('Hosting plan'),
		'TR_MESSAGE_DELETE_ACCOUNT'	=> tr('Are you sure you want to delete this order?', true),
		'TR_ADD'					=> tr('Add/Details')
	)
);

gen_reseller_mainmenu($tpl, 'reseller/main_menu_orders.tpl');
gen_reseller_menu($tpl, 'reseller/menu_orders.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/*
 * Functions
 */

/**
 * @param EasySCP_TemplateEngine $tpl
 * @param EasySCP_Database $sql
 * @param int $user_id
 */
function gen_order_page($tpl, $sql, $user_id) {
	$cfg = EasySCP_Registry::get('Config');

	$start_index = 0;
	// NXW: Unused variable so...
	// $current_psi = 0;

	if (isset($_GET['psi']) && is_numeric($_GET['psi'])) {
		$start_index = $_GET['psi'];
		// NXW: Unused variable so...
		// $current_psi = $_GET['psi'];
	}

	$rows_per_page = $cfg->DOMAIN_ROWS_PER_PAGE;
	// count query
	$count_query = "
		SELECT
			COUNT(`id`) AS cnt
		FROM
			`orders`
		WHERE
			`user_id` = ?
		AND
			`status` != ?
	";
	// let's count
	$rs = exec_query($sql, $count_query, array($user_id, 'added'));
	$records_count = $rs->fields['cnt'];

	$query = "
		SELECT
			*
		FROM
			`orders`
		WHERE
			`user_id` = ?
		AND
			`status` != ?
		ORDER BY
			`date` DESC
		LIMIT
			$start_index, $rows_per_page
	";
	$rs = exec_query($sql, $query, array($user_id, 'added'));

	$prev_si = $start_index - $rows_per_page;

	if ($start_index == 0) {
		$tpl->assign('SCROLL_PREV', '');
	} else {
		$tpl->assign(
			array(
				'SCROLL_PREV_GRAY' => '',
				'PREV_PSI' => $prev_si
			)
		);
	}

	$next_si = $start_index + $rows_per_page;

	if ($next_si + 1 > $records_count) {
		$tpl->assign('SCROLL_NEXT', '');
	} else {
		$tpl->assign(
			array(
				'SCROLL_NEXT_GRAY' => '',
				'NEXT_PSI' => $next_si
			)
		);
	}

	if ($rs->recordCount() == 0) {
		set_page_message(tr('You do not have new orders!'), 'info');
		$tpl->assign('ORDERS_TABLE', '');
		$tpl->assign('SCROLL_NEXT_GRAY', '');
		$tpl->assign('SCROLL_PREV_GRAY', '');
	} else {
		while (!$rs->EOF) {
			$plan_id = $rs->fields['plan_id'];
			$order_status = tr('New order');
			// let's get hosting plan name
			$planname_query = "
				SELECT
					`name`
				FROM
					`hosting_plans`
				WHERE
					`id` = ?
			";
			$rs_planname = exec_query($sql, $planname_query, $plan_id);
			$plan_name = $rs_planname->fields['name'];

			$status = $rs->fields['status'];
			if ($status === 'update') {
				$customer_id = $rs->fields['customer_id'];
				$cusrtomer_query = "
					SELECT
						*
					FROM
						`admin`
					WHERE
						`admin_id` = ?
				";
				$rs_customer = exec_query($sql, $cusrtomer_query, $customer_id);
				$user_details = tohtml($rs_customer->fields['fname']) . "&nbsp;"
					. tohtml($rs_customer->fields['lname'])
					. "<br /><a href=\"mailto:" . tohtml($rs_customer->fields['email'])
					. "\" class=\"link\">" . tohtml($rs_customer->fields['email'])
					. "</a><br />" . tohtml($rs_customer->fields['zip'])
					. "&nbsp;" . tohtml($rs_customer->fields['city'])
					. "&nbsp;" . tohtml($rs_customer->fields['state'])
					. "&nbsp;" . tohtml($rs_customer->fields['country']);
				$order_status = tr('Update order');
				$tpl->append('LINK', 'orders_update.php?order_id=' . $rs->fields['id']);
			} else {
				$user_details = $rs->fields['fname'] . "&nbsp;"
					. tohtml($rs->fields['lname'])
					. "<br /><a href=\"mailto:" . tohtml($rs->fields['email'])
					. "\" class=\"link\">" . tohtml($rs->fields['email'])
					. "</a><br />" . tohtml($rs->fields['zip'])
					. "&nbsp;" . tohtml($rs->fields['city'])
					. "&nbsp;" . tohtml($rs->fields['state'])
					. "&nbsp;" . tohtml($rs->fields['country']);
				$tpl->append('LINK', 'orders_details.php?order_id=' . $rs->fields['id']);
			}
			$tpl->append(
				array(
					'ID'		=> $rs->fields['id'],
					'HP'		=> tohtml($plan_name),
					'DOMAIN'	=> tohtml($rs->fields['domain_name']),
					'USER'		=> $user_details,
					'STATUS'	=> $order_status,
				)
			);

			$rs->moveNext();
		}
	}
}
?>