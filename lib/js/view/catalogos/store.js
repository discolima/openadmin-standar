$(document).ready(function(){
	$("#gridProductos").jqGrid({
		url: base_url + 'catalogos/jsonStore',
		editurl: base_url + 'catalogos/saveStore',
		datatype: "json",
		mtype: 'Post',
		height: 280,
		colNames:['Tipo','Codigo','Unidad','Descripcion','Precio','Cantidad'], 
		colModel:[
			{name:'type',index:'type',width:90,sorttype:"string",editable:true,edittype:"select",editoptions:{value:"producto:Producto;servicio:Servicio",defaultValue:''}},
			{name:'code',index:'code',width:80,sorttype:"string",editable:true},
			{name:'unidad',index:'unidad',width:100,sorttype:"string",editable:true},
			{name:'name',index:'name',sorttype:"string",editable:true},
			{name:'precio',index:'precio',width:100,sorttype:"float",editable:true,formatter:'number',formatoptions:{decimalPlaces:2},align:"right"},
			{name:'cantidad',index:'cantidad',width:100,sorttype:"float",editable:true,formatter:'number',formatoptions:{decimalPlaces:2},align:"right"}
		],
		rowList:[10,12,15,20,100000000],
		multiselect: false,
		emptyrecords: "Sin registros",
		pager: '#navbar', 
		sortname: 'code',
		gridComplete: function(){
			$("option[value=100000000]").text('Todos');
        },
        ondblClickRow: function(rowid) {
			edit();
        },
		loadError: function(xhr,status,error){
			msg(error,'warning');
		}
	});
	$("#gridProductos").jqGrid('navGrid',"#navbar",{del:false,add:false,edit:false,search:false,refresh:false});
	$("#gridProductos").jqGrid('setGridWidth', $("#content").width(), true);
});
function back(){
	document.location.href=base_url+"catalogos";
}
function buscar(){
	$('#gridProductos').jqGrid('searchGrid',{closeOnEscape:true,closeAfterSearch:true,
	afterShowSearch:function(form_id){
		var thisForm = form_id.selector.replace('fbox','searchmodfbox');
        var dialogHeight = $(thisForm).height();
        var dialogWidth = $(thisForm).width();
        var windowHeight = $(window).height();
        var windowWidth = $(window).width();
        $(thisForm).css('position','fixed');
        $(thisForm).css('top',(windowHeight-dialogHeight)/2);
        $(thisForm).css('left',(windowWidth-dialogWidth)/2);
	}});
}
function add(){
	$("#gridProductos").jqGrid('editGridRow',"new",{reloadAfterSubmit:true,closeOnEscape:true,closeAfterAdd:true,
	afterShowForm:function(form_id) {
		var thisForm = form_id.selector.replace('FrmGrid_','editmod');
        var dialogHeight = $(thisForm).height();
        var dialogWidth = $(thisForm).width();
        var windowHeight = $(window).height();
        var windowWidth = $(window).width();
        $(thisForm).css('position','fixed');
        $(thisForm).css('top',(windowHeight-dialogHeight)/2);
        $(thisForm).css('left',(windowWidth-dialogWidth)/2);
        $('#type').change(function(){
			if($(this).val()=="servicio"){
				$('#unidad').val('NO APLICA');
				$('#cantidad').val('0.0');
			} else {
				$('#unidad').val('');
				$('#cantidad').val('');
			}
		});
	},beforeSubmit:function(postdata, formid){
		if(!postdata.name.length)
			return [false,"Por favor tecle el nombre del producto",""];
		if(!/^([0-9])*[.]?[0-9]*$/.test(postdata.precio))
			return [false,"El precio solo puede ser un valor numerico",""];
		if (!/^([0-9])*[.]?[0-9]*$/.test(postdata.cantidad))
			return [false,"La cantidad solo puede ser un valor numerico",""];
		return [true];
		
    },afterSubmit:function(response,postdata){
		showMessageSys();
		return [true];
	}});
	$("#code").keyup(function(){
		this.value=this.value.toUpperCase();
	});
	$("#unidad").keyup(function(){
		this.value=this.value.toUpperCase();
	});
	$("#name").keyup(function(){
		this.value=this.value.toUpperCase();
	});
}
function edit(){
	var rowid = $("#gridProductos").jqGrid('getGridParam','selrow');
	if( rowid != null ){
		$("#gridProductos").jqGrid('editGridRow',rowid,{reloadAfterSubmit:true,closeOnEscape:true,closeAfterEdit:true,
		afterShowForm:function(form_id) {
			
			var thisForm = form_id.selector.replace('FrmGrid_','editmod');
			var dialogHeight = $(thisForm).height();
			var dialogWidth = $(thisForm).width();
			var windowHeight = $(window).height();
			var windowWidth = $(window).width();
			$(thisForm).css('position','fixed');
			$(thisForm).css('top',(windowHeight-dialogHeight)/2);
			$(thisForm).css('left',(windowWidth-dialogWidth)/2);
		},beforeSubmit:function(postdata, formid){
			if(!postdata.name.length)
				return [false,"Por favor tecle el nombre del impuesto",""];
			if(!postdata.value.length)
				return [false,"Por favor tecle el porcentaje del impuesto",""];
			if (!/^([0-9])*[.]?[0-9]*$/.test(postdata.value))
				return [false,"Porcentaje solo puede ser un valor numerico",""];
			return [true];
		
		},afterSubmit:function(response,postdata){
			showMessageSys();
			return [true];
		}});
		$("#name").keyup(function(){
			this.value=this.value.toUpperCase();
		});
	} else msg("Selecciona un impuesto","warning");
}
function eliminar(){
	var rowid = $("#gridProductos").jqGrid('getGridParam','selrow');
	if( rowid != null ){
		$("#gridProductos").jqGrid('delGridRow',rowid,{reloadAfterSubmit:true,closeOnEscape:true,closeAfterDelete:true,
		afterShowForm:function(form_id) {
			var thisForm = form_id.selector.replace('DelTbl_','delmod');
			var dialogHeight = $(thisForm).height();
			var dialogWidth = $(thisForm).width();
			var windowHeight = $(window).height();
			var windowWidth = $(window).width();
			$(thisForm).css('position','fixed');
			$(thisForm).css('top',(windowHeight-dialogHeight)/2);
			$(thisForm).css('left',(windowWidth-dialogWidth)/2);
		},afterSubmit:function(response,postdata){
			showMessageSys();
			return [true];
		}});
	} else msg("Selecciona un impuesto","warning");
}
