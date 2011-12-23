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
$template = 'orderpanel/checkout.tpl';

if (isset($_SESSION['user_id']) && isset($_SESSION['plan_id'])) {
	$user_id = $_SESSION['user_id'];
	$plan_id = $_SESSION['plan_id'];
} else {
	throw new EasySCP_Exception_Production(
		tr('You do not have permission to access this interface!')
	);
}

if (!isset($_POST['capcode']) || $_POST['capcode'] != $_SESSION['image']) {
	set_page_message(tr('Security code was incorrect!'), 'error');
	user_goto('chart.php');
}


// If term of service field was set (not empty value)
if (isset($_SESSION['tos']) && $_SESSION['tos'] == true) {
	if (!isset($_POST['tosAccept']) || $_POST['tosAccept'] != 1) {
		set_page_message(
			tr('You have to accept the Term of Service!'),
			'warning'
		);
		user_goto('chart.php');
	}
}

if ((isset($_SESSION['fname']) && $_SESSION['fname'] != '')
	&& (isset($_SESSION['lname']) && $_SESSION['lname'] != '')
	&& (isset($_SESSION['email']) && $_SESSION['email'] != '')
	&& (isset($_SESSION['zip']) && $_SESSION['zip'] != '')
	&& (isset($_SESSION['city']) && $_SESSION['city'] != '')
	&& (isset($_SESSION['country']) && $_SESSION['country'] != '')
	&& (isset($_SESSION['street1']) && $_SESSION['street1'] != '')
	&& (isset($_SESSION['phone']) && $_SESSION['phone'] != '')
	) {
	gen_checkout($tpl, $sql, $user_id, $plan_id);
} else {
	user_goto('index.php?user_id=' . $user_id);
}

// static page messages
$tpl->assign(
	array(
		'CHECK_OUT' => tr('Check Out'),
		'THANK_YOU_MESSAGE' => tr('<strong>Thank you for purchasing.</strong><br />You will receive an e-mail with more details and information.')
	)
);

gen_purchase_haf($tpl, $sql, $user_id);

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
 * @return void
 */
function gen_checkout($tpl, $sql, $user_id, $plan_id) {
	$date = time();
	$domain_name = $_SESSION['domainname'];
	$fname = $_SESSION['fname'];
	$lname = $_SESSION['lname'];
	$gender = $_SESSION['gender'];

	$firm = (isset($_SESSION['firm'])) ? $_SESSION['firm'] : '';

	$zip = $_SESSION['zip'];
	$city = $_SESSION['city'];
	$state = $_SESSION['state'];
	$country = $_SESSION['country'];
	$email = $_SESSION['email'];
	$phone = $_SESSION['phone'];

	$fax = (isset($_SESSION['fax'])) ? $_SESSION['fax'] : '';

	$street1 = $_SESSION['street1'];

	$street2 = (isset($_SESSION['street2'])) ? $_SESSION['street2'] : '';

	$status = 'unconfirmed';

	$query = "
		INSERT INTO
			`orders`
				(`user_id`,
				`plan_id`,
				`date`,
				`domain_name`,
				`fname`,
				`lname`,
				`gender`,
				`firm`,
				`zip`,
				`city`,
				`state`,
				`country`,
				`email`,
				`phone`,
				`fax`,
				`street1`,
				`street2`,
				`status`)
		VALUES
			(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	";

	exec_query($sql, $query, array($user_id, $plan_id, $date, $domain_name, $fname, $lname, $gender, $firm, $zip, $city, $state, $country, $email, $phone, $fax, $street1, $street2, $status));

	$order_id = $sql->insertId();
	send_order_emails($user_id, $domain_name, $fname, $lname, $email, $order_id);

	// Remove useless data
	unset($_SESSION['details']);
	unset($_SESSION['domainname']);
	unset($_SESSION['fname']);
	unset($_SESSION['lname']);
	unset($_SESSION['gender']);
	unset($_SESSION['email']);
	unset($_SESSION['firm']);
	unset($_SESSION['zip']);
	unset($_SESSION['city']);
	unset($_SESSION['state']);
	unset($_SESSION['country']);
	unset($_SESSION['street1']);
	unset($_SESSION['street2']);
	unset($_SESSION['phone']);
	unset($_SESSION['fax']);
	unset($_SESSION['plan_id']);
	unset($_SESSION['image']);
	unset($_SESSION['tos']);
}

/*
 * functions end
 */
?>