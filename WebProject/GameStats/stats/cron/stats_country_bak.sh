#!/bin/sh

SIDLIST=`/usr/local/bin/php /data/htdocs/stats/scripts/get_sid_list.php`

for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
  for j in {20140701..20140731}
  do
  	echo "[$dt] run /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j"
  	/usr/local/bin/php /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j
  done
done

for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
  for j in {20140801..20140831}
  do
  	echo "[$dt] run /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j"
  	/usr/local/bin/php /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j
  done
done

for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
  for j in {20140901..20140930}
  do
  	echo "[$dt] run /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j"
  	/usr/local/bin/php /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j
  done
done

for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
  for j in {20141001..20141031}
  do
  	echo "[$dt] run /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j"
  	/usr/local/bin/php /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j
  done
done

for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
  for j in {20141101..20141130}
  do
  	echo "[$dt] run /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j"
  	/usr/local/bin/php /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j
  done
done

for i in $SIDLIST
do
  dt=`date "+%Y-%m-%d %T"`
  for j in {20141201..20141202}
  do
  	echo "[$dt] run /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j"
  	/usr/local/bin/php /data/htdocs/stats/infobright/stats_country.php sid=$i fixdate=$j
  done
done