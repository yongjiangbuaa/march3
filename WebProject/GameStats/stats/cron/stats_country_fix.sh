#!/bin/sh

SIDLIST=`/usr/local/bin/php /data/htdocs/stats/scripts/get_sid_list.php`

for i in $SIDLIST
do
  	dt=`date "+%Y-%m-%d %T"`
  	echo "[$dt] run /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=20141203"
  	/usr/local/bin/php /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=20141203
done
