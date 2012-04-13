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

SYSTEM_DAEMON_ROOT=$(SYSTEM_ROOT)/daemon

CMD_INSTALL=/usr/bin/install -m 0700 -o root -g root
CMD_DIR_INSTALL=/usr/bin/install -d -o root -g root

export

install:

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_DAEMON_ROOT)
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_DAEMON_ROOT)

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_DAEMON_ROOT)/extlibs
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_DAEMON_ROOT)/extlibs

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon

	$(SYSTEM_MAKE_DIRS) $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_DIR_INSTALL) -m 0700 $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS

	$(CMD_INSTALL) ./DaemonCore.php $(SYSTEM_DAEMON_ROOT)
	$(CMD_INSTALL) ./DaemonDomain.php $(SYSTEM_DAEMON_ROOT)
	$(CMD_INSTALL) ./easyscp_daemon $(SYSTEM_DAEMON_ROOT)
	$(CMD_INSTALL) ./Maps.php $(SYSTEM_DAEMON_ROOT)
	$(CMD_INSTALL) ./SocketHandler.php $(SYSTEM_DAEMON_ROOT)

	$(CMD_INSTALL) ./extlibs/Daemon.php $(SYSTEM_DAEMON_ROOT)/extlibs

	$(CMD_INSTALL) ./extlibs/Daemon/Exception.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon
	$(CMD_INSTALL) ./extlibs/Daemon/Options.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon
	$(CMD_INSTALL) ./extlibs/Daemon/OS.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon

	$(CMD_INSTALL) ./extlibs/Daemon/OS/Arch.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_INSTALL) ./extlibs/Daemon/OS/BSD.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_INSTALL) ./extlibs/Daemon/OS/Debian.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_INSTALL) ./extlibs/Daemon/OS/Exception.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_INSTALL) ./extlibs/Daemon/OS/Fedora.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_INSTALL) ./extlibs/Daemon/OS/Linux.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_INSTALL) ./extlibs/Daemon/OS/RedHat.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_INSTALL) ./extlibs/Daemon/OS/Ubuntu.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS
	$(CMD_INSTALL) ./extlibs/Daemon/OS/Windows.php $(SYSTEM_DAEMON_ROOT)/extlibs/Daemon/OS

uninstall:

	rm -rf $(SYSTEM_DAEMON_ROOT)
