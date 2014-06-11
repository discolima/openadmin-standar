$(document).ready(function(){
	$(document.formAutho).submit(function(){
		if($('#user').val().length<4){
			msg('Ingreso un nombre de usuario valido','warning');
			return false;
		}
		if($('#pass').val().length<4){
			msg('Ingreso un password valido','warning');
			return false;
		}
		$('#pass').val($.md5($('#pass').val()));
	});
});
