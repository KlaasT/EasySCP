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

DAEMON = easyscp_daemon
CC := gcc
CFLAGS := -g -ansi -Wall -Wstrict-prototypes -pedantic

ifneq (,$(findstring noopt,$(DAEMON_OPTIONS)))
	CFLAGS += -O0
else
	CFLAGS += -O3
endif

OBJS := easyscp_daemon.o message.o daemon_init.o say.o signal-handlers.o\
receive_data.o send_data.o take_connection.o send_line.o recv_line.o\
helo_cmd.o helo_syntax.o lr_cmd.o lr_syntax.o bye_cmd.o bye_syntax.o

$(DAEMON): $(OBJS)
	$(CC) $(OBJS) -o $(DAEMON)
	strip -s $(DAEMON)

%.o: %.c
	$(CC) $(CFLAGS) -c $< -o $@

clean:
	rm -f *~ *.o $(DAEMON)

.PHONY: clean