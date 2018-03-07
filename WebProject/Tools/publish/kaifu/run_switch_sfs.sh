#!/bin/sh
# COK wangxianwei 2015-9-6

# main params
#if [ $# -lt 4 ]; then
	#echo "Usage: $0 SID OLD_INNER_IP NEW_INNER_IP REF_SID"
	echo "Usage: `basename $0` SID REF_SID NEW_PUB_IP NEW_INNER_IP"
#	exit 1
#fi

SID=$1
REF_SID=$2
#OLD_INNER_IP=$2
NEW_PUB_IP=$3
NEW_INNER_IP=$4
#REF_SID=$4
if [ -z "$SID" ] ;then
    while read -p "SID: " SID;do
        if [ -z "$SID" ] ;then
            continue
        else
            break
        fi
    done
fi

if [ -z "$REF_SID" ] ;then
    while read -p "REF_SID: " REF_SID;do
        if [ -z "$REF_SID" ] ;then
            continue
        else
            break
        fi
    done
fi

if [ -z "$NEW_PUB_IP" ] ;then
    while read -p "NEW_PUB_IP: " NEW_PUB_IP;do
        if [ -z "$NEW_PUB_IP" ] ;then
            continue
        else
            break
        fi
    done
fi

if [ -z "$NEW_INNER_IP" ] ;then
    while read -p "NEW_INNER_IP: " NEW_INNER_IP;do
        if [ -z "$NEW_INNER_IP" ] ;then
            continue
        else
            break
        fi
    done
fi
SFS_INFO=(`curl -sS http://10.81.103.90:8800/deploy/sfs?sid=$SID`)
if [ ${#SFS_INFO[@]} -lt 6 ] ;then
    echo "Get Smartfox server info error."
    exit 1
fi
OLD_PUB_IP=${SFS_INFO[0]}
OLD_INNER_IP=${SFS_INFO[1]}
DB_PUB_IP=${SFS_INFO[2]}
DB_INNER_IP=${SFS_INFO[3]}
SLAVE_PUB_IP=${SFS_INFO[4]}
SLAVE_INNER_IP=${SFS_INFO[5]}
if [ -z "$OLD_PUB_IP" -o -z "$OLD_INNER_IP" -o -z "$DB_PUB_IP" -o -z "$DB_INNER_IP" -o -z "$SLAVE_PUB_IP" -o -z "$SLAVE_INNER_IP" ] ; then
    echo "Smartfox Server info error"
    exit 1
fi
SAVE_PWD=`pwd`

# SFSPARAMS: copy from EXCEL
SFSPARAMS="server_id=$SID server_ip_pub=$NEW_PUB_IP server_ip_inner=$NEW_INNER_IP db_ip_pub=$DB_PUB_IP db_ip_inner=$DB_INNER_IP slave_ip_pub=$SLAVE_PUB_IP slave_ip_inner=$SLAVE_INNER_IP"

# confirm action.
echo "Migrate $SID from $OLD_PUB_IP/$OLD_INNER_IP ==> $NEW_PUB_IP/$NEW_INNER_IP"
echo "Other Params:"
echo -e "\t\tDB Master: $DB_PUB_IP/$DB_INNER_IP"
echo -e "\t\tDB Slave: $SLAVE_PUB_IP/$SLAVE_INNER_IP"
echo "$SFSPARAMS"
read -p "Are you sure? (yes/no) : " -r
echo
if [[ $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
	echo "confirm OK. op continue ..."
	echo
else
    # do dangerous stuff
    echo "op terminated !"
    echo
    exit 1
fi



# prepare sfs package 
echo cd /publish
echo php kaifu/prepare.php ref_server_id=$REF_SID 
echo php kaifu/switch_sfs.php $SFSPARAMS

read  -p "Prepare smartfox package done. press ENTER to continue"

# change cached ip
echo sed -i "s/$OLD_INNER_IP/$NEW_INNER_IP/g" /usr/local/cok/SFS2X/fabfile.py
echo sed -i "s/$OLD_INNER_IP/$NEW_INNER_IP/g" /publish/logs/server_list.cache.txt
read  -p "Change cached ip done. press ENTER to continue"

# start sfs
echo cd /usr/local/cok/SFS2X
echo sh publish_control start $SID
read  -p "start smartfox done. press ENTER to continue"

# valid new sfs ip to all servers
echo cd /usr/local/cok/SFS2X
echo fab uploadServersXml2PhpServer
echo fab -P uploadServersXml
echo redis-cli -h GLOBAL_REDIS_IP publish CrossRefreshRMIChannel $SID
read  -p "valid new sfs ip to all servsers done. press ENTER to continue"

# recover env
echo cd $SAVE_PWD
