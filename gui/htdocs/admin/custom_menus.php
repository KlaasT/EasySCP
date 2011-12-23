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

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'admin/custom_menus.tpl';

if (isset($_GET['delete_id'])) {
	delete_button($sql);
}

if (isset($_GET['edit_id'])) {
	edit_button($tpl, $sql);
}

if (isset($_POST['uaction']) && $_POST['uaction'] != '' ) {
	switch ($_POST['uaction']){
		case 'new_button':
			add_new_button($sql);
			break;
		case 'edit_button':
			update_button($sql);
			break;
	}
}

gen_button_list($tpl, $sql);

$tpl->assign(
	array(
		'TR_PAGE_TITLE' => tr('EasySCP - Admin - Manage custom menus'),
		'TR_TITLE_CUSTOM_MENUS' => tr('Manage custom menus'),
		'TR_ADD_NEW_BUTTON' => tr('Add new button'),
		'TR_BUTTON_NAME' => tr('Button name'),
		'TR_BUTTON_LINK' => tr('Button link'),
		'TR_BUTTON_TARGET' => tr('Button target'),
		'TR_BUTTON_ICON' => tr('Button icon'),
		'TR_VIEW_FROM' => tr('Show in'),
		'ADMIN' => tr('Administrator level'),
		'RESELLER' => tr('Reseller level'),
		'USER' => tr('Enduser level'),
		'RESSELER_AND_USER' => tr('Reseller and enduser level'),
		'TR_ADD' => tr('Add'),
		'TR_MENU_NAME' => tr('Menu button'),
		'TR_ACTON' => tr('Action'),
		'TR_EDIT' => tr('Edit'),
		'TR_DELETE' => tr('Delete'),
		'TR_LEVEL' => tr('Level'),
		'TR_SAVE' => tr('Save'),
		'TR_EDIT_BUTTON' => tr('Edit button'),
		'TR_MESSAGE_DELETE'	=> tr('Are you sure you want to delete %s?', true, '%s')
	)
);

gen_admin_mainmenu($tpl, 'admin/main_menu_settings.tpl');
gen_admin_menu($tpl, 'admin/menu_settings.tpl');

gen_page_message($tpl);

if (isset($_GET['edit_id'])) {
	$tpl->assign('EDIT_BUTTON', true);
} else {
	$tpl->assign('ADD_BUTTON', true);
}

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/**
 * @param EasySCP_TemplateEngine $tpl
 * @param EasySCP_Database $sql
 * @return void
 */
function gen_button_list($tpl, $sql) {
	$query = "
		SELECT
			*
		FROM
			`custom_menus`
	";

	$rs = exec_query($sql, $query);
	if ($rs->recordCount() == 0) {
		$tpl->assign('BUTTON_LIST', '');

		set_page_message(
			tr('You have no custom menus.'),
			'info'
		);
	} else {
		global $i;

		while (!$rs->EOF) {
			$menu_id = $rs->fields['menu_id'];
			$menu_level = $rs->fields['menu_level'];
			$menu_name = $rs->fields['menu_name'];
			$menu_link = $rs->fields['menu_link'];

			if ($menu_level === 'admin') {
				$menu_level = tr('Administrator');
			} else if ($menu_level === 'reseller') {
				$menu_level = tr('Reseller');
			} else if ($menu_level === 'user') {
				$menu_level = tr('User');
			} else if ($menu_level === 'all') {
				$menu_level = tr('All');
			}

			$tpl->append(
				array(
					'BUTTON_LINK'		=> tohtml($menu_link),
					'BUTTON_ID'			=> $menu_id,
					'LEVEL'				=> tohtml($menu_level),
					'MENU_NAME'			=> tohtml($menu_name),
					'MENU_NAME2'		=> addslashes(clean_html($menu_name)),
					'LINK'				=> tohtml($menu_link),
					'CONTENT'			=> ($i % 2 == 0) ? 'content' : 'content2'
				)
			);

			$rs->moveNext();
			$i++;
		} // end while
	} // end else
}

function add_new_button($sql) {
	$button_name = clean_input($_POST['bname']);
	$button_link = clean_input($_POST['blink']);
	$button_target = clean_input($_POST['btarget']);
	$button_icon = clean_input($_POST['bticon']);
	$button_view = $_POST['bview'];

	if (empty($button_name) || empty($button_link) || empty($button_icon)) {
		set_page_message(
			tr('Missing or incorrect data input!'),
			'error'
		);
		return;
	}

	if (!filter_var($button_link, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED)) {
		set_page_message(
			tr('Invalid URL!'),
			'warning'
		);
		return;
	}

	if (!empty($button_target)
		&& !in_array($button_target, array('_blank', '_parent', '_self', '_top'))) {
		set_page_message(
			tr('Invalid target!'),
			'warning'
		);
		return;
	}

	$query = "
		INSERT INTO `custom_menus`
			(
			`menu_level`,
			`menu_name`,
			`menu_link`,
			`menu_target`
			`menu_icon`
			)
		VALUES (?, ?, ?, ?, ?)
	";

	exec_query($sql, $query, array(
		$button_view,
		$button_name,
		$button_link,
		$button_target,
		$button_icon
		)
	);

	set_page_message(
		tr('Custom menu data updated successful!'),
		'success'
	);
	return;
}

function delete_button($sql) {
	if ($_GET['delete_id'] === '' || !is_numeric($_GET['delete_id'])) {
		set_page_message(
			tr('Missing or incorrect data input!'),
			'warning'
		);
		return;
	} else {
		$delete_id = $_GET['delete_id'];

		$query = "
			DELETE FROM
				`custom_menus`
			WHERE
				`menu_id` = ?
		";

		exec_query($sql, $query, $delete_id);

		set_page_message(
			tr('Custom menu deleted successful!'),
			'success'
		);
		return;
	}
}

/**
 * @param EasySCP_TemplateEngine $tpl
 * @param EasySCP_Database $sql
 */
function edit_button($tpl, $sql) {

	$cfg = EasySCP_Registry::get('Config');

	if ($_GET['edit_id'] === '' || !is_numeric($_GET['edit_id'])) {
		set_page_message(
			tr('Missing or incorrect data input!'),
			'warning'
		);
		return;
	} else {
		$edit_id = $_GET['edit_id'];

		$query = "
			SELECT
				*
			FROM
				`custom_menus`
			WHERE
				`menu_id` = ?
		";

		$rs = exec_query($sql, $query, $edit_id);
		if ($rs->recordCount() == 0) {
			set_page_message(
				tr('Missing or incorrect data input!'),
				'warning'
			);
			$tpl->assign('ADD_BUTTON', true);
			return;
		} else {
			$tpl->assign('EDIT_BUTTON', true);

			$admin_view = '';
			$reseller_view = '';
			$user_view = '';
			$all_view = '';

			switch ($rs->fields['menu_level']){
				case 'admin':
					$admin_view = $cfg->HTML_SELECTED;
					break;
				case 'reseller':
					$reseller_view = $cfg->HTML_SELECTED;
					break;
				case 'user':
					$user_view = $cfg->HTML_SELECTED;
					break;
				default:
					$all_view = $cfg->HTML_SELECTED;
			}

			$tpl->assign(
				array(
					'BUTTON_NAME_EDIT'	=> tohtml($rs->fields['menu_name']),
					'BUTTON_LINK_EDIT'	=> tohtml($rs->fields['menu_link']),
					'BUTTON_TARGET_EDIT'=> tohtml($rs->fields['menu_target']),
					'BUTTON_ICON_EDIT'	=> tohtml($rs->fields['menu_icon']),
					'ADMIN_VIEW'		=> $admin_view,
					'RESELLER_VIEW'		=> $reseller_view,
					'USER_VIEW'			=> $user_view,
					'ALL_VIEW'			=> $all_view,
					'EID'				=> $_GET['edit_id']
				)
			);

		}
	}
}

function update_button($sql) {
	$button_name = clean_input($_POST['bname']);
	$button_link = clean_input($_POST['blink']);
	$button_target = clean_input($_POST['btarget']);
	$button_icon = clean_input($_POST['bticon']);
	$button_view = $_POST['bview'];
	$button_id = $_POST['eid'];

	if (empty($button_name) || empty($button_link) || empty($button_id) || empty($button_icon)) {
		set_page_message(
			tr('Missing or incorrect data input!'),
			'warning'
		);
		return;
	}

	if (!filter_var($button_link, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED)) {
		set_page_message(
			tr('Invalid URL!'),
			'warning'
		);
		return;
	}

	if (!empty($button_target)
		&& !in_array($button_target, array('_blank', '_parent', '_self', '_top'))) {
		set_page_message(
			tr('Invalid target!'),
			'warning'
		);
		return;
	}

	$query = "
		UPDATE
			`custom_menus`
		SET
			`menu_level` = ?,
			`menu_name` = ?,
			`menu_link` = ?,
			`menu_target` = ?,
			`menu_icon` = ?
		WHERE
			`menu_id` = ?
	";

	exec_query($sql, $query, array(
			$button_view,
			$button_name,
			$button_link,
			$button_target,
			$button_icon,
			$button_id
		)
	);

	set_page_message(
		tr('Custom menu data updated successful!'),
		'success'
	);
	return;
}
?>