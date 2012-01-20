<?php
/**
 * EasySCP a Virtual Hosting Control Panel
 *
 * @copyright 	2001-2006 by moleSoftware GmbH
 * @copyright 	2006-2010 by ispCP | http://isp-control.net
 * @copyright 	2010-2012 by Easy Server Control Panel - http://www.easyscp.net
 * @link 		http://www.easyscp.net
 * @author 		EasySCP Team
 *
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "ispCP ω (OMEGA) a Virtual Hosting Control Panel".
 *
 * The Initial Developer of the Original Code is moleSoftware GmbH.
 * Portions created by Initial Developer are Copyright (C) 2001-2006
 * by moleSoftware GmbH. All Rights Reserved.
 *
 * Portions created by the ispCP Team are Copyright (C) 2006-2010 by
 * isp Control Panel. All Rights Reserved.
 *
 * Portions created by the EasySCP Team are Copyright (C) 2010-2012 by
 * Easy Server Control Panel. All Rights Reserved.
 */

// GUI root directory absolute path
$gui_root_dir = '{GUI_ROOT_DIR}';

if(preg_match('/^\{GUI_ROOT_DIR\}$/', $gui_root_dir)) {
    print 'Error: The gui root directory is not defined in the ' . __FILE__ .
		" file!\n";
	exit(1);
}

try {
	// Include EasySCP core libraries and initialize the environment
	require_once $gui_root_dir . '/include/easyscp-lib.php';

	// Gets an EasySCP_Update_Database instance
	$dbUpdate = EasySCP_Update_Database::getInstance();

	if(!$dbUpdate->executeUpdates()) {
		print "\n[ERROR]: " .$dbUpdate->getErrorMessage() . "\n\n";

		exit(1);
	}

} catch(Exception $e) {

	$message = "\n[ERROR]: " . $e->getMessage() . "\n\nStackTrace:\n" .
		$e->getTraceAsString() . "\n\n";

	print "$message\n\n";

	exit(1);
}

print "\n[INFO]: EasySCP database update succeeded!\n\n";

exit(0);
