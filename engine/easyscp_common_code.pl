#!/usr/bin/perl

# EasySCP a Virtual Hosting Control Panel
# Copyright (C) 2001-2006 by moleSoftware GmbH - http://www.molesoftware.com
# Copyright (C) 2006-2010 by isp Control Panel - http://ispcp.net
# Copyright (C) 2010-2012 by Easy Server Control Panel - http://www.easyscp.net
#
# The contents of this file are subject to the Mozilla Public License
# Version 1.1 (the "License"); you may not use this file except in
# compliance with the License. You may obtain a copy of the License at
# http://www.mozilla.org/MPL/
#
# Software distributed under the License is distributed on an "AS IS"
# basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
# License for the specific language governing rights and limitations
# under the License.
#
# The Original Code is "VHCS - Virtual Hosting Control System".
#
# The Initial Developer of the Original Code is moleSoftware GmbH.
# Portions created by Initial Developer are Copyright (C) 2001-2006
# by moleSoftware GmbH. All Rights Reserved.
#
# Portions created by the ispCP Team are Copyright (C) 2006-2010 by
# isp Control Panel. All Rights Reserved.
#
# Portions created by the EasySCP Team are Copyright (C) 2010-2012 by
# Easy Server Control Panel. All Rights Reserved.

BEGIN {

	my %needed 	= (
		'strict' => '',
		'warnings' => '',
		'IO::Socket'=> '',
		'DBI'=> '',
		DBD::mysql => '',
		MIME::Entity => '',
		MIME::Parser => '',
		Crypt::CBC => '',
		Crypt::Blowfish => '',
		Crypt::PasswdMD5 => '',
		MIME::Base64 => '',
		Term::ReadPassword => '',
		File::Basename => '',
		File::Path => '',
		HTML::Entities=> '',
		File::Temp => 'qw(tempdir)',
		File::Copy::Recursive => 'qw(rcopy)',
		Net::LibIDN => 'qw(idn_to_ascii idn_to_unicode)'
	);

	my ($mod, $mod_err, $mod_missing) = ('', '_off_', '');

	for $mod (keys %needed) {

		if (eval "require $mod") {

			eval "use $mod $needed{$mod}";

		} else {

			print STDERR "\n[FATAL] Module [$mod] WAS NOT FOUND !\n" ;

			$mod_err = '_on_';

			if ($mod_missing eq '') {
				$mod_missing .= $mod;
			} else {
				$mod_missing .= ", $mod";
			}
		}
	}

	if ($mod_err eq '_on_') {
		print STDERR "\nModules [$mod_missing] WAS NOT FOUND in your system...\n";

		exit 1;

	} else {
		$| = 1;
	}
}

use strict;
use warnings;

# Hide the "used only once: possible typo" warnings
no warnings qw(once);

$main::engine_debug = undef;

require 'easyscp_common_methods.pl';
require easyscp_common_methods;

################################################################################
# Load EasySCP configuration from the easyscp.conf file

if(-e '/usr/local/etc/easyscp/easyscp.conf'){
	$main::cfg_file = '/usr/local/etc/easyscp/easyscp.conf';
	$main::easyscp_etc_dir = '/usr/local/etc/easyscp';
} else {
	$main::cfg_file = '/etc/easyscp/easyscp.conf';
	$main::easyscp_etc_dir = '/etc/easyscp';
}

require 'easyscp-load-db-keys.pl';

my $rs = get_conf($main::cfg_file);
die("FATAL: Can't load the easyscp.conf file") if($rs != 0);

################################################################################
# Enable debug mode if needed
if ($main::cfg{'DEBUG'} != 0) {
	$main::engine_debug = '_on_';
}

################################################################################
# Generating EasySCP Db key and initialization vector if needed
#
if ($main::db_pass_key eq '{KEY}' || $main::db_pass_iv eq '{IV}') {

	print STDOUT "\tGenerating database keys, it may take some time, please ".
		"wait...\n";

	print STDOUT "\tIf it takes to long, please check: ".
	 "http://www.easyscp,net\n";

	map {s/'/\\'/g, chop}
		my $db_pass_key = generateRandomChars(32, ''),
		my $db_pass_iv = generateRandomChars(8, '');

	$main::db_pass_key = $db_pass_key;
	$main::db_pass_iv = $db_pass_iv;

	$rs = write_easyscp_key_cfg();

	die('FATAL: Error during database keys generation!') if ($rs != 0);
}

# Exit script execution if Database Parameters are not set
die("FATAL: Cannot load database parameters")  if (setup_main_vars() != 0);

################################################################################
# Lock file system variables
#
$main::lock_file = $main::cfg{'MR_LOCK_FILE'};
$main::fh_lock_file = undef;

$main::log_dir = $main::cfg{'LOG_DIR'};
$main::root_dir = $main::cfg{'ROOT_DIR'};

$main::easyscp = "$main::log_dir/easyscp-rqst-mngr.el";

################################################################################
# easyscp_rqst_mngr variables
#
$main::easyscp_rqst_mngr = "$main::root_dir/engine/easyscp-rqst-mngr";
$main::easyscp_rqst_mngr_el = "$main::log_dir/easyscp-rqst-mngr.el";
$main::easyscp_rqst_mngr_stdout = "$main::log_dir/easyscp-rqst-mngr.stdout";
$main::easyscp_rqst_mngr_stderr = "$main::log_dir/easyscp-rqst-mngr.stderr";

################################################################################
# easyscp_dmn_mngr variables
#
$main::easyscp_dmn_mngr = "$main::root_dir/engine/easyscp-dmn-mngr";
$main::easyscp_dmn_mngr_el = "$main::log_dir/easyscp-dmn-mngr.el";
$main::easyscp_dmn_mngr_stdout = "$main::log_dir/easyscp-dmn-mngr.stdout";
$main::easyscp_dmn_mngr_stderr = "$main::log_dir/easyscp-dmn-mngr.stderr";

################################################################################
# easyscp_sub_mngr variables
#
$main::easyscp_sub_mngr = "$main::root_dir/engine/easyscp-sub-mngr";
$main::easyscp_sub_mngr_el = "$main::log_dir/easyscp-sub-mngr.el";
$main::easyscp_sub_mngr_stdout = "$main::log_dir/easyscp-sub-mngr.stdout";
$main::easyscp_sub_mngr_stderr = "$main::log_dir/easyscp-sub-mngr.stderr";

################################################################################
# easyscp_alssub_mngr variables
#
$main::easyscp_alssub_mngr = "$main::root_dir/engine/easyscp-alssub-mngr";
$main::easyscp_alssub_mngr_el = "$main::log_dir/easyscp-alssub-mngr.el";
$main::easyscp_alssub_mngr_stdout = "$main::log_dir/easyscp-alssub-mngr.stdout";
$main::easyscp_alssub_mngr_stderr = "$main::log_dir/easyscp-alssub-mngr.stderr";

################################################################################
# easyscp_als_mngr variables
#
$main::easyscp_als_mngr = "$main::root_dir/engine/easyscp-als-mngr";
$main::easyscp_als_mngr_el = "$main::log_dir/easyscp-als-mngr.el";
$main::easyscp_als_mngr_stdout = "$main::log_dir/easyscp-als-mngr.stdout";
$main::easyscp_als_mngr_stderr = "$main::log_dir/easyscp-als-mngr.stderr";

################################################################################
# easyscp_mbox_mngr variables
#
$main::easyscp_mbox_mngr = "$main::root_dir/engine/easyscp-mbox-mngr";
$main::easyscp_mbox_mngr_el = "$main::log_dir/easyscp-mbox-mngr.el";
$main::easyscp_mbox_mngr_stdout = "$main::log_dir/easyscp-mbox-mngr.stdout";
$main::easyscp_mbox_mngr_stderr = "$main::log_dir/easyscp-mbox-mngr.stderr";

################################################################################
# easyscp_serv_mngr variables
#
$main::easyscp_serv_mngr = "$main::root_dir/engine/easyscp-serv-mngr";
$main::easyscp_serv_mngr_el = "$main::log_dir/easyscp-serv-mngr.el";
$main::easyscp_serv_mngr_stdout = "$main::log_dir/easyscp-serv-mngr.stdout";
$main::easyscp_serv_mngr_stderr = "$main::log_dir/easyscp-serv-mngr.stderr";

################################################################################
# easyscp_net_interfaces_mngr variables
#
$main::easyscp_net_interfaces_mngr = "$main::root_dir/engine/tools/easyscp-net-interfaces-mngr";
$main::easyscp_net_interfaces_mngr_el = "$main::log_dir/easyscp-net-interfaces-mngr.el";
$main::easyscp_net_interfaces_mngr_stdout = "$main::log_dir/easyscp-net-interfaces-mngr.log";

################################################################################
# easyscp_htaccess_mngr variables
#
$main::easyscp_htaccess_mngr = "$main::root_dir/engine/easyscp-htaccess-mngr";
$main::easyscp_htaccess_mngr_el = "$main::log_dir/easyscp-htaccess-mngr.el";
$main::easyscp_htaccess_mngr_stdout = "$main::log_dir/easyscp-htaccess-mngr.stdout";
$main::easyscp_htaccess_mngr_stderr = "$main::log_dir/easyscp-htaccess-mngr.stderr";

################################################################################
# easyscp_htusers_mngr variables
#
$main::easyscp_htusers_mngr = "$main::root_dir/engine/easyscp-htusers-mngr";
$main::easyscp_htusers_mngr_el = "$main::log_dir/easyscp-htusers-mngr.el";
$main::easyscp_htusers_mngr_stdout = "$main::log_dir/easyscp-htusers-mngr.stdout";
$main::easyscp_htusers_mngr_stderr = "$main::log_dir/easyscp-htusers-mngr.stderr";

################################################################################
# easyscp_htgroups_mngr variables
#
$main::easyscp_htgroups_mngr = "$main::root_dir/engine/easyscp-htgroups-mngr";
$main::easyscp_htgroups_mngr_el = "$main::log_dir/easyscp-htgroups-mngr.el";
$main::easyscp_htgroups_mngr_stdout = "$main::log_dir/easyscp-htgroups-mngr.stdout";
$main::easyscp_htgroups_mngr_stderr = "$main::log_dir/easyscp-htgroups-mngr.stderr";


################################################################################
# easyscp_vrl_traff variables
#
$main::easyscp_vrl_traff = "$main::root_dir/engine/messenger/easyscp-vrl-traff";
$main::easyscp_vrl_traff_el = "$main::log_dir/easyscp-vrl-traff.el";
$main::easyscp_vrl_traff_stdout = "$main::log_dir/easyscp-vrl-traff.stdout";
$main::easyscp_vrl_traff_stderr = "$main::log_dir/easyscp-vrl-traff.stderr";

################################################################################
# easyscp_svr_traff variables
#
$main::easyscp_srv_traff_el = "$main::log_dir/easyscp-srv-traff.el";

################################################################################
# easyscp_httpd_logs variables
#
$main::easyscp_httpd_logs_mngr_el = "$main::log_dir/easyscp-httpd-logs-mngr.el";
$main::easyscp_httpd_logs_mngr_stdout = "$main::log_dir/easyscp-httpd-logs-mngr.stdout";
$main::easyscp_httpd_logs_mngr_stderr = "$main::log_dir/easyscp-httpd-logs-mngr.stderr";

################################################################################
# easyscp_bk variables
#
$main::easyscp_bk_task_el = "$main::log_dir/easyscp-bk-task.el";

################################################################################
# easyscp_dsk_quota variables
#
$main::easyscp_dsk_quota_el = "$main::log_dir/easyscp-dsk-quota.el";

1;
