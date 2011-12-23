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

if (strtolower($cfg->HOSTING_PLANS_LEVEL) != 'admin') {
	user_goto('index.php');
}

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'admin/hosting_plan.tpl';
// Table with hosting plans

// static page messages
gen_hp_table($tpl, $_SESSION['user_id']);

$tpl->assign(
		array(
			'TR_PAGE_TITLE'				=> tr('EasySCP - Administrator/Hosting Plan Management'),
			'TR_HOSTING_PLANS'			=> tr('Hosting plans'),
			'TR_PAGE_MENU'				=> tr('Manage hosting plans'),
			'TR_PURCHASING'				=> tr('Purchasing'),
			'TR_ADD_HOSTING_PLAN'		=> tr('Add hosting plan'),
			'TR_TITLE_ADD_HOSTING_PLAN'	=> tr('Add new user hosting plan'),
			'TR_BACK'					=> tr('Back'),
			'TR_TITLE_BACK'				=> tr('Return to previous menu'),
			'TR_MESSAGE_DELETE'			=> tr('Are you sure you want to delete %s?', true, '%s')
		)
);

gen_admin_mainmenu($tpl, 'admin/main_menu_hosting_plan.tpl');
gen_admin_menu($tpl, 'admin/menu_hosting_plan.tpl');

gen_hp_message();
gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

// BEGIN FUNCTION DECLARE PATH

function gen_hp_message() {

	// global $externel_event, $hp_added, $hp_deleted, $hp_updated;
	// global $external_event;

	if (isset($_SESSION["hp_added"]) && $_SESSION["hp_added"] == '_yes_') {
		// $external_event = '_on_';
		set_page_message(tr('Hosting plan added!'), 'success');
		unset($_SESSION["hp_added"]);
		if (isset($GLOBALS['hp_added']))
			unset($GLOBALS['hp_added']);
	} else if (isset($_SESSION["hp_deleted"]) && $_SESSION["hp_deleted"] == '_yes_') {
		// $external_event = '_on_';
		set_page_message(tr('Hosting plan deleted!'), 'success');
		unset($_SESSION["hp_deleted"]);
		if (isset($GLOBALS['hp_deleted']))
			unset($GLOBALS['hp_deleted']);
	} else if (isset($_SESSION["hp_updated"]) && $_SESSION["hp_updated"] == '_yes_') {
		// $external_event = '_on_';
		set_page_message(tr('Hosting plan updated!'), 'success');
		unset($_SESSION["hp_updated"]);
		if (isset($GLOBALS['hp_updated']))
			unset($GLOBALS['hp_updated']);
	} else if (isset($_SESSION["hp_deleted_ordererror"]) && $_SESSION["hp_deleted_ordererror"] == '_yes_') {
		//$external_event = '_on_';
		set_page_message(
			tr('Hosting plan can\'t be deleted, there are orders!'),
			'error'
		);
		unset($_SESSION["hp_deleted_ordererror"]);
	}

} // End of gen_hp_message()

/**
 * Extract and show data for hosting plans
 * @param EasySCP_TemplateEngine $tpl
 * @param int $reseller_id
 */
function gen_hp_table($tpl, $reseller_id) {

	$cfg = EasySCP_Registry::get('Config');
	$sql = EasySCP_Registry::get('Db');

	$query = "
		SELECT
			t1.`id`, t1.`reseller_id`, t1.`name`, t1.`props`, t1.`status`,
			t2.`admin_id`, t2.`admin_type`
		FROM
			`hosting_plans` AS t1,
			`admin` AS t2
		WHERE
			t2.`admin_type` = ?
		AND
			t1.`reseller_id` = t2.`admin_id`
		ORDER BY
			t1.`name`
	";
	$rs = exec_query($sql, $query, 'admin');

	if ($rs->rowCount() == 0) {
		set_page_message(tr('No hosting plans found!'), 'info');
		$tpl->assign('HP_TABLE', '');
	} else { // There are data for hosting plans :-)
		$tpl->assign(
			array(
				'TR_HOSTING_PLANS'	=> tr('Hosting plans'),
				'TR_NOM'			=> tr('No.'),
				'TR_EDIT' 			=> tr('Edit'),
				'TR_DELETE'			=> tr('Delete'),
				'PLAN_SHOW'			=> tr('Show hosting plan'),
				'TR_PLAN_NAME'		=> tr('Name'),
				'TR_ACTION'			=> tr('Action')
			)
		);

		$coid = $cfg->exists('CUSTOM_ORDERPANEL_ID') ? $cfg->CUSTOM_ORDERPANEL_ID : '';
		$i = 1;

		while (($data = $rs->fetchRow())) {
			$tpl->assign(array('CLASS_TYPE_ROW' => ($i % 2 == 0) ? 'content' : 'content2'));
			$status = ($data['status']) ? tr('Enabled') : tr('Disabled');

			$tpl->append(
				array(
					'PLAN_NOM'				=> $i++,
					'PLAN_NAME'				=> tohtml($data['name']),
					'PLAN_NAME2'			=> addslashes(clean_html($data['name'], true)),
					'PURCHASING'			=> $status,
					'CUSTOM_ORDERPANEL_ID'	=> $coid,
					'HP_ID'					=> $data['id'],
					'ADMIN_ID'				=> $_SESSION['user_id']
				)
			);
		} // end while
	}

}
?>