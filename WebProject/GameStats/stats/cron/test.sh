#!/bin/sh
SIDLIST=`/usr/local/bin/php /data/htdocs/stats/scripts/get_sid_list.php`

RSORTSIDLIST=$SIDLIST
SIDLIST=""
for a in $RSORTSIDLIST
do
    SIDLIST=${a}" "${SIDLIST}
done

 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/test.php sid=$i"
  /usr/local/bin/php /data/htdocs/stats/infobright/test.php sid=$i
 done
exit
