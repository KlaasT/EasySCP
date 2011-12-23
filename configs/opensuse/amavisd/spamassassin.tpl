#!/usr/bin/perl

# EasySCP a Virtual Hosting Control Panel
# Copyright (C) 2010-2012 by Easy Server Control Panel - http://www.easyscp.net
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
# @link 		http://www.easyscp.net
# @author 		EasySCP Team

# EasySCP specific:
#
# If you do not want this file to be regenerated from scratch during EasySCP
# update process, change the 'SPAMASSASSIN_REGENERATE' parameter value to 'no' in
# the easyscp.conf file.

# SpamAssassin local.cf template file

# Disable it if using NFS
lock_method                            flock

# How many hits before a message is considered spam.
required_score                         4.3

# Change the subject of suspected spam
rewrite_header                         Subject *****SPAM*****

# Encapsulate spam in an attachment (0=no, 1=yes, 2=safe)
report_safe                            0

clear_internal_networks
clear_trusted_networks
internal_networks                      {BASE_SERVER_IP} 10.0.0/24
trusted_networks                       {BASE_SERVER_IP} 10.0.0/24

#
# statistical, "Bayesian" analysis system - Begin
#

# Enable the Bayes system
use_bayes                              1
bayes_auto_expire                      0

# Bayes storage module
# Default: Mail::SpamAssassin::BayesStore::DBM
#bayes_store_module                     Mail::SpamAssassin::BayesStore::MySQL
#bayes_sql_dsn                          DBI:mysql:{SPAMASSASSIN_DATABASE}:{DATABASE_HOST}
#bayes_sql_username                     {SPAMASSASSIN_SQL_USER}
#bayes_sql_password                     {SPAMASSASSIN_SQL_PASSWORD}
#bayes_sql_override_username            {AMAVIS_SQL_USERNAME}

# Enable Bayes auto-learning
bayes_auto_learn                       1
bayes_auto_learn_threshold_nonspam     0.1
bayes_auto_learn_threshold_spam        7.0

#
# statistical, "Bayesian" analysis system - End
#

#
# auto white-list (AWL) - Begin
#

#use_auto_whitelist                     0
# auto white-list sorage module
# default: Mail::SpamAssassin::DBBasedAddrList
#auto_whitelist_factory                  Mail::SpamAssassin::SQLBasedAddrList
#user_awl_dsn                            DBI:mysql:{SPAMASSASSIN_DATABASE}:{DATABASE_HOST}
#user_awl_sql_username                   {SPAMASSASSIN_SQL_USER}
#user_awl_sql_password                   {SPAMASSASSIN_SQL_PASSWORD}

#
# auto white-list (AWL) - End
#

skip_rbl_checks                      0
dns_available                        yes
auto_whitelist_distinguish_signed    1

# Enable or disable network checks (used if available)
skip_rbl_checks                      1
use_razor2                           1
#use_dcc                              1
use_pyzor                            1
