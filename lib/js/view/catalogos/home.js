$(document).ready(function(){
	$( "#dialog-form-new" ).dialog({
		autoOpen: false,
		height: 350,
		width: 350,
		modal: true,
		buttons: {
			'Cerrar': function() {
				$(this).dialog( "close" );
			},
			"Guardar":function() {
				$(document.formNew).submit();
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
});
function add(){
	$('#dialog-form-new').dialog('open');
	$(document.formNew).html5form();
}
