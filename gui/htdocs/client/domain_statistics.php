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
$template = 'client/domain_statistics.tpl';

// dynamic page data.
$current_month = date('m');
$current_year = date('Y');

list($current_month, $current_year) = gen_page_post_data($tpl, $current_month, $current_year);
gen_dmn_traff_list($tpl, $sql, $current_month, $current_year, $_SESSION['user_id']);

// static page messages.
gen_logged_from($tpl);

check_permissions($tpl);

$tpl->assign(
	array(
		'TR_PAGE_TITLE' => tr('EasySCP - Client/Domain Statistics'),
		'TR_DOMAIN_STATISTICS' => tr('Domain statistics'),
		'DOMAIN_URL' => 'http://' . $_SESSION['user_logged'] . '/stats/',
		'TR_AWSTATS' => tr('Web Stats'),
		'TR_MONTH' => tr('Month'),
		'TR_YEAR' => tr('Year'),
		'TR_SHOW' => tr('Show'),
		'TR_DATE' => tr('Date'),
		'TR_WEB_TRAFF' => tr('WEB'),
		'TR_FTP_TRAFF' => tr('FTP'),
		'TR_SMTP_TRAFF' => tr('SMTP'),
		'TR_POP_TRAFF' => tr('POP3/IMAP'),
		'TR_SUM' => tr('Sum'),
		'TR_ALL' => tr('Total')
	)
);

gen_client_mainmenu($tpl, 'client/main_menu_statistics.tpl');
gen_client_menu($tpl, 'client/menu_statistics.tpl');

gen_page_message($tpl);

if ($cfg->DUMP_GUI_DEBUG) {
	dump_gui_debug($tpl);
}

$tpl->display($template);

unset_messages();

// page functions.

/**
 * @param EasySCP_TemplateEngine $tpl
 * @param int $month
 * @param int $year
 */
function gen_page_date($tpl, $month, $year) {

	$cfg = EasySCP_Registry::get('Config');

	for ($i = 1; $i <= 12; $i++) {
		$tpl->append(
			array(
				'MONTH_SELECTED' => ($i == $month) ? $cfg->HTML_SELECTED : '',
				'MONTH' => $i
			)
		);
	}

	for ($i = $year - 1; $i <= $year + 1; $i++) {
		$tpl->append(
			array(
				'YEAR_SELECTED' => ($i == $year) ? $cfg->HTML_SELECTED : '',
				'YEAR' => $i
			)
		);
	}
}

function gen_page_post_data($tpl, $current_month, $current_year) {

	if (isset($_POST['uaction']) && $_POST['uaction'] === 'show_traff') {
		$current_month = $_POST['month'];
		$current_year = $_POST['year'];
	}

	gen_page_date($tpl, $current_month, $current_year);
	return array($current_month, $current_year);
}

function get_domain_trafic($from, $to, $domain_id) {

	$sql = EasySCP_Registry::get('Db');

	$query = "
		SELECT
			IFNULL(SUM(`dtraff_web`), 0) AS web_dr,
			IFNULL(SUM(`dtraff_ftp`), 0) AS ftp_dr,
			IFNULL(SUM(`dtraff_mail`), 0) AS mail_dr,
			IFNULL(SUM(`dtraff_pop`), 0) AS pop_dr
		FROM
			`domain_traffic`
		WHERE
			`domain_id` = ?
		AND
			`dtraff_time` >= ?
		AND
			`dtraff_time` <= ?
	";

	$rs = exec_query($sql, $query, array($domain_id, $from, $to));

	if ($rs->recordCount() == 0) {
		return array(0, 0, 0, 0);
	} else {
		return array(
			$rs->fields['web_dr'],
			$rs->fields['ftp_dr'],
			$rs->fields['pop_dr'],
			$rs->fields['mail_dr']
		);
	}
}

/**
 * @todo Check the out commented code at the end of this function, can we remove it?
 * @param EasySCP_TemplateEngine $tpl
 * @param EasySCP_Database $sql
 * @param int $month
 * @param int $year
 * @param int $user_id
 */
function gen_dmn_traff_list($tpl, $sql, $month, $year, $user_id) {

	global $web_trf, $ftp_trf, $smtp_trf, $pop_trf,
	$sum_web, $sum_ftp, $sum_mail, $sum_pop;

	$cfg = EasySCP_Registry::get('Config');

	$domain_admin_id = $_SESSION['user_id'];
	$query = "
		SELECT
			`domain_id`
		FROM
			`domain`
		WHERE
			`domain_admin_id` = ?
	";

	$rs = exec_query($sql, $query, $domain_admin_id);
	$domain_id = $rs->fields('domain_id');

	if ($month == date('m') && $year == date('Y')) {
		$curday = date('j');
	} else {
		$tmp = mktime(1, 0, 0, $month + 1, 0, $year);
		$curday = date('j', $tmp);
	}

	$all[0] = 0;
	$all[1] = 0;
	$all[2] = 0;
	$all[3] = 0;
	$all[4] = 0;
	$all[5] = 0;
	$all[6] = 0;
	$all[7] = 0;
	$counter = 0;

	for ($i = 1; $i <= $curday; $i++) {
		$ftm = mktime(0, 0, 0, $month, $i, $year);
		$ltm = mktime(23, 59, 59, $month, $i, $year);
		$query = "
			SELECT
				`dtraff_web`, `dtraff_ftp`, `dtraff_mail`, `dtraff_pop`, `dtraff_time`
			FROM
				`domain_traffic`
			WHERE
				`domain_id` = ?
			AND
				`dtraff_time` >= ?
			AND
				`dtraff_time` <= ?
		";

		exec_query($sql, $query, array($domain_id, $ftm, $ltm));

		list($web_trf,
			$ftp_trf,
			$pop_trf,
			$smtp_trf) = get_domain_trafic($ftm, $ltm, $domain_id);

		$tpl->append('ITEM_CLASS', ($counter % 2 == 0) ? 'content' : 'content2');

		$sum_web += $web_trf;
		$sum_ftp += $ftp_trf;
		$sum_mail += $smtp_trf;
		$sum_pop += $pop_trf;

		$date_formt = $cfg->DATE_FORMAT;

		$tpl->append(
			array(
				'DATE' => date($date_formt, strtotime($year . "-" . $month . "-" . $i)),
				'WEB_TRAFFIC' => sizeit($web_trf),
				'FTP_TRAFFIC' => sizeit($ftp_trf),
				'SMTP_TRAFFIC' => sizeit($smtp_trf),
				'POP3_TRAFFIC' => sizeit($pop_trf),
				'ALL_TRAFFIC' => sizeit($web_trf + $ftp_trf + $smtp_trf + $pop_trf),
				'WEB_TRAFF' => sizeit($web_trf),
				'FTP_TRAFF' => sizeit($ftp_trf),
				'SMTP_TRAFF' => sizeit($smtp_trf),
				'POP_TRAFF' => sizeit($pop_trf),
				'SUM_TRAFF' => sizeit($web_trf + $ftp_trf + $smtp_trf + $pop_trf),
				'CONTENT' => ($i % 2 == 0) ? 'content' : 'content2'
			)
		);
		$tpl->assign(
			array(
				'DOMAIN_ID' => $domain_id,
				'WEB_ALL' => sizeit($sum_web),
				'FTP_ALL' => sizeit($sum_ftp),
				'SMTP_ALL' => sizeit($sum_mail),
				'POP_ALL' => sizeit($sum_pop),
				'SUM_ALL' => sizeit($sum_web + $sum_ftp + $sum_mail + $sum_pop)
			)
		);
		$counter++;
	}

	/*
	$start_date = mktime(0,0,0, $month, 1, $year);
	$end_date = mktime(0,0,0, $month + 1, 1, $year);
	$dmn_id = get_user_domain_id($sql, $user_id);
	$query = "
		SELECT
			`dtraff_time` AS traff_date,
			`dtraff_web` AS web_traff,
			`dtraff_ftp` AS ftp_traff,
			`dtraff_mail` AS smtp_traff,
			`dtraff_pop` AS pop_traff,
			(`dtraff_web` + `dtraff_ftp` + `dtraff_mail` + `dtraff_pop`) AS sum_traff
		FROM
			`domain_traffic`
		WHERE
			`domain_id` = '$dmn_id'
		AND
			`dtraff_time` >= '$start_date'
		AND
			`dtraff_time` < '$end_date'
		ORDER BY
			`dtraff_time`
	";

	$rs = execute_query($sql, $query);

	if ($rs->RecordCount() == 0) {

		$tpl->assign('TRAFF_LIST', '');

		set_page_message(
			tr('Traffic accounting for the selected month is missing!'),
			'warning'
		);

	} else {

		$web_all = 0; $ftp_all = 0; $smtp_all = 0; $pop_all = 0; $sum_all = 0; $i = 1;

		while (!$rs->EOF) {

			$tpl->assign(
				array(
					'DATE' => date("d.m.Y, G:i", $rs->fields['traff_date']),
					'WEB_TRAFF' => sizeit($rs->fields['web_traff']),
					'FTP_TRAFF' => sizeit($rs->fields['ftp_traff']),
					'SMTP_TRAFF' => sizeit($rs->fields['smtp_traff']),
					'POP_TRAFF' => sizeit($rs->fields['pop_traff']),
					'SUM_TRAFF' => sizeit($rs->fields['sum_traff']),
					'CONTENT' => ($i % 2 == 0) ? 'content3' : 'content2'
				)
			);


			$web_all += $rs->fields['web_traff'];

			$ftp_all += $rs->fields['ftp_traff'];

			$smtp_all += $rs->fields['smtp_traff'];

			$pop_all += $rs->fields['pop_traff'];

			$sum_all += $rs->fields['sum_traff'];

			$rs->MoveNext(); $i++;

		}

		$tpl->assign(
			array(
				'WEB_ALL' => sizeit($web_all),
				'FTP_ALL' => sizeit($ftp_all),
				'SMTP_ALL' => sizeit($smtp_all),
				'POP_ALL' => sizeit($pop_all),
				'SUM_ALL' => sizeit($sum_all)
			)
		);

	}
*/

}
?>