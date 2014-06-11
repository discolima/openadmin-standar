<div id="form">
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
	<label>Ordenar por</label>
	<select name="sortname" id="sortname">
		<option value="fecha" selected>Fecha</option>
		<option value="nombre">Razon social</option>
		<option value="catid">Categoria</option>
		<option value="uuid">UUID</option>
	</select>
	&nbsp;&nbsp;
</div>
	
<table id="gridIngresos"></table>
<div id="navbar"></div>
        
<form name="sendto" method="post">
	<input type="hidden" name="sf"/>
</form>
