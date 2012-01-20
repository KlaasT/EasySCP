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

check_login(__FILE__);

$cfg = EasySCP_Registry::get('Config');

$tpl = EasySCP_TemplateEngine::getInstance();
$template = 'admin/server_status.tpl';

// static page messages

gen_admin_mainmenu($tpl, 'admin/main_menu_general_information.tpl');
gen_admin_menu($tpl, 'admin/menu_general_information.tpl');

$tpl->assign(
	array(
		'TR_PAGE_TITLE' => tr('EasySCP Admin / System Tools / Server Status'),
		'TR_HOST' => tr('Host'),
		'TR_SERVICE' => tr('Service'),
		'TR_STATUS' => tr('Status'),
		'TR_SERVER_STATUS' => tr('Server status'),
	)
);

get_server_status($tpl, $sql);

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

/*
 * Site functions
 */

/**
 * @todo respect naming convention: getSth not GetSth and class Status not status
 */
class status {
	var $all = array();

	/**
	 * AddService adds a service to a multi-dimensional array
	 */
	function AddService($ip, $port, $service, $type) {
		$small_array = array('ip' => $ip, 'port' => $port, 'service' => $service, 'type' => $type, 'status' => '');
		array_push($this->all, $small_array);
		return $this->all;
	}

	/**
	 * GetCount returns the number of services added
	 */
	function GetCount() {
		return count($this->all);
	}

	/**
	 * CheckStatus checks the status
	 */
	function CheckStatus($timeout = 5) {
		for ($i = 0, $x = $this->GetCount() - 1; $i <= $x; $i++) {
			$ip = $this->all[$i]['ip'];
			$port = $this->all[$i]['port'];
			$errno = null;
			$errstr = null;

			if ($this->all[$i]['type'] == 'tcp') {
				$fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
			}
			else if ($this->all[$i]['type'] == 'udp') {
				$fp = @fsockopen('udp://' . $ip, $port, $errno, $errstr, $timeout);
			}
			else {
				write_log(sprintf('FIXME: %s:%d' . "\n" . 'Unknown connection type %s',__FILE__, __LINE__, $this->all[$i]['type']));
				throw new EasySCP_Exception('FIXME: ' . __FILE__ . ':' . __LINE__);
			}

			if ($fp) {
				$this->all[$i]['status'] = true;
			}
			else {
				$this->all[$i]['status'] = false;
			}

			if ($fp)
				fclose($fp);
		}
	}

	/**
	 * GetStatus a unecessary function to return the status
	 */
	function GetStatus() {
		return $this->all;
	}

	/**
	 * GetSingleStatus will get the status of single address
	 */
	function GetSingleStatus($ip, $port, $type, $timeout = 5) {
		$errno = null;
		$errstr = null;
		if ($type == 'tcp') {
			$fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
		}
		else if ($type == 'udp') {
			$fp = @fsockopen('udp://' . $ip, $port, $errno, $errstr, $timeout);
		}
		else {
			write_log(sprintf('FIXME: %s:%d' . "\n" . 'Unknown connection type %s',__FILE__, __LINE__, $type));
			throw new EasySCP_Exception('FIXME: ' . __FILE__ . ':' . __LINE__);
		}

		if (!$fp)
			return false;

		fclose($fp);
		return true;
	}
}

/**
 * @param EasySCP_TemplateEngine $tpl
 * @param EasySCP_Database $sql
 */
function get_server_status($tpl, $sql) {

	$cfg = EasySCP_Registry::get('Config');

	$query = "
		SELECT
			*
		FROM
			`config`
		WHERE
			`name` LIKE 'PORT_%'
		ORDER BY
			`name` ASC
	";

	$rs = exec_query($sql, $query);

	$easyscp_status = new status;

	$easyscp_status->AddService('localhost', 9876, 'EasySCP Daemon', 'tcp');

	// Dynamic added Ports
	while (!$rs->EOF) {
		$value = (count(explode(";", $rs->fields['value'])) < 6)
			? $rs->fields['value'].';'
			: $rs->fields['value'];
		list($port, $protocol, $name, $status, , $ip) = explode(";", $value);
		if ($status) {
			$easyscp_status->AddService(($ip == '127.0.0.1' ? 'localhost' : (empty($ip) ? $cfg->BASE_SERVER_IP : $ip)), (int)$port, $name, $protocol);
		}

		$rs->moveNext();
	} // end while

	$easyscp_status->CheckStatus(5);
	$data = $easyscp_status->GetStatus();
	$up = tr('UP');
	$down = tr('DOWN');

	for ($i = 0, $cnt_data = count($data); $i < $cnt_data; $i++) {
		if ($data[$i]['status']) {
			$img = $up;
			$class = "content up";
		} else {
			$img = '<strong>' . $down . '</strong>';
			$class = "content down";
		}

		if ($data[$i]['port'] == 23) { // 23 = telnet
			if ($data[$i]['status']) {
				$class = 'content2 down';
				$img = '<strong>' . $up . '</strong>';
			} else {
				$class = 'content2 up';
				$img = $down;
			}
		}

		$tpl->append(
			array(
				'HOST' => $data[$i]['ip'],
				'PORT' => $data[$i]['port'],
				'SERVICE' => tohtml($data[$i]['service']),
				'STATUS' => $img,
				'CLASS' => $class,
			)
		);

	}
}
?>