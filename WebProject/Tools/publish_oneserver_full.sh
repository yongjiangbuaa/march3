#!/bin/sh
if [ $# -lt 3 ]; then
        echo "Usage: $0 HOST(root@ip) DBIPNAME(dbip:port/dbname) SID"
        exit 1
fi

HOST=$1
DBIPNAME=$2
SID=$3
i=$HOST
sfsip=${HOST#*@}

echo "$HOST $sfsip $DBIPNAME"

dbipport=`echo $DBIPNAME|cut -d '/' -f 1`
dbname=`echo $DBIPNAME|cut -d '/' -f 2`
dbip=`echo $dbipport|cut -d ':' -f 1`
dbport=`echo $dbipport|cut -d ':' -f 2`

# ----------------------------
fab stopsfs:host=$HOST

echo "mysql -f -u -p -h $dbip -P $dbport $dbname < currdeploy/db_struct_changes.sql"
mysql -f -u -p -h $dbip -P $dbport $dbname < currdeploy/db_struct_changes.sql

#if [[ $SID -ge 581 ]] && [[ $SID -le 730 ]] || [[ $SID -ge 900001 ]] && [[ $SID -le 900010 ]]; then
#       echo "redis-cli -h $sfsip hmset cross_fight_act$SID"
#	redis-cli -h $sfsip hmset cross_fight_act$SID round 0 startTime 1446854400000 endTime 1446940800000
#	mysql -f -u -p -h $dbip -P $dbport $dbname -e "INSERT INTO activity VALUES(110007,'CrossKingdomFight',7,1446595200000,1446854400000,1446865200000) ON DUPLICATE KEY update startTime=1446854400000,endTime=1446865200000;"
#fi

#fab setCurrentVersion upload:host=$HOST uploadResourceXml:host=$HOST,xmlfiles="exchange.xml" startsfs:host=$HOST

fab startsfs:host=$HOST

#fab setCurrentVersion upload:host=$HOST uploadPatchJarFile:host=$HOST,ver=1.0.96 uploadResourceXml:host=$HOST,xmlfiles="item.xml|exchange.xml" startsfs:host=$HOST
#fab uploadPatchJarFile:host=$HOST,ver=1.1.6 startsfs:host=$HOST
#fab uploadPatchJarFile:host=$i,ver=1.1.15 uploadResourceXml:host=$i,xmlfiles="item.xml" startsfs:host=$i
