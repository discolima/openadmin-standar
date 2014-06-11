$(document).ready(function(){
	$(document.formNew).html5form();
	$( "#dialog-confirm" ).dialog({
		autoOpen: false,
		resizable: false,
		modal: true,
		buttons: {
			"Eliminar": function() {
				var form = document.formSend;
				$(form).attr('action',base_url + "catalogos/saveContacto");
				$(form).submit();
			},
			"Cancelar": function() {
				$( this ).dialog( "close" );
			}
		}
	});
});
function back(){
	document.location.href=base_url+"catalogos/showContacs";
}
function eliminar(){
	$('#dialog-confirm').dialog('open');
}
