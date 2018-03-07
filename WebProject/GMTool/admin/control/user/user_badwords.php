<?php
!defined('IN_ADMIN') && exit('Access Denied');
$key = 'BADWORDS';
$type = $_REQUEST ['action'];
$redis = new Redis();
$redis->connect(GLOBAL_REDIS_SERVER_IP,6379);
if($type){
   	$html = "<div style='float:left;width:105%;height:600px;text-align:center;overflow-x:auto;overflow-y:auto;'>";
	$html .= "<table class='listTable' cellspacing=1 padding=0 style='width: 100%; text-align: center'>";
    switch ($type){
        case 'view':
            $num = $redis->sSize($key);
            if($num){
                $html .= '<tr class="listTr"><th>编号</th><th>内容</th><th>修改值</th><th>操作</th></tr>';
                $result = $redis->sMembers($key);
                $no = 0;
                foreach ($result as $value){
                    $no++;
                    $html .= "<tr class='listTr' onMouseOver=this.style.background='#ffff99' onMouseOut=this.style.background='#fff'>".
                    "<td>{$no}</td><td>{$value}</td><td><input type='text' id='value_$no'  original='{$value}' value='{$value}' /></td><td>";
                    $html .= '<input type="button" onclick="domodify('.$no.');" value="修改" /> '.
                    '<input type="button" onclick="dodelete('.$no.');" value="删除" /> '."</td></tr>";
                }
                $html .= '</table></div>';
            }
            else{
               $html = '数据为空！';
            }
            break;
        case 'add':
            $wordstr = $_REQUEST['words'];
            if(trim($wordstr)){
                foreach (explode(',', $wordstr) as $word){
                    $redis->sAdd($key,$word);
                }
                adminLogSystem($adminid,array('add_badwords'=>$wordstr));
                $html = 'ok';
            }
            else{
                $html = '添加屏蔽字不能为空！';
            }
            break;
        case 'delete':
            $oldvalue = $_REQUEST['old_value'];
            $newvalue = $_REQUEST['new_value'];
            $result = $redis->sRemove($key,$oldvalue);
            adminLogSystem($adminid,array('del_badwords'=>$oldvalue));
            if($newvalue){
                $result = $redis->sAdd($key,$newvalue);
                adminLogSystem($adminid,array('add_badwords'=>$newvalue));
            }
            $html = json_encode($result);
            break;
        case 'sync':
            $result = array();
            $arr = $redis->sMembers($key);
            foreach ($servers as $server=>$info){
            	if ($server[0] != 's') {
            		continue;
            	}
                $result[$server][$key] = $page->redis(9, $key,'',$server);
                foreach($arr as $word){
                    $page->redis(11, $key,$word,$server);
                }
            }
            adminLogSystem($adminid,array('sync_badwords'=>time()));
            $html = json_encode($result);
            break;
        default:
            exit('invalid type');
    }
    echo $html;
    exit();
}

include( renderTemplate("{$module}/{$module}_{$action}") );
?>