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

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_CONF)/proftpd

	cp -R ./backup $(SYSTEM_CONF)/proftpd
	cp -R ./parts $(SYSTEM_CONF)/proftpd
	cp -R ./working $(SYSTEM_CONF)/proftpd

	if [ ! -d $(ROOT_CONF)/proftpd ]; then \
		$(SYSTEM_MAKE_DIRS) $(ROOT_CONF)/proftpd ; \
	fi

	$(SYSTEM_MAKE_DIRS) $(ROOT_CONF)/proftpd/easyscp
	cp ./root_domain.conf $(ROOT_CONF)/proftpd/easyscp

	if [ -e /etc/proftpd.conf ] ; then \
		mv /etc/proftpd.conf /etc/proftpd.conf.bak ; \
	fi ; \
	cp ./proftpd.conf $(SYSTEM_CONF)/proftpd/proftpd.conf ; \


uninstall:

	rm -rf $(SYSTEM_CONF)/proftpd
