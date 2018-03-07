<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>XingCloud API</title>
<link rel="stylesheet" href="main.css" type="text/css" title='main' media="screen" />
<script language="JavaScript" type="text/javascript" src="../js/jquery-1.4.4.min.js"></script>
<script language="JavaScript" type="text/javascript" src="../js/json2.js"></script>
<script language="JavaScript" type="text/javascript" src="../js/jquery.idTabs.min.js"></script>
<script language="JavaScript" type="text/javascript">
</script>
</head>
<body scroll="no">
<div><span>Server: http://<input type="text" id="address" name="address" value="<?php echo $_SERVER['HTTP_HOST']?><?php echo preg_replace('/\/test\/api(\/api\.php)*$/i','',$_SERVER['PHP_SELF'])?>" style="width:190px;" onchange="showAPI();">
</span>
<span>API: <input type="text" id="api_name" name="api_name" value="<?php echo $_GET['api']?>" style="width:190px;" onchange="showAPI();">
</span></div>
<div id="usual1" class="usual">
  <ul> 
    <li><a href="#tab1">AMF</a></li> 
    <li><a href="#tab2" class="selected">REST</a></li> 
    <li><a href="#tab3">JSON RPC</a></li> 
  </ul>
<div id="tab1" style="display: block; "><iframe src="/test/amf/index.html" id="iframe_amf" height="100%" width="100%"></iframe></div> 
<div id="tab2" style="display: none; "><iframe src="/test/rest/index.php?api=<?php echo $_GET['api']?>" id="iframe_rest" height="100%" width="100%"></iframe></div> 
<div id="tab3" style="display: none; "><iframe src="/test/jsonrpc/index.php?api=<?php echo $_GET['api']?>" id="iframe_json_rpc" height="100%" width="100%"></iframe></div> 
</div> 
<script type="text/javascript"> 
  $("#usual1 ul").idTabs();
</script>
</body>
</html>