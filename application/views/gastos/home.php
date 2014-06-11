<form action="<?=base_url('gastos')?>" method="POST" name="formDate" id="formDate">
	<label>Mes contable: </label>
	<select name="mes" id="varmes">
		<?for($i=1; $i<=12; $i++){?>
		<option value="<?=sprintf("%02u", $i)?>"<?=($mes==$i)?'selected':''?>><?=sprintf("%02s", $i)?></option>
		<?}?>
	</select>
	<select name="anio" id="varanio">
		<?for($i = $start; $i <= $end; $i++){?>
		<option value="<?=$i?>"<?=($anio==$i)?'selected':''?>><?=$i?></option>
		<?}?>
	</select>
	
	<label>Filtrar</label>
	<select name="catid" id="catid">
		<option value="">--</option>
		<option value="sin categoria">Sin categoria</option>
		<option value="combustible">Combustible</option>
	</select>
	
	<label>Ordenar por</label>
	<select name="sortname" id="sortname">
		<option value="fecha" selected>Fecha</option>
		<option value="nombre">Razon social</option>
		<option value="catid">Categoria</option>
		<option value="uuid">UUID</option>
	</select>
	&nbsp;&nbsp;
</form>
	
<table id="gridGastos"></table>
<div id="navbar"></div>

<!--Formulario de agregar-->
<div id="dialog-form-xml" title="Registros">
	<div id="respuesta" class="ui-state-error ui-corner-all">Panel</div>
	<form action="<?=base_url('gastos/uploadFile')?>" method="post" enctype="multipart/form-data" name="formXml">
		<label>Categoria</label>
		<select name="catid">
			<option value="sin categoria">Sin categoria</option>
			<option value="combustible">Combustible</option>
		</select>
		<label for="file">Archivo de gasto XML:</label>
		<input type="file" name="filexml" id="filexml">
	</form>
</div>

<div id="dialog-form-email" title="Enviar reporte">
	<form name="formMail" id="formMail" method="POST">
		<div id="respuesta2" class="ui-state-error ui-corner-all">Panel</div>
		<label for="email">Email</label>
		<input type="email" name="email" id="email" class="text ui-widget-content ui-corner-all" required />
		<label for="mensaje">Mensaje</label>
		<textarea name="mensaje" id="mensaje" class="text ui-widget-content ui-corner-all"></textarea>
		<input type="hidden" name="id" value="reporte_mensual"/>
		<input type="hidden" name="mes" value=""/>
		<input type="hidden" name="anio" value=""/>
	</form>
</div>

<form name="formRow" action="<?=base_url('gastos/showRow/')?>" method="post" style="display:none">
	<input name="fecha" type="hidden">
	<input name="id" type="hidden">
</form>
        
