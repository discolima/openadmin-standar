$(document).ready(function(){
	$("#gridContacs").jqGrid({
		url: base_url + 'catalogos/jsonContacts',
		editurl: base_url + 'catalogos/saveContacto',
		datatype: "json",
		mtype: 'Post',
		height: 280,
		colNames:['Nombre','RFC','Domicilio','Tipo'], 
		colModel:[ 
			{name:'name',index:'name', width:100, sorttype:"string"},
			{name:'rfc',index:'rfc', width:35, sorttype:"string"},
			{name:'domicilioFiscal',index:'domicilioFiscal', sorttype:"string"},
			{name:'type',index:'type', width:30, sorttype:"string"}
		],
		rowList:[10,12,15,20,100000000],
		multiselect: false,
		emptyrecords: "Sin registros",
		pager: '#navbar', 
		sortname: 'name',
		gridComplete: function(){
			$("option[value=100000000]").text('Todos');
        },
        ondblClickRow: function(rowid) {
			document.location.href=base_url + 'catalogos/verContacto/'+rowid;
        }
	});
	$("#gridContacs").jqGrid('navGrid',"#navbar",{del:false,add:false,edit:false,search:false,refresh:false});
	$("#gridContacs").jqGrid('setGridWidth', $("#content").width(), true);
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
			$("#formNew-res").html('');
			$("#formNew-res").css('visibility','hidden');
			allFields.val( "" ).removeClass( "ui-state-error" );
		}
	});
});
function back(){
	document.location.href=base_url+"catalogos";
}
function buscar(){
	$('#gridContacs').jqGrid('searchGrid',{closeOnEscape:true,closeAfterSearch:true,
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
	$('#dialog-form-new').dialog('open');
	$(document.formNew).html5form();
}
function edit(){
	var rowid = $("#gridContacs").jqGrid('getGridParam','selrow');
	if( rowid != null )
		document.location.href=base_url + 'catalogos/verContacto/'+rowid;
	else msg("Selecciona un gasto para ver mas detalles","warning");
}
function eliminar(){
	var rowid = $("#gridContacs").jqGrid('getGridParam','selrow');
	if( rowid != null ){
		$("#gridContacs").jqGrid('delGridRow',rowid,{reloadAfterSubmit:true,closeOnEscape:true,closeAfterDelete:true,
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
