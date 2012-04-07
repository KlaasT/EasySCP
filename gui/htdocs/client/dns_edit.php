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

require_once '../../include/easyscp-lib.php';
require_once '../../include/Net/DNS.php';

check_login(__FILE__);

$cfg = EasySCP_Registry::get('Config');

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'client/dns_edit.tpl';

$DNS_allowed_types = array('A', 'AAAA', 'CNAME', 'MX', 'SRV', 'NS');

$add_mode = preg_match('~dns_add.php~', $_SERVER['REQUEST_URI']);

// static page messages
$tpl->assign(
	array(
		'TR_PAGE_TITLE'			=> ($add_mode)
			? tr("EasySCP - Manage Domain Alias/Add DNS zone's record")
			: tr("EasySCP - Manage Domain Alias/Edit DNS zone's record"),
		'ACTION_MODE'			=> ($add_mode) ? 'dns_add.php' : 'dns_edit.php?edit_id={ID}',
		'TR_MODIFY'				=> tr('Modify'),
		'TR_CANCEL'				=> tr('Cancel'),
		'TR_ADD'				=> tr('Add'),
		'TR_DOMAIN'				=> tr('Domain'),
		'TR_EDIT_DNS'			=> ($add_mode) ? tr("Add DNS zone's record") : tr("Edit DNS zone's record"),
		'TR_DNS'				=> tr("DNS zone's records"),
		'TR_DNS_NAME'			=> tr('Name'),
		'TR_DNS_CLASS'			=> tr('Class'),
		'TR_DNS_TYPE'			=> tr('Type'),
		'TR_DNS_SRV_NAME'		=> tr('Service name'),
		'TR_DNS_IP_ADDRESS'		=> tr('IP address'),
		'TR_DNS_IP_ADDRESS_V6'	=> tr('IPv6 address'),
		'TR_DNS_SRV_PROTOCOL'	=> tr('Service protocol'),
		'TR_DNS_SRV_TTL'		=> tr('TTL'),
		'TR_DNS_SRV_PRIO'		=> tr('Priority'),
		'TR_DNS_SRV_WEIGHT'		=> tr('Relative weight for records with the same priority'),
		'TR_DNS_SRV_HOST'		=> tr('Target host'),
		'TR_DNS_SRV_PORT'		=> tr('Target port'),
		'TR_DNS_TXT'			=> tr('Text'),
		'TR_DNS_CNAME'			=> tr('Canonical name'),
		'TR_DNS_PLAIN'			=> tr('Plain record data'),
		'TR_MANAGE_DOMAIN_DNS'	=> tr("DNS zone's records"),
		'TR_DNS_NS'				=> tr('Hostname of Nameserver'),
	)
);

gen_client_mainmenu($tpl, 'client/main_menu_manage_domains.tpl');
gen_client_menu($tpl, 'client/menu_manage_domains.tpl');

gen_logged_from($tpl);
$tpl->assign(($add_mode) ? 'FORM_ADD_MODE' : 'FORM_EDIT_MODE', true);

// "Modify" button has been pressed
$editid = null;
if (isset($_POST['uaction']) && ($_POST['uaction'] === 'modify')) {
	if (isset($_GET['edit_id'])) {
		$editid = $_GET['edit_id'];
	} else if (isset($_SESSION['edit_ID'])) {
		$editid = $_SESSION['edit_ID'];
	} else {
		unset($_SESSION['edit_ID']);
		not_allowed();
	}
	// Save data to db
	if (check_fwd_data($tpl, $editid)) {
		$_SESSION['dnsedit'] = "_yes_";
		user_goto('domains_manage.php');
	}
} elseif (isset($_POST['uaction']) && ($_POST['uaction'] === 'add')) {
	if (check_fwd_data($tpl, true)) {
		$_SESSION['dnsedit'] = "_yes_";
		user_goto('domains_manage.php');
	}

} else {
	// Get user id that come for edit
	if (isset($_GET['edit_id'])) {
		$editid = $_GET['edit_id'];
	} else{
		$editid = 0;
	}
	$_SESSION['edit_ID'] = $editid;
}

gen_editdns_page($tpl, $editid);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

// Begin function block

function mysql_get_enum($sql, $object, &$default = null) {

	list($table, $col) = explode(".", $object);

	$res = exec_query($sql, "SHOW COLUMNS FROM ".$table." LIKE '".$col."'");
	$row = $res->fetchRow();
	$default = $row['Default'];

	return (($row)
		? explode("','", preg_replace("/(enum|set)\('(.+?)'\)/","\\2", $row['Type']))
		: array(0 => 'None'));
}

/**
 * @todo use template loop instead of this hardcoded HTML
 */
function create_options($data, $value = null) {

	$cfg = EasySCP_Registry::get('Config');

	$res = '';
	reset($data);

	foreach ($data as $item) {
		$res .= '<option value="' . $item . '"' .
				(($item == $value) ? $cfg->HTML_SELECTED : '') . '>' . $item .
				'</option>';
	}
	return $res;
}

// Show user data
function not_allowed() {
	$_SESSION['dnsedit'] = '_no_';
	user_goto('domains_manage.php');
}

function decode_zone_data($data) {

	$address = $addressv6 = $srv_name = $srv_proto = $cname = $txt = $name = '';
	$srv_TTL = $srv_prio = $srv_weight = $srv_host = $srv_port = $ns = '';

	if (is_array($data)) {
		$name = $data['domain_dns'];
		switch ($data['domain_type']) {
			case 'A':
				$address = $data['domain_text'];
				break;
			case 'AAAA':
				$addressv6 = $data['domain_text'];
				break;
			case 'CNAME':
				$cname = $data['domain_text'];
				break;
			case 'NS':
				$ns = $data['domain_text'];
				break;
			case 'SRV':
				$name = '';
				if (preg_match('~_([^\.]+)\._([^\s]+)[\s]+([\d]+)~', $data['domain_dns'], $srv)) {
					$srv_name = $srv[1];
					$srv_proto = $srv[2];
					$srv_TTL = $srv[3];
				}
				if (preg_match('~([\d]+)[\s]+([\d]+)[\s]+([\d]+)[\s]+([^\s]+)+~', $data['domain_text'], $srv)) {
					$srv_prio = $srv[1];
					$srv_weight = $srv[2];
					$srv_port = $srv[3];
					$srv_host = $srv[4];
				}
				break;
			case 'MX':
				$name = '';
				if (preg_match('~([\d]+)[\s]+([^\s]+)+~', $data['domain_text'], $srv)) {
					$srv_prio = $srv[1];
					$srv_host = $srv[2];
				}
				break;
			case 'TXT':
				$name = '';
				// @todo implement
				break;
			default:
				$txt = $data['domain_text'];
		}
	}
	return array(
		$name, $address, $addressv6, $srv_name, $srv_proto, $srv_TTL, $srv_prio,
		$srv_weight, $srv_host, $srv_port, $cname, $txt, $ns, $data['protected']
	);
}

/**
 * @todo use template loop instead of this hardcoded HTML
 * @param EasySCP_TemplateEngine $tpl
 * @param int $edit_id
 */
function gen_editdns_page($tpl, $edit_id) {

	global $sql, $DNS_allowed_types;
	$cfg = EasySCP_Registry::get('Config');

	list(
		$dmn_id, ,,,,,,,,,,,,,,,,,,,,,$dmn_dns
	) = get_domain_default_props($sql, $_SESSION['user_id']);

	if ($dmn_dns != 'yes') {
		not_allowed();
	}
	if ($GLOBALS['add_mode']) {
		$data = null;

		$query = "
			SELECT
				'0' AS `alias_id`,
				`domain`.`domain_name` AS `domain_name`
			FROM
				`domain`
			WHERE
				`domain_id` = :domain_id
			UNION
			SELECT
				`domain_aliasses`.`alias_id`,
				`domain_aliasses`.`alias_name`
			FROM
				`domain_aliasses`
			WHERE
				`domain_aliasses`.`domain_id` = :domain_id
			AND `alias_status` <> :state
		";

		$res = exec_query($sql, $query, array('domain_id' => $dmn_id, 'state' => $cfg->ITEM_ORDERED_STATUS));
		$sel = '';
		while ($row = $res->fetchRow()) {
			$sel.= '<option value="' . $row['alias_id'] . '">' .
					decode_idna($row['domain_name']) . '</option>';
		}
		$tpl->assign(
			array(
				'SELECT_ALIAS'	=> $sel,
				'ADD_RECORD'	=> true
			)
		);

	} else {
		$query = "SELECT * FROM
					`domain_dns`
				WHERE
					`domain_dns_id` = ?
				AND
					`domain_id` = ?
			;";
		$res = exec_query($sql, $query, array($edit_id, $dmn_id));
		if ($res->recordCount() <= 0)
		not_allowed();
		$data = $res->fetchRow();
	}

	list(
		$name, $address, $addressv6, $srv_name, $srv_proto, $srv_ttl, $srv_prio,
		$srv_weight, $srv_host, $srv_port, $cname, $plain, $protected, $ns
	) = decode_zone_data($data);

	// Protection against edition (eg. for external mail MX record)
	if($protected == 'yes') {
		set_page_message(
			tr('You are not allowed to edit this DNS record!'),
			'error'
		);
		not_allowed();
	}

	$dns_type = create_options(array_intersect($DNS_allowed_types, mysql_get_enum($sql, "domain_dns.domain_type")), tryPost('type', $data['domain_type']));
	$dns_class = create_options(mysql_get_enum($sql, "domain_dns.domain_class"), tryPost('class', $data['domain_class']));

	$tpl->assign(
		array(
			'SELECT_DNS_TYPE'			=> $dns_type,
			'SELECT_DNS_CLASS'			=> $dns_class,
			'DNS_NAME'					=> tohtml($name),
			'DNS_ADDRESS'				=> tohtml(tryPost('dns_A_address', $address)),
			'DNS_ADDRESS_V6'			=> tohtml(tryPost('dns_AAAA_address', $addressv6)),
			'SELECT_DNS_SRV_PROTOCOL'	=> create_options(array('tcp', 'udp'), tryPost('srv_proto', $srv_proto)),
			'DNS_SRV_NAME'				=> tohtml(tryPost('dns_srv_name', $srv_name)),
			'DNS_SRV_TTL'				=> tohtml(tryPost('dns_srv_ttl', $srv_ttl)),
			'DNS_SRV_PRIO'				=> tohtml(tryPost('dns_srv_prio', $srv_prio)),
			'DNS_SRV_WEIGHT'			=> tohtml(tryPost('dns_srv_weight', $srv_weight)),
			'DNS_SRV_HOST'				=> tohtml(tryPost('dns_srv_host', $srv_host)),
			'DNS_SRV_PORT'				=> tohtml(tryPost('dns_srv_port', $srv_port)),
			'DNS_CNAME'					=> tohtml(tryPost('dns_cname', $cname)),
			'DNS_PLAIN'					=> tohtml(tryPost('dns_plain_data', $plain)),
			'DNS_NS_HOSTNAME'					=> tohtml(tryPost('dns_ns', $ns)),
			'ID'						=> $edit_id
		)
	);
}

// Check input data
function tryPost($id, $data) {

	if (array_key_exists($id, $_POST)) {
		return $_POST[$id];
	}
	return $data;
}

function validate_NS($record, &$err = null) {
	if (!preg_match('~([^a-z,A-Z,0-9\.])~u', $record['dns_ns'], $e)) {
		$err .= sprintf(tr('Use of disallowed char("%s") in NS'), $e[1]);
		return false;
	}
	if (empty($record['dns_ns'])) {
		$err .= tr('Name must be filled.');
		return false;
	}
	return true;
}

function validate_CNAME($record, &$err = null) {

	if (preg_match('~([^a-z,A-Z,0-9\.])~u', $record['dns_cname'], $e)) {
		$err .= sprintf(tr('Use of disallowed char("%s") in CNAME'), $e[1]);
		return false;
	}
	if (empty($record['dns_name'])) {
		$err .= tr('Name must be filled.');
		return false;
	}
	return true;
}

function validate_A($record, &$err = null) {

	if (filter_var($record['dns_A_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
		$err .= sprintf(tr('Wrong IPv4 address ("%s").'), $record['dns_A_address']);
		return false;
	}
	if (empty($record['dns_name'])) {
		$err .= tr('Name must be filled.');
		return false;
	}
	return true;
}

function validate_AAAA($record, &$err = null) {

	if (filter_var($record['dns_AAAA_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
		$err .= sprintf(tr('Wrong IPv6 address ("%s").'), $record['dns_AAAA_address']);
		return false;
	}

	if (empty($record['dns_name'])) {
		$err .= tr('Name must be filled.');
		return false;
	}

	return true;
}

function validate_SRV($record, &$err, &$dns, &$text) {

	if (!preg_match('~^([\d]+)$~', $record['dns_srv_port'])) {
		$err .= tr('Port must be a number!');
		return false;
	}

	if (!preg_match('~^([\d]+)$~', $record['dns_srv_ttl'])) {
		$err .= tr('TTL must be a number!');
		return false;
	}

	if (!preg_match('~^([\d]+)$~', $record['dns_srv_prio'])) {
		$err .= tr('Priority must be a number!');
		return false;
	}

	if (!preg_match('~^([\d]+)$~', $record['dns_srv_weight'])) {
		$err .= tr('Relative weight must be a number!');
		return false;
	}

	if (empty($record['dns_srv_name'])) {
		$err .= tr('Service must be filled.');
		return false;
	}

	if (empty($record['dns_srv_host'])) {
		$err .= tr('Host must be filled.');
		return false;
	}

	$dns = sprintf("_%s._%s\t%d", $record['dns_srv_name'], $record['srv_proto'], $record['dns_srv_ttl']);
	$text = sprintf("%d\t%d\t%d\t%s", $record['dns_srv_prio'], $record['dns_srv_weight'], $record['dns_srv_port'], $record['dns_srv_host']);

	return true;
}

function validate_MX($record, &$err, &$text) {

	// Add a dot in the end if not
	if (substr($record['dns_srv_host'], -1) != '.') {
		$record['dns_srv_host'] .= '.';
	}


	if (!preg_match('~^([\d]+)$~', $record['dns_srv_prio'])) {
		$err .= tr('Priority must be a number!');
		return false;
	}

	if (empty($record['dns_srv_host'])) {
		$err .= tr('Host must be filled.');
		return false;
	}

	$text = sprintf("%d\t%s", $record['dns_srv_prio'], $record['dns_srv_host']);
	return true;
}

function check_CNAME_conflict($domain, &$err) {

	$resolver = new Net_DNS_resolver();
	$resolver->nameservers = array('localhost');
	$res = $resolver->query($domain, 'CNAME');

	if ($res === false) {
		return true;
	}

	$err .= tr('conflict with CNAME record');
	return false;
}

function validate_NAME($domain, &$err) {
	if (preg_match('~([^-a-z,A-Z,0-9.])~u', $domain['name'], $e)) {
		$err .= sprintf(tr('Use of disallowed char("%s") in NAME'), $e[1]);
		return false;
	}
	if (preg_match('/\.$/', $domain['name'])) {
		if (!preg_match('/'.str_replace('.', '\.', $domain['domain']).'\.$/', $domain['name'])) {
			$err .= sprintf(tr('Record "%s" is not part of domain "%s".', $domain['name'], $domain['domain']));
			return false;
		}
	}
	return true;
}

/**
 * @throws EasySCP_Exception_Database
 * @param EasySCP_TemplateEngine $tpl
 * @param int $edit_id
 * @return bool
 */
function check_fwd_data($tpl, $edit_id) {

	global $sql;
	$cfg = EasySCP_Registry::get('Config');

	$add_mode = $edit_id === true;

	// unset errors
	$ed_error = '_off_';
	$err = '';

	$_text = '';
	$_class = $_POST['class'];
	$_type = $_POST['type'];

	list($dmn_id) = get_domain_default_props($sql, $_SESSION['user_id']);
	if ($add_mode) {
		$query = "
			SELECT
				*
			FROM (
				SELECT
					'0' AS `alias_id`,
					`domain`.`domain_name` AS `domain_name`
				FROM
					`domain`
				WHERE
					`domain_id` = ?
				UNION
				SELECT
					`domain_aliasses`.`alias_id`,
					`domain_aliasses`.`alias_name`
				FROM
					`domain_aliasses`
				WHERE
					`domain_aliasses`.`domain_id` = ?
			) AS `tbl`
			WHERE
				IFNULL(`tbl`.`alias_id`, 0) = ?
		";
		$res = exec_query($sql, $query, array($dmn_id, $dmn_id, $_POST['alias_id']));
		if ($res->recordCount() <= 0) {
			not_allowed();
		}
		$alias_id = $res->fetchRow();
		$record_domain = $alias_id['domain_name'];
		// if no alias is selected, ID is 0 else the real alias_id
		$alias_id = $alias_id['alias_id'];
	} else {
		$res = exec_query($sql, "
		SELECT
			 powerdns.domains.*,
			IFNULL(`domain_aliasses`.`alias_name`,`domain`.`domain_name`) AS `domain_name`
		FROM
			`powerdns`.`domains`
			RIGHT JOIN `powerdns`.`records` ON (powerdns.records.domain_id = powerdns.domains.id)
			LEFT JOIN `domain_aliasses` USING (`domain_id`, `alias_id`)
			LEFT JOIN `domain` USING (`domain_id`)
		WHERE
			`domain_dns_id` = ?
		AND
		`domain_id` = ?
		", array($edit_id, $dmn_id));
		if ($res->recordCount() <= 0) {
			not_allowed();
		}
		$data = $res->fetchRow();
		$record_domain = $data['domain_name'];
		$alias_id = $data['alias_id'];
		$_dns = $data['domain_dns'];
	}

	if (!validate_NAME(array('name' => $_POST['dns_name'], 'domain' => $record_domain), $err)) {
		$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
	}
	switch ($_POST['type']) {
		case 'CNAME':
			if (!validate_CNAME($_POST, $err))
				$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
			$_text = $_POST['dns_cname'];
			$_dns = $_POST['dns_name'];
			break;
		case 'A':
			if (!validate_A($_POST, $err))
				$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
			if (!check_CNAME_conflict($_POST['dns_name'].'.'.$record_domain, $err))
				$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
			$_text = $_POST['dns_A_address'];
			$_dns = $_POST['dns_name'];
			break;
		case 'AAAA':
			if (!validate_AAAA($_POST, $err))
				$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
			if (!check_CNAME_conflict($_POST['dns_name'].'.'.$record_domain, $err))
				$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
			$_text = $_POST['dns_AAAA_address'];
			$_dns = $_POST['dns_name'];
			break;
		case 'SRV':
			if (!validate_SRV($_POST, $err, $_dns, $_text))
				$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
			break;
		case 'MX':
			$_dns = '';
			if (!validate_MX($_POST, $err, $_text)) {
				$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
			} else {
				$_dns = $record_domain . '.';
			}
			break;
		case 'NS':
			$_text = '';
			if (!validate_NS($_POST, $err)) {
				$ed_error = sprintf(tr('Cannot validate %s record. Reason \'%s\'.'), $_POST['type'], $err);
			}
			$_text = $_POST['dns_ns'];
			break;
		default :
			$ed_error = sprintf(tr('Unknown zone type %s!'), $_POST['type']);
	}

	if ($ed_error === '_off_') {

		if ($add_mode) {
			$query = "
				INSERT INTO
					`domain_dns` (
						`domain_id`, `alias_id`, `domain_dns`, `domain_class`,
						`domain_type`, `domain_text`
					) VALUES (
						?, ?, ?, ?, ?, ?
					)
				;
			";

			$rs = exec_query(
				$sql, $query,
				array($dmn_id, $alias_id, $_dns, $_class, $_type, $_text),
				false
			);

			# Error because duplicate entry ? (SQLSTATE 23000)
			if($rs === false) {
				if($sql->getLastErrorCode() == 23000) {
					$tpl->assign(
						array(
							'MESSAGE' => tr('DNS record already exist!'),
							'MYG_TYPE' => 'error'
						)
					);

					return false;
				} else { # Another error ? Throw exception
					throw new EasySCP_Exception_Database(
						$sql->getLastErrorMessage() . " - Query: $query"
					);
				}
			}

		} else {

			$query = "
				UPDATE
					`domain_dns`
				SET
					`domain_dns` = ?, `domain_class` = ?, `domain_type` = ?,
					`domain_text` = ?
				WHERE
					`domain_dns_id` = ?
				;
			";

			exec_query(
				$sql, $query, array($_dns, $_class, $_type, $_text, $edit_id)
			);
		}

		if ($alias_id == 0) {

			$query = "
				UPDATE
					`domain`
 				SET
					`domain`.`domain_status` = ?
 				WHERE
    				`domain`.`domain_id` = ?
    			;
   			";

			exec_query(
				$sql, $query, array($cfg->ITEM_DNSCHANGE_STATUS, $dmn_id)
			);

		} else {

			$query = "
 				UPDATE
 					`domain_aliasses`
				SET
					`domain_aliasses`.`alias_status` = ?
 				WHERE
					`domain_aliasses`.`domain_id` = ?
				AND	`domain_aliasses`.`alias_id` = ?
			";

			exec_query(
				$sql, $query,
				array($cfg->ITEM_DNSCHANGE_STATUS, $dmn_id, $alias_id)
			);
		}

		// Send request to ispCP daemon
		send_request();

		$admin_login = $_SESSION['user_logged'];
		write_log("$admin_login: " . (($add_mode) ? 'add new' : ' modify') . " dns zone record.");

		unset($_SESSION['edit_ID']);
		$tpl->assign('MESSAGE', "");
		return true;
	} else {
		$tpl->assign('MESSAGE', $ed_error);
		return false;
	}
} // End of check_user_data()
?>