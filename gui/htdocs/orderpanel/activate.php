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

$cfg = EasySCP_Registry::get('Config');

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'orderpanel/activate.tpl';

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || !isset($_GET['k'])) {
	throw new EasySCP_Exception_Production(tr('You do not have permission to access this interface!'));
}

if (validate_order_key($_GET['id'], $_GET['k'])) {
	confirm_order($_GET['id']);
	$msg = tr('Your order has been successfully created.');
} else {
	$msg = tr('Error creating order! Perhaps already activated?');
}

// static page messages
$tpl->assign(
	array(
		'PAGE_TITLE' =>  tr('Order confirmation'),
		'ORDER_STATUS_MESSAGE' => $msg
	)
);

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/**
 * Validate activation parameters
 * @param integer $order_id ID in table orders
 * @param string $key hash value to compare with
 * @return boolean true - validation correct
 */
function validate_order_key($order_id, $key) {

	$cfg = EasySCP_Registry::get('Config');

	$result = false;
	$sql = EasySCP_Registry::get('Db');
	$query = "
		SELECT
			*
		FROM
			`orders`
		WHERE
			`id` = ?
		AND
			`status` = ?
	";
	$rs = exec_query($sql, $query, array($order_id, 'unconfirmed'));
	if ($rs->recordCount() == 1) {
		$domain_name	= $rs->fields['domain_name'];
		$admin_id		= $rs->fields['user_id'];
		$coid =    isset($cfg->CUSTOM_ORDERPANEL_ID) ? $cfg->CUSTOM_ORDERPANEL_ID : '';
		$ckey = sha1($order_id.'-'.$domain_name.'-'.$admin_id.'-'.$coid);
		if ($ckey == $key)
			$result = true;
	}
	return $result;
}

/**
 * Set order to confirmed so that reseller can activate this
 * @param integer $order_id
 */
function confirm_order($order_id) {

	$cfg = EasySCP_Registry::get('Config');
	$sql = EasySCP_Registry::get('Db');

	$query = "
		SELECT
			*
		FROM
			`orders`
		WHERE
			`id` = ?
	";
	$rs = exec_query($sql, $query, $order_id);
	if ($rs->recordCount() == 1) {

		$query = "
			UPDATE `orders` SET `status` = ? WHERE `id` = ?
		";
		exec_query($sql, $query, array('new', $order_id));

		$admin_id		= $rs->fields['user_id'];
		$domain_name	= $rs->fields['domain_name'];
		$ufname			= $rs->fields['fname'];
		$ulname			= $rs->fields['lname'];
		$uemail			= $rs->fields['email'];
		$name = trim($ufname.' '.$ulname);

		$data = get_order_email($admin_id);

		$from_name = $data['sender_name'];
		$from_email = $data['sender_email'];

		$search [] = '{DOMAIN}';
		$replace[] = $domain_name;
		$search [] = '{MAIL}';
		$replace[] = $uemail;
		$search [] = '{NAME}';
		$replace[] = $name;

		if ($from_name) {
			$from = '"' . mb_encode_mimeheader($from_name, 'UTF-8') . "\" <" . $from_email . ">";
		} else {
			$from = $from_email;
		}

		// moved from reseller-functions.php:
		// let's send mail to the reseller => new order
		$subject = mb_encode_mimeheader(tr("You have a new order"), 'UTF-8');

		$message = tr('

Dear {RESELLER},
you have a new order from {NAME} <{MAIL}> for domain {DOMAIN}

Please login into your EasySCP control panel for more details.

'); // Please, do not put tab here - i18n issue

		$search [] = '{RESELLER}';
		$replace[] = $from_name;
		$message = str_replace($search, $replace, $message);
		$message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');

		$headers = "From: ". $from . "\n";
		$headers .= "MIME-Version: 1.0\n" . "Content-Type: text/plain; charset=utf-8\n" . "Content-Transfer-Encoding: 8bit\n" . "X-Mailer: EasySCP " . $cfg->Version . " Service Mailer";

		mail($from, $subject, $message, $headers);
	}
}
?>