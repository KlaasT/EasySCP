#!/usr/bin/make -f

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

export

install:

	$(SYSTEM_MAKE_DIRS) $(ROOT_CONF)/rc.d/init.d
	$(SYSTEM_MAKE_DIRS) $(ROOT_CONF)/default

	cp easyscp_daemon $(ROOT_CONF)/rc.d/init.d
	cp easyscp_network $(ROOT_CONF)/rc.d/init.d

	chown root:root $(ROOT_CONF)/rc.d/init.d/easyscp_network $(ROOT_CONF)/rc.d/init.d/easyscp_daemon
	chmod 0755 $(ROOT_CONF)/rc.d/init.d/easyscp_network $(ROOT_CONF)/rc.d/init.d/easyscp_daemon

	if [[ -f easyscp_daemon.default ]]; then \
		cp -p easyscp_daemon.default $(ROOT_CONF)/default/easyscp_daemon ; \
	fi

	if [[ -f easyscp_network.default ]]; then \
		cp -p easyscp_network.default $(ROOT_CONF)/default/easyscp_network ; \
	fi

uninstall:

	rm -f $(ROOT_CONF)/rc.d/init.d/easyscp_daemon
	rm -f $(ROOT_CONF)/rc.d/init.d/easyscp_network
	rm -f $(ROOT_CONF)/default/easyscp_daemon
	rm -f $(ROOT_CONF)/default/easyscp_network
