#!/bin/sh

RUNCOUNT=`ps aux|grep stats_regremain_test.sh|grep -v log|grep -v grep|wc -l`
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

#for j in {20150818..20150801};do
 #for i in $SIDLIST;do
  #dt=`date "+%Y-%m-%d %T"`
  #echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  #/usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 #done
#done

for j in {20150718..20150701};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done

for j in {20150630..20150601};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done

for j in {20150531..20150501};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done
exit

for j in {20150430..20150401};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done

for j in {20150331..20150301};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done

for j in {20150228..20150201};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done

for j in {20150131..20150101};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done

exit

for j in {20141031..20141001};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done

for j in {20140930..20140901};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_reg.php sid=$i fixdate=$j
 done
done





