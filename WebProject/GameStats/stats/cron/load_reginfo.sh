#!/bin/sh

RUNCOUNT=`ps aux|grep load_reginfo.sh|grep -v log|grep -v grep|wc -l`
if [ $RUNCOUNT -gt 2 ]; then
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] still running"
  exit
fi

SIDLIST=`/usr/local/bin/php /data/htdocs/stats/scripts/get_sid_list.php`
#for j in {20160725..20160731};do
for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
    #echo "[$dt] run /data/htdocs/stats/infobright/load_reginfo.php sid=$i fixdate=$j"
    #/usr/local/bin/php /data/htdocs/stats/infobright/load_reginfo.php sid=$i fixdate=$j
    echo "[$dt] run /data/htdocs/stats/infobright/load_reginfo.php sid=$i"
    /usr/local/bin/php /data/htdocs/stats/infobright/load_reginfo.php sid=$i
done
#done

