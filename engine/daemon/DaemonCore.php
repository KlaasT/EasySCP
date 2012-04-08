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

/**
 * Handles DaemonCore requests.
 *
 * @param string $Input
 * @return boolean
 */
function DaemonCore($Input) {
	$retVal = null;
	switch ($Input) {
		case 'checkAll':
			System_Daemon::info('Running checkAllData subprocess.');
			$retVal = checkAllData();
			System_Daemon::info('Finished checkAllData subprocess.');
			break;
		default:
			System_Daemon::warning("Don't know what to do with " . $Input);
			$retVal = false;
			break;
	}
	return $retVal;
}

/**
 * Checks all data eg. Domain, Mail.
 * 
 * @return boolean
 */
function checkAllData() {
	$retVal = null;
	$sql_query = "
	    SELECT
			domain_name
	    FROM
			domain
	    WHERE
	    	domain_status
	    IN (
			'toadd',
			'change',
			'restore',
			'toenable',
			'todisable'
		)
		ORDER BY
			domain_name
	";
	foreach (DB::query($sql_query) as $row) {
		require_once('DaemonDomain.php');
		$retVal = DaemonDomain($row['domain_name']);
		if($retVal == false){
			return false;
		}

	}
	return $retVal;
}
?>