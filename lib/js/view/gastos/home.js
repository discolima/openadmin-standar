$(document).ready(function(){
	var c = new Object();
	var s = localStorage.getItem("gastos");
	if(s==null) s="{}";
	var c = $.parseJSON(s);
	var $mes=getMes();
	var $anio=getAnio();
	
	if(typeof c.jqgrid != 'undefined'){
		if(c.jqgrid.mes==null) c.jqgrid.mes = getMes();
		else $('#varmes').val(c.jqgrid.mes);
		if(c.jqgrid.anio==null) c.jqgrid.anio= getAnio();
		else $('#varanio').val(c.jqgrid.anio);
		if(c.jqgrid.url==null) c.jqgrid.url = base_url + 'gastos/jsonRows/'+ c.jqgrid.anio;
		if(c.jqgrid.editurl==null) c.jqgrid.editurl = base_url + 'gastos/editRows/'+ c.jqgrid.anio;
		if(c.jqgrid.sortname==null) c.jqgrid.sortname = $('#sortname').val();
		else $('#sortname').val(c.jqgrid.sortname);
		if(c.jqgrid.sortorder==null) c.jqgrid.sortorder = 'asc';
		if(c.jqgrid.page==null) c.jqgrid.page = 1;
		if(c.jqgrid.rowNum==null) c.jqgrid.rowNum=12;
		else c.jqgrid.rowNum = Number(c.jqgrid.rowNum);
		if(c.jqgrid.postData==null) c.jqgrid.postData='';
		else{ 
			$('#catid').val(c.jqgrid.postData.catid);
			c.jqgrid.postData.mes=c.jqgrid.mes;
		}
	} else {
		c = {
		jqgrid:{
			mes:$mes,
			anio:$anio,
			url:base_url + 'gastos/jsonRows/'+ $anio,
			editurl:base_url + 'gastos/editRows/'+ $anio,
			sortname:$('#sortname').val(),
			postData:{mes:$mes},
			page:1,
			rowNum:12
			}
		}
	}
	$("#gridGastos").jqGrid({
		url: c.jqgrid.url,
		editurl: c.jqgrid.editurl,
		postData:c.jqgrid.postData,
		datatype: "json",
		mtype: 'Post',
		height: 280,
		colNames:['UUID','Razon social','Fecha','Forma de pago','SubTotal','Impuestos','Total'], 
		colModel:[ 
			{name:'uuid',index:'uuid',width:125,search:true},
			{name:'nombre',index:'nombre',search:true},
			{name:'fecha',index:'fecha', width:80,sorttype:"date",search:false},
			{name:'metodoDePago',index:'metodoDePago',width:100,search:false},
			{name:'subtotal',index:'subtotal',width:40,formatter:'number',formatoptions:{decimalPlaces:2},align:"right",search:false},
			{name:'impuestos',index:'impuestos',width:40,formatter:'number',formatoptions:{decimalPlaces:2},align:"right",search:false},
			{name:'total',index:'total',width:40,formatter:'number',formatoptions:{decimalPlaces:2},align:"right",search:false},
		],
		rowNum:c.jqgrid.rowNum,
		page:c.jqgrid.page,
		rowList:[10,12,15,20,100000000],
		multiselect: false,
		emptyrecords: "Sin registros",
		pager: '#navbar', 
		sortname: c.jqgrid.sortname,
		sortorder:c.jqgrid.sortorder,
		footerrow: true,
		userDataOnFooter: true,
		gridComplete: function(){
			var $mes=getMes();
			var $anio=getAnio();
			var parseTotal=  $(this).jqGrid('getCol', 'total', false, 'sum');
			var parseImp=  $(this).jqGrid('getCol', 'impuestos', false, 'sum');
			var parseSub=  $(this).jqGrid('getCol', 'subtotal', false, 'sum');
			//Totales al pie
            $(this).jqGrid('footerData', 'set', {metodoDePago:'TOTAL',subtotal:parseSub,impuestos:parseImp,total:parseTotal});
            $("option[value=100000000]").text('Todos');
            $(this).setGridParam({ url:base_url +'gastos/jsonRows/'+ $anio});
            $(this).setGridParam({ editurl:base_url + 'gastos/editRows/'+ $anio});
            //Almacenamos parametros
            var gridInfo = new Object();
			gridInfo = {
			 jqgrid:{
				 url:$(this).jqGrid('getGridParam', 'url'),
				 sortname:$(this).jqGrid('getGridParam', 'sortname'),
				 sortorder:$(this).jqGrid('getGridParam', 'sortorder'),
				 page:$(this).jqGrid('getGridParam', 'page'),
				 rowNum:$(this).jqGrid('getGridParam', 'rowNum'),
				 editurl:$(this).jqGrid('getGridParam', 'editurl'),
				 postData:$(this).jqGrid('getGridParam', 'postData'),
				 mes:$mes,
				 anio:$anio
			 }
			}
			localStorage.setItem("gastos",JSON.stringify(gridInfo));
        },
        ondblClickRow: function(rowid) {
			var data = $(this).getRowData(rowid);
			var form = document.formRow;
			form.id.value = data.uuid;
			form.fecha.value = data.fecha;
			form.submit();
        }
	});
	$("#gridGastos").jqGrid('navGrid',"#navbar",{del:false,add:false,edit:false,search:false,refresh:false});
	$("#gridGastos").jqGrid('setGridWidth', $("#content").width(), true);
	
	$("#varmes").change(function(){
		var $mes=getMes();
		$("#gridGastos").jqGrid("clearGridData", true);
		$('#gridGastos').setGridParam({postData:{mes:$mes}});
        $('#gridGastos').trigger('reloadGrid');
        $("#textarea").html("");
	});
	$("#varanio").change(function(){
		var $anio=getAnio();
		$("#gridGastos").jqGrid("clearGridData", true);
		$('#gridGastos').setGridParam({url:base_url +'gastos/jsonRows/'+ $anio});
        $('#gridGastos').trigger('reloadGrid');
        $("#textarea").html("");
	});
	$("#filexml").change(function(){
		var iframe = $('<iframe name="postframe" id="postiframe" style="display:none" />');
		$("body").append(iframe);
        $(this.form).attr("enctype", "multipart/form-data");
        $(this.form).attr("encoding", "multipart/form-data");
        $(this.form).attr("target", "postframe");
        $(this.form).attr("file", $(this).val());
        $(this.form).submit();
        //need to get contents of the iframe
        $("#postiframe").load(function(){
            if(typeof $("#postiframe")[0].contentWindow.document.formxml != 'undefined'){
				$anio = $("#postiframe")[0].contentWindow.document.formxml.anio.value;
				$mes = $("#postiframe")[0].contentWindow.document.formxml.mes.value;
				$("#gridGastos").jqGrid("clearGridData", true);
				$('#varmes').val($mes);
				$('#varanio').val($anio);
				$('#gridGastos').setGridParam({ url:base_url +'gastos/jsonRows/'+ $anio});
				$('#gridGastos').trigger('reloadGrid');
			} else {
				showMessageSys();
			}
        });
        $(this).val("");
	});
	$('#sortname').change(function(){
		var $sortname = $(this).val();
		$('#gridGastos').jqGrid('setGridParam',{sortname:$sortname,sortorder:'asc'});
		$("#gridGastos").jqGrid("clearGridData", true);
		$('#gridGastos').trigger('reloadGrid');
	});
	$('#catid').change(function(){
		var $catid = $(this).val();
		if($catid!=null)
			$('#gridGastos').jqGrid('setGridParam',{postData:{catid:$catid}});
		else
			$('#gridGastos').jqGrid('setGridParam',{postData:{}});
			
		$("#gridGastos").jqGrid("clearGridData", true);
		$('#gridGastos').trigger('reloadGrid');
	});
	$( "#dialog-form-xml" ).dialog({
		autoOpen: false,
		height: 350,
		width: 350,
		modal: true,
		buttons: {
			"Cerrar": function() {
				$("#respuesta").html('');
				$("#respuesta").css('visibility','hidden');
				$(this).dialog( "close" );
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
	$( "#dialog-form-email" ).dialog({
		autoOpen: false,
		height: 300,
		width: 350,
		modal: true,
		buttons: {
			Cancel: function() {
				var $form = document.formMail;
				$("#respuesta2").html('');
				$("#respuesta2").css('visibility','hidden');
				$form.email.value = '';
				$form.mensaje.value = '';
				$(this).dialog( "close" );
			},
			"Enviar":function() {
				var $form = document.formMail;
				$($form).attr("action",base_url + 'gastos/sendToemail');
				$($form).submit();
				$($form).attr("action","");
			}
		},
		close: function() {
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
	$("#formMail").submit(function(){
		var $url = $("#formMail").attr("action");
		
		$("#respuesta2").html('');
		if($("#email").val() == ''){
			$("#respuesta2").html('Tecle el email a enviar');
			$("#respuesta2").css('visibility','visible');
		}else if(validar_email($("#email").val())){
			var $form = document.formMail;
			var $postData = $($form).serializeArray();
			$("#respuesta2").removeClass("ui-state-error").addClass("ui-state-highlight");
			$("#respuesta2").html('Enviando...');
			$("#respuesta2").css('visibility','visible');
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
					$("#respuesta2").removeClass("ui-state-highlight").addClass("ui-state-error");
					$("#dialog-form-email").dialog("close");
				},
				error:function(e){
					msg("Error de comunicaci&oacute;n con el servidor. Estado: "+e['readyState']+", Estatus:"+e['status'],"error");
					$("#dialog-form-email").dialog("close");
				} 
			});
		} else { 
			$("#respuesta2").html('El email es invalido');
			$("#respuesta2").css('visibility','visible');
		}
		
		return false;
	});
});
function getMes(){
	var str = "";
	$("#varmes option:selected").each(function(){
		str += $( this ).text();
	});
	return str;cell
}
function getAnio(){
	var str = "";
	$("#varanio option:selected").each(function(){
		str += $( this ).text();
	});
	return str;
}
function getCatid(){
	var str = "";
	$("#selectCat option:selected").each(function(){
		str += $( this ).text();
	});
	return str;
}
function searchRow(){
	$('#gridGastos').jqGrid('searchGrid',{});
}
function delRows(){
	var gr = $("#gridGastos").jqGrid('getGridParam','selrow'); 
	if( gr != null ){
		$("#gridGastos").jqGrid('delGridRow',gr,{
			afterSubmit: function(response, postdata) {
				showMessageSys();
				return [true," message"];
			}
		});
		$("#gridGastos").jqGrid("clearGridData", true);
        $('#gridGastos').trigger('reloadGrid');
	}
	else msg("Selecciona un gasto a eliminar","warning");
}
function seemore(){
	var rowid = $("#gridGastos").jqGrid('getGridParam','selrow');
	if( rowid != null ){
		var data = $('#gridGastos').getRowData(rowid);
		var form = document.formRow;
		form.id.value = data.uuid;
		form.fecha.value = data.fecha;
		form.submit();
	}
	else msg("Selecciona un gasto para ver mas detalles","warning");
}
function addXml(){
	$('#dialog-form-xml').dialog('open');
	$('#formNew').html5form();
}
function toReport(){
	var $form = document.formRow;
	var $mes = getMes();
	var $anio = getAnio();
	
	$form.fecha.value=$anio+"-"+$mes+"-01";
	$form.id.value='reporte_'+$mes+$anio;
	$($form).attr("action",base_url + 'gastos/reportMes');
	$($form).attr("target","_blank");
    $($form).submit();
    $($form).attr("target","");
    $($form).attr("action","");
}
function toMail(){
	$('#dialog-form-email').dialog('open');
	$('#formMail').html5form();
	$(document.formMail.mes).val(getMes());
	$(document.formMail.anio).val(getAnio());
}
function validar_email(valor){
	var filter = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
	if(filter.test(valor))
		return true;
	else
		return false;
}
