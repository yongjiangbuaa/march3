#!/bin/sh

RUNCOUNT=`ps aux|grep load_function_log.sh|grep -v log|grep -v grep|wc -l`
if [ $RUNCOUNT -gt 2 ]; then
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] still running"
  exit
fi


dt=`date "+%Y-%m-%d %T"`
echo "[$dt] run /data/htdocs/stats/infobright/load_function_log.php"
/usr/local/bin/php /data/htdocs/stats/infobright/load_function_log.php