$(document).ready(function(){
	$("#gridImpuestos").jqGrid({
		url: base_url + 'catalogos/jsonImpuestos',
		editurl: base_url + 'catalogos/saveImpuestos/',
		datatype: "json",
		mtype: 'Post',
		height: 280,
		colNames:['Tipo','Nombre','Porcentaje'], 
		colModel:[ 
			{name:'type',index:'type',width:100,sorttype:"string",editable:true,edittype:"select",editoptions:{value:"translado:Translado;retenciones:Retenciones",defaultValue:''}},
			{name:'name',index:'name',sorttype:"string",editable:true},
			{name:'value',index:'value',width:100,sorttype:"float",editable:true,formatter:'number',formatoptions:{decimalPlaces:5},align:"right"}
		],
		rowList:[10,12,15,20,100000000],
		multiselect: false,
		emptyrecords: "Sin registros",
		pager: '#navbar', 
		sortname: 'type',
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
	$("#gridImpuestos").jqGrid('navGrid',"#navbar",{del:false,add:false,edit:false,search:false,refresh:false});
	$("#gridImpuestos").jqGrid('setGridWidth', $("#content").width(), true);
});
function back(){
	document.location.href=base_url+"catalogos";
}
function buscar(){
	$('#gridImpuestos').jqGrid('searchGrid',{closeOnEscape:true,closeAfterSearch:true,
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
	$("#gridImpuestos").jqGrid('editGridRow',"new",{reloadAfterSubmit:true,closeOnEscape:true,closeAfterAdd:true,
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
}
function edit(){
	var rowid = $("#gridImpuestos").jqGrid('getGridParam','selrow');
	if( rowid != null ){
		$("#gridImpuestos").jqGrid('editGridRow',rowid,{reloadAfterSubmit:true,closeOnEscape:true,closeAfterEdit:true,
		afterShowForm:function(form_id) {
			var imp = $(this).jqGrid('getRowData',rowid);
			var value = imp['type'].toLowerCase();
			$("#type").val(value);
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
	var rowid = $("#gridImpuestos").jqGrid('getGridParam','selrow');
	if( rowid != null ){
		$("#gridImpuestos").jqGrid('delGridRow',rowid,{reloadAfterSubmit:true,closeOnEscape:true,closeAfterDelete:true,
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
