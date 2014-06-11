$(document).ready(function(){
	notify = setInterval(showMessageSys, 30000);
	showMessageSys();
});
$(function(){
	$("input[type=submit]").button();
});
//Muestra los mensades de sistema
function showMessageSys(){
	$.ajax({
	 	url: base_url+'home/getmessage',
  		datatype: 'json',
      	success: function(data){
      		for (var i in data){
      			msg(data[i].message,data[i].type);
   			}
      	},
      	error:function(){
         	msg("Sin servicio de notificaci&oacute;n",'error');
         	clearInterval(notify);
      	}   
    });
}
//Despliega las notificaciones
function msg($text,$type){
	if($type==null) $type='alert';
	var n = noty({
		text: $text,
		layout: 'top',
		type: $type,
    	maxVisible: 5,
    	modal: false,
    	timeout: 15000
	});
}
//Despliega las confirmaciones
function confirm($text,$buttons){
	var n = noty({
		text: $text,
		layout: 'bottomCenter',
		type: 'confirm',
    	maxVisible: 5,
    	modal: true,
    	killer: true,
    	dismissQueue: true,
    	buttons: $buttons
	});
}
function loadingScreen(){
	$("body").append(
		"<div id='loadingScreen'><img src='"+base_url+"lib/css/images/ajax-loader.gif'/></div>"
	);
	$("body").css("overflow", "hidden");
}
function closeScreen(){
	$("#loadingScreen").remove();
	$("body").css("overflow", "auto");
	$("body").css("overflow-x", "hidden");
}
