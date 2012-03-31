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

install:
	cp ./easyscp_config_db.tpl $(SYSTEM_CONF)
	cp ./easyscp_pdns_db.tpl $(SYSTEM_CONF)
	cp -R ./apache $(SYSTEM_CONF)
	cp -R ./awstats $(SYSTEM_CONF)
	cp -R ./bind $(SYSTEM_CONF)
	cp -R ./database $(SYSTEM_CONF)
	cp -R ./fcgi $(SYSTEM_CONF)
	cp -R ./pma $(SYSTEM_CONF)
	cp -R ./roundcube $(SYSTEM_CONF)
	cp -R ./pdns $(SYSTEM_CONF)

uninstall:
	rm -rf $(SYSTEM_CONF)/easyscp_config_db.php
	rm -rf $(SYSTEM_CONF)/easyscp_config_db.tpl
	rm -rf $(SYSTEM_CONF)/easyscp_pdns_db.php
	rm -rf $(SYSTEM_CONF)/easyscp_pdns_db.tpl
	rm -rf $(SYSTEM_CONF)/apache
	rm -rf $(SYSTEM_CONF)/awstats
	rm -rf $(SYSTEM_CONF)/bind
	rm -rf $(SYSTEM_CONF)/database
	rm -rf $(SYSTEM_CONF)/fcgi
	rm -rf $(SYSTEM_CONF)/pma
	rm -rf $(SYSTEM_CONF)/roundcube
	rm -rf $(SYSTEM_CONF)/pdns

.PHONY: install uninstall
