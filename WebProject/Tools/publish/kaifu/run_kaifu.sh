#!/bin/sh

REF_SID=$1
PARAMS_FILE=$2
# TODO
NEW_SERVERS_FROM_SID=1
NEW_SERVERS_TO_SID=4

if [ -z "$REF_SID" ] ;then
    while read -p "REF_SID: " REF_SID;do
        if [ -z "$REF_SID" ] ;then
            continue
        else
            break
        fi
    done
fi

if [ -z "$PARAMS_FILE" ] ;then
    while read -p "PARAMS_FILE: " PARAMS_FILE;do
        if [ -z "$PARAMS_FILE" ] ;then
            continue
        else
            break
        fi
    done
fi

if [ -f "$PARAMS_FILE" ] ;then
    echo "$PARAMS_FILE OK."
else
    echo "$PARAMS_FILE not exists !"
    exit 1
fi

SAVE_PWD=`pwd`


# prepare sfs package 
echo cd /publish
echo php kaifu/prepare.php ref_server_id=$REF_SID 
read  -p "Prepare smartfox package done. press ENTER to continue"


# SFSPARAMS: read from file
while read SFSPARAMS; do
    echo $SFSPARAMS
    echo php kaifu/kaifu.php $SFSPARAMS
done < $PARAMS_FILE

# checkxmlformat.
php /publish/tools/check_xml_farmat.php
read  -p "Check xml format done. press ENTER to continue"

echo cd /usr/local/cok/SFS2X

# rsync cross file to All NEW servers.
echo sh publish_control syncCrossFile "${NEW_SERVERS_FROM_SID}-${NEW_SERVERS_TO_SID}"
read  -p "syncCrossFile done. press ENTER to continue"

# start server [ will cost 8 mins]
echo sh publish_control start "${NEW_SERVERS_FROM_SID}-${NEW_SERVERS_TO_SID}"
read  -p "Start sfs done. press ENTER to continue"

# upload servers.xml to php getserverlist servers
echo fab uploadServersXml2PhpServer
read  -p "Upload servers.xml to php getserverlist servers done. press ENTER to continue"

# upload servers.xml/rmiClient.xml/mybatis-cross.xml to ALL SFS servers
echo fab -P uploadMybatisCross
echo fab -P uploadServersXml
read  -p "syncCrossFile to ALL sfs done. press ENTER to continue"

# refresh cross server info
echo redis-cli -h GLOBAL_REDIS_IP publish CrossRefreshMybatisChannel ${NEW_SERVERS_FROM_SID}
echo redis-cli -h GLOBAL_REDIS_IP publish CrossRefreshRMIChannel ${NEW_SERVERS_FROM_SID}
read  -p "Refresh cross server infodone. press ENTER to continue"

# TODO modify fab. Add NEW servers' info
# TODO deploy ccsa to NEW servers
#   fab -P deployCCSA:fromsid=${NEW_SERVERS_FROM_SID},tosid=${NEW_SERVERS_TO_SID}
#   fab -P startCCSA:fromsid=${NEW_SERVERS_FROM_SID},tosid=${NEW_SERVERS_TO_SID}
#   fab -P add_forbidden_words

# recover env
echo cd $SAVE_PWD
