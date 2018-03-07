<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>XingCloud UnitTest</title>
<script language="JavaScript" type="text/javascript" src="../js/jquery-1.4.4.min.js"></script>
<script language="JavaScript" type="text/javascript" src="../js/json2.js"></script>
<script type="text/javascript">
window.onload=list_testcases;

function list_testcases(){
	var e = $('#testMode');
	var type = e[0].value;
	var protocol = getProtocol();
	
	$.ajax({
		  url: 'cmd.php',
		  type: 'post',
		  data: 'param='+type+'&type=listTestCase&protocol='+protocol,
		  success: function(data) {
			  var results = data;
			  var length = results.length;
			  var str = '';
			  var e = $('#testcaseList');
			  e.html(results);
		  }
	});
	
}

function checkAll()
{
	var e=document.getElementsByTagName( "input"); 
	for (i = 0; i < e.length; i++) {
		e[i].checked = true;
	}
}

function checkSubBox(dir)
{
	var e=document.getElementsByName( dir); 
	var status = true;
	if(e[0].checked==false){
		status = false;
	}
	for (i = 0; i < e.length; i++) {
		e[i].checked = status;
	}
}

function uncheckAll()
{
	var e=document.getElementsByTagName( "input"); 
	for (i = 0; i < e.length; i++) {
		if (e[i].checked==true) {
			e[i].checked = false;
		}
		else{
			//e[i].checked=true;
		}
	}
	$('#testcase_list').val('');
}

function changeProtocolDiv(){
	var e = $('#testMode');
	var type = e[0].value;
	var e = document.getElementById("protocolDiv");
    if(type=='APITest'){
    	e.style.display = "";
    }else{
    	e.style.display = "none";
    }
    list_testcases();
}

function getTestType(){
	var e = $('#testMode');
	var type = e[0].value;
	return type;
}

function getProtocol(){
	var e = $('#protocol');
	var protocol = e[0].value;
	return protocol;
}

function startTest(){
	var e=document.getElementsByTagName( "input"); 
	var testType=getTestType();
	var protocol=getProtocol();
	var testcase='';
	for (i = 0; i < e.length; i++) {
		if (e[i].checked==true && e[i].value!="" &&e[i].type!="button") {
			if(testcase!=""){
				testcase += ',';
			}
			testcase += e[i].value;
		}
	}
	
	$.ajax({
		  url: 'cmd.php',
		  type: 'post',
		  data: 'param='+testcase+'&type='+testType+'&protocol='+protocol,
		  success: function(data) {
			  var results = data;
			  var length = results.length;
			  $('#test_result').val(results);
		  }
	});
}

</script>
</head>
<body scroll="no">
<h3>XingCloud PHP SDK Unit Test</h3>
<span id='testType' style="display: none;">unitTest</span>
<table width=850px>
	<tbody>
		<tr>
			<td width="350px" valign="top">
				<table>
					<tbody>
						<tr>
							<td>
								Test Mode  &nbsp&nbsp
								<select id="testMode" onchange="changeProtocolDiv();">
									<option value="unitTest">Unit Test</option>
									<option value="APITest">API Test</option>
									<option value="deploy">Deploy Test</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<div id="protocolDiv" style="display: none;" onchange="list_testcases();">
								Protocol &nbsp&nbsp
								<select id="protocol">
									<option value="rest">REST</option>
									<option value="jsonrpc">JSONRPC</option>
								</select>
								<br/><br/>
								</div>
							</td>
						</tr>
						<tr >
							<td>
								Testcase List
								<form class="path_form" action="action.php" method="post">
									<div id="testcaseList" width="350px">
									</div>
									
									<p/>
									<input type="button" value="Check All" onclick="checkAll();"></input>
									<input type="button" value="Uncheck All" onclick="uncheckAll();"></input>
									<input type="button" value="Run Test" onclick="startTest();"></input>
								</form>
								
							</td>
						</tr>
						<!-- 
						<tr >
							<td>
								TestCase List:
								<textarea id="testcase_list" name="testcase_list" rows="25" cols="50" style="overflow-x: scroll;"></textarea>
							</td>
							
						</tr>
						-->
					</tbody>
				</table>
				
			</td>
			<td width="500px" valign="top">
				<table>
					<tr>
						<td>
							Test Result：
							<textarea id="test_result" name="test_result" rows="25" cols="70" style="overflow-x: scroll;"></textarea>
						</td>
					</tr>
				</table>
				
			</td>
		</tr>
	</tbody>
</table>
</body>
</html>

