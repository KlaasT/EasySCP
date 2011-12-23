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
$template = 'orderpanel/addon.tpl';

if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];

	if (isset($_SESSION['plan_id'])) {
		$plan_id = $_SESSION['plan_id'];
	} else if (isset($_GET['id'])) {
		$plan_id = $_GET['id'];
		if (is_plan_available($sql, $plan_id, $user_id)) {
			$_SESSION['plan_id'] = $plan_id;
		} else {
			throw new EasySCP_Exception_Production(
				tr('This hosting plan is not available for purchase')
			);
		}
	} else {
		throw new EasySCP_Exception_Production(
			tr('You do not have permission to access this interface!')
		);
	}
} else {
	throw new EasySCP_Exception_Production(
		tr('You do not have permission to access this interface!')
	);
}

if (isset($_SESSION['domainname'])) {
	user_goto('address.php');
}

if (isset($_POST['domainname']) && $_POST['domainname'] != '') {
	addon_domain($_POST['domainname']);
}

// static page messages
$tpl->assign(
	array(
		'DOMAIN_ADDON'		=> tr('Add On A Domain'),
		'TR_DOMAIN_NAME'	=> tr('Domain name'),
		'TR_CONTINUE'		=> tr('Continue'),
		'TR_EXAMPLE'		=> tr('(e.g. domain-of-your-choice.com)')
	)
);

gen_purchase_haf($tpl, $sql, $user_id);

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/**
 * functions start
 */

function addon_domain($dmn_name) {

	if (!validates_dname($dmn_name)) {
		global $validation_err_msg;
		set_page_message(tr($validation_err_msg), 'warning');
		return;
	}

	// Should be performed after domain name validation now
	$dmn_name = encode_idna(strtolower($dmn_name));

	if (easyscp_domain_exists($dmn_name, 0) || $dmn_name == EasySCP_Registry::get('Config')->BASE_SERVER_VHOST) {
		set_page_message(tr('Domain already exists on the system!'), 'warning');
		return;
	}

	$_SESSION['domainname'] = $dmn_name;
	user_goto('address.php');
}

function is_plan_available($sql, $plan_id, $user_id) {

	$cfg = EasySCP_Registry::get('Config');

	if (isset($cfg->HOSTING_PLANS_LEVEL) && $cfg->HOSTING_PLANS_LEVEL == 'admin') {
		$query = "
			SELECT
				*
			FROM
				`hosting_plans`
			WHERE
				`id` = ?
			";

		$rs = exec_query($sql, $query, $plan_id);
	} else {
		$query = "
			SELECT
				*
			FROM
				`hosting_plans`
			WHERE
				`reseller_id` = ?
			AND
				`id` = ?
		";

		$rs = exec_query($sql, $query, array($user_id, $plan_id));
	}

	return $rs->recordCount() > 0 && $rs->fields['status'] != 0;
}

/**
 * functions end
 */
?>