#!/bin/sh

SIDLIST=`/usr/local/bin/php /data/htdocs/stats/scripts/get_sid_list.php`
for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/dump_db_test.php sid=$i"
  /usr/local/bin/php /data/htdocs/stats/infobright/dump_db_test.php sid=$i
done
