#!/bin/sh
# COK wangxianwei 2015-9-6
PWD=`pwd`

# SFSPARAMS: copy from EXCEL
SFSPARAMS=""

if [ -z "$SFSPARAMS" ]; then 
	echo "SFSPARAMS is empty! Input SFSPARAMS in shell file..." 
	exit 1
fi


# main params
if [ $# -lt 2 ]; then
	echo "Usage: $0 SID REF_SID"
	exit 1
fi

SID=$1
REF_SID=$2

# confirm action.
echo "change db $SID"
echo "db params is => $SFSPARAMS"
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

# stop sfs
cd /usr/local/cok/SFS2X
#sh publish_control stop $SID
fab killSfs:fromsid=$SID,tosid=$SID

# prepare sfs package 
cd /publish
php kaifu/prepare.php ref_server_id=$REF_SID 
php kaifu/switch_db.php $SFSPARAMS

# start sfs
cd /usr/local/cok/SFS2X
sh publish_control start $SID

# valid new sfs ip to all servers
cd /usr/local/cok/SFS2X
fab -P uploadMybatisCross
redis-cli -h GLOBAL_REDIS_IP publish CrossRefreshMybatisChannel $SID

# recover env
cd $PWD

