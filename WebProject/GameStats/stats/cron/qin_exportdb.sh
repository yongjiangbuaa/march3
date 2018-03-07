#!/bin/sh

#统计服  查询数据
#脚本所在位置 /data/htdocs/stats/cron
RUNCOUNT=`ps aux|grep qin_export.sh|grep -v log|grep -v grep|wc -l`
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

#for j in {20160626..20160630};do
#for i in $SIDLIST;do
#    k=$(($j+1));
#    if [ $j -eq 20160630 ];then
#        k=20160701;
#    fi
##echo $k.$j.$i
#/home/elex/mysql/bin/mysql -h10.153.120.26 -P5029 -uroot -pK2NDBm6zegpiE -e "select f.pf,f.country,ur.appversion ,f.castlelevel,count(1) cnt from (select distinct uid from snapshot_s$i.stat_login_full where date=$j) AA inner join snapshot_s$i.stat_login_full f on f.uid = AA.uid inner join snapshot_s$i.user_reg ur on ur.uid=f.uid left join (select distinct uid from snapshot_s$i.stat_login_full where date=$k) BB on AA.uid=BB.uid where BB.uid is NULL group by f.pf,f.country,ur.appversion ,f.castlelevel order by f.pf,f.country,ur.appversion ,f.castlelevel ;"> /home/qinbinbin/qin$j.$i
#
#done
#done

for i in $SIDLIST;do
/home/elex/mysql/bin/mysql -h10.153.120.26 -P5029 -uroot -pK2NDBm6zegpiE -e "select $i,sum(spend) from snapshot_s$i.paylog p inner join test.account_new_tmp1 a on a.gameuid=p.uid where a.server=$i and p.time>1469664000000 and p.time<1470182400000;"> /home/qinbinbin/xiaoyu_fb.$i
done
exit






#/home/elex/mysql/bin/mysql -h10.153.120.26 -P5029 -uroot -pK2NDBm6zegpiE -e "select case when nn.pic in(100008,100015,100050,100053,100054,'g008','g015','g024','g044','g050','g053','g054') then 1 when nn.pic in('g026','g032','g038','g041','g045','g046','g052','g007','g012',100052,100026,100032,100038,100041) then 2 end as sex ,count(1) from snapshot_s$i.userprofile_full nn  inner join (select uid from snapshot_s$i.stat_login where date=20160807) l on nn.uid=l.uid group by nn.pic;"> /home/qinbinbin/xiaoyu_0808.$i

#/home/elex/mysql/bin/mysql -h10.153.120.26 -P5029 -uroot -pK2NDBm6zegpiE -e "replace into stat_allserver.ad_temp(uid,gaid,ip,country) select r.uid as uid,u.gaid as gaid ,r.ip as ip ,r.country as country from snapshot_s$i.stat_reg r inner join snapshot_s$i.userprofile_full u on u.uid=r.uid where r.type=0 and r.date>=20160601 and r.date<=20160630;"> /home/qinbinbin/data/qin.$i
#/home/elex/mysql/bin/mysql -h10.153.120.26 -P5029 -uroot -pK2NDBm6zegpiE -e "select f.uid,f.date,f.regdate from snapshot_s$i.stat_login_full f inner join snapshot_s$i.stat_reg r on f.uid = r. uid where r.pf='market_global' and r.referrer='facebook' group by f.uid,f.date order by f.uid ,f.date;"> /home/qinbinbin/data/qin.$i
#/home/elex/mysql/bin/mysql -h10.153.120.26 -P5029 -uroot -pK2NDBm6zegpiE -e "select f.pf,f.country,ur.appversion ,f.castlelevel,count(1) cnt from (select distinct uid from snapshot_s$i.stat_login_full where date=$j) AA inner join snapshot_s$i.stat_login_full f on f.uid = AA.uid inner join snapshot_s$i.user_reg ur on ur.uid=f.uid left join (select distinct uid from snapshot_s$i.stat_login_full where date=$k) BB on AA.uid=BB.uid where BB.uid is NULL group by f.pf,f.country,ur.appversion ,f.castlelevel order by f.pf,f.country,ur.appversion ,f.castlelevel ;"> /home/qinbinbin/qin$j.$i
