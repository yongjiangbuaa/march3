var flashvars = {
	v: "1",
	sig_user: "testuid1",
	plateform_sig_api_key: "xxxxxx",
	sig_time: "123123213",
	flash_vars:"&xxxx",
	sig_locale:"en",
	sig_app_id:"xxxxx",
	sig_api_key:"xxxx",
	sig_api_secret:"xxxxx",
	game_config:"xml/flash.xml",
	sig_server_status:"0",
	sig_auth_key: "xxxxxxx",
	sig_game_client:game_client,
	sig_analytics:"UA-11729502-31",
	sig_producer:"elex",
	mouse:"1"
};
var params = {
	menu: "false",
	allowFullScreen: "true",
	scale: "noscale",
	wmode:"window",
	menu:"false",
	allowScriptAccess:"always"
};
var attributes = {
	id: "game",
	name: "game"
};



var isNS = (navigator.appName == "Netscape") ? 1 : 0;

if(navigator.appName == "Netscape") document.captureEvents(Event.MOUSEDOWN||Event.MOUSEUP);

function mischandler(){
  return false;
}

function mousehandler(e){
	var myevent = (isNS) ? e : event;
	var eventbutton = (isNS) ? myevent.which : myevent.button;
   if((eventbutton==2)||(eventbutton==3)) return false;
}
document.oncontextmenu = mischandler;
document.onmousedown = mousehandler;
document.onmouseup = mousehandler;

function dispatchRightClick(){

if (document.createEvent) {

  var rightClick = document.createEvent('MouseEvents');
  rightClick.initMouseEvent(
    'click', // type
    true,    // canBubble
    true,    // cancelable
    window,  // view - set to the window object
    1,       // detail - # of mouse clicks
    10,       // screenX - the page X coordinate
    10,       // screenY - the page Y coordinate
    10,       // clientX - the window X coordinate
    10,       // clientY - the window Y coordinate
    false,   // ctrlKey
    false,   // altKey
    false,   // shiftKey
    false,   // metaKey
    2,       // button - 1 = left, 2 = right
    null     // relatedTarget
  );
  document.dispatchEvent(rightClick);

} else if (document.createEventObject) { // for IE

  var rightClick = document.createEventObject();
  rightClick.type = 'click';
  rightClick.cancelBubble = true;
  rightClick.detail = 1;
  rightClick.screenX = 10;
  rightClick.screenY = 10;
  rightClick.clientX = 10;
  rightClick.clientY = 10;
  rightClick.ctrlKey = false;
  rightClick.altKey = false;
  rightClick.shiftKey = false;
  rightClick.metaKey = false;
  rightClick.button = 2;
  document.fireEvent('onclick', rightClick);
}
}




var page_width = $(window).width();
swfobject.embedSWF("container/CommonContainer.swf", "game", page_width, "800", "9.0.115", null, flashvars, params, attributes);

function reLoadGame(){
	top.location.reload();
	
}
function thisMovie(movieName) {
	 if (navigator.appName.indexOf("Microsoft") != -1) {
		 return window[movieName];
	 } else {
		 return document[movieName];
	 }
}
function getUserProfile(){
	var result = new Array(userInfo);
	thisMovie("game").sendUserProfile(result);
}
function getFriendsInfo(){
	thisMovie("game").sendFriendsInfo(friendInfo);
}
function postFeed(obj){
	var a = "";
	for(i in obj){
		a += i + ":" + obj[i] + "\n";
	}
	alert(a);
}
function postMessage(obj){
	var a = "";
	for(i in obj){
		a += i + ":" + obj[i] + "\n";
	}
	alert(a);
}
function showInviteFriends(obj){
	var a = "";
	for(i in obj){
		a += i + ":" + obj[i] + "\n";
	}
	alert(a);
	thisMovie("game").showInviteFriendsBack(["testUId1","testuid2"]);
}
function showPayments(uid){
	alert("call showPayments is OK");
}

function setCookie(name,value){
//alert("setCookie");
	try{
	    var Days = 365; //此 cookie 将被保存 30 天
	    var exp  = new Date();    //new Date("December 31, 9998");
	    exp.setTime(exp.getTime() + Days*24*60*60*1000);
	    document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
	    return true;
	}catch(e){
		return false;
	}
};
function getCookie(name){
//alert("getCookie");
    var arr = document.cookie.match(new RegExp("(^| )"+name+"=([^;]*)(;|$)"));
     if(arr != null) return unescape(arr[2]); return null;
};
function deleCookie(name){
//alert("deleCookie");
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval=getCookie(name);
    if(cval!=null) document.cookie= name + "="+cval+";expires="+exp.toGMTString();
};
