$(document).ready(function(){
	var impuestos = {translado:{IVA:16.00,IEPS:26.5},retenciones:{IVA:10.66667,ISR:10.00}};
	var status=$("#formFactura input[name='status']").val();
	jQuery("#gridConceptos").jqGrid({
		datatype: "local",
		height: 250,
		colNames:['Codigo','Cantidad', 'Unidad', 'Descripcion','Unitario','Importe'],
		colModel:[
			{name:'ID',index:'ID', width:60},
			{name:'cantidad',index:'cantidad', width:40},
			{name:'unidad',index:'unidad', width:40},
			{name:'descripcion',index:'descripcion'},
			{name:'valorunitario',index:'valorunitario', width:70, align:"right",sorttype:"float"},
			{name:'importe',index:'importe', width:70,align:"right",sorttype:"float"}
		],
		gridComplete: function(){
			var parseImporte=  $(this).jqGrid('getCol', 'importe', false, 'sum');
			var count = $(this).jqGrid('getGridParam','records');
			$('#subtotal').val(parseImporte.toFixed(2));
			if(count>0 && status=='sin timbrar'){
				$('#ItemTraslado').removeAttr('disabled');
				$('#ItemRetencion').removeAttr('disabled');
			} else {
				$('#ItemTraslado').attr('disabled','disabled');
				$('#ItemRetencion').attr('disabled','disabled');
			}
			var fullData = $(this).jqGrid('getRowData');
			$("#conceptos").val(JSON.stringify(fullData));
			sumTotal();
		},
		ondblClickRow: function(rowid) {
			if(status=='sin timbrar'){
				var data = $(this).getRowData(rowid);
				$('#codigo').val(rowid);
				$('#cantidad').val(data.cantidad);
				$('#unidad').val(data.unidad);
				$('#descripcion').val(data.descripcion);
				$('#unitario').val(data.valorunitario);
				$(this).jqGrid('delRowData',rowid);
			}
		}
	});
	$("#gridConceptos").jqGrid('setGridWidth', $("#fielsetConcept").width(), true);
	$('#addimport').click(function(){
		if(!$('#descripcion').val().length){
			msg('Valores no validos','warning');
			$('#codigo').focus();
			return false;
		}
		if(!$('#codigo').val().length) $('#codigo').val('001');
		if(!$('#cantidad').val().length) $('#cantidad').val('1');
		if(!$('#unidad').val().length) $('#unidad').val('NO APLICA');
		var $importe = parseFloat($('#cantidad').val() * $('#unitario').val());
		var $unitario = parseFloat($('#unitario').val());
		var datarow = {
			ID:$('#codigo').val(),
			cantidad:$('#cantidad').val(),
			unidad:$('#unidad').val(),
			descripcion:$('#descripcion').val(),
			valorunitario:$unitario.toFixed(2),
			importe: $importe.toFixed(2)
		}; 
		var su=jQuery("#gridConceptos").jqGrid('addRowData',$('#codigo').val(),datarow);
		$('#codigo').val('');
		$('#cantidad').val('');
		$('#unidad').val('');
		$('#descripcion').val('');
		$('#unitario').val('');
		$('#codigo').focus();
	});
	$('#ItemTraslado').change(function(){
		if($(this).val()=="") return false;
		var $select = $(this).val();
		var $result = 0;
		var $msg = 'Impuesto eliminado';
		var $id = "translados_"+$select;
		$("#formFactura input[name^='impuestos[translados]']input[name$='[impuesto]']").each(function(){
			if(this.value == $select){
				$('#'+$id).remove();
				$result=1;
				$msg = "El impuesto transladado "+ this.value + ", fue eliminado";
			}
		});
		if($result){
			msg($msg,'warning');
			$(this).val("");
			sumTotal();
			return false;
		}
		var $row = $('#rows input').length;
		var $porcentaje = impuestos.translado[$select] / 100;
		var $importe = parseFloat($porcentaje * $('#subtotal').val());
		if(isNaN($importe))$importe=0;
		
		$('#rows').append(
			"<div id='"+$id+"'><label>Traslado "+$select+
			"</label><input type='number' name='impuestos[translados]["+$row+"][importe]' class='sum' value='"+
			$importe.toFixed(2)+"'/></div>"
		);
		$("#"+$id).append(
		"<input type='hidden' name='impuestos[translados]["+$row+"][impuesto]' value='"+$select+"'/>"
		);
		$("#translados_"+$(this).val()).append(
		"<input type='hidden' name='impuestos[translados]["+$row+"][tasa]' value='"+impuestos.translado[$select]+"'/>"
		);
		sumTotal();
		$(this).val("");
	});
	$('#ItemRetencion').change(function(){
		if($(this).val()=="") return false;
		var $select = $(this).val();
		var $result = 0;
		var $msg = 'Impuesto eliminado';
		var $id = "retenidos_"+$select;
		$("#formFactura input[name^='impuestos[retenidos]']input[name$='[impuesto]']").each(function(){
			if(this.value == $select){
				$('#'+$id).remove();
				$result=1;
				$msg = "El impuesto retenido "+ this.value + ", fue eliminado";
			}
		});
		if($result){
			msg($msg,'warning');
			$(this).val("");
			sumTotal();
			return false;
		}
		var $row = $('#rows input').length;
		var $porcentaje = impuestos.retenciones[$select] / 100;
		var $importe = parseFloat($porcentaje * $('#subtotal').val());
		if(isNaN($importe))$importe=0;
		else $importe = $importe * -1;
		
		$('#rows').append(
		"<div id='"+$id+"'><label>Retencion "+$select+
		"</label><input type='number' name='impuestos[retenidos]["+$row+"][importe]' class='sum' value='"+
		$importe.toFixed(2)+"'/></div>"
		);
		$("#"+$id).append(
		"<input type='hidden' name='impuestos[retenidos]["+$row+"][impuesto]' value='"+$select+"'/>"
		);
		$("#"+$id).append(
		"<input type='hidden' name='impuestos[retenidos]["+$row+"][tasa]' value='"+impuestos.retenciones[$select]+"'/>"
		);
		sumTotal();
		$(this).val("");
	});
});

function sumTotal(){
	var $sum = 0;
    $('.sum').each(function(){
        if(!isNaN(this.value) && this.value.length!=0) {
            $sum += parseFloat(this.value);
        }
    });
    $('#total').val($sum.toFixed(2));
}
$(function(){
	var $min = new Date();
	var $max = new Date($min.getFullYear(),$min.getMonth()+1,0);
	$( "#fecha_expedicion" ).datepicker({inline: true,dateFormat: "yy-mm-dd",maxDate:$max});
});

$(function(){
	$( "#rfc" ).autocomplete({
		source:function(request,response){
			$.post(base_url + 'catalogos/jsonContacSearch',{rfc:$('#rfc').val()},response);
		},
		minLength: 2,
		select: function(event,ui) {
			var $rfc = $("<div/>").html(ui.item.row.rfc).text();
			$(this).val($rfc);
			var $name = $("<div/>").html(ui.item.row.name).text();
			$('#nombre').val($name).text();
			var $d = $.parseJSON(ui.item.row.domicilioFiscal);
			var $dom = $("<div/>").html($d.calle + ' ' + $d.noExterior + ', ' + $d.colonia).text();
			$('#domicilio').val($dom);
			$('#domicilioFiscal').val(ui.item.row.domicilioFiscal);
			return false;
		}
	});
	$( "#dialog-form-email" ).dialog({
		autoOpen: false,
		height: 310,
		width: 350,
		modal: true,
		buttons: {
			'Cerrar': function() {
				$(this).dialog( "close" );
			},
			"Enviar":function() {
				var $form = document.formEmail;
				$($form).submit();
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
});
function back(){
	document.location.href=base_url + "ingresos";
}
function timbrar(){
	loadingScreen();
	$('#formFactura').attr('action',base_url + "ingresos/timbrar");
	$('#formFactura').submit();
}
function cancelar(){
	loadingScreen();
	$('#formSend').attr('action',base_url + "ingresos/cancelar");
	$('#formSend').submit();
}
function topdf(){
	$('#formSend').attr('action',base_url + "ingresos/toPdf");
	$('#formSend').attr('target',"_blank");
	$('#formSend').submit();
	$('#formSend').attr('target',"_self");
}
function toMail(){
	var $form = document.formEmail;
	$('#dialog-form-email').dialog('open');
	$($form).html5form();
}
