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

SYSTEM_ENGINE_ROOT=$(SYSTEM_ROOT)/engine

CMD_CHOWN=/bin/chown
CMD_RM=/bin/rm -rf
CMD_INSTALL=/usr/bin/install -m 0700 -o root -g root
CMD_MAIL_INSTALL=/usr/bin/install -m 0700 -o vmail -g mail
CMD_DIR_INSTALL=/usr/bin/install -d -o root -g root

export

install:

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)
	$(CMD_DIR_INSTALL) -m 0755 $(SYSTEM_ENGINE_ROOT)

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/awstats
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/awstats

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/backup
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/backup

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/daemon
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/daemon

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/daemon/extlibs
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/daemon/extlibs

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/messenger
	$(CMD_DIR_INSTALL) -m 0755 $(SYSTEM_ENGINE_ROOT)/messenger

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/quota
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/quota

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/setup

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/tools
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/tools

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_ENGINE_ROOT)/traffic
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_ENGINE_ROOT)/traffic

	cd ./traffic && $(MAKE) install

	$(CMD_INSTALL) ./easyscp_common_code.pl $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp_common_methods.pl $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp_common_methods.pm $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp_daemon $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-load-db-keys.pl $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-apache-logger $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-rqst-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-dmn-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-sub-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-alssub-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-als-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-htusers-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-htaccess-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-htgroups-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-mbox-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-serv-mngr $(SYSTEM_ENGINE_ROOT)
	$(CMD_INSTALL) ./easyscp-db-passwd $(SYSTEM_ENGINE_ROOT)

	$(CMD_INSTALL) ./awstats/awstats_buildstaticpages.pl $(SYSTEM_ENGINE_ROOT)/awstats
	$(CMD_INSTALL) ./awstats/awstats_updateall.pl $(SYSTEM_ENGINE_ROOT)/awstats

	$(CMD_INSTALL) ./backup/easyscp-bk-task $(SYSTEM_ENGINE_ROOT)/backup
	$(CMD_INSTALL) ./backup/easyscp-backup-all $(SYSTEM_ENGINE_ROOT)/backup
	$(CMD_INSTALL) ./backup/easyscp-backup-easyscp $(SYSTEM_ENGINE_ROOT)/backup

	$(CMD_INSTALL) ./daemon/DaemonDomain.php $(SYSTEM_ENGINE_ROOT)/daemon
	$(CMD_INSTALL) ./daemon/Maps.php $(SYSTEM_ENGINE_ROOT)/daemon
	$(CMD_INSTALL) ./daemon/SocketHandler.php $(SYSTEM_ENGINE_ROOT)/daemon

	$(CMD_INSTALL) ./daemon/extlibs/Daemon.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs

	$(CMD_INSTALL) ./daemon/extlibs/Daemon/Exception.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/Options.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon

	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/Arch.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/BSD.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/Debian.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/Exception.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/Fedora.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/Linux.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/RedHat.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/Ubuntu.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS
	$(CMD_INSTALL) ./daemon/extlibs/Daemon/OS/Windows.php $(SYSTEM_ENGINE_ROOT)/daemon/extlibs/Daemon/OS

	$(CMD_INSTALL) ./messenger/easyscp-arpl-msgr $(SYSTEM_ENGINE_ROOT)/messenger

	$(CMD_INSTALL) ./quota/easyscp-dsk-quota $(SYSTEM_ENGINE_ROOT)/quota

	$(CMD_INSTALL) ./setup/easyscp-permission-functions.sh $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/set-engine-permissions.sh $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/set-gui-permissions.sh $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/easyscp-setup $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/easyscp-setup-methods.pl $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/easyscp-uninstall $(SYSTEM_ENGINE_ROOT)/setup
	$(CMD_INSTALL) ./setup/updDB.php $(SYSTEM_ENGINE_ROOT)/setup

	cp -dR ./setup/PerlVendor $(SYSTEM_ENGINE_ROOT)/setup
	cp -dR ./setup/PerlLib $(SYSTEM_ENGINE_ROOT)/setup

	$(CMD_INSTALL) ./tools/easyscp-del-logs.sh $(SYSTEM_ENGINE_ROOT)/tools
	$(CMD_INSTALL) ./tools/easyscp-del-phptemp.sh $(SYSTEM_ENGINE_ROOT)/tools
	$(CMD_INSTALL) ./tools/easyscp-httpd-logs-mngr $(SYSTEM_ENGINE_ROOT)/tools
	$(CMD_INSTALL) ./tools/easyscp-net-interfaces-mngr $(SYSTEM_ENGINE_ROOT)/tools

	$(CMD_INSTALL) ./traffic/easyscp-srv-traff $(SYSTEM_ENGINE_ROOT)/traffic
	$(CMD_INSTALL) ./traffic/easyscp-vrl-traff $(SYSTEM_ENGINE_ROOT)/traffic

	if test $(HOST_OS) = suse ; then \
		$(CMD_INSTALL) ./traffic/easyscp-vrl-traff-SUSE $(SYSTEM_ENGINE_ROOT)/traffic; \
	elif test $(HOST_OS) = opensuse ; then \
		$(CMD_INSTALL) ./traffic/easyscp-vrl-traff-SUSE $(SYSTEM_ENGINE_ROOT)/traffic; \
	fi

uninstall:

	rm -rf $(SYSTEM_ENGINE_ROOT)
