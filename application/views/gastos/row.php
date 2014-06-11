<div id="catid">
	<label>Almacenado por:</label>
	<span><?=$rowdb['user']?></span>

	<label>Categoria</label>
	<select name="categoria" id="selectCat">
		<option value="sin categoria"<?=($rowdb['catid']=='sin categoria')?' selected':''?>>Sin categoria</option>
		<option value="combustible"<?=($rowdb['catid']=='combustible')?' selected':''?>>Combustible</option>
	</select>
</div>
<br/>
<hr/>
<h2>Resumen</h2>
<ul class="form">
	<li><label>UUID</label><span><?=$id?></span>
		<label>Fecha</label><span><?=str_replace("T", " ",$Comprobante[0]['fecha'])?></span></li>
	<?if(!count($Comprobante['Emisor']['ExpedidoEn']) && count($Comprobante['Emisor']['DomicilioFiscal'])) $Comprobante['Emisor']['ExpedidoEn'][0]=$Comprobante['Emisor']['DomicilioFiscal'][0]?>
	<li><label>Expedido en</label><span>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['calle']))?"Domicilio conocido":trim($Comprobante['Emisor']['ExpedidoEn'][0]['calle'])?>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['noExterior']))?" SN":" ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['noExterior'])?>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['noInterior']))?"":" (".trim($Comprobante['Emisor']['ExpedidoEn'][0]['noInterior']).")"?>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['colonia']))?"":", ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['colonia'])?>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['localidad']))?"":", Loc. ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['localidad'])?>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['municipio']))?"":", ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['municipio'])?>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['estado']))?"":", ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['estado'])?>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['pais']))?"":", ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['pais'])?>
		<?=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['codigoPostal']))?"":", C.P.".trim($Comprobante['Emisor']['ExpedidoEn'][0]['codigoPostal'])?>
	</span></li>
	<?$sf=(empty($Comprobante[0]['serie']))?"-":$Comprobante[0]['serie']." ";
	$sf.=(empty($Comprobante[0]['folio']))?"-":$Comprobante[0]['folio']?>
	<li><label>Serie y folio</label><span><?=$sf?></span>
		<label>Tipo de comprobante</label><span><?=$Comprobante[0]['tipoDeComprobante']?></span>
		<label>Forma de pago</label><span><?=$Comprobante[0]['formaDePago']?></span></li>
	<li><label>Metodo de pago</label><span><?=$Comprobante[0]['metodoDePago']?></span>
		<label>NumCta de pago</label><span><?=(empty($Comprobante[0]['NumCtaPago']))?"-":str_pad($Comprobante[0]['NumCtaPago'],16,'*',STR_PAD_LEFT)?></span>
		<?$tcambio=(empty($Comprobante[0]['TipoCambio']))?"1.00":number_format((float)$Comprobante[0]['TipoCambio'],2)?>
		<label>Tipo de cambio</label><span><?=$tcambio?></span></li>
</ul>
<h2>Emisor</h2>
<ul class="form">
	<li><label>Nombre</label><span><?=$Comprobante['Emisor'][0]['nombre']?></span></li>
	<li><label>RFC</label><span><?=$Comprobante['Emisor'][0]['rfc']?></span>
		<label>Regimen fiscal</label><span><?=$Comprobante['Emisor']['RegimenFiscal'][0]['Regimen']?></span></li>
	<li><label>Domicilio fiscal</label><span>
		<?$d=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['calle']))?"Domicilio conocido":trim($Comprobante['Emisor']['DomicilioFiscal'][0]['calle'])?>
		<?$d.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['noExterior']))?" SN":" ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['noExterior'])?>
		<?$d.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['noInterior']))?"":" (".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['noInterior']).")"?>
		<?$d.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['colonia']))?"":", ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['colonia'])?>
		<?$d.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['localidad']))?"":", Loc. ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['localidad'])?>
		<?$d.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['municipio']))?"":", ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['municipio'])?>
		<?$d.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['estado']))?"":", ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['estado'])?>
		<?$d.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['pais']))?"":", ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['pais'])?>
		<?$d.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['codigoPostal']))?"":", C.P.".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['codigoPostal'])?>
		<?=$d?>
	</span></li>
</ul>
<h2>Conceptos</h2>
<?$importe=0;$impuestos=0;$moneda=(empty($Comprobante[0]['Moneda']))?"MXN":$Comprobante[0]['Moneda'];$moneda=(strlen($moneda)>3)?"MXN":$moneda?>
<table>
	<thead>
	<tr>
		<th style="width:40px">Cantidad</th>
		<th style="width:120px">Unidad</th>
		<th>Descripcion</th>
		<th style="width:120px">Valor unitario</th>
		<th style="width:120px">Importe</th>
	</tr>
	</thead>
	<tbody>
	<?foreach($Comprobante['Conceptos'] as $row): $importe+=(float)$row['importe'];?>
	<tr>
		<td class="Num"><?=number_format((float)$row['cantidad'],2)?></td>
		<td><?=$row['unidad']?></td>
		<td><?=$row['descripcion']?></td>
		<td class="Num"><?=money_format('%.2n',(float)$row['valorUnitario'])?> <?=$moneda?></td>
		<td class="Num"><?=money_format('%.2n',(float)$row['importe'])?> <?=$moneda?></td>
	</tr>
	<?endforeach?>
	</tbody>
	<tfood>
	<tr class="Key">
		<td class="Num" colspan="4"><strong>SubTotal</strong></td>
		<td class="Num"><strong><?=money_format('%.2n',$importe)?> <?=$moneda?></strong></td>
	</tr>
	<?foreach($Comprobante['Impuestos'] as $key=>$val): if(!count($val)) continue;?>
	<tr class="Key">
		<th colspan="4">Impuesto <?=$key?></th>
		<th>&nbsp;</th>
	</tr>
		<?foreach($val as $row): $impuestos+=(float)$row['importe']?>
		<tr class="Key">
			<td class="Num" colspan="4"><?=$row['impuesto']?>: <?=$row['tasa']?>%</td>
			<td class="Num"><?=money_format('%.2n',(float)$row['importe'])?> <?=$moneda?></td>
		</tr>
		<?endforeach?>
	<?endforeach?>
	<tr class="Key">
		<td class="Num" colspan="4"><strong>Total</strong></td>
		<td class="Num"><strong><?=money_format('%.2n',$importe+$impuestos)?> <?=$moneda?></strong></td>
	</tr>
	<?if($tcambio!=1):?>
	<tr class="Key">
		<td class="Num" colspan="4"><strong>Total en MXN</strong></td>
		<td class="Num"><strong><?=money_format('%.2n',$tcambio*($importe+$impuestos))?> <?=$moneda?></strong></td>
	</tr>
	<?endif?>
	</tfood>
</table>
<div id="dialog-form" title="Enviar factura">
	<form name="formDel" id="formDel" method="POST">
		<div id="respuesta" class="ui-state-error ui-corner-all">Panel</div>
		<label for="email">Email</label>
		<input type="email" name="email" id="email" class="text ui-widget-content ui-corner-all" required />
		<label for="mensaje">Mensaje</label>
		<textarea name="mensaje" id="mensaje" class="text ui-widget-content ui-corner-all"></textarea>
		<input type="hidden" name="id" value="<?=$id?>"/>
		<input type="hidden" name="mes" value="<?=date("m",strtotime($fecha))?>"/>
		<input type="hidden" name="anio" value="<?=date("Y",strtotime($fecha))?>"/>
	</form>
</div>
<!--Formulario de agregar-->
<div id="dialog-form-new" title="Registros">
	<form name="formNew" id="formNew" method="POST" action="<?=base_url('catalogos/saveContacto')?>">
		<div id="formNew-res" class="ui-state-error ui-corner-all">Panel</div>
		<label for="rfc">RFC</label>
		<input type="text" name="rfc" id="rfc" class="text ui-widget-content ui-corner-all"
		value="<?=$Comprobante['Emisor'][0]['rfc']?>"  readonly/>
		<label for="name">Razon social</label>
		<input type="text" name="name" id="name" class="text ui-widget-content ui-corner-all"
		value="<?=$Comprobante['Emisor'][0]['nombre']?>"  readonly/>
		<label for="dfiscal">Domicilio fiscal</label>
		<input type="text" name="dfiscal" id="dfiscal" class="text ui-widget-content ui-corner-all"
		value="<?=$d?>"  disabled/>
		<label for="name">E-mail</label>
		<input type="email" name="email" id="email" class="text ui-widget-content ui-corner-all" value="" />
		<select name="type"  class="text ui-widget-content ui-corner-all">
			<option value="cliente">Cliente</option>
			<option value="proveedor">Proveedor</option>
		</select>
		<?
		if(count($Comprobante['Emisor']['DomicilioFiscal'])){
			foreach($Comprobante['Emisor']['DomicilioFiscal'][0]->attributes() as $key=>$val){?>
			<input type="hidden" name="domicilio[<?=$key?>]" value="<?=$val?>" />
		<?} }?>
		<input type="hidden" name="registro" value="<?=$_SERVER['PHP_AUTH_USER']?>"/>
		<input type="hidden" name="fecha" value="<?=date("Y-m-d h:i")?>"/>
	</form>
</div>
