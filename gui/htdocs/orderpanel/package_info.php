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
$template = 'orderpanel/package_info.tpl';

$coid = isset($cfg->CUSTOM_ORDERPANEL_ID) ? $cfg->CUSTOM_ORDERPANEL_ID : '';
$bcoid = (empty($coid) || (isset($_GET['coid']) && $_GET['coid'] == $coid));

if (isset($_GET['id']) && $bcoid) {
	$plan_id = $_GET['id'];
	$_SESSION['plan_id'] = $plan_id;
	if (isset($_SESSION['user_id'])) {
		$user_id = $_SESSION['user_id'];
	} else if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
		$_SESSION['user_id'] = $user_id;
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

// static page messages
$tpl->assign(
	array(
		'TR_DOMAINS'			=> tr('Domains'),
		'TR_WEBSPACE'			=> tr('Webspace'),
		'TR_HDD'				=> tr('Disk limit'),
		'TR_TRAFFIC'			=> tr('Traffic limit'),
		'TR_FEATURES'			=> tr('Domain Features'),
		'TR_STANDARD_FEATURES'	=> tr('Package Features'),
		'TR_WEBMAIL'			=> tr('Webmail'),
		'TR_FILEMANAGER'		=> tr('Filemanager'),
		'TR_BACKUP'				=> tr('Backup and Restore'),
		'TR_ERROR_PAGES'		=> tr('Custom Error Pages'),
		'TR_HTACCESS'			=> tr('Protected Areas'),
		'TR_PHP_SUPPORT'		=> tr('PHP support'),
		'TR_CGI_SUPPORT'		=> tr('CGI support'),
		'TR_DNS_SUPPORT'		=> tr('Manual DNS support'),
		'TR_MYSQL_SUPPORT'		=> tr('SQL support'),
		'TR_SUBDOMAINS'			=> tr('Subdomains'),
		'TR_DOMAIN_ALIAS'		=> tr('Domain aliases'),
		'TR_MAIL_ACCOUNTS'		=> tr('Mail accounts'),
		'TR_FTP_ACCOUNTS'		=> tr('FTP accounts'),
		'TR_SQL_DATABASES'		=> tr('SQL databases'),
		'TR_SQL_USERS'			=> tr('SQL users'),
		'TR_STATISTICS'			=> tr('Statistics'),
		'TR_CUSTOM_LOGS'		=> tr('Custom Apache Logs'),
		'TR_ONLINE_SUPPORT'		=> tr('Web & E-Mail Support'),
		'TR_OWN_DOMAIN'			=> tr('Your Own Domain'),
		'TR_EASYSCP'			=> tr('EasySCP Control Panel'),
		'TR_UPDATES'			=> tr('Automatic Updates'),
		'TR_PRICE'				=> tr('Price'),
		'TRR_PRICE'				=> tr('Package Price'),
		'TR_SETUP_FEE'			=> tr('Setup Fee'),
		'TR_PERFORMANCE'		=> tr('Performance'),
		'TR_PURCHASE'			=> tr('Purchase'),
		'TR_BACK'				=> tr('Back'),
		'YES'					=> tr('Yes')
	)
);

gen_purchase_haf($tpl, $sql, $user_id);
gen_plan_details($tpl, $sql, $user_id, $plan_id);

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/*
 * functions start
 */

function translate_sse($value) {
	if ($value == '_yes_') {
		return tr('Yes');
	} else if ($value == '_no_') {
		return tr('No');
	} else if ($value == '_sql_') {
		return tr('SQL');
	} else if ($value == '_full_') {
		return tr('Full');
	} else if ($value == '_dmn_') {
		return tr('Domain');
	} else {
		return $value;
	}
}

/**
 * @param EasySCP_TemplateEngine $tpl
 * @param EasySCP_Database $sql
 * @param int $user_id
 * @param int $plan_id
 */
function gen_plan_details($tpl, $sql, $user_id, $plan_id) {

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

	if ($rs->recordCount() == 0) {
		user_goto('index.php?user_id=' . $user_id);
	} else {
		$props = $rs->fields['props'];
		list($hp_php, $hp_cgi, $hp_sub, $hp_als, $hp_mail, $hp_ftp, $hp_sql_db, $hp_sql_user, $hp_traff, $hp_disk, $hp_backup, $hp_dns) = explode(";", $props);

		$price = $rs->fields['price'];
		$setup_fee = $rs->fields['setup_fee'];

		if ($price == 0 || $price == '') {
			$price = tr('free of charge');
		} else {
			$price .= ' ' . tohtml($rs->fields['value']) . ' ' . tohtml($rs->fields['payment']);
		}

		if ($setup_fee == 0 || $setup_fee == '') {
			$setup_fee = tr('free of charge');
		} else {
			$setup_fee .= ' ' . $rs->fields['value'];
		}
		$description = $rs->fields['description'];

		$hp_disk = translate_limit_value($hp_disk, true) . "<br />";

		$hp_traff = translate_limit_value($hp_traff, true);

		$coid = isset($cfg->CUSTOM_ORDERPANEL_ID) ? $cfg->CUSTOM_ORDERPANEL_ID : '';

		$tpl->assign(
			array(
				'PACK_NAME'		=> $rs->fields['name'],
				'DESCRIPTION'	=> tohtml($description),
				'PACK_ID'		=> $rs->fields['id'],
				'USER_ID'		=> $user_id,
				'PURCHASE'		=> tr('Purchase'),
				'ALIAS'			=> translate_limit_value($hp_als),
				'SUBDOMAIN'		=> translate_limit_value($hp_sub),
				'HDD'			=> $hp_disk,
				'TRAFFIC'		=> $hp_traff,
				'PHP'			=> translate_sse($hp_php),
				'CGI'			=> translate_sse($hp_cgi),
				'DNS'			=> translate_sse($hp_dns),
				'BACKUP'		=> translate_sse($hp_backup),
				'MAIL'			=> translate_limit_value($hp_mail),
				'FTP'			=> translate_limit_value($hp_ftp),
				'SQL_DB'		=> translate_limit_value($hp_sql_db),
				'SQL_USR'		=> translate_limit_value($hp_sql_user),
				'PRICE'			=> $price,
				'SETUP'			=> $setup_fee,
				'CUSTOM_ORDERPANEL_ID'	=> $coid
			)
		);

		if ($rs->fields['status'] == 1) {
			$tpl->assign('ISENABLED', true);
		}
	}
}

/*
 * functions end
 */
?>
