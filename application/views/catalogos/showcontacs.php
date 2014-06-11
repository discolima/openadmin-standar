<table id="gridContacs"></table>
<div id="navbar"></div>
<!--Formulario de agregar-->
<div id="dialog-form-new" title="Nuevo contacto">
	<form name="formNew" method="POST" action="<?=base_url('catalogos/saveContacto')?>">
		<div id="formNew-res" class="ui-state-error ui-corner-all">Panel</div>
		<label for="rfc">RFC</label>
		<input type="text" name="rfc" id="rfc" class="text ui-widget-content ui-corner-all" value="" 
		onkeyup="javascript:this.value=this.value.toUpperCase();"/>
		<label for="name">Razon social</label>
		<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" value="" 
		onkeyup="javascript:this.value=this.value.toUpperCase();"/>
		<label for="name">E-mail</label>
		<input type="email" name="email" id="email" class="text ui-widget-content ui-corner-all" value=""/>
		
		<select name="type"  class="text ui-widget-content ui-corner-all">
			<option value="cliente">Cliente</option>
			<option value="proveedor">Proveedor</option>
		</select>
		<input type="hidden" name="fecha" value="<?=date("Y-m-d h:i")?>"/>
		<input type="hidden" name="oper" value="add"/>
	</form>
</div>
