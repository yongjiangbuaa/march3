#!/bin/sh
PWD=`pwd`


# main params
if [ $# -lt 3 ]; then
	echo "Usage: $0 SERVER_ID_LIST SLAVE_IP_PUB SLAVE_IP_INNER"
	exit 1
fi

SERVER_ID_LIST=$1
SLAVE_IP_PUB=$2
SLAVE_IP_INNER=$3

# confirm action.
echo "change slavedb $SERVER_ID_LIST"
echo "db params is => $SLAVE_IP_PUB $SLAVE_IP_INNER"
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


# change slave db
cd /publish
echo php kaifu/switch_slavedb.php server_id_list=$SERVER_ID_LIST slave_ip_pub=$SLAVE_IP_PUB slave_ip_inner=$SLAVE_IP_INNER


# recover env
cd $PWD

