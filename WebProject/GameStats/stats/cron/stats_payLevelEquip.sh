#!/bin/sh

RUNCOUNT=`ps aux|grep stats_payLevelEquip|grep -v log|grep -v grep|wc -l`
if [ $RUNCOUNT -gt 2 ]; then
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] still running"
  exit
fi

SIDLIST=`/usr/local/bin/php /data/htdocs/stats/scripts/get_sid_list.php`

RSORTSIDLIST=$SIDLIST
SIDLIST=""
for a in $RSORTSIDLIST
do
    SIDLIST=${a}" "${SIDLIST}
done

for i in $SIDLIST
do
  	dt=`date "+%Y-%m-%d %T"`
  	echo "[$dt] run /data/htdocs/stats/infobright/paylevel_stat_equip.php sid=$i"
  	/usr/local/bin/php /data/htdocs/stats/infobright/paylevel_stat_equip.php sid=$i
done
