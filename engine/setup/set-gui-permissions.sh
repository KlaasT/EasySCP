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
# for spacing
echo -n "	Setting GUI Permissions: ";

if [ $DEBUG -eq 1 ]; then
    echo	"";
fi

# By default, gui files must be readable by both the panel user (php files are
# run under this user) and apache (static files are served by it).
recursive_set_permissions "$ROOT_DIR/gui/" \
	$PANEL_USER $APACHE_GROUP 0550 0440

# But the following folders must be writable by the panel user, because
# php-generated or uploaded files will be stored there.
recursive_set_permissions "$ROOT_DIR/gui/phptmp" \
	$PANEL_USER $APACHE_GROUP 0750 0640
recursive_set_permissions "$ROOT_DIR/gui/themes/**/templates_c" \
	$PANEL_USER $APACHE_GROUP 0750 0640
recursive_set_permissions "$ROOT_DIR/gui/tools/filemanager/temp" \
	$PANEL_USER $APACHE_GROUP 0750 0640
recursive_set_permissions "$ROOT_DIR/gui/tools/webmail/logs" \
	$PANEL_USER $APACHE_GROUP 0750 0640
recursive_set_permissions "$ROOT_DIR/gui/tools/webmail/temp" \
	$PANEL_USER $APACHE_GROUP 0750 0640

# Main virtual webhosts directory must be owned by root and readable by all
# the domain-specific users.
set_permissions $APACHE_WWW_DIR $ROOT_USER $ROOT_GROUP 0555

# Main fcgid directory must be world-readable, because all the domain-specific
# users must be able to access its contents.
set_permissions "$PHP_STARTER_DIR" $ROOT_USER $ROOT_GROUP 0555

# Required on centos
set_permissions "$PHP_STARTER_DIR/master" $PANEL_USER $PANEL_GROUP 0755

echo " done";

exit 0
