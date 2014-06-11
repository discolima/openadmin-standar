<?$receptor=(isset($row['receptor']))?$row['receptor']:array()?>
<form id="formFactura" action="<?=base_url('ingresos/save')?>" method="POST">
<fieldset>
<legend>Detalles</legend>
	<label for="serie">Serie</label>
		<input type="text" name="factura[serie]" id="serie" value="<?=(isset($row['serie']))?$row['serie']:$anio?>" readonly/>
	<label for="folio">Folio</label>
		<input type="text" name="factura[folio]" id="folio" value="<?=(isset($row['folio']))?$row['folio']:$sequence?>" readonly/>
	<label for="fecha_expedicion">Fecha</label>
		<input type="text" name="factura[fecha_expedicion]" id="fecha_expedicion" value="<?=(isset($row['fecha']))?date('Y-m-d',strtotime($row['fecha'])):date('Y-m-d')?>" />
	<br/>
	<label for="metodo_pago">Metodo de pago</label>
	<?$factura=(isset($row['factura']))?json_decode($row['factura'],true):array()?>
		<select name="factura[metodo_pago]" id="metodo_pago">
			<option value="EFECTIVO"<?=(isset($factura['metodo_pago']) && $factura['metodo_pago']=='EFECTIVO')?' selected':''?>>EFECTIVO</option>
			<option value="CHEQUE"<?=(isset($factura['metodo_pago']) && $factura['metodo_pago']=='CHEQUE')?' selected':''?>>CHEQUE</option>
			<option value="TARJETA DE CREDITO"<?=(isset($factura['metodo_pago']) && $factura['metodo_pago']=='TARJETA DE CREDITO')?' selected':''?>>TARJETA DE CREDITO</option>
			<option value="TRANSFERENCIA BANCARIA"<?=(isset($factura['metodo_pago']) && $factura['metodo_pago']=='TRANSFERENCIA BANCARIA')?' selected':''?>>TRANSFERENCIA BANCARIA</option>
			<option value="NO IDENTIFICADO"<?=(isset($factura['metodo_pago']) && $factura['metodo_pago']=='NO IDENTIFICADO')?' selected':''?>>NO IDENTIFICADO</option>
		</select>
	<label for="forma_pago">Forma pago</label>
		<select name="factura[forma_pago]" id="forma_pago">
			<option value="PAGO EN UNA SOLA EXHIBICION"<?=(isset($factura['forma_pago']) && $factura['forma_pago']=='PAGO EN UNA SOLA EXHIBICION')?' selected':''?>>PAGO EN UNA SOLA EXHIBICION</option>
			<option value="CREDITO"<?=(isset($factura['forma_pago']) && $factura['forma_pago']=='CREDITO')?' selected':''?>>CREDITO</option>
		</select>
	<label for="tipocomprobante">Tipo de comprobante</label>
		<select name="factura[tipocomprobante]" id="tipocomprobante">
			<option value="ingreso"<?=(isset($factura['tipocomprobante']) && $factura['tipocomprobante']=='ingreso')?' selected':''?>>ingreso</option>
			<option value="egreso"<?=(isset($factura['tipocomprobante']) && $factura['tipocomprobante']=='egreso')?' selected':''?>>egreso</option>
		</select>
	<br/>
	<label for="moneda">Moneda</label>
		<input type="text" name="factura[moneda]" id="moneda" value="<?=(isset($factura['moneda']))?$factura['moneda']:'MXN'?>" />
	<label for="tipocambio">Tipo de cambio</label>
		<input type="text" name="factura[tipocambio]" id="tipocambio" value="<?=(isset($factura['tipocambio']))?$factura['tipocambio']:1.0?>" />
	<label for="NumCtaPago">Num. Cta. Pago</label>
		<input type="text" name="factura[NumCtaPago]" id="NumCtaPago" value="<?=(isset($factura['NumCtaPago']))?$factura['NumCtaPago']:''?>"/>
</fieldset>	
<br/>

<fieldset>
<legend>Receptor</legend>
	<label for="rfc">RFC</label>
		<input type="search" name="receptor[rfc]" id="rfc" value="<?=(isset($receptor['rfc']))?$receptor['rfc']:''?>" />
	<label for="nombre">Razon social</label>
		<input type="text" name="receptor[nombre]" id="nombre" style="width:600px" value="<?=(isset($receptor['nombre']))?$receptor['nombre']:''?>" readonly/>
	<br/>
	<label for="domicilio">Domicilio</label>
		<?$dom=(isset($receptor['Domicilio']))?$receptor['Domicilio']:array()?>
		<input type="text" name="domicilio" id="domicilio" style="width:600px" value="<?=(count($dom))?$dom['calle'].' '.$dom['noExterior'].', '.$dom['colonia']:''?>" readonly/>
		<input type="hidden" name="receptor[Domicilio]" id="domicilioFiscal" value='<?=(count($dom))?json_encode($dom,JSON_HEX_TAG):'{}'?>' />
</fieldset>
<br/>
	
<fieldset id="fielsetConcept">
<legend>Conceptos</legend>
	<input type="text" id="codigo" style="width:110px" onkeyup="javascript:this.value=this.value.toUpperCase();"/>
	<input type="text" id="cantidad" style="width:80px"/>
	<input type="text" id="unidad" style="width:80px" onkeyup="javascript:this.value=this.value.toUpperCase();"/>
	<input type="text" id="descripcion" style="width:300px" onkeyup="javascript:this.value=this.value.toUpperCase();"/>
	<input type="text" id="unitario" style="width:140px"/>
	<input type="button" value="Agregar" id="addimport" />
	<br/>
	<table id="gridConceptos"></table>
	<div id="navbar"></div>
</fieldset>

<table>
<tbody>
	<tr>
		<td>
		<fieldset>
		<?if(!count($row) || (isset($row['status']) && $row['status']=='sin timbrar')):?>
		<legend>Impuestos Translados</legend>
			<select id="ItemTraslado" disabled>
				<option value="">Seleccionar</option>
                <option value="IVA">IVA 16%</option>
                <option value="IEPS">IEPS</option>
            </select>
		</fieldset>
		<br/>
		<fieldset>
		<legend>Impuestos Retenciones</legend>
			<select id="ItemRetencion" disabled>
				<option value="">Seleccionar</option>
				<option value="IVA">IVA</option>
				<option value="ISR">ISR</option>
			</select>
		<?else:?>
		<img src="<?=base_url("files/{$sf[0]}/".date('m',strtotime($row['fecha']))."/ingresos/{$row['uuid']}.png")?>" />
		<?endif?>
		</fieldset>
		</td>
		<td style="text-align:right;" id="rows">
			<label>Subtotal</label> 
			<input type="number" id="subtotal" class='sum' name="factura[subtotal]" />
			<? $impuestos=(isset($row['impuestos']))?json_decode($row['impuestos'],true):array();
			foreach($impuestos as $name=>$array):?>
			<?$i=0;?>
			<?foreach($array as $imp):?>
			<div id="<?=$name?>_<?=$imp['impuesto']?>">
				<label><?=$name?> <?=$imp['impuesto']?></label> 
				<input type='number' name='impuestos[<?=$name?>][<?=$i?>][importe]' class='sum' value='<?=number_format(($name=='retenidos')?$imp['importe']*(-1):$imp['importe'],2)?>'/>
				<input type="hidden" value="<?=$imp['impuesto']?>" name="impuestos[<?=$name?>][<?=$i?>][impuesto]">
				<input type="hidden" value="<?=$imp['tasa']?>" name="impuestos[<?=$name?>][<?=$i?>][tasa]">
				<br/>
				<?$i++?>
			</div>
			<?endforeach?>
			<?endforeach?>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:right;">
			<label>Total</label> <input name="factura[total]" id="total" />
		</td>
	</tr>
</tbody>
</table>
<script>
<?if(isset($row['conceptos']) && !empty($row['conceptos'])):?>
	$(function(){
		var c = $.parseJSON('<?=$row['conceptos']?>');
		for(var i=0;i<=c.length;i++)
			$('#gridConceptos').jqGrid('addRowData',i+1,c[i]);
	});
<?endif?>
<?if(isset($row['status']) && $row['status']!='sin timbrar'):?>
	$('#formFactura').find('input,button,select').attr('disabled','disabled');
<?endif?>
</script>
<input type='hidden' name='conceptos' id='conceptos'/>
<input type='hidden' name='status' value="<?=(isset($row['status']))?$row['status']:'sin timbrar'?>"/>
<?if(!isset($row['status']) || $row['status']=='sin timbrar'):?>
	<p class="submit"><input type="submit" value="Guardar"/></p>
<?endif?>
</form>
<form name="formSend" id="formSend" method="POST">
	<input type='hidden' name='anio' value="<?=(isset($sf[0]))?$sf[0]:''?>"/>
	<input type='hidden' name='mes' value="<?=(isset($row['fecha']))?date('m',strtotime($row['fecha'])):''?>"/>
	<input type='hidden' name='folio' value="<?=(isset($row['folio']))?$row['folio']:''?>"/>
</form>
<!--Formulario de agregar-->
<div id="dialog-form-email" title="Enviar factura">
<form name="formEmail" method="POST">
	<label for="email">Email</label>
	<input type="email" id="email" name="email" value="<?=(isset($receptor['email']))?$receptor['email']:''?>"/>
	<label for="mensaje">Mensaje</label>
	<textarea name="mensaje" id="mensaje">Estimado <?=(isset($receptor['contacto']) && !empty($receptor['contacto']))?$receptor['contacto']:'Sr(a):'?>,<?="\n"?>Envio los archivos de la factura <?=$row['serie']?>-<?=$row['folio']?><?=($row['status']!="timbrada")?", para su verificacion":""?>.</textarea>
</form>
</div>
