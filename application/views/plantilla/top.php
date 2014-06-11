<!DOCTYPE html>
<html lang="es">
<head>
<title><?=$title?></title>
<meta charset="utf-8" />
<link rel="shortcut icon" type="image/png" href="<?=base_url('lib/images/apple-touch-icon.png')?>"/>
<link rel="stylesheet" href="<?=base_url('lib/css/themes/blitzer/jquery-ui.min.css')?>" />
<link rel="stylesheet" href="<?=base_url('lib/css/ui.jqgrid.css')?>" />
<link rel="stylesheet" href="<?=base_url('lib/css/template/theme.css')?>" />
<link rel="stylesheet" href="<?=base_url('lib/css/template/style.css')?>" />
<? if(isset($cssf) && count($cssf)){
		foreach ($cssf as $css){
			if(empty($css['href'])) continue;
			if(!filter_var($css['href'], FILTER_VALIDATE_URL)) $css['href']=base_url($css['href']); ?>
<link rel="stylesheet"<? foreach ($css as $key => $value) echo " $key='$value'"?>/>
<?		}
	}?>
<script type="text/javascript" src="<?=base_url('lib/js/jquery-1.9.1.min.js')?>"></script>
<script type="text/javascript" src="<?=base_url('lib/js/jquery-ui-1.9.2.min.js')?>"></script>
<script type="text/javascript" src="<?=base_url('lib/js/jquery.noty.packaged.min.js')?>"></script>
<script type="text/javascript" src="<?=base_url('lib/js/layouts/topCenter.js')?>"></script>
<script type="text/javascript" src="<?=base_url('lib/js/es/grid.locale-es.js')?>"></script>
<script type="text/javascript" src="<?=base_url('lib/js/jquery.jqGrid.min.js')?>"></script>
<script type="text/javascript" src="<?=base_url('lib/js/system.js')?>"></script>
<? if(isset($scripts) && count($scripts)){
		foreach ($scripts as $script){
			if(empty($script['src'])) continue;
			if(!filter_var($script['src'], FILTER_VALIDATE_URL)) $script['src']=base_url($script['src']); ?>
<script type="text/javascript"<? foreach ($script as $key => $value) echo " $key='$value'"?>></script>
<?}}?>
<script>
	var base_url = '<?=base_url()?>';
	var notify;
</script>
</head>
<div id="container">
	<div id="header">
		
		<div id="tophead">
			<img src="<?=base_url('lib/images/logo_oa_small.png')?>" title="openAdmin" />
		</div>
		<div id="tophead"><h2><?=$title?></h2></div>
		<div id="topmenu">
			<ul>
				<li<?=($menu=='home')?" class=\"current\"":"";?>><a id="menuDash" href="<?=base_url()?>">Dashboard</a></li>
                <li<?=($menu=='gastos')?" class=\"current\"":"";?>><a href="<?=base_url('gastos')?>">Gastos</a></li>
                <li<?=($menu=='ingresos')?" class=\"current\"":"";?>><a href="<?=base_url('ingresos')?>">Ingresos</a></li>
                <li<?=($menu=='catalogos')?" class=\"current\"":"";?>><a href="<?=base_url('catalogos')?>">Catalogos</a></li>
                <li<?=($menu=='settings')?" class=\"current\"":"";?>><a href="<?=base_url('settings')?>">Configuraciones</a></li>
			</ul>
		</div>
	</div>
    <div id="top-panel">
		<div id="panel">
			<ul>
				<?if(isset($main) && count($main)):?>
				<?foreach($main as $item):?>
					<?if($item['label']!='sp'):?>
					<li><a href="javascript:void(0)" onclick="<?=(isset($item['click']))?$item['click']:''?>" class="<?=(isset($item['class']))?$item['class']:''?>"><?=(isset($item['label']))?$item['label']:''?></a></li>
					<?else:?>
					<li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
					<?endif?>
				<?endforeach?>
				<?endif?>
                <?if($menu=='gastos'):?>
					<?if($submenu=='index'):?>
					<li><a href="javascript:void(0)" onclick="searchRow();" class="search">Buscar</a></li>
					<li><a href="javascript:void(0)" onclick="seemore();" class="invoices">Detalles</a></li>
					<li><a href="javascript:void(0)" onclick="addXml();" class="add">Agregar</a></li>
					<li><a href="javascript:void(0)" onclick="toReport();" class="report">Reporte</a></li>
					<li><a href="javascript:void(0)" onclick="toMail();" class="email">Enviar</a></li>
					<li>&nbsp;</li>
					<li><a href="javascript:void(0)" onclick="delRows();" class="page_delete">Eliminar</a></li>
					<?endif?>
					<?if($submenu=='showRow'):?>
					<li><a href="javascript:void(0)" onclick="window.location.href='<?=base_url('gastos')?>'" class="left">Volver</a></li>
					<li>&nbsp;</li>
					<li><a href="javascript:void(0)" onclick="toPDF();" class="report_seo">PDF</a></li>
					<li><a href="javascript:void(0)" onclick="toMail();" class="email">Email</a></li>
					<?if(!$reg['count']):?>
					<li><a href="javascript:void(0)" onclick="addReg();" class="contacto">Agregar</a></li>
					<?endif?>
					<li>&nbsp;</li>
					<li><a href="javascript:void(0)" onclick="delRow();" class="page_delete">Eliminar</a></li>
					<?endif?>
                <?endif?>
                <li><a href="<?=base_url('home/logout')?>" class="user" style="float:right;">Salir (<?=$user['name']?>)</a></li>
			</ul>
		</div>
	</div>
    <div id="wrapper">
		<div id="content">
