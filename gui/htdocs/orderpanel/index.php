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

$cfg = EasySCP_Registry::get('Config');

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'orderpanel/index.tpl';

$coid = isset($cfg->CUSTOM_ORDERPANEL_ID) ? $cfg->CUSTOM_ORDERPANEL_ID : '';
$bcoid = (empty($coid) || (isset($_GET['coid']) && $_GET['coid'] == $coid));

if (isset($_GET['user_id']) && is_numeric($_GET['user_id']) && $bcoid) {
	$user_id = $_GET['user_id'];
	$_SESSION['user_id'] = $user_id;
} else if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
} else {
	system_message(
		tr('You do not have permission to access this interface!'),
		'error'
	);
}
unset($_SESSION['plan_id']);

// static page messages
gen_purchase_haf($tpl, $sql, $user_id);
gen_packages_list($tpl, $sql, $user_id);

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/*
 * functions start
 */

/**
 * @throws EasySCP_Exception_Production
 * @param object $tpl	EasySCP_TemplateEngine instance
 * @param object $sql	EasySCP_Database instance
 * @param int $user_id
 */
function gen_packages_list($tpl, $sql, $user_id) {

	$cfg = EasySCP_Registry::get('Config');

	if (isset($cfg->HOSTING_PLANS_LEVEL) && $cfg->HOSTING_PLANS_LEVEL == 'admin') {
		$query = "
			SELECT
				t1.*,
				t2.`admin_id`, t2.`admin_type`
			FROM
				`hosting_plans` AS t1,
				`admin` AS t2
			WHERE
				t2.`admin_type` = ?
			AND
				t1.`reseller_id` = t2.`admin_id`
			AND
				t1.`status` = 1
			ORDER BY
				t1.`id`
		";

		$rs = exec_query($sql, $query, 'admin');
	} else {
		$query = "
			SELECT
				*
			FROM
				`hosting_plans`
			WHERE
				`reseller_id` = ?
			AND
				`status` = '1'
		";

		$rs = exec_query($sql, $query, $user_id);
	}

	if ($rs->recordCount() == 0) {
		throw new EasySCP_Exception_Production(
			tr('No available hosting packages')
		);
	} else {
		while (!$rs->EOF) {
			$description = $rs->fields['description'];

			$price = $rs->fields['price'];
			if ($price == 0 || $price == '') {
				$price = "/ " . tr('free of charge');
			} else {
				$price = "/ " . $price . " " . tohtml($rs->fields['value']) . " " . tohtml($rs->fields['payment']);
			}

			$tpl->append(
				array(
					'PACK_NAME'	=> tohtml($rs->fields['name']),
					'PACK_ID'	=> $rs->fields['id'],
					'USER_ID'	=> $user_id,
					'PURCHASE'	=> tr('Purchase'),
					'PACK_INFO'	=> tohtml($description),
					'PRICE'		=> $price,
				)
			);

			$rs->moveNext();
		}
	}
}

/*
 * functions end
 */
?>