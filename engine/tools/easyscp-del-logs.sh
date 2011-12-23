#!/bin/bash

# EasySCP a Virtual Hosting Control Panel
# Copyright (C) 2006-2010 by isp Control Panel - http://ispcp.net
# Copyright (C) 2010-2011 by Easy Server Control Panel - http://www.easyscp.net
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
# The Original Code is "ispCP ω (OMEGA) a Virtual Hosting Control Panel".
#
# The Initial Developer of the Original Code is ispCP Team.
# Portions created by the ispCP Team are Copyright (C) 2006-2010 by
# isp Control Panel. All Rights Reserved.
#
# Portions created by the EasySCP Team are Copyright (C) 2010-2011 by
# Easy Server Control Panel. All Rights Reserved.
#
# The Easy Server Control Panel Home Page is:
#
#    http://www.easyscp.net
#

# Load the required entries from easyscp's configuration
if [ -f /usr/local/etc/easyscp/easyscp.conf ]
then
	CONF=/usr/local/etc/easyscp/easyscp.conf
else
	CONF=/etc/easyscp/easyscp.conf
fi
for a in `cat $CONF  | grep -E '(^APACHE_LOG_DIR|^APACHE_BACKUP_LOG_DIR|^APACHE_USERS_LOG_DIR)' | sed -e 's/ //g'`; do
	export $a
done

# -r is a GNU-xargs option (BSD doesn't have it, behaving always as if it was specified)
export XARGS="xargs$(echo '' |xargs -r 2>/dev/null && echo ' -r')"

# Remove `Apache` logs that are older than 365 days
for i in `ls -1 ${APACHE_LOG_DIR}/*.log*`
	do
		nice -n 19 find $i -type f -mtime +365 -print0 | ${XARGS} -0 rm;
done

# Remove `Users` logs that are older than 365 days
for i in `ls -1 ${APACHE_USERS_LOG_DIR}/*.log*`
	do
		nice -n 19 find $i -type f -mtime +365 -print0 | ${XARGS} -0 rm;
done

# Remove `Backup` logs that are older than 365 days
for i in `ls -1 ${APACHE_BACKUP_LOG_DIR}/*`
	do
		nice -n 19 find $i -type f -mtime +365 -print0 | ${XARGS} -0 rm;
done