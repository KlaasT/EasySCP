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
 * @since               1.2.0
 */
require '../../include/easyscp-lib.php';

check_login(__FILE__);

$cfg = EasySCP_Registry::get('Config');
$html_selected = $cfg->HTML_SELECTED;

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'client/domain_manage_ssl.tpl';

// static page messages.
gen_logged_from($tpl);

check_permissions($tpl);

$sql = EasySCP_Registry::get('Db');

if (isset($_POST['uaction']) && ($_POST['uaction'] === 'apply')) {
    update_ssl_data($sql);
}

$values = get_domain_default_props($sql, $_SESSION['user_id'], true);

switch ($values['ssl_status']) {
    case 0:
        $tpl->assign('SSL_SELECTED_DISABLED', $html_selected);
        $tpl->assign('SSL_SELECTED_SSLONLY', '');
        $tpl->assign('SSL_SELECTED_BOTH', '');
        break;
    case 1:
        $tpl->assign('SSL_SELECTED_DISABLED', '');
        $tpl->assign('SSL_SELECTED_SSLONLY', $html_selected);
        $tpl->assign('SSL_SELECTED_BOTH', '');
        break;
    default:
        $tpl->assign('SSL_SELECTED_DISABLED', '');
        $tpl->assign('SSL_SELECTED_SSLONLY', '');
        $tpl->assign('SSL_SELECTED_BOTH', $html_selected);
} // end switch

// static page messages
$tpl->assign(
        array(
            'TR_PAGE_TITLE'		=> tr('EasySCP - Manage SSL configuration'),
            'TR_SSL_CONFIG_TITLE'       => tr('EasySCP SSL config'),
            'TR_SSL_CERTIFICATE'        => tr('SSL certificate'),
            'TR_SSL_KEY'                => tr('SSL key'),
            'TR_SSL_ENABLED'            => tr('SSL enabled'),
            'TR_APPLY_CHANGES'          => tr('Apply changes'),
            'TR_SSL_STATUS_DISABLED'    => tr('SSL disabled'),
            'TR_SSL_STATUS_SSLONLY'     => tr('SSL enabled'),
            'TR_SSL_STATUS_BOTH'        => tr('both'),
            'TR_MESSAGE'                => tr('Message'),
            'SSL_KEY'                   => $values['ssl_key'],
            'SSL_CERTIFICATE'           => $values['ssl_cert'],
            'SSL_STATUS'                => $values['ssl_status']
        )
);

gen_client_mainmenu($tpl, 'client/main_menu_manage_domains.tpl');
gen_client_menu($tpl, 'client/menu_manage_domains.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

function update_ssl_data($sql){
    if ((isset($_POST['ssl_key'])) && 
            isset($_POST['ssl_cert']) && 
            isset($_POST['ssl_status']))

        $cert = clean_input($_POST['ssl_cert']);
        $key = clean_input($_POST['ssl_key']);
        $domainid = get_user_domain_id($sql, $_SESSION['user_id']);
	$query = "
		UPDATE `domain` set 
                       `ssl_cert`   = '$cert',
                       `ssl_key`    = '$key',
                       `ssl_status` = ${_POST['ssl_status']}
                WHERE `domain_id` = $domainid
		;";
        
        $rs = exec_query($sql, $query);
        
        // get number of updates 
        $update_count = $rs->recordCount();

        if ($update_count==0){
            set_page_message(tr("SSL configuration unchanged"), 'info');
        }elseif ($update_count > 0) {
            set_page_message(tr('SSL configuration updated!'), 'success');
        }

    user_goto('domain_manage_ssl.php');
}
?>
