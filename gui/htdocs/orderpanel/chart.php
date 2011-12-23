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
$template = 'orderpanel/chart.tpl';

if (isset($_SESSION['user_id']) && isset($_SESSION['plan_id'])) {
	$user_id = $_SESSION['user_id'];
	$plan_id = $_SESSION['plan_id'];
} else {
	throw new EasySCP_Exception_Production(
		tr('You do not have permission to access this interface!')
	);
}

// static page messages
$tpl->assign(
	array(
		'YOUR_CHART'				=> tr('Your Chart'),
		'TR_COSTS'					=> tr('Costs'),
		'TR_PACKAGE_PRICE'			=> tr('Price'),
		'TR_PACKAGE_SETUPFEE'		=> tr('Setup fee'),
		'TR_TOTAL'					=> tr('Total'),
		'TR_CONTINUE'				=> tr('Purchase'),
		'TR_CHANGE'					=> tr('Change'),
		'TR_FIRSTNAME'				=> tr('First name'),
		'TR_LASTNAME'				=> tr('Last name'),
		'TR_GENDER'					=> tr('Gender'),
		'TR_COMPANY'				=> tr('Company'),
		'TR_POST_CODE'				=> tr('Zip/Postal code'),
		'TR_CITY'					=> tr('City'),
		'TR_STATE'					=> tr('State/Province'),
		'TR_COUNTRY'				=> tr('Country'),
		'TR_STREET1'				=> tr('Street 1'),
		'TR_STREET2'				=> tr('Street 2'),
		'TR_EMAIL'					=> tr('Email'),
		'TR_PHONE'					=> tr('Phone'),
		'TR_FAX'					=> tr('Fax'),
		'TR_EMAIL'					=> tr('Email'),
		'TR_PERSONAL_DATA'			=> tr('Personal Data'),
		'TR_CAPCODE'				=> tr('Security code'),
		'TR_IMGCAPCODE_DESCRIPTION'	=> tr('(To avoid abuse, we ask you to write the combination of letters on the above picture into the field "Security code")'),
		'TR_IMGCAPCODE'				=> '<img src="/imagecode.php" width="' . $cfg->LOSTPASSWORD_CAPTCHA_WIDTH . '" height="' . $cfg->LOSTPASSWORD_CAPTCHA_HEIGHT . '" border="0" alt="captcha image" />'
	)
);

gen_purchase_haf($tpl, $sql, $user_id);
gen_chart($tpl, $sql, $user_id, $plan_id);
gen_personal_data($tpl);

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
 * @param EasySCP_pTemplate $tpl
 * @param EasySCP_Database $sql
 * @param int $user_id
 * @param int $plan_id
 */
function gen_chart($tpl, $sql, $user_id, $plan_id) {

	$cfg = EasySCP_Registry::get('Config');

	if (isset($cfg->HOSTING_PLANS_LEVEL)&& $cfg->HOSTING_PLANS_LEVEL == 'admin') {
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
		user_goto('index.php');
	} else {
		$price = $rs->fields['price'];
		$setup_fee = $rs->fields['setup_fee'];
		$total = $price + $setup_fee;


		if ($price == 0 || $price == '') {
			$price = tr('free of charge');
		} else {
			$price .= ' ' . tohtml($rs->fields['value']) . ' ' . tohtml($rs->fields['payment']);
		}

		if ($setup_fee == 0 || $setup_fee == '') {
			$setup_fee = tr('free of charge');
		} else {
			$setup_fee .= ' ' . tohtml($rs->fields['value']);
		}

		if ($total == 0) {
			$total = tr('free of charge');
		} else {
			$total .= ' ' . tohtml($rs->fields['value']);
		}

		$tpl->assign(
			array(
				'PRICE' => $price,
				'SETUP' => $setup_fee,
				'TOTAL' => $total,
				'TR_PACKAGE_NAME' => tohtml($rs->fields['name']),
			)
		);

		if ($rs->fields['tos'] != '') {
			$tpl->assign(
				array(
					'TR_TOS_PROPS'	=> tr('Term of Service'),
					'TR_TOS_ACCEPT' => tr('I Accept The Term of Service'),
					'TOS'			=> $rs->fields['tos']
				)
			);

			$_SESSION['tos'] = true;
		} else {
			$_SESSION['tos'] = false;
		}
	}
}

/**
 * @param EasySCP_pTemplate $tpl
 */
function gen_personal_data($tpl) {

	$first_name		= (isset($_SESSION['fname'])) ? $_SESSION['fname'] : '';
	$last_name		= (isset($_SESSION['lname'])) ? $_SESSION['lname'] : '';
	$company		= (isset($_SESSION['firm'])) ? $_SESSION['firm'] : '';
	$postal_code	= (isset($_SESSION['zip'])) ? $_SESSION['zip'] : '';
	$city			= (isset($_SESSION['city'])) ? $_SESSION['city'] : '';
	$state			= (isset($_SESSION['state'])) ? $_SESSION['state'] : '';
	$country		= (isset($_SESSION['country'])) ? $_SESSION['country'] : '';
	$street1		= (isset($_SESSION['street1'])) ? $_SESSION['street1'] : '';
	$street2		= (isset($_SESSION['street2'])) ? $_SESSION['street2'] : '';
	$phone			= (isset($_SESSION['phone'])) ? $_SESSION['phone'] : '';
	$fax			= (isset($_SESSION['fax'])) ? $_SESSION['fax'] : '';
	$email			= (isset($_SESSION['email'])) ? $_SESSION['email'] : '';
	$gender			= (isset($_SESSION['gender'])) ? get_gender_by_code($_SESSION['gender']) : get_gender_by_code('');

	$tpl->assign(
		array(
			'VL_USR_NAME'		=> tohtml($first_name),
			'VL_LAST_USRNAME'	=> tohtml($last_name),
			'VL_USR_FIRM'		=> tohtml($company),
			'VL_USR_POSTCODE'	=> tohtml($postal_code),
			'VL_USR_GENDER'		=> tohtml($gender),
			'VL_USRCITY'		=> tohtml($city),
			'VL_USRSTATE'		=> tohtml($state),
			'VL_COUNTRY'		=> tohtml($country),
			'VL_STREET1'		=> tohtml($street1),
			'VL_STREET2'		=> tohtml($street2),
			'VL_PHONE'			=> tohtml($phone),
			'VL_FAX'			=> tohtml($fax),
			'VL_EMAIL'			=> tohtml($email),
		)
	);
}

/*
 * functions end
 */
?>