$(document).ready(function(){
	var c = new Object();
	var s = localStorage.getItem("ingresos");
	if(s==null) s="{}";
	var c = $.parseJSON(s);
	var $mes=getMes();
	var $anio=getAnio();
	
	if(typeof c.jqgrid != 'undefined'){
		if(c.jqgrid.mes==null) c.jqgrid.mes = getMes();
		else $('#varmes').val(c.jqgrid.mes);
		if(c.jqgrid.anio==null) c.jqgrid.anio= getAnio();
		else $('#varanio').val(c.jqgrid.anio);
		if(c.jqgrid.url==null) c.jqgrid.url = base_url + 'ingresos/jsonRows/'+ c.jqgrid.anio;
		if(c.jqgrid.editurl==null) c.jqgrid.editurl = base_url + 'ingresos/editRows/'+ c.jqgrid.anio;
		if(c.jqgrid.sortname==null) c.jqgrid.sortname = $('#sortname').val();
		else $('#sortname').val(c.jqgrid.sortname);
		if(c.jqgrid.sortorder==null) c.jqgrid.sortorder = 'asc';
		if(c.jqgrid.page==null) c.jqgrid.page = 1;
		if(c.jqgrid.rowNum==null) c.jqgrid.rowNum=12;
		else c.jqgrid.rowNum = Number(c.jqgrid.rowNum);
		if(c.jqgrid.postData==null) c.jqgrid.postData='';
		else{ 
			c.jqgrid.postData.mes=c.jqgrid.mes;
		}
	} else {
		c = {
		jqgrid:{
			mes:$mes,
			anio:$anio,
			url:base_url + 'ingresos/jsonRows/'+ $anio,
			editurl:base_url + 'ingresos/editRows/'+ $anio,
			sortname:$('#sortname').val(),
			postData:{mes:$mes},
			page:1,
			rowNum:12
			}
		}
	}
	
	$("#gridIngresos").jqGrid({
		url: c.jqgrid.url,
		editurl: c.jqgrid.editurl,
		postData:c.jqgrid.postData,
		datatype: "json",
		mtype: 'Post',
		height: 280,
		colNames:['SF','Estado','Razon social','Fecha','Subtotal','Impuestos','Total'], 
		colModel:[ 
			{name:'id',index:'id',width:30, sorttype:"string"},
			{name:'status',index:'status',width:40, sorttype:"string"},
			{name:'nombre',index:'nombre', sorttype:"string"},
			{name:'fecha',index:'fecha',width:50,align:"center",sorttype:"date",formatter:"date",formatoptions:{newformat:"Y-m-d"}},
			{name:'subtotal',index:'subtotal',width:40,formatter:'number',formatoptions:{decimalPlaces:2},align:"right"},
			{name:'impuestos',index:'impuestos',width:40,formatter:'number',formatoptions:{decimalPlaces:2},align:"right"},
			{name:'total',index:'total',width:40,formatter:'number',formatoptions:{decimalPlaces:2},align:"right"}
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
			var parseSubtotal=  $(this).jqGrid('getCol', 'subtotal', false, 'sum');
			var parseImpuestos=  $(this).jqGrid('getCol', 'impuestos', false, 'sum');
			var parseTotal=  $(this).jqGrid('getCol', 'total', false, 'sum');
            $(this).jqGrid('footerData', 'set', {fecha:'TOTAL',subtotal:parseSubtotal,impuestos:parseImpuestos,total:parseTotal});
            $("option[value=100000000]").text('Todos');
            $(this).setGridParam({ url:base_url +'ingresos/jsonRows/'+ $anio});
            $(this).setGridParam({ editurl:base_url + 'ingresos/editRows/'+ $anio});
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
			localStorage.setItem("ingresos",JSON.stringify(gridInfo));
        },
        ondblClickRow: function(rowid) {
			var form = document.sendto;
			$(form).attr('action', base_url + "ingresos/formIngreso");
			form.sf.value = rowid;
			form.submit();
        }
	});
	$("#gridIngresos").jqGrid('navGrid',"#navbar",{del:false,add:false,edit:false,search:false,refresh:false});
	$("#gridIngresos").jqGrid('setGridWidth', $("#content").width(), true);
	
	$("#varmes").change(function(){
		var $mes=getMes();
		$("#gridIngresos").jqGrid("clearGridData", true);
		$('#gridIngresos').setGridParam({postData:{mes:$mes}});
        $('#gridIngresos').trigger('reloadGrid');
	});
	$("#varanio").change(function(){
		var $anio=getAnio();
		$("#gridIngresos").jqGrid("clearGridData", true);
		$('#gridIngresos').setGridParam({url:base_url +'ingresos/jsonRows/'+ $anio});
        $('#gridIngresos').trigger('reloadGrid');
	});
	$('#sortname').change(function(){
		var $sortname = $(this).val();
		$('#gridIngresos').jqGrid('setGridParam',{sortname:$sortname,sortorder:'asc'});
		$("#gridIngresos").jqGrid("clearGridData", true);
		$('#gridIngresos').trigger('reloadGrid');
	});
});
function getMes(){
	var str = "";
	$("#varmes option:selected").each(function(){
		str += $( this ).text();
	});
	return str;
}
function getAnio(){
	var str = "";
	$("#varanio option:selected").each(function(){
		str += $( this ).text();
	});
	return str;
}
function add(){
	document.location.href= base_url + "ingresos/formIngreso";
}
function buscar(){
	$('#gridIngresos').jqGrid('searchGrid',{});
}
function edit(){
	var rowid = $("#gridIngresos").jqGrid('getGridParam','selrow');
	if( rowid != null ){
		var form = document.sendto;
		$(form).attr('action', base_url + "ingresos/formIngreso");
		form.sf.value = rowid;
		form.submit();
	} else msg("Selecciona una factura","warning");
}
function eliminar(){
	var rowid = $("#gridIngresos").jqGrid('getGridParam','selrow');
	if( rowid != null ){
		var data = $('#gridIngresos').getRowData(rowid);
		if(data.status=='sin timbrar'){
			$("#gridIngresos").jqGrid('delGridRow',rowid,{
				afterSubmit: function(response, postdata) {
					showMessageSys();
					return [true," message"];
				}
			});
			$("#gridIngresos").jqGrid("clearGridData", true);
			$('#gridIngresos').trigger('reloadGrid');
		} else msg("Esta factura no se puede eliminar","warning");
	} else msg("Selecciona una factura","warning");
}
