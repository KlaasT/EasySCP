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

if (isset($_GET['edit_id']) && $_GET['edit_id'] !== '') {

	$cfg = EasySCP_Registry::get('Config');

	$dns_id = (int) $_GET['edit_id'];
	$dmn_id = get_user_domain_id($sql, $_SESSION['user_id']);

	$query = "
		SELECT
			`domain_dns`.`domain_dns_id`, `domain_dns`.`domain_dns`,
			`domain_dns`.`alias_id`,
			IFNULL(`domain_aliasses`.`alias_name`, `domain`.`domain_name`) AS domain_name,
			IFNULL(`domain_aliasses`.`alias_id`, `domain_dns`.`domain_id`) AS id,
			`domain_dns`.`protected`
		FROM
			`domain_dns`
		LEFT JOIN
			`domain_aliasses` USING (`alias_id`, `domain_id`), `domain`
		WHERE
			`domain_dns`.`domain_id` = ?
		AND
			`domain_dns`.`domain_dns_id` = ?
		AND
			`domain`.`domain_id` = `domain_dns`.`domain_id`
		;
	";

	$rs = exec_query($sql, $query, array($dmn_id, $dns_id));
	$dom_name = $rs->fields['domain_name'];
	$dns_name = $rs->fields['domain_dns'];
	$id = $rs->fields['id'];
	$alias_id = $rs->fields['alias_id'];

	// DNS record not found or not owned by current customer ?
	if ($rs->recordCount() == 0) {
		// Back to the main page
		user_goto('domains_manage.php');
	} elseif($rs->fields['protected'] == 'yes') {
		set_page_message(
			tr('You are not allowed to remove this DNS record!'),
			'error'
		);
		user_goto('domains_manage.php');
	}

	// Delete DNS record from the database
	$query = "
		DELETE FROM
			`domain_dns`
		WHERE
			`domain_dns_id` = ?
		;
	";

	$rs = exec_query($sql, $query, $dns_id);

	if (empty($alias_id)) {

		$query = "
			UPDATE
				`domain`
			SET
				`domain`.`domain_status` = ?
			WHERE
   				`domain`.`domain_id` = ?
   			;
  		";

		exec_query($sql, $query, array($cfg->ITEM_DNSCHANGE_STATUS, $dmn_id));

	} else {

		$query = "
 			UPDATE
 				`domain_aliasses`
			SET
				`domain_aliasses`.`alias_status` = ?
 			WHERE
				`domain_aliasses`.`domain_id` = ?
			AND
				`domain_aliasses`.`alias_id` = ?
			;
		";

		exec_query(
			$sql, $query,
			array($cfg->ITEM_DNSCHANGE_STATUS, $dmn_id, $alias_id)
		);
	}

	// Send request to ispCP daemon
	send_request();

	write_log(
		$_SESSION['user_logged'] . ': deletes dns zone record: ' . $dns_name .
		' of domain ' . $dom_name
	);

	set_page_message(tr('Custom DNS record scheduled for deletion!'), 'success');
}

//  Back to the main page
user_goto('domains_manage.php');
