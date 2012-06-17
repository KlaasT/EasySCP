#!/bin/sh

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

SELFDIR=$(dirname "$0")
. $SELFDIR/easyscp-permission-functions.sh

echo -n "	Setting Engine Permissions: ";
if [ $DEBUG -eq 1 ]; then
    echo	"";
fi

# easyscp.conf must be world readable because user "vmail" needs to access it.
if [ -f /usr/local/etc/easyscp/easyscp.conf ]; then
	set_permissions "/usr/local/etc/easyscp/easyscp.conf" \
		$ROOT_USER $ROOT_GROUP 0644
else
	set_permissions "/etc/easyscp/easyscp.conf" $ROOT_USER $ROOT_GROUP 0644
fi

# Only root can run engine scripts
recursive_set_permissions "$ROOT_DIR/engine" $ROOT_USER $ROOT_GROUP 0700 0700

# Engine folder must be world-readable because "vmail" user must be able
# to access its "messenger" subfolder.
set_permissions "$ROOT_DIR/engine" $ROOT_USER $ROOT_GROUP 0755

# Messenger script is run by user "vmail".
recursive_set_permissions "$ROOT_DIR/engine/messenger" \
	$MTA_MAILBOX_UID_NAME $MTA_MAILBOX_GID_NAME 0750 0550
recursive_set_permissions "$LOG_DIR/easyscp-arpl-msgr" \
	$MTA_MAILBOX_UID_NAME $MTA_MAILBOX_GID_NAME 0750 0640

echo " done";

exit 0
