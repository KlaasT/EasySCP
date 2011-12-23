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
	$(SYSTEM_MAKE_DIRS) $(SYSTEM_CONF)/amavisd

	cp -R ./backup $(SYSTEM_CONF)/amavisd
	cp -R ./working $(SYSTEM_CONF)/amavisd
	cp ./amavisd.tpl $(SYSTEM_CONF)/amavisd
	cp ./amavisd.domains $(SYSTEM_CONF)/amavisd/amavisd.domains
	cp ./sender_scores_sitewide $(SYSTEM_CONF)/amavisd/sender_scores_sitewide
	cp ./spamassassin.tpl $(SYSTEM_CONF)/amavisd/spamassassin.tpl

uninstall:

	rm -rf $(SYSTEM_CONF)/amavisd
