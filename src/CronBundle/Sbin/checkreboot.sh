#!/bin/bash
if [ -f /tmp/reboot.server ]; then
  rm -f /tmp/reboot.server
  /sbin/shutdown -h now
fi