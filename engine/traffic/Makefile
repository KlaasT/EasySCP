#!/usr/bin/make -f

# EasySCP a Virtual Hosting Control Panel
# Copyright (C) 2001-2006 by moleSoftware GmbH - http://www.molesoftware.com
# Copyright (C) 2006-2010 by isp Control Panel - http://ispcp.net
# Copyright (C) 2010-2012 by Easy Server Control Panel - http://www.easyscp.net
#
# Version: $Id$
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
# The Original Code is "VHCS - Virtual Hosting Control System".
#
# The Initial Developer of the Original Code is moleSoftware GmbH.
# Portions created by Initial Developer are Copyright (C) 2001-2006
# by moleSoftware GmbH. All Rights Reserved.
#
# Portions created by the ispCP Team are Copyright (C) 2006-2010 by
# isp Control Panel. All Rights Reserved.
#
# Portions created by the EasySCP Team are Copyright (C) 2010-2012 by
# Easy Server Control Panel. All Rights Reserved.

SYSTEM_ENGINE_ROOT=$(SYSTEM_ROOT)/engine

CMD_INSTALL=/usr/bin/install -m 0700 -o root -g root

CMD_CHOWN=/bin/chown

export

install:

	$(SYSTEM_MAKE_DIRS) $(INST_PREF)/usr/sbin
	$(CMD_INSTALL) ./maillogconvert/maillogconvert.pl $(INST_PREF)/usr/sbin

uninstall:

	rm -rf $(INST_PREF)/usr/sbin/maillogconvert.pl
