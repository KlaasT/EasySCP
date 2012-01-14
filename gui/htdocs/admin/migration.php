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
redirect_to_level_page();

$query = "
	UPDATE
		`domain`
	SET
		`domain_status` = 'toadd'
";

$rs = execute_query($sql, $query);
print "Domains updated";

$query = "
	UPDATE
		`domain_aliasses`
	SET
		`alias_status` = 'toadd'
";

$rs = execute_query($sql, $query);
print "Domain aliases updated";

$query = "
	UPDATE
		`subdomain`
	SET
		`subdomain_status` = 'toadd'
";

$rs = execute_query($sql, $query);
print "Subdomains updated";

$query = "
	UPDATE
		`subdomain_alias`
	SET
		`subdomain_alias_status` = 'toadd'
";

$rs = execute_query($sql, $query);
print "Subdomains alias updated";

$query = "
	UPDATE
		`mail_users`
	SET
		`status` = 'toadd'
";

$rs = execute_query($sql, $query);
print "Emails updated";
