<form name="formNew" method="POST" action="<?=base_url('catalogos/saveContacto')?>">
	<p class="submit"><input type="submit" onclick="$(this.form).submit()" value="Guardar"/></p>
	
	<label for="rfc">RFC</label>
	<input type="text" name="rfc" id="rfc" class="text ui-widget-content ui-corner-all" value="<?=html_entity_decode($row['rfc'])?>" readonly/>
	<label for="name">Razon social</label>
	<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all" value="<?=html_entity_decode($row['name'])?>" onkeyup="javascript:this.value=this.value.toUpperCase();" required/>
	
	<h3>Domicilio fiscal</h3>
	<?$dom=json_decode($row['domicilioFiscal'],true);
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
	<input type="text" name="domicilio[calle]" id="calle" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper(html_entity_decode($dom['calle']))?>" required/>
	<label for="noExterior">No. exterior</label>
	<input type="number" name="domicilio[noExterior]" id="noExterior" class="text ui-widget-content ui-corner-all" value="<?=html_entity_decode($dom['noExterior'])?>"/>
	<label for="noInterior">No. interior</label>
	<input type="number" name="domicilio[noInterior]" id="noInterior" class="text ui-widget-content ui-corner-all" value="<?=html_entity_decode($dom['noInterior'])?>"/>
	<label for="colonia">Colonia</label>
	<input type="text" name="domicilio[colonia]" id="colonia" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper(html_entity_decode($dom['colonia']))?>"/>
	<label for="localidad">Localidad</label>
	<input type="text" name="domicilio[localidad]" id="localidad" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper(html_entity_decode($dom['localidad']))?>"/>
	<label for="municipio">Municipio</label>
	<input type="text" name="domicilio[municipio]" id="municipio" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper(html_entity_decode($dom['municipio']))?>"/>
	<label for="estado">Estado</label>
	<input type="text" name="domicilio[estado]" id="estado" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper(html_entity_decode($dom['estado']))?>"/>
	<label for="pais">Pais</label>
	<input type="text" name="domicilio[pais]" id="pais" class="text ui-widget-content ui-corner-all" onkeyup="javascript:this.value=this.value.toUpperCase();" value="<?=strtoupper(html_entity_decode($dom['pais']))?>"/>
	<label for="CodigoPostal">C.P.</label>
	<input type="number" name="domicilio[CodigoPostal]" id="CodigoPostal" class="text ui-widget-content ui-corner-all" value="<?=html_entity_decode($dom['CodigoPostal'])?>"/>
	
	<h3>Contacto</h3>
	<label for="contacto">Nombre de contacto</label>
	<input type="text" name="contacto" id="contacto" class="text ui-widget-content ui-corner-all" value="<?=$row['contacto']?>"/>
	<label for="name">E-mail</label>
	<input type="email" name="email" id="email" class="text ui-widget-content ui-corner-all" value="<?=$row['email']?>"/>
	<label for="telefono">Telefono</label>
	<input type="tel" name="telefono" id="telefono" class="text ui-widget-content ui-corner-all" value="<?=$row['telefono']?>"/>
	
	<select name="type"  class="text ui-widget-content ui-corner-all">
		<option value="cliente" <?=($row['type']=='cliente')?'selected':''?>>Cliente</option>
		<option value="proveedor" <?=($row['type']=='proveedor')?'selected':''?>>Proveedor</option>
	</select>
	<input type="hidden" name="oper" value="edit"/>
	<p class="submit"><input type="submit" onclick="$(this.form).submit()" value="Guardar"/></p>
</form>
<form name="formSend" method="POST">
	<input type="hidden" name="oper" value="del"/>
	<input type="hidden" name="id" value="<?=html_entity_decode($row['rfc'])?>"/>
	<input type="hidden" name="goto" value="true"/>
</form>
<div id="dialog-confirm" title="Eliminar contacto">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
	¿Esta segura de eliminar a <?=substr(html_entity_decode($row['name']),0,10)?>...?</p>
</div>
