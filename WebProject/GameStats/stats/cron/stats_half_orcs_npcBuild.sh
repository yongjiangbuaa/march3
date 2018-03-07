#!/bin/sh

RUNCOUNT=`ps aux|grep stats_half_orcs_npcBuild.sh|grep -v log|grep -v grep|wc -l`
if [ $RUNCOUNT -gt 2 ]; then
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] still running"
  exit
fi


  	dt=`date "+%Y-%m-%d %T"`
  	echo "[$dt] run /data/htdocs/stats/infobright/stats_half_orcs_npcBuild.php"
  	/usr/local/bin/php /data/htdocs/stats/infobright/stats_half_orcs_npcBuild.php

