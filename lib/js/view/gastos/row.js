$(document).ready(function(){
	$('#selectCat').change(function(){
		var $form = document.formDel;
		var $mes = $form.mes.value;
		var $anio = $form.anio.value;
		var $catid = $(this).val();
		
		var $parametros = {"id":$form.id.value,"oper":'edit',"catid":$catid};
		$.ajax({
			url: base_url + 'gastos/editRows/'+ $anio,
			data: $parametros,
			type: "post",
			datatype: 'json',
			success: function(data){
				showMessageSys();
			},
			error:function(e){
				msg("Error de comunicaci&oacute;n con el servidor. Estado: "+e['readyState']+", Estatus:"+e['status'],"error");
			} 
		});
	});
	$( "#dialog-form" ).dialog({
		autoOpen: false,
		height: 300,
		width: 350,
		modal: true,
		buttons: {
			"Enviar":function() {
				var $form = document.formDel;
				$($form).attr("action",base_url + 'gastos/sendToemail');
				$($form).submit();
				$($form).attr("action","");
			},
			Cancel: function() {
				var $form = document.formDel;
				$("#respuesta").html('');
				$("#respuesta").css('visibility','hidden');
				$form.email.value = '';
				$form.mensaje.value = '';
				$(this).dialog( "close" );
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
	$("#formDel").submit(function(){
		var $url = $("#formDel").attr("action");
		var $pdf = base_url + 'gastos/toPdf';
		if($url==$pdf) return true;
		
		$("#respuesta").html('');
		if($("#email").val() == ''){
			$("#respuesta").html('Tecle el email a enviar');
			$("#respuesta").css('visibility','visible');
		}else if(validar_email($("#email").val())){
			var $form = document.formDel;
			var $postData = $($form).serializeArray();
			$("#respuesta").removeClass("ui-state-error").addClass("ui-state-highlight");
			$("#respuesta").html('Enviando...');
			$("#respuesta").css('visibility','visible');
			$.ajax({
				url: $url,
				type: "POST",
				data: $postData,
				datatype: 'json',
				success: function(r){
					if(!$.isEmptyObject(r)){
						if(typeof r.data != 'undefined'){
							$("#respuesta").html('');
							$("#respuesta").css('visibility','hidden');
							$form.email.value = '';
							$form.mensaje.value = '';
							msg(r.data,'success');
						} else 
							msg(r.error,'error');
					} else
						msg('Se perdio la conexion con el servidor de email','eror');
					$("#respuesta").removeClass("ui-state-highlight").addClass("ui-state-error");
					$("#dialog-form").dialog("close");
					
				},
				error:function(e){
					msg("Error de comunicaci&oacute;n con el servidor. Estado: "+e['readyState']+", Estatus:"+e['status'],"error");
					$("#dialog-form").dialog("close");
				} 
			});
		}else {
			$("#respuesta").html('El email es invalido');
			$("#respuesta2").css('visibility','visible');
		}
		
		return false;
	});
	$( "#dialog-form-new" ).dialog({
		autoOpen: false,
		height: 410,
		width: 350,
		modal: true,
		buttons: {
			'Cerrar': function() {
				$("#formNew-res").html('');
				$("#formNew-res").css('visibility','hidden');
				$(this).dialog( "close" );
			},
			"Enviar":function() {
				var $form = document.formNew;
				$($form).submit();
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
});
function toPDF(){
	var $form = document.formDel;
	$($form).attr("action",base_url + 'gastos/toPdf');
	$($form).attr("target","_blank");
    $($form).submit();
    $($form).attr("target","");
    $($form).attr("action","");
  
}
function toMail(){
	$('#dialog-form').dialog('open');
	$('#formDel').html5form();
}
function validar_email(valor){
	var filter = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
	if(filter.test(valor))
		return true;
	else
		return false;
}
function addReg(){
	$('#dialog-form-new').dialog('open');
	$('#formNew').html5form();
}
function delRow(){
	var $form = document.formDel;
	var $mes = $form.mes.value;
	var $anio = $form.anio.value;
	var $parametros = {"id":$form.id.value,"oper":'del'};
	confirm('Eliminar el folio '+$form.id.value,
	[{addClass:'btn btn-primary',text:'Confirmar',onClick:function($noty){
			$.ajax({
				url: base_url + 'gastos/editRows/'+ $mes + '/'+ $anio,
				data: $parametros,
				type: "post",
				datatype: 'json',
				success: function(data){
					window.location.href=base_url + 'gastos';
				},
				error:function(e){
					msg("Error de comunicaci&oacute;n con el servidor. Estado: "+e['readyState']+", Estatus:"+e['status'],"error");
				} 
			});
			$noty.close();
	}},
    {addClass:'btn btn-danger',text:'Cancelar',onClick:function($noty){
			$noty.close();
	}}]);
}
