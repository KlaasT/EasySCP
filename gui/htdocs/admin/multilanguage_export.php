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

// Check for login
check_login(__FILE__);

if (isset($_GET['export_lang']) && $_GET['export_lang'] !== '') {

	$sql = EasySCP_Registry::get('Db');
	$language_table = $_GET['export_lang'];

	$query = "
		SELECT
			`msgstr`
		FROM
			`$language_table`
		WHERE
			`msgid` = 'encoding'
		;
	";

	$stmt = execute_query($sql, $query);

	if ($stmt->RowCount() > 0 && $stmt->fields['msgstr'] != '') {

		$encoding = $stmt->fields['msgstr'];
	} else {
		$encoding = 'UTF-8';
	}

	$query = "
		SELECT
			`msgid`,
			`msgstr`
		FROM
			`$language_table`
		;
	";

	/**
	 * @var $stmt EasySCP_Database_ResultSet
	 */
	$stmt = exec_query($sql, $query);

	if ($stmt->recordCount() == 0) {
		set_page_message(tr('Incorrect data input!'), 'warning');
		user_goto('multilanguage.php');
	} else {
		// Get all translation strings
		$data = '';

		while (!$stmt->EOF) {
			$msgid = $stmt->fields['msgid'];
			$msgstr = $stmt->fields['msgstr'];

			if ($msgid !== '' && $msgstr !== '') {
				$data .= "$msgid = $msgstr\n";
			}

			$stmt->moveNext();
		}

		$filename = str_replace('lang_', '', $language_table) . '.txt';

		if(isset($_GET['compress'])) {
			$filter = new EasySCP_Filter_Compress_Gzip();
			$data = $filter->filter($data);
			$filename .= '.gz';
			$mime_type = 'application/x-gzip';
		} else {
			$mime_type = 'text/plain;';
		}

		// Common headers
		header("Content-type: $mime_type;");
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename="' . $filename . '"');

		// Get client browser information
		$browserInfo = get_browser(null, true);

		// Headers according client browser
		if($browserInfo['browser'] == 'msie') {
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Pragma: no-cache');

			if($browserInfo['browser'] == 'safari') {
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			}
		}
		print $data;
	}
} else {
	set_page_message(tr('Incorrect data input!'), 'warning');
	user_goto('multilanguage.php');
}
