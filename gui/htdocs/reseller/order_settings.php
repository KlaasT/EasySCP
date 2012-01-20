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
$template = 'reseller/order_settings.tpl';

if (isset($_POST['header']) && $_POST['header'] !== ''
	&& isset ($_POST['footer']) && $_POST['footer'] !== '') {
	save_haf($tpl, $sql);
}
gen_purchase_haf($tpl, $sql, $_SESSION['user_id'], true);

// static page messages
gen_logged_from($tpl);

$coid = isset($cfg->CUSTOM_ORDERPANEL_ID) ? $cfg->CUSTOM_ORDERPANEL_ID : '';

$url = $cfg->BASE_SERVER_VHOST_PREFIX . $cfg->BASE_SERVER_VHOST . '/orderpanel/index.php?';
$url .= 'coid='.$coid;
$url .= '&amp;user_id=' . $_SESSION['user_id'];

$tpl->assign(
	array(
		'TR_PAGE_TITLE'		=> tr('EasySCP - Reseller/Order settings'),
		'TR_MANAGE_ORDERS'	=> tr('Manage Orders'),
		'TR_APPLY_CHANGES'	=> tr('Apply changes'),
		'TR_HEADER'			=> tr('Header'),
		'TR_PREVIEW'		=> tr('Preview'),
		'TR_IMPLEMENT_INFO'	=> tr('Implementation URL'),
		'TR_IMPLEMENT_URL'	=> $url,
		'TR_FOOTER'			=> tr('Footer')
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

function save_haf($tpl, $sql) {
	$user_id = $_SESSION['user_id'];
	$header = $_POST['header'];
	$footer = $_POST['footer'];

	$query = "
		SELECT
			`id`
		FROM
			`orders_settings`
		WHERE
			`user_id` = ?
	";
	$rs = exec_query($sql, $query, $user_id);

	if ($rs->recordCount() !== 0) {
		// update query
		$query = "
			UPDATE
				`orders_settings`
			SET
				`header` = ?,
				`footer` = ?
			WHERE
				`user_id` = ?
		";

		exec_query($sql, $query, array($header, $footer, $user_id));
	} else {
		// create query
		$query = "
			INSERT INTO
				`orders_settings`(`user_id`, `header`, `footer`)
			VALUES
				(?, ?, ?)
		";

		exec_query($sql, $query, array($user_id, $header, $footer));
	}
}
?>