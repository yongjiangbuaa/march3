#!/bin/sh

RUNCOUNT=`ps aux|grep dump_stat_regAndPaylog.sh|grep -v log|grep -v grep|wc -l`
if [ $RUNCOUNT -gt 2 ]; then
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] still running"
  exit
fi

SIDLIST=`/usr/local/bin/php /data/htdocs/stats/scripts/get_sid_list.php`
for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/dump_stat_regAndPaylog.php sid=$i"
  /usr/local/bin/php /data/htdocs/stats/infobright/dump_stat_regAndPaylog.php sid=$i
done
