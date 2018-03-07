function checkSubmit(){
	var username = $("#uname").attr("value");
	var password = $("#pwd").attr("value");
	
	if(username == "" || password == ""){
		alert("用户名或者密码不能为空!");
		return false;
	}else{
		return true;
	}
}

function changemenu(menu){
var sub_menu = $("#"+menu).css("display");

	if(sub_menu == "none"){
		$("#"+menu).css("display","");
	}else{
		$("#"+menu).css("display","none");
	}

}