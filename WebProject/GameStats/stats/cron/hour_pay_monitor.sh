#!/bin/sh

dt=`date "+%Y-%m-%d %T"`
echo "[$dt] run /data/htdocs/stats/scripts/hour_pay_monitor/hour_pay_monitor.php"
/usr/local/bin/php /data/htdocs/stats/scripts/hour_pay_monitor/hour_pay_monitor.php
