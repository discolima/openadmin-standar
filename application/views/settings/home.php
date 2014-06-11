<form method="POST" action="<?=base_url('settings/save')?>">
<p class="submit"><input type="submit" value="Guardar"/></p>

<div id="tabs">
<ul>
	<li><a href="#tabs-1">Emisor</a></li>
	<li><a href="#tabs-2">Domicilio fiscal</a></li>
	<li><a href="#tabs-3">Expedido en</a></li>
	<li><a href="#tabs-4">PAC</a></li>
	<li><a href="#tabs-5">SAT</a></li>
</ul>
<div id="tabs-1">
	<label for="rfc">RFC</label>
	<input type="text" name="emisor[rfc]" id="rfc" class="text ui-widget-content ui-corner-all" value="<?=$row['emisor']['rfc']?>" onkeyup="javascript:this.value=this.value.toUpperCase();" required/>
	<label for="nombre">Razon social</label>
	<input type="text" name="emisor[nombre]" id="nombre" class="text ui-widget-content ui-corner-all" value="<?=$row['emisor']['nombre']?>" onkeyup="javascript:this.value=this.value.toUpperCase();" required/>
	<label for="RegimenFiscal">Regimen fiscal</label>
	<input type="text" name="emisor[RegimenFiscal]" id="RegimenFiscal" class="text ui-widget-content ui-corner-all" value="<?=$row['emisor']['RegimenFiscal']?>" onkeyup="javascript:this.value=this.value.toUpperCase();" required/>
</div>

<div id="tabs-2">
	<?$dom=$row['emisor']['DomicilioFiscal'];
	if(!isset($dom['calle'])) $dom['calle']='';
	if(!isset($dom['noExterior'])) $dom['noExterior']='';
	if(!isset($dom['noInterior'])) $dom['noInterior']='';
	if(!isset($dom['colonia'])) $dom['colonia']='';
	if(!isset($dom['localidad'])) $dom['localidad']='';
	if(!isset($dom['municipio'])) $dom['municipio']='';
	if(!isset($dom['estado'])) $dom['estado']='COLIMA';
	if(!isset($dom['pais'])) $dom['pais']='MEXICO';
	if(!isset($dom['CodigoPostal'])) $dom['CodigoPostal']='';?>
	<label for="calle">Calle</label>
	<input type="text" name="emisor[DomicilioFiscal][calle]" id="calle" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['calle'])?>"/>
	<label for="noExterior">No. exterior</label>
	<input type="number" name="emisor[DomicilioFiscal][noExterior]" id="noExterior" class="text ui-widget-content ui-corner-all" value="<?=$dom['noExterior']?>"/>
	<label for="noInterior">No. interior</label>
	<input type="number" name="emisor[DomicilioFiscal][noInterior]" id="noInterior" class="text ui-widget-content ui-corner-all" value="<?=$dom['noInterior']?>"/>
	<label for="colonia">Colonia</label>
	<input type="text" name="emisor[DomicilioFiscal][colonia]" id="colonia" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['colonia'])?>"/>
	<label for="localidad">Localidad</label>
	<input type="text" name="emisor[DomicilioFiscal][localidad]" id="localidad" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['localidad'])?>"/>
	<label for="municipio">Municipio</label>
	<input type="text" name="emisor[DomicilioFiscal][municipio]" id="municipio" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['municipio'])?>"/>
	<label for="estado">Estado</label>
	<input type="text" name="emisor[DomicilioFiscal][estado]" id="estado" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['estado'])?>"/>
	<label for="pais">Pais</label>
	<input type="text" name="emisor[DomicilioFiscal][pais]" id="pais" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['pais'])?>"/>
	<label for="CodigoPostal">C.P.</label>
	<input type="number" name="emisor[DomicilioFiscal][CodigoPostal]" id="CodigoPostal" class="text ui-widget-content ui-corner-all" value="<?=$dom['CodigoPostal']?>"/>
</div>
<div id="tabs-3">
	<?$dom=$row['emisor']['ExpedidoEn'];
	if(!isset($dom['calle'])) $dom['calle']='';
	if(!isset($dom['noExterior'])) $dom['noExterior']='';
	if(!isset($dom['noInterior'])) $dom['noInterior']='';
	if(!isset($dom['colonia'])) $dom['colonia']='';
	if(!isset($dom['localidad'])) $dom['localidad']='';
	if(!isset($dom['municipio'])) $dom['municipio']='';
	if(!isset($dom['estado'])) $dom['estado']='';
	if(!isset($dom['pais'])) $dom['pais']='';
	if(!isset($dom['CodigoPostal'])) $dom['CodigoPostal']='';?>
	<label for="calle">Calle</label>
	<input type="text" name="emisor[ExpedidoEn][calle]" id="calle" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['calle'])?>" />
	<label for="noExterior">No. exterior</label>
	<input type="number" name="emisor[ExpedidoEn][noExterior]" id="noExterior" class="text ui-widget-content ui-corner-all" value="<?=$dom['noExterior']?>"/>
	<label for="noInterior">No. interior</label>
	<input type="number" name="emisor[ExpedidoEn][noInterior]" id="noInterior" class="text ui-widget-content ui-corner-all" value="<?=$dom['noInterior']?>"/>
	<label for="colonia">Colonia</label>
	<input type="text" name="emisor[ExpedidoEn][colonia]" id="colonia" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['colonia'])?>"/>
	<label for="localidad">Localidad</label>
	<input type="text" name="emisor[ExpedidoEn][localidad]" id="localidad" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['localidad'])?>"/>
	<label for="municipio">Municipio</label>
	<input type="text" name="emisor[ExpedidoEn][municipio]" id="municipio" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['municipio'])?>"/>
	<label for="estado">Estado</label>
	<input type="text" name="emisor[ExpedidoEn][estado]" id="estado" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['estado'])?>"/>
	<label for="pais">Pais</label>
	<input type="text" name="emisor[ExpedidoEn][pais]" id="pais" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper($dom['pais'])?>"/>
	<label for="CodigoPostal">C.P.</label>
	<input type="number" name="emisor[ExpedidoEn][CodigoPostal]" id="CodigoPostal" class="text ui-widget-content ui-corner-all" value="<?=$dom['CodigoPostal']?>"/>
</div>
<div id="tabs-4">
	<label for="usuario">Usuario</label>
	<input type="text" name="PAC[usuario]" id="usuario" class="text ui-widget-content ui-corner-all" value="<?=$row['PAC']['usuario']?>" />
	<label for="pass">Password</label>
	<input type="text" name="PAC[pass]" id="pass" class="text ui-widget-content ui-corner-all" value="<?=$row['PAC']['pass']?>" />
	<label for="produccion">Produccion</label>
	<select name="PAC[produccion]" id="produccion" class="text ui-widget-content ui-corner-all">
		<option value="SI"<?=($row['PAC']['produccion']=='SI')?'selected':''?>>SI</option>
		<option value="NO"<?=($row['PAC']['produccion']=='NO')?'selected':''?>>NO</option>
	</select>
</div>

<div id="tabs-5">
	<label for="cer">Certificado</label>
	<input type="text" name="PAC[cer]" id="cer" class="text ui-widget-content ui-corner-all" value="<?=$row['PAC']['cer']?>" />
	<label for="key">Key</label>
	<input type="text" name="PAC[key]" id="key" class="text ui-widget-content ui-corner-all" value="<?=$row['PAC']['key']?>" />
	<label for="pass">Password</label>
	<input type="text" name="PAC[SAT][pass]" id="pass" class="text ui-widget-content ui-corner-all" value="<?=$row['PAC']['SAT']['pass']?>" />
</div>
</div>
</form>
