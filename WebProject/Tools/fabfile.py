#fabfile.py
#ver. 4g ; new server framework. support multi zones at one sfs server.

from fabric.api import *
from fabric.contrib import files
from fabric.contrib.console import confirm
from fabric.colors import red, green
import time
import os
import json
import random
import MySQLdb

db = MySQLdb.connect("10.82.60.173","gow","ZPV48MZH6q9V8oVNtu","cokdb_admin_deploy" )
cursor = db.cursor()
cursor.execute("select ip_inner from tbl_webserver where svr_id>0 and svr_id < 999000")
data = cursor.fetchall()
production=[]
for ip in data:
    production.append("root@"+ip[0])
db.close()

env.roledefs = {
  'production': production,
  'webgameserver': ['root@xxx', 'root@xxx'],
  'phpserver': ['root@xxx', 'root@xxx'],
  'payserver': ['root@xxx', 'root@xxx'],
  'cobarserver': ['root@xxx', 'root@xxx'],
  'newservers': ['root@xxx', 'root@xxx'],
}

serverInfoJson = local("/home/elex/php/bin/php /publish/util/get_server_info_json_for_fab.php sids=ALL", capture=True)
serverInfo = json.loads(serverInfoJson)

GLOBAL_DB_IP = '10.82.60.173'
TEMPLATE_COKDB_DBNAME = 'cokdb_template'
GLOBAL_COKDB_DBNAME = 'cokdb_global'
PUBLISH_DIR = '/publish'
SFS2X_LOCAL = '/usr/local/cok/SFS2X'
SFS2X_REMOTE = '/usr/local/cok/SFS2X'

def showEnv():
  print 'production: ', env.roledefs['production']
  for shost, sinfo in serverInfo.items():
    print "S%s %s" % (sinfo['id'], shost)

def getSid():
  print env.host, serverInfo[env.host]['id']

def setCurrentVersion():
    global APP_VERSION, CLIENT_CONF_VERSION, PACKAGE_NAME, UPLOAD_PATH, DEPLOY_FILES
    UPLOAD_PATH = local("ls -l %s/currdeploy | awk -F'->' '{print $2}'" % SFS2X_LOCAL, capture=True)
    UPLOAD_PATH = UPLOAD_PATH.strip()
    APP_VERSION = local("awk -F'=' '$0 ~ /APP_VERSION/ {print $2}' %s/%s/deploy.log" % (SFS2X_LOCAL, UPLOAD_PATH), capture=True)
    CLIENT_CONF_VERSION = local("awk -F'=' '$0 ~ /CLIENT_CONF_VERSION/ {print $2}' %s/%s/deploy.log" % (SFS2X_LOCAL, UPLOAD_PATH), capture=True)
    PACKAGE_NAME = local("awk -F'=' '$0 ~ /PACKAGE_NAME/ {print $2}' %s/%s/deploy.log" % (SFS2X_LOCAL, UPLOAD_PATH), capture=True)
    files = local("awk -F'=' '$0 ~ /DEPLOY_FILES/ {print $2}' %s/%s/deploy.log" % (SFS2X_LOCAL, UPLOAD_PATH), capture=True)
    DEPLOY_FILES = files.split(',')
    print APP_VERSION, CLIENT_CONF_VERSION, PACKAGE_NAME, UPLOAD_PATH
    print DEPLOY_FILES

@roles('production')
def upload():
  with cd(SFS2X_REMOTE):
    #3g->4g changes: -->>
    run("rm -f extensions/COK*/COK-*-Extension.jar")
    run("rm -f extensions/__lib__/COK-*-Extension.jar")
    run("rm -rf www/gameservice/")
    run("rm -f lib/slf4j-api-1.5.10.jar")
    run("rm -f lib/slf4j-log4j12.jar")
    run("rm -f extensions/__lib__/gcm-server.jar")
    run("rm -f extensions/__lib__/mongo*")
    run("rm -f extensions/__lib__/ApiGateway-HMAC_2.0.0.jar")
    run("rm -f lib/ApiGateway-HMAC_2.0.0.jar")
    run("rm -f gameconfig/mybatis*.xml")
    run("rm -f gameconfig/config*.properties")
    run("rm -f config/log4j.properties")
    run("rm -f resource/dragon_position.xml")
    run("rm -f extensions/__lib__/guava-12.0.jar ")
    #3g->4g changes: <<--
    run("rm -f extensions/__lib__/guava-15.0.jar")
    run("rm -f extensions/__lib__/jackson-core-2.4.0.jar")
    run("rm -f extensions/__lib__/jackson-databind-2.4.0.jar")
    run("rm -f extensions/__lib__/jedis-2.4.2.jar")
    #4g->5g changes: <<--
    run("rm -rf www/root/")
    run("rm -rf www/ServletExample/")
    if env.host != '10.41.163.10':
      run("rm -f www/jolokia-war-1.2.3.war")
    run("mkdir -p %s" % UPLOAD_PATH)
    run("rm -f currdeploy")
    run("ln -s %s currdeploy" % UPLOAD_PATH)
    run("rm -rf source")
    run("mkdir -p resource/cn/")
    put("%s/gameconfig/badwords.txt" % (SFS2X_LOCAL), "gameconfig/")
    put("%s/%s/%s" % (SFS2X_LOCAL, UPLOAD_PATH, PACKAGE_NAME), "%s/%s" % (SFS2X_REMOTE, UPLOAD_PATH))
    with cd(UPLOAD_PATH):
      run("tar zxf %s" % PACKAGE_NAME)
      #delete server-based individul files
      run("rm -f cok-game/config.properties")
      #config*.zip/lua*.zip use CDN. and wont upload to sfs server.
      for file in DEPLOY_FILES:
        if not files.exists(file) and not confirm('%s is not exists' % file):
          abort('%s is not exists' % file)
      for file in DEPLOY_FILES:
        if file == 'serverxml.tgz':
          run("tar zxf %s -C ../../resource" % file)
        if file == 'newlib' and run("ls -A %s/" % file):
          with settings(warn_only=True):
            run("cp %s/*.jar ../../lib" % file)
        if file == 'newextlib' and run("ls -A %s/" % file):
          with settings(warn_only=True):
            run("cp %s/*.jar ../../extensions/__lib__" % file)
        if file == 'logback.xml':
          run("cp %s ../../" % file)
          run("sed -i -e 's/127.0.0.1:3306\/cokdb_global/10.121.248.45:3306\/cokdb_monitor/g' ../../logback.xml")
          run("sed -i -e 's/10.41.163.20:3306\/cokdb_global/10.121.248.45:3306\/cokdb_monitor/g' ../../logback.xml")
          run("sed -i -e 's/10.142.9.80:3306\/cokdb_global/10.121.248.45:3306\/cokdb_monitor/g' ../../logback.xml")
          run("sed -i -e 's/admin123/t9qUzJh1uICZkA/g' ../../logback.xml")
      run("cp ini-lang/* ../../resource/cn/")
      run("cp cok-common/* ../../extensions/__lib__/")
      run("cp cok-game/* ../../extensions/COK%s/" % (serverInfo[env.host]['id']))
      run("cp cok-web/* ../../www/")
      run("cp -r source ../../")
    # server-based individul files. special process.
    put("%s/onlineconfig/onlineconfig%s" % (SFS2X_LOCAL, serverInfo[env.host]['id']), "%s/extensions/COK%s/config.properties" % (SFS2X_REMOTE, serverInfo[env.host]['id']))
    put("%s/update/config/mybatis-cross.xml" % (PUBLISH_DIR), "%s/gameconfig/mybatis-cross.xml" % (SFS2X_REMOTE))
    put("%s/update/config/rmiClient.xml" % (PUBLISH_DIR), "%s/gameconfig/rmiClient.xml" % (SFS2X_REMOTE))
    put("%s/update/config/servers.xml" % (PUBLISH_DIR), "%s/resource/servers.xml" % (SFS2X_REMOTE))
    put("/tmp/logback.xml", "%s/" % (SFS2X_REMOTE))

@roles('production')
def stopsfs():
      nowts = int(time.time())
      run("echo %s > /tmp/update.txt" % nowts)
      run('redis-cli set ServerStatus:S%s 2' % (serverInfo[env.host]['id']))
      run('redis-cli set ServerStatus:S%s:StopStartTime %s' % (serverInfo[env.host]['id'], nowts))
      with cd(SFS2X_REMOTE):
        with settings(warn_only=True):
            result = run("./sfs2x-service stop && sleep 10")
            #if result.failed and not confirm("failed to stop smrtfoxserver. Continue anyway?"):
              #abort("it's failed to stop smartfoxserver")
            #if result.failed:
              #warn("it's failed to stop smartfoxserver")
            if result.failed:
              sfs_status = run('redis-cli get ServerStatus:S%s' % (serverInfo[env.host]['id']))
              while (sfs_status != '"2"' and sfs_status != '(nil)'):
                #print("havnot stopped. waiting... S%s %s" % (serverInfo[env.host]['id'], env.host))
                stime = random.randint(10, 30)
                time.sleep(stime)
                sfs_status = run('redis-cli get ServerStatus:S%s' % (serverInfo[env.host]['id']))
              print("sfs2x-service stopped successfully S%s %s" % (serverInfo[env.host]['id'], env.host))
            else:
              print("sfs2x-service stopped successfully S%s %s" % (serverInfo[env.host]['id'], env.host))

@roles('production')
def startsfs():
    with cd(SFS2X_REMOTE):
        with settings(warn_only=True):
            result = run("./sfs2x-service start && sleep 10")
            sfs_status = run('redis-cli get ServerStatus:S%s' % (serverInfo[env.host]['id']))
            while sfs_status != '"0"':
              #print("havnot started. waiting... S%s %s" % (serverInfo[env.host]['id'], env.host))
              stime = random.randint(10, 30)
              time.sleep(stime)
              sfs_status = run('redis-cli get ServerStatus:S%s' % (serverInfo[env.host]['id']))
            nowts = int(time.time())
            run('redis-cli set ServerStatus:S%s:StopEndTime %s' % (serverInfo[env.host]['id'], nowts))
            print("sfs2x-service started successfully S%s %s" % (serverInfo[env.host]['id'], env.host))

def changeDbStructTemplate():
  with lcd(SFS2X_LOCAL):
      with settings(warn_only=True):
          result = local("mysql -uroot -pt9qUzJh1uICZkA -h %s -P 3306 -f %s < currdeploy/db_struct_changes.sql 2>&1" % (GLOBAL_DB_IP, TEMPLATE_COKDB_DBNAME), capture=True)
          print result
          if result.failed and not confirm("failed to update database. Continue anyway?"):
              abort("it's failed to update database")

def changeDbStructGlobal():
  with lcd(SFS2X_LOCAL):
      with settings(warn_only=True):
          result = local("mysql -uroot -pt9qUzJh1uICZkA -h %s -P 3306 -f %s < currdeploy/db_struct_changes_global.sql 2>&1" % (GLOBAL_DB_IP, GLOBAL_COKDB_DBNAME), capture=True)
          print result
          if result.failed and not confirm("failed to update database. Continue anyway?"):
              abort("it's failed to update database")

def changeDbStruct(dbipname):
  with lcd(SFS2X_LOCAL):
      with settings(warn_only=True):
          dbip = dbipname.split('/')[0].split(':')[0]
          dbport = dbipname.split('/')[0].split(':')[1]
          dbname = dbipname.split('/')[1]
          result = local("mysql -uroot -pt9qUzJh1uICZkA -h %s -P %s -f %s < currdeploy/db_struct_changes.sql 2>&1" % (dbip, dbport, dbname), capture=True)
          print result
          if result.failed:
              print ("failed to update database")

@roles('production')
def uploadJar():
    with cd(SFS2X_REMOTE):
        run("mkdir -p %s" % UPLOAD_PATH)
        run("rm -f currdeploy")
        run("ln -s %s currdeploy" % UPLOAD_PATH)
        put("%s/%s/%s" % (SFS2X_LOCAL, UPLOAD_PATH, PACKAGE_NAME), "%s/%s" % (SFS2X_REMOTE, UPLOAD_PATH))
        with cd(UPLOAD_PATH):
            run("tar zxf %s" % PACKAGE_NAME)
            run("mkdir -p ../../extensions/COK%s" % serverInfo[env.host]['id'])
            run("rm -rf ../../extensions/COK%s/COK*" % serverInfo[env.host]['id'])
            run("cp cok-game/COK-%s-Extension.jar ../../extensions/COK%s/" % (APP_VERSION, serverInfo[env.host]['id']))

@roles('production')
def uploadJarFile(ver):
    with cd(SFS2X_REMOTE):
        put("%s/extensions/COK/COK-%s-Extension.jar" % (SFS2X_LOCAL, ver), "%s/extensions/COK%s/" % (SFS2X_REMOTE, serverInfo[env.host]['id']))

@roles('production')
def uploadPatchJarFile(ver):
    with cd(SFS2X_REMOTE):
        put("%s/patch_jarfile/COK-%s-Extension.jar" % (SFS2X_LOCAL, ver), "%s/extensions/COK%s/" % (SFS2X_REMOTE, serverInfo[env.host]['id']))
        put("%s/patch_jarfile/cok-common-1.0.jar" % (SFS2X_LOCAL),  "%s/extensions/__lib__/cok-common-1.0.jar" % (SFS2X_REMOTE))
        put("%s/patch_jarfile/gameservice.war" % (SFS2X_LOCAL),  "%s/www/gameservice.war" % (SFS2X_REMOTE))
        #put("%s/extensions/__lib__/maxmind-db-1.0.0.jar" % SFS2X_LOCAL, "/usr/local/cok/SFS2X/extensions/__lib__/")
        #put("%s/extensions/__lib__/geoip2-2.3.0.jar" % SFS2X_LOCAL, "/usr/local/cok/SFS2X/extensions/__lib__/")
        #run("rm -f extensions/__lib__/guava-12.0.jar ")
        #put("%s/extensions/__lib__/guava-15.0.jar" % (SFS2X_LOCAL),  "%s/extensions/__lib__/" % (SFS2X_REMOTE))
        #put("%s/extensions/__lib__/bonecp-0.8.0.RELEASE.jar" % (SFS2X_LOCAL),  "%s/extensions/__lib__/" % (SFS2X_REMOTE))

@roles('production')
def downloadGameconfig():
    get('%s/extensions/COK%s/config.properties' % (SFS2X_REMOTE, serverInfo[env.host]['id']), 'onlineconfig/onlineconfig%s' % serverInfo[env.host]['id'])

@roles('production')
def updateGameconfigAppVersion(appVer):
  with cd(SFS2X_REMOTE):
      run('sed -i "s/realtime_app_version=.*/realtime_app_version=%s/" extensions/COK%s/config.properties' % (appVer, serverInfo[env.host]['id']))
      run('redis-cli set property%s_realtime_app_version "%s"' % (serverInfo[env.host]['id'], appVer))

@roles('production')
def updateGameconfigClientVersion(appVer,clientVer):
  with cd(SFS2X_REMOTE):
      run('sed -i "s/realtime_app_%s=.*/realtime_app_%s=%s/" extensions/COK%s/config.properties' % (appVer, appVer, clientVer, serverInfo[env.host]['id']))
      run('redis-cli set property%s_realtime_app_%s "%s"' % (serverInfo[env.host]['id'], appVer, clientVer))
      #put("%s/resource/cn/*.ini" % SFS2X_LOCAL, "resource/cn/")

def updateGameconfigClientVersionRedisOnly(targetsid,appVer,clientVer):
  for shost, sinfo in serverInfo.items():
    if targetsid == 'ALL' or sinfo['id'] == targetsid:
      local('/usr/local/redis/redis-cli -h %s set property%s_realtime_app_%s "%s"' % (shost, sinfo['id'], appVer, clientVer))

@roles('production')
def updateGameconfigAddNewVersion(appVer,clientVer):
  with cd(SFS2X_REMOTE):
      run('sed -i "/realtime_app_version=.*/arealtime_app_%s=%s" extensions/COK%s/config.properties' % (appVer, clientVer, serverInfo[env.host]['id']))
      run('redis-cli set property%s_realtime_app_%s "%s"' % (serverInfo[env.host]['id'], appVer, clientVer))
      run('sed -i "s/realtime_app_version=.*/realtime_app_version=%s/" extensions/COK%s/config.properties' % (appVer, serverInfo[env.host]['id']))
      run('redis-cli set property%s_realtime_app_version "%s"' % (serverInfo[env.host]['id'], appVer))
      #put("%s/resource/cn/*.ini" % SFS2X_LOCAL, "resource/cn/")

@roles('production')
def updateServerxmlversion():
  with cd(SFS2X_REMOTE):
      serversxml_version=time.time()
      run('sed -i "s/realtime_server_xml_version=.*/realtime_server_xml_version=%s/" extensions/COK%s/config.properties' % (serversxml_version, serverInfo[env.host]['id']))
      run('redis-cli set property%s_realtime_server_xml_version %s' % (serverInfo[env.host]['id'], serversxml_version))

@roles('production')
def uploadResourceXml(xmlfiles,fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
    with cd(SFS2X_REMOTE):
      now_time = int(time.time())
      multifiles = xmlfiles.split('|')
      for xmlfile in multifiles:
        run("cp %s/resource/%s /tmp/%s_%s" % (SFS2X_REMOTE, xmlfile, now_time, xmlfile))
        put("%s/resource/%s" % (SFS2X_LOCAL, xmlfile), "%s/resource/%s" % (SFS2X_REMOTE, xmlfile))
      serversxml_version=time.time()
      run('redis-cli set property%s_realtime_server_xml_version %s' % (serverInfo[env.host]['id'], serversxml_version))

@roles('production')
def uploadPatchResourceXml(xmlfiles,fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
    with cd(SFS2X_REMOTE):
      now_time = int(time.time())
      multifiles = xmlfiles.split('|')
      for xmlfile in multifiles:
        run("cp %s/resource/%s /tmp/%s_%s" % (SFS2X_REMOTE, xmlfile, now_time, xmlfile))
        put("%s/patch_jarfile/%s" % (SFS2X_LOCAL, xmlfile), "%s/resource/%s" % (SFS2X_REMOTE, xmlfile))
      serversxml_version=time.time()
      run('redis-cli set property%s_realtime_server_xml_version %s' % (serverInfo[env.host]['id'], serversxml_version))

@roles('production')
def downloadResourceXml2PatchDir(xmlfiles):
  with cd(SFS2X_REMOTE):
      now_time=int(time.time())
      multifiles = xmlfiles.split('|')
      for xmlfile in multifiles:
        get("resource/%s" % (xmlfile), "%s/patch_jarfile/%s_%s" % (SFS2X_LOCAL, xmlfile, serverInfo[env.host]['id']))

@roles('production')
def uploadServersXml():
  with cd(SFS2X_REMOTE):
      put("%s/update/config/servers.xml" % (PUBLISH_DIR), "%s/resource/servers.xml" % (SFS2X_REMOTE))
      #put("%s/update/config/rmiServer.xml" % (PUBLISH_DIR), "%s/gameconfig/rmiServer%s.xml" % (SFS2X_REMOTE, serverInfo[env.host]['id']))
      put("%s/update/config/rmiClient.xml" % (PUBLISH_DIR), "%s/gameconfig/rmiClient.xml" % (SFS2X_REMOTE))
      serversxml_version=time.time()
      run('redis-cli set property%s_realtime_server_xml_version %s' % (serverInfo[env.host]['id'], serversxml_version))

@roles('production')
def uploadServersXmlOnly():
  with cd(SFS2X_REMOTE):
      put("%s/update/config/servers.xml" % (PUBLISH_DIR), "%s/resource/servers.xml" % (SFS2X_REMOTE))
      serversxml_version=time.time()
      run('redis-cli set property%s_realtime_server_xml_version %s' % (serverInfo[env.host]['id'], serversxml_version))

@roles('production')
def uploadMybatisCross():
  with cd(SFS2X_REMOTE):
      put("%s/update/config/mybatis-cross.xml" % (PUBLISH_DIR), "%s/gameconfig/mybatis-cross.xml" % (SFS2X_REMOTE))
      serversxml_version=time.time()
      run('redis-cli set property%s_realtime_server_xml_version %s' % (serverInfo[env.host]['id'], serversxml_version))

@roles('production')
def uploadServerConfig():
  with cd(SFS2X_REMOTE):
      put("%s/config/server.xml" % (SFS2X_LOCAL), "%s/config/server.xml" % (SFS2X_REMOTE))
      #put("%s/config/core.xml" % (SFS2X_LOCAL), "%s/config/core.xml" % (SFS2X_REMOTE))
      #put("%s/config/log4j.properties" % (SFS2X_LOCAL), "%s/config/log4j.properties" % (SFS2X_REMOTE))

@roles('production')
def uploadScript(files):
  with cd(SFS2X_REMOTE):
      multifiles = files.split('|')
      for xmlfile in multifiles:
        put("%s/scripts/%s" % (SFS2X_LOCAL, xmlfile), "%s/scripts/%s" % (SFS2X_REMOTE, xmlfile))

@roles('phpserver')
def uploadServersXml2PhpServer():
  run("cp /data/htdocs/resource/servers.xml /data/htdocs/resource/servers.xml_%s" % time.time())
  put("%s/update/config/servers.xml" % (PUBLISH_DIR), "/data/htdocs/resource/servers.xml")
  if env.host == '10.60.99.42':
    put("%s/update/config/servers.xml" % (PUBLISH_DIR), "/data/deploy/resource/servers.xml")

@roles('phpserver')
def updateStopServers2PhpServer(sids=''):
  sids = sids.replace('+', ',')
  run("echo '%s' > /data/htdocs/stopped_servers.txt" % sids)
  if env.host == '10.60.99.42':
    run("echo '%s' > /data/deploy/stopped_servers.txt" % sids)

@roles('phpserver')
def uploadDaoliangConfig2PhpServer():
  run("cp /data/htdocs/resource/daoliang_config.json /data/htdocs/resource/daoliang_config.json_%s" % time.time())
  put("%s/update/config/daoliang_config.json" % (PUBLISH_DIR), "/data/htdocs/resource/daoliang_config.json")
  if env.host == '10.60.99.42':
    put("%s/update/config/daoliang_config.json" % (PUBLISH_DIR), "/data/deploy/resource/daoliang_config.json")

@roles('production')
def uploadConfigProperties():
  with cd(SFS2X_REMOTE):
    put("%s/onlineconfig/onlineconfig%s" % (SFS2X_LOCAL, serverInfo[env.host]['id']), "%s/extensions/COK%s/config.properties" % (SFS2X_REMOTE, serverInfo[env.host]['id']))

@roles('production')
def updateRedisProperty(key,val):
  with cd(SFS2X_REMOTE):
    run("redis-cli set property%s_%s '%s'" % (serverInfo[env.host]['id'], key, val))
    run("echo realtime_codis_open=true >> extensions/COK%s/config.properties" % (serverInfo[env.host]['id']))
    #run("sed -i 's/%s=.*/%s=%s/g' extensions/COK%s/config.properties" % (key, key, val, serverInfo[env.host]['id']))

@roles('production')
def getRedisProperty(key):
  with cd(SFS2X_REMOTE):
    run("redis-cli get property%s_%s" % (serverInfo[env.host]['id'], key))

@roles('production')
def getRedisPropertyAppver():
  with cd(SFS2X_REMOTE):
    varnum = run("redis-cli get property%s_realtime_app_1.1.1" % (serverInfo[env.host]['id']))
    if varnum != '"0|1.0.1853"':
      print(red("S%s %s" % (serverInfo[env.host]['id'], varnum)))

def checkCrossRefreshRMI(sid):
  with quiet():
    for shost, sinfo in serverInfo.items():
      rval = local('/usr/local/redis/redis-cli -h %s hget cross_refresh_rmi_flag_%s %s' % (shost, sid, sinfo['id']) , capture=True)
      if rval != 'true':
        print(red("S%s %s" % (sinfo['id'], rval)))
      else:
        print(green("S%s %s" % (sinfo['id'], rval)))

def checkCrossRefreshMybatis(sid):
  with quiet():
    for shost, sinfo in serverInfo.items():
      rval = local('/usr/local/redis/redis-cli -h %s hget cross_refresh_mybatis_flag_%s %s' % (shost, sid, sinfo['id']) , capture=True)
      if rval != 'true':
        print(red("S%s %s" % (sinfo['id'], rval)))
      else:
        print(green("S%s %s" % (sinfo['id'], rval)))

@roles('production')
def changeLoginKey(secret):
  with cd(SFS2X_REMOTE):
    run("echo realtime_web_login_key=%s >> extensions/COK%s/config.properties" % (secret, serverInfo[env.host]['id']))
    run("redis-cli set property%s_realtime_web_login_key '%s'" % (serverInfo[env.host]['id'], secret))

@roles('production')
def addProtect():
  with cd(SFS2X_REMOTE):
    run("echo protect_on_server_start=1 >> extensions/COK%s/config.properties" % (serverInfo[env.host]['id']))

############################# TOOLS #############################
def upload2FTP(version,ftpver,xmlversion):
  with lcd(SFS2X_LOCAL):
    local('sh ./scripts/ftp_upload_file.sh %s %s %s %s' % (SFS2X_LOCAL, version, ftpver, xmlversion))

@roles('production')
def getRealtimeProperties():
    result = run('redis-cli keys property%s_realtime_*' % serverInfo[env.host]['id'])
    if '(empty list or set)' != result:
      items = result.split('\n')
      for item in items:
        run('redis-cli get %s' % item.split(') ')[1])

@roles('production')
def tailLog(Num=5):
  run("tail -%s %s/logs/smartfox.log|awk '{print substr($0,0,200)}'" % (Num, SFS2X_REMOTE))

@roles('production')
def downloadFile(dir, file):
  downdir='/publish/update/onlinefiles/%s' % dir
  if not os.path.exists(downdir):
    local('mkdir -p %s' % downdir)
  get('%s/%s/%s' % (SFS2X_REMOTE, dir, file), '%s/%s%s' % (downdir, file,  serverInfo[env.host]['id']))

@roles('production')
def setRedisServerStatus(status=0,fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
    nowts = int(time.time())
    run('redis-cli set ServerStatus:S%s %s' % (serverInfo[env.host]['id'], status))
    run('redis-cli set ServerStatus:S%s:StopStartTime %s' % (serverInfo[env.host]['id'], nowts))

@roles('production')
def killSfs(fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
    nowts = int(time.time())
    run('redis-cli set ServerStatus:S%s %s' % (serverInfo[env.host]['id'], status))
    run('redis-cli set ServerStatus:S%s:StopStartTime %s' % (serverInfo[env.host]['id'], nowts))
    with settings(warn_only=True):
      pid = run("ps -ef |grep SFS2X| grep -v grep|awk '{print $2}'")
      if len(pid)>0:
        run("kill -9 %s" % pid)

@roles('production')
def getRedisServerStatus():
  with quiet():
    nowts = int(time.time())
    sfs1 = run('redis-cli get ServerStatus:S%s' % (serverInfo[env.host]['id']))
    sfs2 = run('redis-cli get ServerStatus:S%s:StopStartTime' % (serverInfo[env.host]['id']))
    sfs3 = run('redis-cli get ServerStatus:S%s:StopEndTime' % (serverInfo[env.host]['id']))
    sfs1 = int(sfs1.strip('"'))
    sfs2 = int(sfs2.strip('"'))
    sfs3 = int(sfs3.strip('"'))
    if sfs1 == 0:
      print(green("S%s %s pass=%s total=%s" % (serverInfo[env.host]['id'], sfs1, (sfs3-sfs2)/60, (sfs3-sfs2)/60)))
    else:
      print(red("S%s %s pass=%s total=%s" % (serverInfo[env.host]['id'], sfs1, (nowts-sfs2)/60, (sfs3-sfs2)/60)))

@roles('production')
def moniterRedis():
  conn = run('redis-cli info | grep connected_clients')
  print conn

@roles('production')
def checkServerState():
  with settings(warn_only=True):
    state = run('/usr/local/nagios/libexec/check_cok_login.sh')
    if state.failed:
        print "aaa"

@roles('production')
def checkLoginError():
  with settings(warn_only=True):
    run("tac /usr/local/cok/SFS2X/logs/smartfox.log | grep 'loading user'")

@roles('production')
def getRedisCityInfo():
  sfs_status = run('redis-cli -h %s hgetall world_born_%s' % (env.host, serverInfo[env.host]['id']))

@roles('production')
def dumpdb():
  run('mysqldump --opt -d cokdb1 -u root -padmin123 > onlinedb.sql')
  get('onlinedb.sql', 'db_diff/')
  with lcd('$SFS2X'):
    local('mysqldump -u root -padmin123 -d cokdb1 > db_diff/localdb.sql')
  local("svn commit db_diff/ -m 'db diff'")

@roles('production')
def copyRes():
  with cd(SFS2X_REMOTE):
    with settings(warn_only=True):
        run("sh ./copyRes.sh")

@roles('production')
def listClientVersion():
  with cd(SFS2X_REMOTE):
      run('grep app_ extensions/COK%s/config.properties', serverInfo[env.host]['id'])

@roles('cobarserver')
def stopCobar():
  if env.host == '10.142.9.22' or env.host == '10.142.9.26':
    run("cd /usr/local/cobar/cobar-server-1.2.7/ && sh bin/shutdown.sh")
  if env.host == '10.43.212.14' or env.host == '10.43.212.98':
    run("cd /usr/local/cobar-server-1.2.7/ && sh bin/shutdown.sh")

@roles('cobarserver')
def startCobar():
  if env.host == '10.142.9.22' or env.host == '10.142.9.26':
    run("cd /usr/local/cobar/cobar-server-1.2.7/ && sh bin/startup.sh && sleep 10")
  if env.host == '10.43.212.14' or env.host == '10.43.212.98':
    run("cd /usr/local/cobar-server-1.2.7/ && sh bin/startup.sh && sleep 10")


@roles('production')
def deployCCSA(fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
    with cd("/usr/local"):
      run("rm -f ccsa-provider-1.0.tgz")
      run("rm -rf ccsa-provider-1.0")
      put("/cok/new/ccsa-provider-1.0.tgz", "/usr/local/")
      run("tar -xzf ccsa-provider-1.0.tgz")
      run("sed -i 's/dubbo.service.group=.*/dubbo.service.group=s%s/g' ccsa-provider-1.0/conf/dubbo.properties" % (serverInfo[env.host]['id']))
      server_env_path = run("echo $PATH")
      if server_env_path.find("/usr/local/cok/jre/bin") == -1:
        run("echo 'export PATH=$PATH:/usr/local/cok/jre/bin' >> /root/.bash_profile")
        run("source /root/.bash_profile")

@roles('production')
def deployCCSAOnlyJar(fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
    with cd("/usr/local"):
      put("/cok/new/ccsa-provider-1.0/lib/ccsa-provider-1.0.jar", "/usr/local/ccsa-provider-1.0/lib/")
      put("/cok/new/ccsa-provider-1.0/lib/ccsa-api-1.0.jar", "/usr/local/ccsa-provider-1.0/lib/")

@roles('production')
def startCCSA(fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
    with cd("/usr/local"):
      with settings(warn_only=True):
        run("sh ccsa-provider-1.0/bin/start.sh && sleep 5")

@roles('production')
def stopCCSA(fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
    with cd("/usr/local"):
      with settings(warn_only=True):
        pid = run("ps -ef |grep ccsa | grep -v grep|awk '{print $2}'")
        if len(pid)>0:
          run("kill %s" % pid)

@roles('production')
def restartCCSA():
  #put("/cok/new/ccsa-provider-1.0/lib/ccsa-provider-1.0.jar", "/usr/local/ccsa-provider-1.0/lib/")
  with cd("/usr/local"):
    with settings(warn_only=True):
      run("kill `ps -ef |grep ccsa | grep -v grep|awk '{print $2}'` && sleep 10")
      run("sh ccsa-provider-1.0/bin/start.sh && sleep 5")

@roles('production')
def setredisCCSA(fromsid=1,tosid=999999,val=1441209600000):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
      run("redis-cli set GAME_LOG_LAST_MIN_KEY %s" % val)

@roles('production')
def delredisCCSA(fromsid=1,tosid=999999):
  if int(serverInfo[env.host]['id']) >= int(fromsid) and int(serverInfo[env.host]['id']) <= int(tosid):
      run("redis-cli del GAME_LOG_LAST_MIN_KEY")

############################# TEMP #############################
@roles('production')
def addMonitor():
  put('/publish/monitor/check_cok_login.sh', '/usr/local/nagios/libexec/check_cok_login.sh')

@roles('production')
def delRedis():
    result = run('redis-cli keys WORLD_BATTLE_BULL*')
    if '(empty list or set)' != result:
      items = result.split('\n')
      for item in items:
        run('redis-cli del %s' % item.split(') ')[1])
 
def findSlowUser3(uid, name):
  with cd(SFS2X_REMOTE):
    run("tac logs/smartfox.log | sed -n '/player:%s/p;/push.*{%s}/p' | sed 's/push msg.*cmd{\(.*\)}/PUSH:\\1/' | awk -F'|' '{print $2, $7}' | sed 's/{COK1}:\(.*\)/\\1/; s/\(EVENT:.*Handler\).*/\\1/; s/\(CMD:.*\).*/\\1/' | awk -F'--' '{print $2}'" % (uid, name))

userTables = {
    'userprofile': 'uid',
    'playerinfo': 'uid',
    'user_vip': 'uid',
    'user_resource': 'uid',
    'user_lord': 'uid',
    'user_world': 'uid',
    'user_building': 'uid',
    'user_general': 'uid',
    'queue': 'ownerId',
    'user_army': 'uid',
    'user_hospital': 'uid',
    'user_wall': 'uid',
    'user_science': 'uid',
    'user_task': 'uid',
    'user_state': 'uid',
    'mail': 'toUser',
    'mail_group': 'uid',
    'server_mail_record': 'uid',
    'world_favorite': 'uid',
    'chat_shield': 'owner',
    'alliance_member': 'uid',
    'user_item': 'ownerId',
    'exchange': 'uid',
    'exchange_time': 'uid',
    'user_score': 'uid',
    'user_building_exp': 'uid',
    'user_skill': 'ownerId',
}

def exportUserData(uid):
    local('mkdir -p /serverlog/%s' % serverInfo[env.host]['id'])
    for table, key in userTables.items():
        with settings(warn_only=True):
            local("mysqldump -h %s -u root -p%s -t %s %s -w %s='%s' >> /serverlog/%s/%s.data.sql" % (serverInfo[env.host]['db_ip'], serverInfo[env.host]['db_passwd'], serverInfo[env.host]['db_name'], table, key, uid, serverInfo[env.host]['id'], uid))
    local('mysql -u root -padmin123 -f cokdb1 < /serverlog/%s/%s.data.sql' % (serverInfo[env.host]['id'], uid))

@roles('production')
def putBugfixlogToIb2():
  run("find /usr/local/cok/SFS2X/logs/ -name 'smartfox.log*' -mtime -2 |xargs grep MySQLIntegrityConstraintViolationException >> /tmp/bugfix_mail.log%s" % (serverInfo[env.host]['id']))
  run("rsync -z /tmp/bugfix_mail.log%s elex@10.120.168.95::smartfoxlog --password-file=/etc/rsyncsfslog.pass" % (serverInfo[env.host]['id']))

@roles('production')
def putPayBuglogToIb2():
  run("find /usr/local/cok/SFS2X/logs/ -name 'smartfox.log*' -mtime -1 |xargs grep CMD:PayAndroid.*RetObj:cmd >> /tmp/bugfix_payandroid.log%s" % (serverInfo[env.host]['id']))
  run("rsync -z /tmp/bugfix_payandroid.log%s elex@10.120.168.95::bugfixlog --password-file=/etc/rsyncsfslog.pass" % (serverInfo[env.host]['id']))

@roles('s3to10')
def readLogTemp():
  with cd('/usr/local/cok/SFS2X/scripts'):
    run("cd /usr/local/cok/SFS2X/scripts && /usr/local/cok/jre/bin/java ReadLogBugfix >> /tmp/run_ReadLogBugfix.log &")
    #run("sh runReadLogBugfix.sh && sleep 10")

@roles('s131to151')
def selectWorldUsers():
  sql = "select count(uw.uid) as count from user_world uw inner join worldpoint wp on uw.pointId = wp.id where uw.uid = wp.ownerId"
  count = local('mysql -h %s -u root -p%s %s -s -e "%s"' % (serverInfo[env.host]['db_ip'], serverInfo[env.host]['db_passwd'], serverInfo[env.host]['db_name'], sql), capture=True)
  sql = "select count(id) as count from worldpoint where pointType = 1"
  realCount = local('mysql -h %s -u root -p%s %s -s -e "%s"' % (serverInfo[env.host]['db_ip'], serverInfo[env.host]['db_passwd'], serverInfo[env.host]['db_name'], sql), capture=True)
  local('echo %s %s >> /tmp/worldusers.log' % (serverInfo[env.host]['id'], count))

@roles('production')
def findPayIOS():
  with cd('/usr/local/cok/SFS2X'):
    log = run("sed -n '/PayIOS.*\"itemId\":\"9006\".*\"status\":1/p' logs/smartfox.log.2015-02-02*")
    if len(log) > 0:
        local('echo "%s" >> /tmp/payios.log' % log)

@roles('production')
def nowDate():
  run("date")

@roles('production')
def setRedisKeyValue():
  run('redis-cli set property%s_%s %s' % (serverInfo[env.host]['id'], key, val))


@roles('production')
def add_forbidden_words():
  run('redis-cli del forbidden_words_strict')
  run('redis-cli lpush forbidden_words_pattern .*ocean.*knight.*')
  run('redis-cli lpush forbidden_words_pattern .*okay.*goods.*')
  #forbidden_words_strict
  #forbidden_words_regular
  #forbidden_words_pattern

@roles('production')
def statMarchUsers(day):
  with cd(SFS2X_REMOTE):
    log = run("grep 'user:.*world.user.march return world army, date:' logs/smartfox.log.2015-03-%s-* | awk -F'|' '{print $5}' | awk -F',' '{split($2, userid, /:/); users[userid[2]]++}END{for(user in users) if(users[user] > 10) print user}'" % day)
    if len(log) > 0:
        local("mkdir -p /tmp/marchusersfilter1/%s/" % day) 
        local('echo "%s" >> /tmp/marchusersfilter1/%s/s%s' % (log, day, serverInfo[env.host]['id']))

@roles('production')
def armsChangeLog(day):
  with cd(SFS2X_REMOTE):
    log = run("grep 'user:.*world.user.march return world army, date:' logs/smartfox.log.2015-03-%s* | awk -F'|' '{print $2,$5}' | awk -F',' '{split($2, userid, /: /); split($2,datetime,/:/)} {print userid[2],datetime[2]\":\"datetime[3]\":\"datetime[4]}' | awk '{list[$0]++}END{for(data in list) print data}' | awk '{print $2,$3\".*Arms Update : \"$2\".*MARCH_BACK\"}' | xargs -i grep {} logs/smartfox.log.2015-03-%s* | awk -F'|' '{print $2,$5}'" % (day,day))
    if len(log) > 0:
        local("mkdir -p /tmp/marchusersfilter2/%s/" % day) 
        local('echo "%s" >> /tmp/marchusersfilter2/%s/s%s' % (log, day, serverInfo[env.host]['id']))

@roles('phpserver')
def getServerListLog(key, day):
  with settings(warn_only=True):
    run('grep %s /data/log/getserverlist/%s.log >> /tmp/serverlist.log' % (key, day))

@roles('phpserver')
def getPhpLogfile():
  with settings(warn_only=True):
    get('/data/log/getserverlist/ip_device_action.log', '/publish/data/phpserverlog/ip_device_action.log%s' % env.host)
    get('/data/log/getserverlist/invalid_ipban_real.log', '/publish/data/phpserverlog/invalid_ipban_real.log%s' % env.host)
    get('/data/log/getserverlist/invalid_ip.log', '/publish/data/phpserverlog/invalid_ip.log%s' % env.host)

@roles('phpserver')
def getPhpLogAdustCallfile():
  with cd('/data/htdocs/callback/installCallBackLog'):
    run('grep jp call.201508* > call.jp.log')
    get('call.jp.log', '/publish/data/phpserverlog/call.jp.log%s' % env.host)

@roles('production')
def backupLog():
  run('mkdir -p /usr/local/cok/SFS2X/backlog/')
  run('mv /usr/local/cok/SFS2X/logs/smartfox.log.2015-03-10-* /usr/local/cok/SFS2X/backlog/')
  run('mv /usr/local/cok/SFS2X/logs/smartfox.log.2015-03-11-* /usr/local/cok/SFS2X/backlog/')
  run('mv /usr/local/cok/SFS2X/logs/smartfox.log.2015-03-12-* /usr/local/cok/SFS2X/backlog/')

@roles('production')
def delRedisPropertyKey(key):
  run('redis-cli del property%s_%s' % (serverInfo[env.host]['id'], key))

@roles('production')
def collectBuyItemBugLog():
  run("sh /usr/local/cok/SFS2X/scripts/collBuyItem.sh && sleep 10 &")
  run("mv /tmp/buyitembug_all.log /tmp/buyitembug_all.log%s" % (serverInfo[env.host]['id']))
  run("rsync -z /tmp/buyitembug_all.log%s elex@10.120.168.95::smartfoxlog --password-file=/etc/rsyncsfslog.pass" % (serverInfo[env.host]['id']))

@roles('production')
def statAllianceMsg():
  with cd(SFS2X_REMOTE):
    list = ["00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23"]
    for h in list:
      count = run("grep 'CMD | AllianceMessage' logs/smartfox.log.2015-04-27-%s | wc -l" % h)
      local("echo %s:%s >> /tmp/almsgstat/s%s" % (serverInfo[env.host]['id'], count, h))

@roles('production')
def putBadWordsTxt():
    with cd(SFS2X_REMOTE):
        put("%s/gameconfig/badwords.txt" % (SFS2X_LOCAL), "gameconfig/")

@roles('production')
def putExtlib():
    with cd(SFS2X_REMOTE):
        put("%s/extensions/__lib__/c3p0-0.9.5.jar" % (SFS2X_LOCAL), "extensions/__lib__/")
        put("%s/extensions/__lib__/mchange-commons-java-0.2.9.jar" % (SFS2X_LOCAL), "extensions/__lib__/")

@roles('production')
def getcall_server_list_flag():
  with cd(SFS2X_REMOTE):
    print serverInfo[env.host]['id']
    run("redis-cli hlen call_server_list_flag")

@roles('production')
def add_ban_ip(banip):
    run("redis-cli del ban_ip_set")
    #run("redis-cli sadd ban_ip_set %s" % banip)
    #run("redis-cli sadd ban_ip_set '27.158.137.92' '27.158.160.9' '120.38.121.196'")

@roles('s522to545')
def processFolder():
  with cd(SFS2X_REMOTE):
    run('df -h')

@roles('phpserver')
def checkPhpServerHack():
  with settings(hide('warnings'), warn_only=True):
    with cd("/data/htdocs/"):
      #run("ls /data/htdocs/*.zip")
      #run("ls /data/htdocs/ifadmin/*.zip")
      #run("ls /data/htdocs/ifadmin/admin/*.zip")
      #run("ls /data/htdocs/test")
      #run("ls /data/htdocs/test.php")
      run("rm -f probe.phpbak")
      run("rm -f probe.phpV3")
      run("rm -f probe_v2_test.php")
      run("rm -f probeV4.php")
    with cd("/data/htdocs/gameservice/"):
      run("rm -f getserverlist_*")
      run("rm -f getserverlist.php0204")
      run("rm -f getserverlist.php_0213")
      run("rm -f getserverlist.php20150305")
      run("rm -f getserverlist.php76")
      run("rm -f getserverlist.php_bak")
      run("rm -f getserverlist.php_bak2")
      run("rm -f getserverlist.php_bak3")
      run("rm -f getserverlist.phpNew")
      run("rm -f getserverlist.phpV1")
      run("rm -f getserverlist.phpV2")
      run("rm -f getserverlistNewFB.php")
      run("rm -f test_fb_getserverlist.php")
      run("rm -f test_getserverlist.php")

@roles('production')
def checkStatus():
  with settings(hide('warnings', 'running', 'stdout', 'stderr'), warn_only=True):
    pid = run("/usr/local/cok/jre/bin/jps | grep 'Launcher' | awk '{print $2}'")
    if len(pid) <= 0 :
      print("S%s" % serverInfo[env.host]['id'])


@roles('production')
def findCrack():
  with cd(SFS2X_REMOTE):
    ret = run("cat logs/smartfox.log | awk '$0 ~ /\"620700\"/ && $0 !~ /SaveSkillPoint/{print substr($0, 1,300)}'")
    if len(ret) > 0:
      print("server %s ret %s" % (serverInfo[env.host]['id'], ret))


@roles('production')
def updateCoreConfig():
  with cd(SFS2X_REMOTE):
      run("sed -i 's/<maxIncomingRequestSize>10000/<maxIncomingRequestSize>40000/g' config/core.xml")


@roles('production')
def findBindWinbo():
  with cd(SFS2X_REMOTE):
    result = run('sed -n \'/"bindId":"(null)".*success/s/.*BindingAccountHandler | \([0-9]\{1,\}\) |.*/\\1/p\' logs/smartfox.log.2015-08-05-0[3-7]')
    if len(result) > 0:
      items = result.split('\n')
      for item in items:
        local('redis-cli sadd weibo_null_set %s' % item)

@roles('production')
def findHurted():
  with cd(SFS2X_REMOTE):
    result = run('sed -n \'/"optType":2.*"bindId":"(null)"/s/.*"gameUid":\"\([0-9]\{1,\}\)\".*/\\1/p\' logs/smartfox.log.2015-08-05-0[3-7]')
    if len(result) > 0:
      items = result.split('\n')
      for item in items:
        local('redis-cli sadd weibo_null_hurt_set %s' % item)

@roles('production')
def delstdoutlog():
  with settings(warn_only=True):
    run('rm -f /usr/local/ccsa-provider-1.0/logs/stdout.log')


@roles('production')
def uploadLogbackxml():
  with cd(SFS2X_REMOTE):
    put("/tmp/logback.xml", "%s/" % (SFS2X_REMOTE))

@roles('production')
def uploadGeoIP2():
  with cd(SFS2X_REMOTE):
    put("%s/GeoIP2-Country.mmdb" % SFS2X_LOCAL, "/usr/local/cok/SFS2X/")


@roles('production')
def killReadLog():
    with settings(warn_only=True):
      result=run("ps -ef |grep ReadLog | grep -v grep|grep -v tmp|awk '{print $2}'")
      if len(result) > 0:
        items = result.split('\n')
        for item in items:
          run("kill %s" % item)

@roles('production')
def materialBugNum():
  with cd(SFS2X_REMOTE):
    log = run("awk 'BEGIN{FS = \"|\";}$6==\" HavestTool \"{gsub(/ /,\"\",$7);cmdCount[$7]++;}END{for(uid in cmdCount){if (cmdCount[uid] > 15) {print uid,cmdCount[uid];}}}' logs/smartfox.log.2015-08-18-*")
    if len(log) > 0:
        local('echo "%s" >> /tmp/materialBug/s%s' % (log, serverInfo[env.host]['id']), capture=True)

@roles('bugfixservers')
def armyDoubleLog():
  with cd(SFS2X_REMOTE):
    run("awk 'BEGIN {  FS = \"|\"; } $5 ~ \"Arms Update\" {  split($5, info, \" \");  if (info[6] == \"free\" && info[9] == \"SYNCHRONIZE\") {   if (info[7] > 0) {    update[info[4]\",\"info[5]] += info[7];   }  } } END {  for (uid_army in update) {   print uid_army\",\"update[uid_army];  } }' logs/smartfox.log.2015-09-{02-17,02-18,02-19,02-20,02-21,02-22,02-23,03-00,03-01,03-02,03-03,03-04,03-05,03-06,03-07,03-08}* > double_army.log")

@roles('production')
def updateServerPush():
  sql = "update server_push set updateVersion = '1.1.4' where activityId  = '20150811nw114' and platform = 'AppStore'";
  count = local('mysql -h %s -u root -p%s %s -s -e "%s"' % (serverInfo[env.host]['db_ip'], serverInfo[env.host]['db_passwd'], serverInfo[env.host]['db_name'], sql), capture=True)

@roles('bugfixservers')
def uploadArmyAddAwk():
  with cd(SFS2X_REMOTE):
    put("%s/army_add.awk" % (SFS2X_LOCAL), "%s/army_add.awk" % (SFS2X_REMOTE))
    run("awk -f army_add.awk logs/soldier_repair.log > army_add.sql")
    run("rm %s/army_add.awk" % (SFS2X_REMOTE))

@roles('bugfixservers')
def downloadArmyAddSql():
  get('%s/army_add.sql' % (SFS2X_REMOTE), "%s/armyAdd/s%s.sql" % (SFS2X_LOCAL, serverInfo[env.host]['id']))

@roles('production')
def uploadTail300():
    put("%s/taillog300.sh" % (SFS2X_LOCAL), "%s/taillog300.sh" % (SFS2X_REMOTE))

@roles('production')
def modifyboncp():
  with cd(SFS2X_REMOTE):
      run('sed -i "s/maxConnectionsPerPartition=.*/maxConnectionsPerPartition=150/" extensions/COK%s/config.properties' % (serverInfo[env.host]['id']))
      run('sed -i "s/minConnectionsPerPartition=.*/minConnectionsPerPartition=50/" extensions/COK%s/config.properties' % (serverInfo[env.host]['id']))
