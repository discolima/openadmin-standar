<!--Formulario de agregar-->
<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable ui-resizable ui-dialog-buttons" 
style="outline: 0px none; z-index: 1002; position: absolute; height: auto; width: 350px; top: 150px; left: 454px; display: block;" tabindex="-1">
	<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
		<span id="ui-id-2" class="ui-dialog-title">Autentificacion</span>
	</div>
	
	<div class="ui-dialog-content ui-widget-content" 
	style="width:auto;min-height:0px;height:240px;background-color:#F9F9F9" 
	scrolltop="0" scrollleft="0">
		<form name="formAutho" method="POST" action="<?=base_url('home/session')?>">
			<div id="panel" class="ui-state-error ui-corner-all">Panel</div>
			<label for="user">Usuario</label>
			<input type="text" name="user" id="user" class="text ui-widget-content ui-corner-all"/>
			<label for="pass">Password</label>
			<input type="password" name="pass" id="pass" class="text ui-widget-content ui-corner-all"/>
			<p class="submit"><input type="submit" value="Ingresar"/></p>
		</form>
	</div>
</div>
