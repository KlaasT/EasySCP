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
	if test $(HOST_OS) = opensuse ; then \
		cp ./easyscp.conf $(SYSTEM_CONF) ; \
		cd ./amavisd && $(MAKE) install ; cd .. ; \
		cd ./apache && $(MAKE) install ; cd .. ; \
		cd ./courier && $(MAKE) install ; cd .. ; \
		cd ./cron.d && $(MAKE) install ; cd .. ; \
		cd ./init.d && $(MAKE) install ; cd .. ; \
		cd ./logrotate && $(MAKE) install ; cd .. ; \
		cd ./postfix && $(MAKE) install ; cd .. ; \
		cd ./proftpd && $(MAKE) install ; cd .. ; \
	fi

uninstall:
	if test $(HOST_OS) = opensuse ; then \
		rm -rf $(SYSTEM_CONF)/easyscp.conf ;  \
		cd ./amavisd && $(MAKE) uninstall ; cd.. ; \
		cd ./apache && $(MAKE) uninstall ; cd .. ; \
		cd ./courier && $(MAKE) uninstall ; cd .. ; \
		cd ./cron.d && $(MAKE) uninstall ; cd .. ; \
		cd ./init.d && $(MAKE) uninstall ; cd .. ; \
		cd ./logrotate && $(MAKE) uninstall ; cd .. ; \
		cd ./postfix && $(MAKE) uninstall ; cd .. ; \
		cd ./proftpd && $(MAKE) uninstall ; cd .. ; \
	fi
