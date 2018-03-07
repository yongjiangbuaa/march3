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
  echo "[$dt] run /data/htdocs/stats/infobright/test_referrer.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/test_referrer.php sid=$i fixdate=$j
 done

exit



for j in {20150831..20150814};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done
exit

for j in {20150831..20150831};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done
exit

for j in {20150731..20150701};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

for j in {20150630..20150601};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

for j in {20150531..20150501};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

for j in {20150430..20150401};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done
exit

for j in {20150331..20150301};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

for j in {20150228..20150201};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

for j in {20150131..20150101};do
 for i in $SIDLIST;do
  dt=`date "+%Y-%m-%d %T"`
  echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
  /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done
exit

for j in {20141231..20141201};do
 for i in $SIDLIST;do
 dt=`date "+%Y-%m-%d %T"`
 echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
 /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

for j in {20141130..20141101};do
 for i in $SIDLIST;do
 dt=`date "+%Y-%m-%d %T"`
 echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
 /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

for j in {20141031..20141001};do
 for i in $SIDLIST;do
 dt=`date "+%Y-%m-%d %T"`
 echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
 /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

for j in {20140930..20140901};do
 for i in $SIDLIST;do
 dt=`date "+%Y-%m-%d %T"`
 echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
 /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done
exit

for j in {20140831..20140801};do
 for i in $SIDLIST;do
 dt=`date "+%Y-%m-%d %T"`
 echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
 /usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 done
done

#for j in {20140731..20140701};do
 #for i in $SIDLIST;do
 #dt=`date "+%Y-%m-%d %T"`
 #echo "[$dt] run /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j"
 #/usr/local/bin/php /data/htdocs/stats/infobright/stats_test.php sid=$i fixdate=$j
 #done
#done

