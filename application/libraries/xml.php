<?php if ( ! defined('BASEPATH')) exit('No se permite el acceso directo al script');
class xml{
	public $datos;
	//Funcion inicializadora
	function __construct($datos){
		//Ruta del SDK
		$datos['SDK']['ruta']=ROOT;
		// OPCIONAL GUARDAR EL XML GENERADO ANTES DE TIMBRARLO
		$serie=(isset($datos['factura']['serie']))?$datos['factura']['serie']:date('Y');
		$folio=(isset($datos['factura']['folio']))?$datos['factura']['folio']:date('His');
		$datos['xml_debug']= $datos['SDK']['ruta'].'xml_debug'.DS.'file_'.$serie.$folio.'.xml';
		if(!isset($datos['cfdi'])){
			if(isset($datos['factura']['fecha_expedicion'])){
				$anio = date("Y",strtotime($datos['factura']['fecha_expedicion']));
				$mes = date("m",strtotime($datos['factura']['fecha_expedicion']));
			
				$dir = $datos['SDK']['ruta'].'files'.DS.$anio;
				if(!file_exists($dir) && !is_dir($dir)){
					if(!mkdir($dir,0777,true))
					$this->plantillas->set_message(6000,"Error al crear el directorio: $dir");
				} else @chmod($dir, 0777);
				$dir.= DS.$mes;
				if(!file_exists($dir) && !is_dir($dir)){
					if(!mkdir($dir,0777,true))
					$this->plantillas->set_message(6000,"Error al crear el directorio: $dir");
				} else @chmod($dir, 0777);
				$dir.=DS."ingresos";
				if(!file_exists($dir) && !is_dir($dir)){
					if(!mkdir($dir,0777,true))
					$this->plantillas->set_message(6000,"Error al crear el directorio: $dir");
				} else @chmod($dir, 0777);
				if(is_dir($dir))
					$datos['cfdi'] = $dir.DS;
				else
					$datos['cfdi'] = $datos['SDK']['ruta'].'files'.DS.'debug'.DS;
			} else {
				$dir = $datos['SDK']['ruta'].'files'.DS.'debug';
				if(!file_exists($dir) && !is_dir($dir)){
					if(!mkdir($dir,0777,true))
						$this->plantillas->set_message(6000,"Error al crear el directorio: $dir");
				} else @chmod($dir, 0777);
				$dir.=DS;
				$datos['cfdi']=$dir;
			}
		}
		//$datos['remueve_acentos']='SI';
		$this->datos=$datos;
	}
	private function array_map_recursive($func, $array){
		foreach ($array as $key => $val) {
			if (is_array($array[$key]))
				$array[$key] = $this->array_map_recursive($func, $array[$key]);
			else 
				$array[$key] = call_user_func(array($this,$func), $val );
		}
		return $array;
	}
	public function cfdi_generar_xml(){
		$datos=$this->datos;
		$datos['html_a_txt']=(isset($datos['html_a_txt']))?$datos['html_a_txt']:'NO';
		$datos['factura']['noCertificadoSAT']=(isset($datos['factura']['noCertificadoSAT']))?$datos['factura']['noCertificadoSAT']:null;
		$datos['factura']['certificado']=(isset($datos['factura']['certificado']))?$datos['factura']['certificado']:null;
		$datos['impuestos']['retenidos']=(isset($datos['impuestos']['retenidos']))?$datos['impuestos']['retenidos']:0;
		$datos['impuestos']['translados']=(isset($datos['impuestos']['translados']))?$datos['impuestos']['translados']:0;
		$produccion=$datos['PAC']['produccion'];
		
		if($datos['html_a_txt']=='SI')
			$datos= $this->array_map_recursive('cfd_fix_dato_xml_html_txt', $datos);
			
		if(isset($datos['remueve_acentos']) && $datos['remueve_acentos']=='SI')
			$datos= $this->array_map_recursive('cfd_fix_dato_xml_acentos', $datos);
		else
			$datos= $this->array_map_recursive('cfd_fix_dato_xml', $datos);
        
		$ruta=$datos['SDK']['ruta'];
		$ruta=str_replace('\\','/',$ruta);
		$valor=array('return'=>false);
        $cer= (isset($datos['conf']['cer']))?$ruta.$datos['conf']['cer']:null;
        $key= (isset($datos['conf']['key']))?$ruta.$datos['conf']['key']:null;
        $pass= (isset($datos['conf']['pass']))?$datos['conf']['pass']:null;
        $cer=str_replace('\\','/',$cer);
        $key=str_replace('\\','/',$key);
        
            //SI EL CERTIFICADO NO ESTA PREPARADO
            if(file_exists("$cer")==false || file_exists("$key")==false ){
                $res_certificado= $this->certificado_pem($datos);
                $certificado_numero= $res_certificado['certificado_no_serie'];
                //crea archivo
                $file_target="$cer.txt";
                @unlink($file_target);
                if (file_exists($file_target)) @chmod($file_target, 0777);
                if (($wh = fopen($file_target, 'wb')) === false) return false;
                if (fwrite($wh, $certificado_numero) === false) {
                    fclose($wh);
                    return false;
                }
                fclose($wh);
                @chmod($file_target, 0777);
            }
           
            if(empty($datos['factura']['noCertificadoSAT']) || $datos['factura']['noCertificadoSAT']=='ND'){
                $datos['factura']['noCertificadoSAT']=file_get_contents("$cer.txt");
            }
                             
            $Factura_Serie = (isset($datos['factura']['serie']))?$datos['factura']['serie']:null;
            $Factura_Numero = (isset($datos['factura']['folio']))?$datos['factura']['folio']:null;
            $Factura_Fecha_Expedicion = (isset($datos['factura']['fecha_expedicion']))?$datos['factura']['fecha_expedicion']:date('Y-m-d H:i:s');
            $Factura_Fecha_Expedicion = str_replace(' ', 'T', $Factura_Fecha_Expedicion);
            $certificado=(isset($datos['factura']['certificado']))?$datos['factura']['certificado']:null;
            
            if($certificado=='ND' OR $certificado==''){
                $cer=$datos['conf']['cer'];
                $cer=str_replace('\\','/',$cer);
                $certificado=$this->cfd_certificado_pub($cer);
            }
            
            $noSertificadoSAT = $datos['factura']['noCertificadoSAT'];
            $total = (isset($datos['factura']['total']))?$datos['factura']['total']:0.0;
            $total = sprintf('%1.2f', $total);
            
            //TRANSLADOS
            $translados=null;
            $transladostotal=null;
            if($datos['impuestos']['translados']>0){
                $transladostotal=0.00;
                foreach($datos['impuestos']['translados'] AS $id=>$datostranslados){
                    $trasladoimpuesto=$datostranslados['impuesto'];
                    $transladotasa=$datostranslados['tasa'];
                    $transladoimporte=$datostranslados['importe'];
                    $transladoimporte = sprintf('%1.2f', $transladoimporte);
                    $translados.="<cfdi:Traslado impuesto=\"$trasladoimpuesto\" tasa=\"$transladotasa\" importe=\"$transladoimporte\" /> ";
                    $transladostotal=$transladostotal+$transladoimporte;
                }
                $translados="
                    <cfdi:Traslados>
                   $translados   
                    </cfdi:Traslados> ";
            }
            
			//RETENCIONES            
            $retenidos=null;
            $retenidostotal=null;
            if($datos['impuestos']['retenidos']>0){
                $retenidostotal=0.00;
                foreach($datos['impuestos']['retenidos'] AS $id=>$datosretenidos){
                    $retenidoimpuesto=$datosretenidos['impuesto'];
                    $retenidoimporte=$datosretenidos['importe'];
                    $retenidoimporte = sprintf('%1.2f', $retenidoimporte);
                    $retenidos.="<cfdi:Retencion impuesto=\"$retenidoimpuesto\" importe=\"$retenidoimporte\" /> ";
                    $retenidostotal=$retenidostotal+$retenidoimporte;
                }
                $retenidos="
                    <cfdi:Retenciones>
                   $retenidos   
                    </cfdi:Retenciones>";
            }

            $transladostotal = sprintf('%1.2f', $transladostotal);
            $retenidostotal = sprintf('%1.2f', $retenidostotal);
            $impuestosfinal="
                  <cfdi:Impuestos totalImpuestosTrasladados=\"$transladostotal\"  totalImpuestosRetenidos=\"$retenidostotal\" >
                    $retenidos
                    $translados
                  </cfdi:Impuestos>
            ";
			//FACIVA
			$datos['factura']['subtotal']=(isset($datos['factura']['subtotal']))?$datos['factura']['subtotal']:0;
			$datos['factura']['descuento']=(isset($datos['factura']['descuento']))?$datos['factura']['descuento']:0;
			
            $sub_total = $datos['factura']['subtotal'] + $datos['factura']['descuento'];
            $sub_total = sprintf('%1.2f', $sub_total);
            $descuento = 0;
        
            $Forma_de_Pago = (isset($datos['factura']['forma_pago']))?$datos['factura']['forma_pago']:'PAGO EN UNA SOLA EXHIBICION';
        
            $Metodo_Pago = (isset($datos['factura']['metodo_pago']))?$datos['factura']['metodo_pago']:'EFECTIVO';
            $NumCtaPago = (isset($datos['factura']['NumCtaPago']))?$datos['factura']['NumCtaPago']:null;
            $tipocomprobante = (isset($datos['factura']['tipocomprobante']))?$datos['factura']['tipocomprobante']:'ingreso';
            $emisorrfc = (isset($datos['emisor']['rfc']))?$datos['emisor']['rfc']:null;
            $emisorrfc = $this->cfd_formato_rfc($emisorrfc);
            $emisornombre = (isset($datos['emisor']['nombre']))?$datos['emisor']['nombre']:null;
            $emisorcalle = (isset($datos['emisor']['DomicilioFiscal']['calle']))?$datos['emisor']['DomicilioFiscal']['calle']:null;
            $emisornoexterior = (isset($datos['emisor']['DomicilioFiscal']['noExterior']))?$datos['emisor']['DomicilioFiscal']['noExterior']:null;
            $emisornointerior = (isset($datos['emisor']['DomicilioFiscal']['noInterior']))?$datos['emisor']['DomicilioFiscal']['noInterior']:null;
            $emisorcolonia = (isset($datos['emisor']['DomicilioFiscal']['colonia']))?$datos['emisor']['DomicilioFiscal']['colonia']:null;
            $emisorlocalidad = (isset($datos['emisor']['DomicilioFiscal']['localidad']))?$datos['emisor']['DomicilioFiscal']['localidad']:null;
            $emisormunicipio = (isset($datos['emisor']['DomicilioFiscal']['municipio']))?$datos['emisor']['DomicilioFiscal']['municipio']:null;
            if ($emisorlocalidad == 'ND')
				$emisorlocalidad = $emisormunicipio;
            if ($emisormunicipio == 'ND')
                $emisormunicipio = $emisorlocalidad;
            $emisorcodigopostal = (isset($datos['emisor']['DomicilioFiscal']['CodigoPostal']))?$datos['emisor']['DomicilioFiscal']['CodigoPostal']:null;
            $emisorcodigopostal=sprintf('%05d',$emisorcodigopostal);
            $emisorestado = (isset($datos['emisor']['DomicilioFiscal']['estado']))?$datos['emisor']['DomicilioFiscal']['estado']:null;
            $emisorpais = (isset($datos['emisor']['DomicilioFiscal']['pais']))?$datos['emisor']['DomicilioFiscal']['pais']:null;    
            $expedidocalle = (isset($datos['emisor']['ExpedidoEn']['calle']))?$datos['emisor']['ExpedidoEn']['calle']:null;
            $expedidonoexterior = (isset($datos['emisor']['ExpedidoEn']['noExterior']))?$datos['emisor']['ExpedidoEn']['noExterior']:null;
            $expedidonointerior = (isset($datos['emisor']['ExpedidoEn']['noInterior']))?$datos['emisor']['ExpedidoEn']['noInterior']:null;
            $expedidocolonia = (isset($datos['emisor']['ExpedidoEn']['noInterior']))?$datos['emisor']['ExpedidoEn']['noInterior']:null;
            $expedidocolonia = (isset($datos['emisor']['ExpedidoEn']['colonia']))?$datos['emisor']['ExpedidoEn']['colonia']:null;
            $expedidolocalidad = (isset($datos['emisor']['ExpedidoEn']['localidad']))?$datos['emisor']['ExpedidoEn']['localidad']:null;
            $expedidomunicipio = (isset($datos['emisor']['ExpedidoEn']['municipio']))?$datos['emisor']['ExpedidoEn']['municipio']:null;
            if ($expedidolocalidad == 'ND')
                $expedidolocalidad = $expedidomunicipio;
            if ($expedidomunicipio == 'ND')
                $expedidomunicipio = $expedidolocalidad;
            $expedidoestado = (isset($datos['emisor']['ExpedidoEn']['estado']))?$datos['emisor']['ExpedidoEn']['estado']:null;
            $expedidopais = (isset($datos['emisor']['ExpedidoEn']['pais']))?$datos['emisor']['ExpedidoEn']['pais']:null;
            $expedidocodigopostal = (isset($datos['emisor']['ExpedidoEn']['CodigoPostal']))?$datos['emisor']['ExpedidoEn']['CodigoPostal']:null;
            $expedidocodigopostal=sprintf('%05d',$expedidocodigopostal);
            $receptorrfc = (isset($datos['receptor']['rfc']))?$datos['receptor']['rfc']:null;
            $receptorrfc = $this->cfd_formato_rfc($receptorrfc);
            $receptornombre = (isset($datos['receptor']['nombre']))?$datos['receptor']['nombre']:null;
            $receptorcalle = (isset($datos['receptor']['Domicilio']['calle']))?$datos['receptor']['Domicilio']['calle']:null;
            $receptornoexterior = (isset($datos['receptor']['Domicilio']['noExterior']))?$datos['receptor']['Domicilio']['noExterior']:null;
            $receptornointerior = (isset($datos['receptor']['Domicilio']['noInterior']))?$datos['receptor']['Domicilio']['noInterior']:null;  
            $receptorcolonia = (isset($datos['receptor']['Domicilio']['colonia']))?$datos['receptor']['Domicilio']['colonia']:null;
            $receptorlocalidad = (isset($datos['receptor']['Domicilio']['localidad']))?$datos['receptor']['Domicilio']['localidad']:null;
            $receptormunicipio = (isset($datos['receptor']['Domicilio']['municipio']))?$datos['receptor']['Domicilio']['municipio']:null;
            if ($receptorlocalidad == 'ND')
                $receptorlocalidad = $receptormunicipio;
            if ($receptormunicipio == 'ND')
                $receptormunicipio = $receptorlocalidad;
            $receptorestado = (isset($datos['receptor']['Domicilio']['estado']))?$datos['receptor']['Domicilio']['estado']:null;
            $receptorpais = (isset($datos['receptor']['Domicilio']['pais']))?$datos['receptor']['Domicilio']['pais']:null;
            $receptorcodigopostal = (isset($datos['receptor']['Domicilio']['CodigoPostal']))?$datos['receptor']['Domicilio']['CodigoPostal']:null;
            $receptorcodigopostal=sprintf('%05d',$receptorcodigopostal);
			
			if(!isset($datos['nomina'])){
				$nominaxmlns='';
				$nomina='';
				$complementos=array();
			}elseif(count($datos['nomina'])) {
				//NOMINA
				$datos['nomina']=(isset($datos['nomina']))?$datos['nomina']:null;
				$datos['nomina']['datos']=(isset($datos['nomina']['datos']))?$datos['nomina']['datos']:null;
				$datos['nomina']['percepciones']=(isset($datos['nomina']['percepciones']))?$datos['nomina']['percepciones']:null;
				$datos['nomina']['deducciones']=(isset($datos['nomina']['deducciones']))?$datos['nomina']['deducciones']:null;
				$datos['nomina']['incapacidades']=(isset($datos['nomina']['incapacidades']))?$datos['nomina']['incapacidades']:null;
				$datos['nomina']['horasextras']=(isset($datos['nomina']['horasextras']))?$datos['nomina']['horasextras']:null;
				$nomina_txt=null;
				$datos['ImpuestosLocales']=(isset($datos['ImpuestosLocales']))?$datos['ImpuestosLocales']:0;
			
				$nominaxmlns='xmlns:nomina="http://www.sat.gob.mx/nomina"';
				$nomina='';
				$datosnomina=$datos['nomina']['datos'];
				$percepciones = $datos['nomina']['percepciones'];
				$deducciones= $datos['nomina']['deducciones'];
				$incapacidades= $datos['nomina']['incapacidades'];
				$horasextras= $datos['nomina']['horasextras'];
				
				//NOMINA PERCEPCION    
				if(count($percepciones)>0){
				$total_gravado=0.0;
				$total_excento=0.0;
				
					foreach($percepciones AS $id => $percepcion){
						$TipoPercepcion=$percepcion['TipoPercepcion'];
						$Clave=$percepcion['Clave'];
						$Concepto=$percepcion['Concepto'];
						$ImporteGravado=$percepcion['ImporteGravado'];
						$ImporteExento=$percepcion['ImporteExento'];
						$total_gravado+=$ImporteGravado;
						$total_excento+=$ImporteExento;
						$nomina_percepciones.="<nomina:Percepcion TipoPercepcion=\"$TipoPercepcion\" Clave=\"$Clave\" Concepto=\"$Concepto\" ImporteGravado=\"$ImporteGravado\" ImporteExento=\"$ImporteExento\" />";
					}

					$nomina.="
					<nomina:Percepciones TotalGravado=\"$total_gravado\" TotalExento=\"$total_excento\">
					$nomina_percepciones
					</nomina:Percepciones>
					";        
				}
    
				//NOMINA DEDUCCION
				if(count($deducciones)>0){
					$total_gravado=0.0;
					$total_excento=0.0;
					
					foreach($deducciones AS $id => $deduccion){
						$TipoDeduccion=$deduccion['TipoDeduccion'];
						$Clave=$deduccion['Clave'];
						$Concepto=$deduccion['Concepto'];
						$ImporteGravado=$deduccion['ImporteGravado'];
						$ImporteExento=$deduccion['ImporteExento'];
						$total_gravado+=$ImporteGravado;
						$total_excento+=$ImporteExento;
						$nomina_ducciones.="<nomina:Deduccion TipoDeduccion=\"$TipoDeduccion\" Clave=\"$Clave\" Concepto=\"$Concepto\" ImporteGravado=\"$ImporteGravado\" ImporteExento=\"$ImporteExento\" />";
					}
        
					$nomina.="
					<nomina:Deducciones TotalGravado=\"$total_gravado\" TotalExento=\"$total_excento\">
					$nomina_ducciones
					</nomina:Deducciones>
					";
				}

				//NOMINA INCAPACIDADES
				if(count($incapacidades)>0){

					foreach($incapacidades AS $id => $incapacidadx){
						$DiasIncapacidad=$incapacidadx['DiasIncapacidad'];
						$TipoIncapacidad=$incapacidadx['TipoIncapacidad'];
						$Descuento=$incapacidadx['Descuento'];
						$nomina_incapacidades.="<nomina:Incapacidad DiasIncapacidad=\"$DiasIncapacidad\" TipoIncapacidad=\"$TipoIncapacidad\" Descuento=\"$Descuento\"/>";
					}
        
					$nomina.="
					<nomina:Incapacidades>$nomina_incapacidades
					</nomina:Incapacidades>
					";
				}

				//NOMINA HORAS EXTRAS
				if(count($horasextras)>0){

					foreach($horasextras AS $id => $horasextra){
						$Dias=$horasextra['Dias'];
						$TipoHoras=$horasextra['TipoHoras'];
						$HorasExtra=$horasextra['HorasExtra'];
						$ImportePagado=$horasextra['ImportePagado'];

						$nomina_horasextras.="<nomina:HorasExtra Dias=\"$Dias\" TipoHoras=\"$TipoHoras\" HorasExtra=\"$HorasExtra\"   ImportePagado=\"$ImportePagado\" />";
					}

					$nomina.="
					<nomina:HorasExtras>
					$nomina_horasextras
					</nomina:HorasExtras>
					";
				}

				if(count($datosnomina)>0)
					foreach($datosnomina AS $key=>$val) $nomina_txt.=" $key=\"$val\" ";
				
				$datosnomina['Antiguedad']=(isset($datosnomina['Antiguedad']))?$datosnomina['Antiguedad']:0;
				$antiguedad=intval($datosnomina['Antiguedad']);
				if($antiguedad==0){
					$fecha_inicial=(isset($datosnomina['FechaInicioRelLaboral']))?$datosnomina['FechaInicioRelLaboral']:date('Y-m-d');
					$fecha_final=(isset($datosnomina['FechaFinalPago']))?$datosnomina['FechaFinalPago']:date('Y-m-d');
					list($ano1,$mes1,$dia1)=explode('-',$fecha_inicial);
					list($ano2,$mes2,$dia2)=explode('-',$fecha_final);
        
					$tiempo1= mktime (0,0,1,$mes1, $dia1, $ano1);
					$tiempo2= mktime (0,0,1,$mes2, $dia2, $ano2);
					$antiguedad=intval( ($tiempo2-$tiempo1)/(3600*24)   );
					$nomina_txt.=" Antiguedad=\"$antiguedad\" ";
				}
    
				$nomina="
				<nomina:Nomina Version=\"1.1\" $nomina_txt  xsi:schemaLocation=\"http://www.sat.gob.mx/nomina http://www.sat.gob.mx/sitio_internet/cfd/nomina/nomina11.xsd\" >
				$nomina
				</nomina:Nomina>";
				$complementos[]=$nomina;
			} else {
				$complementos=array();
			}
		
			if(isset($datos['ImpuestosLocales'])){
				$localtotalretenciones=$localtotantranslados=0.00;
				$impuestolocal=null;
				//translados
				if(count($datos['ImpuestosLocales']['TrasladosLocales'])>0){      
					foreach($datos['ImpuestosLocales']['TrasladosLocales'] AS $idx=>$transladolocal){
						$ImpLocTrasladado= $transladolocal['ImpLocTrasladado'];
						$TasadeTraslado= $transladolocal['TasadeTraslado'];
						$Importe=$transladolocal['Importe'];
						$localtotantranslados=$localtotantranslados+$Importe;
						$impuestolocal.="<implocal:TrasladosLocales ImpLocTrasladado=\"$ImpLocTrasladado\" TasadeTraslado=\"$TasadeTraslado\" Importe=\"$Importe\" />";
					}
				}
				//retenciones
				if(count($datos['ImpuestosLocales']['RetencionesLocales'])>0){		
					foreach($datos['ImpuestosLocales']['RetencionesLocales'] AS $idx=>$retencionlocal){
						$ImpLocRetenido=$retencionlocal['ImpLocRetenido'];
						$TasadeRetencion=$retencionlocal['TasadeRetencion'];  //varia 2 o 3% segun el tipo de cliente
						$Importe=$retencionlocal['Importe'];
						$localtotalretenciones=$localtotalretenciones+$Importe;
						$impuestolocal.="<implocal:RetencionesLocales ImpLocRetenido=\"$ImpLocRetenido\" TasadeRetencion=\"$TasadeRetencion\" Importe=\"$Importe\" />";
					}
				}
    
				$impuestoslocales_final="
				<implocal:ImpuestosLocales  version=\"1.0\" TotaldeRetenciones=\"$localtotalretenciones\" TotaldeTraslados=\"$localtotantranslados\"  xmlns:implocal=\"http://www.sat.gob.mx/implocal\" xsi:schemaLocation=\"http://www.sat.gob.mx/implocal http://www.sat.gob.mx/sitio_internet/cfd/implocal/implocal.xsd\"  >    
				$impuestolocal
				</implocal:ImpuestosLocales>";
				$complementos[]=$impuestoslocales_final;
			}
			
			if(count($complementos)>0){
				$complemento_final=null;
				foreach($complementos AS $id=>$valorcomplemento)
					$complemento_final.="
					$valorcomplemento
					";

				$complemento="
				<cfdi:Complemento>
				$complemento_final
				</cfdi:Complemento>
				
				";
			} else $complemento=null;
			
			$conceptos = '';
            $total_desgloce = 0.00;
            $conceptos='';
			foreach($datos['conceptos'] as $datos_producto){
				$cantidad = $datos_producto['cantidad'];
				$descripcion = $datos_producto['descripcion'];
				$valorunitario = $datos_producto['valorunitario'];
				$unidad = $datos_producto['unidad'];
				$noidentificacion = $datos_producto['ID'];
				$importe = (isset($datos_producto['importe']))?$datos_producto['importe']:null;
				$predial = (isset($datos_producto['predial']))?$datos_producto['predial']:null;
				
				if(strlen($predial)>4)
					$predial_txt="<cfdi:CuentaPredial numero=\"$predial\"/>";
				else
					$predial_txt='';
            
				$conceptos .= "<cfdi:Concepto cantidad=\"$cantidad\" unidad=\"$unidad\" noIdentificacion=\"$noidentificacion\" descripcion=\"$descripcion\" valorUnitario=\"$valorunitario\" importe=\"$importe\">$predial_txt</cfdi:Concepto>
                ";
				
				if ($cantidad == 0 OR $cantidad=='ND')
					$error_mensaje .= "; CANTIDAD DICE 0 en $noidentificacion $descripcion";
            
				if ($descripcion == '' OR $descripcion == 'ND')
					$error_mensaje .= "; DESCRIPCION EN BLANCO  EN  $noidentificacion $descripcion";
            
				if ($unidad == '' OR $unidad == 'ND')
					$error_mensaje .= "; FALTA UNIDAD DE PRODUCTO EN  $noidentificacion $descripcion";
			}
			
            $descuento = (isset($datos['factura']['descuento']))?$datos['factura']['descuento']:0;
            $descuento = sprintf('%1.2f', $descuento);
            $idempresa = (isset($datosfactura['idempresa']))?$datosfactura['idempresa']:null;
            $regimenfiscal = (isset($datos['factura']['RegimenFiscal']))?$datos['factura']['RegimenFiscal']:null;
            $tipocambio = (isset($datos['factura']['tipocambio']))?$datos['factura']['tipocambio']:1.00;
            $moneda = (isset($datos['factura']['moneda']))?$datos['factura']['moneda']:'MXN';
            if($moneda=='' OR $moneda=='ND')
				$moneda='MXN';
            if($tipocambio=='' OR $tipocambio=='ND')
				$tipocambio='1.00';
            $LugarExpedicion = (isset($datos['factura']['LugarExpedicion']))?$datos['factura']['LugarExpedicion']:null;
        
            if($LugarExpedicion==''){
                if ($expedidolocalidad != $expedidomunicipio)
					$LugarExpedicion = "$expedidomunicipio $expedidolocalidad, $expedidoestado ";
                else
                    $LugarExpedicion = "$expedidolocalidad, $expedidoestado";
            }

            if ($emisornointerior != 'ND' && !empty($emisornointerior))
                $noInterior_emisor = "noInterior=\"$emisornointerior\"";
            else $noInterior_emisor="";
        
            if ($expedidonointerior != 'ND' && !empty($expedidonointerior))
                $noInterior_expedido = "noInterior=\"$expedidonointerior\"";
            else $noInterior_expedido="";
        
            if ($receptornointerior != 'ND' && !empty($receptornointerior))
                $noInterior_receptor = "noInterior=\"$receptornointerior\"";
            else $noInterior_receptor = "";
        
            if (intval($NumCtaPago) > 0){
                $NumCtaPago=sprintf('%04d',$NumCtaPago);
                $NumCtaPago_txt = "NumCtaPago=\"$NumCtaPago\" ";
            } else $NumCtaPago_txt="";
        
            if ($emisornoexterior != 'ND' && !empty($emisornoexterior))
                $noExterior_emisor = "noExterior=\"$emisornoexterior\"";
            else $noExterior_emisor = "";
        
            if ($expedidonoexterior != 'ND' && !empty($expedidonoexterior))
                $noExterior_expedido = "noExterior=\"$expedidonoexterior\"";
            else $noExterior_expedido = "";
        
            if ($receptornoexterior != 'ND' && !empty($receptornoexterior))
                $noExterior_receptor = "noExterior=\"$receptornoexterior\"";
            else $noExterior_receptor = "";
            
            $receptorrfc = strtoupper($receptorrfc);
            if ($receptorrfc == 'XAXX010101000'){
                $xml_receptor = "
                  <cfdi:Receptor
                        rfc=\"$receptorrfc\"
                        nombre=\"$receptornombre\">
                  </cfdi:Receptor>
                  ";
            } else {
                $xml_receptor = "
                  <cfdi:Receptor
                        rfc=\"$receptorrfc\"
                        nombre=\"$receptornombre\">
                    <cfdi:Domicilio
                        calle=\"$receptorcalle\"
                        $noExterior_receptor
                        $noInterior_receptor
                        colonia=\"$receptorcolonia\"
                        localidad=\"$receptorlocalidad\"
                        municipio=\"$receptormunicipio\"
                        estado=\"$receptorestado\"
                        pais=\"$receptorpais\"
                        codigoPostal=\"$receptorcodigopostal\" />
                  </cfdi:Receptor>
                ";
                $xml_receptor = str_replace('\n',' ',$xml_receptor);
            }
            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <cfdi:Comprobante xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
                                  xmlns:cfdi=\"http://www.sat.gob.mx/cfd/3\"
                                  $nominaxmlns
                                  xmlns:tfd=\"http://www.sat.gob.mx/TimbreFiscalDigital\"
                                  xsi:schemaLocation=\"http://www.sat.gob.mx/cfd/3
                                  http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd
                                  http://www.sat.gob.mx/TimbreFiscalDigital
                                  http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd\"
                                  version=\"3.2\"
                                  serie=\"$Factura_Serie\"
                                  folio=\"$Factura_Numero\"
                                  fecha=\"$Factura_Fecha_Expedicion\"
                                  formaDePago=\"$Forma_de_Pago\"
                                  noCertificado=\"$noSertificadoSAT\"
                                  certificado=\"$certificado\"
                                  subTotal=\"$sub_total\"
                                  descuento=\"$descuento\"
                                  total=\"$total\"
                                  metodoDePago=\"$Metodo_Pago\"
                                  tipoDeComprobante=\"$tipocomprobante\"
                                  TipoCambio=\"$tipocambio\"
                                  Moneda=\"$moneda\"
                                  LugarExpedicion=\"$LugarExpedicion\"
                                  $NumCtaPago_txt  >
        
                  <cfdi:Emisor
                         rfc=\"$emisorrfc\"
                         nombre=\"$emisornombre\">
                    <cfdi:DomicilioFiscal
                                calle=\"$emisorcalle\"
                                $noExterior_emisor
                                $noInterior_emisor
                                colonia=\"$emisorcolonia\"
                                localidad=\"$emisorlocalidad\"
                                municipio=\"$emisormunicipio\"
                                estado=\"$emisorestado\"
                                pais=\"$emisorpais\"
                                codigoPostal=\"$emisorcodigopostal\" />
                    <cfdi:ExpedidoEn
                                calle=\"$expedidocalle\"
                                $noExterior_expedido 
                                $noInterior_expedido
                                colonia=\"$expedidocolonia\"
                                localidad=\"$expedidolocalidad\"
                                municipio=\"$expedidomunicipio\"
                                estado=\"$expedidoestado\"
                                pais=\"$expedidopais\"
                                codigoPostal=\"$expedidocodigopostal\" />
                    <cfdi:RegimenFiscal Regimen=\"$regimenfiscal\" />
                  </cfdi:Emisor>
                $xml_receptor
                  <cfdi:Conceptos>
		$conceptos            
                  </cfdi:Conceptos>
                  $impuestosfinal
                  $complemento
                </cfdi:Comprobante>
			";
			//aki mash
            $xmlutf8 = utf8_encode($xml);
            
			/// AGREGAR SELLO
			// GENERAR CADENA
            @mkdir($ruta.'tmp');
            @chmod($ruta.'tmp',0777);
            //nombre temporal del archivo xml a generar la cadena
            $file_target = $ruta.'tmp/'.time() . rand() . '.xml';
			
            @unlink($file_target);
            if (file_exists($file_target)) @chmod($file_target, 0777);
            // add write permission
            if (($wh = fopen($file_target, 'wb')) === false) return false;
            // error messages.
            if (fwrite($wh, $xmlutf8) === false) {
                fclose($wh);
                return false;
            }
            fclose($wh);
            @chmod($file_target, 0777);
			
			$xsl_file = $ruta.'xslt/cadenaoriginal_3_2.xslt';
			$xmlDoc = new DomDocument();
			$xmlDoc->load($file_target);
			$xslDoc = new DomDocument();
			$xslDoc->load($xsl_file);
			
			if (!class_exists('XsltProcessor')) die("No tiene habilitado XSLT");
			$xslt = new XsltProcessor();
			@$xslt->importStylesheet($xslDoc);
			@$xslt->setParameter(NULL, (string) $name, (string) $value);
			$cadenaoriginal = @$xslt->transformToXml($xmlDoc); // returns string
			
            //$comando_cadenaoriginal = "xsltproc  $xsl \"$file_target\"";
            //$comando_cadenaoriginal=str_replace('\\','/',$comando_cadenaoriginal);
            //$comando_cadenaoriginal=str_replace('///','/',$comando_cadenaoriginal);
            //$cadenaoriginal = shell_exec($comando_cadenaoriginal);
            
            if(strlen($cadenaoriginal)<10){
                unset($res);
                $res['pac']=0;
                $res['produccion']=$produccion;
                $res['codigo_mf_numero']=7;
                $res['codigo_mf_texto']='ERROR AL GENERAR CADENA ORIGINAL: XML MAL GENERADO O FALTA XSLTPROC';
                $res['cancelada']=1;
                $res['servidor']=0;
                return $res;
            }
			//Genera sello
			$sello = $this->cfd_genera_sello($cadenaoriginal,$datos);
            if(strlen($sello)<30){
                unset($res);
                $res['pac']=0;
                $res['produccion']=$produccion;
                $res['codigo_mf_numero']=7;
                $res['codigo_mf_texto']='ERROR AL GENERAR EL SELLO, REVISA LOS DATOS DE TU CERTIFICADO CSD';
                $res['cancelada']=1;
                $res['servidor']=0;
                return $res;
            }
            @unlink($file_target); // elimina XML temporal para generar la cadena
			
            $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                <cfdi:Comprobante xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
                                  xmlns:cfdi=\"http://www.sat.gob.mx/cfd/3\"
                                  $nominaxmlns
                                  xmlns:tfd=\"http://www.sat.gob.mx/TimbreFiscalDigital\"
                                  xsi:schemaLocation=\"http://www.sat.gob.mx/cfd/3
                                  http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd
                                  http://www.sat.gob.mx/TimbreFiscalDigital
                                  http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd\"
                                  version=\"3.2\"
                                  sello=\"$sello\"
                                  serie=\"$Factura_Serie\"
                                  folio=\"$Factura_Numero\"
                                  fecha=\"$Factura_Fecha_Expedicion\"
                                  formaDePago=\"$Forma_de_Pago\"
                                  noCertificado=\"$noSertificadoSAT\"
                                  certificado=\"$certificado\"
                                  subTotal=\"$sub_total\"
                                  descuento=\"$descuento\"
                                  total=\"$total\"
                                  metodoDePago=\"$Metodo_Pago\"
                                  tipoDeComprobante=\"$tipocomprobante\"
                                  TipoCambio=\"$tipocambio\"
                                  Moneda=\"$moneda\"
                                  LugarExpedicion=\"$LugarExpedicion\"
                                  $NumCtaPago_txt  >
        
                  <cfdi:Emisor
                         rfc=\"$emisorrfc\"
                         nombre=\"$emisornombre\">
                    <cfdi:DomicilioFiscal
                                calle=\"$emisorcalle\"
                                $noExterior_emisor
                                $noInterior_emisor
                                colonia=\"$emisorcolonia\"
                                localidad=\"$emisorlocalidad\"
                                municipio=\"$emisormunicipio\"
                                estado=\"$emisorestado\"
                                pais=\"$emisorpais\"
                                codigoPostal=\"$emisorcodigopostal\" />
                    <cfdi:ExpedidoEn
                                calle=\"$expedidocalle\"
                                $noExterior_expedido 
                                $noInterior_expedido
                                colonia=\"$expedidocolonia\"
                                localidad=\"$expedidolocalidad\"
                                municipio=\"$expedidomunicipio\"
                                estado=\"$expedidoestado\"
                                pais=\"$expedidopais\"
                                codigoPostal=\"$expedidocodigopostal\" />
                    <cfdi:RegimenFiscal Regimen=\"$regimenfiscal\" />
                  </cfdi:Emisor>
                $xml_receptor
                  <cfdi:Conceptos>
				$conceptos            
                  </cfdi:Conceptos>
                  $impuestosfinal
                  $complemento
                </cfdi:Comprobante>";
			/////////  END XML ///////////
			//guardamos en xml debug
			$file_target=$datos['xml_debug'];
			$tmpx=(string)$xml;
			
			if(strlen($tmpx)>10){
				if($file_target!=''){
					@unlink($file_target);
					if (file_exists($file_target)) @chmod($file_target, 0777);
					// add write permission
					if (($wh = fopen($file_target, 'wb')) === false) return "ERROR ESCRITURA EN  $file_target";
					// error messages.
					if (fwrite($wh,utf8_encode((string)$xml)) === false) {
						fclose($wh);
						return "ERROR ESCRITURA EN  $file_target";
					}
					fclose($wh);
					@chmod($file_target, 0777);
					$valor=array(
						'return'=>true,
						'xml'=>utf8_encode((string)$xml),
						'path'=>$file_target
					);
				}
			}
		//}//fin solo sellar  

		return $valor;
	}
	/////////////////////////////////////////////////////////////////////////////////
	function cfd_formato_rfc($rfc){
		$rfc = strtoupper($rfc);
		$rfc = str_replace(' ', '', $rfc);
		$rfc = str_replace(' ', '', $rfc);
		$rfc = str_replace(' ', '', $rfc);
		$rfc = str_replace('-', '', $rfc);
		$rfc = str_replace('/', '', $rfc);
		$rfc = str_replace('.', '', $rfc);
		$rfc=$this->cfd_fix_dato_xml($rfc);
		return $rfc;
	}
	/////////////////////////////////////////////////////////////////////////////////
	function cfdi_cancelar($uuid=''){
		$datos = $this->datos;
		$ruta = $datos['SDK']['ruta'];
		
		if(!class_exists('nusoap_client')){
			if(function_exists('libreria_mash')) libreria_mash('nusoap');
			else include $ruta."lib/nusoap/nusoap.php";
		}

		$produccion=$datos['PAC']['produccion'];
		if($produccion=='SI')
			$soapclient = new nusoap_client('https://www.sefactura.com.mx/sefacturapac/TimbradoService?wsdl',$esWSDL=true);
		else
			$soapclient = new nusoap_client('http://www.jonima.com.mx:3014/sefacturapac/TimbradoService?wsdl',$esWSDL = true);
		
		if(empty($uuid)){
			$xml = simplexml_load_file($datos['cfdi']);
			$ns = $xml->getNamespaces(true);
			$xml->registerXPathNamespace('c', $ns['cfdi']);
			$xml->registerXPathNamespace('t', $ns['tfd']);
			foreach ($xml->xpath('//t:TimbreFiscalDigital') as $tfd) $uuid=$tfd['UUID'];
		}
		
		$cer= $datos['conf']['cer'];
		$key= $datos['conf']['key'];
		$pass_key=$datos['conf']['pass'];
		$cer_txt=base64_encode(file_get_contents($datos['conf']['cer']));
		$key_txt=base64_encode(file_get_contents($datos['conf']['key']));
		
		//creamos arreglo con parametos de Solicitud de Cancelación
		$sol = array('certificado' => $cer_txt, 'llavePrivada' => $key_txt, 'password' =>
        $pass_key, 'uuid' => $uuid);
		
		//Parametros para cancelar
		$usuario = $datos['PAC']['usuario'];
		$clave = $datos['PAC']['pass'];
		
		//Creamos arreglo con parametros para llamar el servicio de cancelación
		$can = array('solicitud' => $sol, 'usuario' => $usuario, 'clave' => $clave);
		
		//Llamamos al servicio de Cancelacion asignandole los parametros correspondientes
		$soap_cancelacion = $soapclient->call('cancelacion', $can);

		if ($soap_cancelacion == false) return array("status"=>"ERROR DE COMUNICACION");
		return $soap_cancelacion;
	}
	/////////////////////////////////////////////////////////////////////////////////
	//ELIMINA CARACTERES HTML Y JAVA SCRIPT
	function cfd_fix_dato_xml_html_txt($dato){
		$dato=_html_txt($dato);
		return $dato;
	}
	/////////////////////////////////////////////////////////////////////////////////
	// ELIMINA CARACTERES INVALIDOS Y DA FORMATO A UN DATO SEGUN EL ANEXO 20
	function cfd_fix_dato_xml_acentos($dato){
		$dato=$this->acentos_remueve_multifacturas($dato);
		return $this->cfd_fix_dato_xml($dato);
	}
	/////////////////////////////////////////////////////////////////////////////////
	function cfd_fix_dato_xml($dato){
		$dato=trim($dato);
		$dato = str_replace("\r", ' ', $dato);
		$dato = str_replace("\n", ' ', $dato);
		$dato = str_replace("\t", ' ', $dato);
		$dato = str_replace('|', '', $dato);
		$dato = str_replace('  ', ' ', $dato);
		$dato = str_replace('&', '&amp;', $dato);
		$dato = str_replace('"', '&quot;', $dato);
		$dato = str_replace('<', '&lt;', $dato);
		$dato = str_replace('>', '&gt;', $dato);
		$dato = str_replace("'", '&apos;', $dato);
		if($dato=='') $dato='ND';
		return $dato;
	}
	/////////////////////////////////////////////////////////////////////////////////
	function cfd_genera_sello($cadena,$datos){
		$ruta=$datos['SDK']['ruta'];
		$linea_comandos=true;
		@mkdir($ruta.'tmp');
		@chmod($ruta.'tmp',0777);
		
		$key=$datos['conf']['key'];
		$key_pem=str_replace('\\','/',$key);
		$pkeyid = openssl_get_privatekey(file_get_contents($key_pem));
		if(openssl_sign($cadena, $cadena_generada, $pkeyid, OPENSSL_ALGO_SHA1))
			openssl_free_key($pkeyid);
		$sello = base64_encode($cadena_generada);
		return (string) $sello;
	}
	/////////////////////////////////////////////////////////////////////////////////
	function certificado_pem($datos){
		$cer=$datos['SDK']['ruta'].$datos['conf']['cer'];
		$key=$datos['SDK']['ruta'].$datos['conf']['key'];
		$pass=$datos['conf']['pass'];
		$cer=str_replace('\\','/',$cer);
		$key=str_replace('\\','/',$key);
		if(file_exists($cer))
			@unlink("$cer");
		if(file_exists($key))
			@unlink("$key");
		$cer=str_replace('.pem','',$cer);
		$key=str_replace('.pem','',$key);
		//genera PEM privado
		$comando="openssl pkcs8 -inform DER -in $key -out $key.pem -passin pass:$pass";
		$resultado=shell_exec($comando);
		//genera PEM publico
		$comando="openssl x509 -inform DER -outform PEM -in $cer -pubkey >$cer.pem";
		$resultado=shell_exec($comando);
		//LEE Certificado
		//datos generales
		$comando="openssl x509 -in $cer.pem -issuer -noout";
		$resultado1=shell_exec($comando);
		//fecha valides
		$comando="openssl x509 -in $cer.pem -startdate -enddate -noout";
		$resultado2=shell_exec($comando);
		// serie matriz
		$comando="openssl x509 -in $cer.pem -subject -noout";
		$resultado3=shell_exec($comando);
		//serie
		$comando="openssl x509 -in $cer.pem  -serial -noout";
		$resultado4=shell_exec($comando);
		$resultado="$resultado1
		$resultado2
		$resultado3
		$resultado4";
		if(filesize("$key.pem")<10) unlink("$key.pem");
		if(filesize("$cer.pem")<10) unlink("$cer.pem");
		$resultado=str_replace('\x','%',$resultado);
		$resultado=rawurldecode($resultado);
		
		list($fecha_inicial_tmp,$fecha_final_tmp)=explode("\n",$resultado2);
		list($tmp,$fecha_inicial_txt)=explode('=',$fecha_inicial_tmp);
		list($tmp,$fecha_final_txt)=explode('=',$fecha_final_tmp);
		$fecha_inicial_time=$this->hora_txt_time($fecha_inicial_txt);
		$fecha_final_time=$this->hora_txt_time($fecha_final_txt);
		list($tmp,$tmp,$rfc)=explode('/',$resultado3);
		$rfc=str_replace(' ','',$rfc);
		list($tmp,$serial)=explode('=',$resultado4);
		$serial=str_replace(' ','',$serial);
		$cnt=strlen($serial);
		$s=1;
		$no_serie=null;
		for($i=0;$i<$cnt;$i++){
			if($s==0){
				$no_serie="$no_serie".$serial[$i];
				$s=1;
			}else $s=0;
		}
		$serial=$no_serie;
		$cer_txt=$this->cfd_certificado_pub("$cer.pem");
		$res['certificado_pem_txt']=$cer_txt;
		$res['certificado_pem']="$cer.pem";
		$res['key_pem']="$key.pem";
		$res['certificado_info']=$resultado;
		$res['fecha_valido_inicia']=$fecha_inicial_time;
		$res['fecha_valido_fin']=$fecha_final_time;
		$res['certificado_no_serie']=$serial;
		return $res;
	}
	////////////////////////////////////////////////////////////////////////////////
	function hora_txt_time($hora_txt){
		//$hora="Aug 21 15:22:08 2008";
		//$hora="Aug 21 15:22:08 2010 GMT";
        $segundos=strtotime($hora_txt);
        return $segundos;
	}
	/////////////////////////////////////////////////////////////////////////////////
	function key_certificado_txt($archivo_key,$cancelacion='NO'){
		$file=$archivo_key;
		$datos = file($file);
		$certificado = ''; $carga=false;

		for ($i=0; $i<sizeof($datos); $i++){
			if (strstr($datos[$i],"PRIVATE")) $carga=false;

			if ($carga) {
				$certificado.=trim($datos[$i]);
				if($certificado!='NO') $certificado.="\n";
			}
			$carga=true;
		}
		$certificado=str_replace(' ','',$certificado);
		return $certificado;
	}
	////////////////////////////////////////////////////////////////////////////////
	function cfd_certificado_pub($cer,$cancelacion='NO'){
		$file="$cer";
		$datos = file($file);
		$certificado = "";
		$carga=false;
		for ($i=0; $i<sizeof($datos); $i++) {
			if (strstr($datos[$i],"END CERTIFICATE")) $carga=false;
			if ($carga){
				$certificado .= trim($datos[$i]);
				if($cancelacion!='NO') $certificado.="\n";
			}
			if (strstr($datos[$i],"BEGIN CERTIFICATE")) $carga=true;
		}
		$certificado=str_replace(' ','',$certificado);
		return $certificado;
	}
	////////////////////////////////////////////////////////////////////////////////
	function acentos_remueve_multifacturas($str){
		$orig=$str;
		$strmd5=md5($str);
		
		//CACHE        
		$cache_nombre="acentos_remueve_mash:$strmd5";
		static $acentos_remueve_mash_cache_static;
		if(isset($acentos_remueve_mash_cache_static[$cache_nombre]))
			return $acentos_remueve_mash_cache_static[$cache_nombre];
		
		if(function_exists('apc_fetch')){
			$resultado = apc_fetch($cache_nombre);
			$acentos_remueve_mash_cache_static[$cache_nombre]=$resultado;
		} else $resultado=null;
    
		if ($resultado==''){ 
			$text=str_replace('&aacute;','á',$str);
			$text=str_replace('&eacute;','é',$text);
			$text=str_replace('&iacute;','í',$text);
			$text=str_replace('&oacute;','ó',$text);
			$text=str_replace('&uacute;','ú',$text);
			$text=str_replace('&Aacute;','Á',$text);
			$text=str_replace('&Eacute;','É',$text);
			$text=str_replace('&Iacute;','Í',$text);
			$text=str_replace('&Oacute;','Ó',$text);
			$text=str_replace('&Uacute;','Ú',$text);
			$text=str_replace('&aacute;','á',$text);
			$text=str_replace('&eacute;','é',$text);
			$text=str_replace('&iacute;','í',$text);
			$text=str_replace('&oacute;','ó',$text);
			$text=str_replace('&uacute;','ú',$text);
			$text=str_replace('&auml;','Á',$text);
			$text=str_replace('&euml;','É',$text);
			$text=str_replace('&iuml;','Í',$text);
			$text=str_replace('&ouml;','Ó',$text);
			$text=str_replace('&uuml;','Ú',$text); 
			$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï',  'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö',  'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü');
			$b = array('A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I',  'N', 'O', 'O', 'O', 'O', 'O',  'U', 'U', 'U', 'U', 'Y', 'a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u');
			$text=str_replace($a, $b, $text);
			$text=str_replace('&ntilde;','ñ',$text);
			$resultado=str_replace('&Ntilde;','Ñ',$text);

			$acentos_remueve_mash_cache_static[$cache_nombre]=$resultado;
			if(function_exists('apc_add')){
				$tiempo_cache=3600;
				apc_add($cache_nombre, $resultado, $tiempo_cache*48);//24hrs de cache
			}
		}
		return $resultado;
	}
	////////////////////////////////////////////////////////////////////////////////
	function _html_txt($html){
		if($html=='') return $html;
        
		$htmlmd5=md5($html);
		//CACHE        
		$cache_nombre="html_txt:$htmlmd5";
		$cache_nombre_global="html_txt_global";
    
		static $html_txt_cache_static;
		if(isset($html_txt_cache_static[$cache_nombre]))
			return $html_txt_cache_static[$cache_nombre];
    
		if(function_exists('apc_fetch')){
			$text = apc_fetch($cache_nombre);
			$html_txt_cache_static[$cache_nombre]=$text;
		}

		if ($text=='') { 
			$html=strip_tags($html);
			$buscar = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                       '@<[\\/\\!]*?[^<>]*?>@si',            // Strip out HTML tags
                       '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
                       '@<![\\s\\S]*?--[ \\t\\n\\r]*>@'          // Strip multi-line comments including CDATA
			);
			$text = preg_replace($buscar, '', $html);
			$a = array('&nbsp;', '   ', '  ', '  ', '\n\n', '\r\r', '&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;', '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;',  '&Uacute;', '&auml;', '&euml;', '&iuml;', '&ouml;', '&uuml;',  '&ntilde;', '&Ntilde;');
			$b = array(' ', ' ', ' ', ' ', '\n',              '\r', 'á', 'é', 'í', 'ó', 'ú', 'A', 'E', 'I', 'O',                                                                    'U',        'A', 'E', 'I', 'O',             'U',        'ñ', 'Ñ');
			$text=str_replace($a, $b, $text);

			if(function_exists('apc_add')){
				if($html!=''){
					$tiempo_cache=3600;
					$html_txt_cache_static[$cache_nombre]=$text;
					apc_add($cache_nombre, $text, $tiempo_cache*1);//1hr
				}            
			}
		}
		return $text;
	}
	////////////////////////////////////////////////////////////////////////////////
	function ini_to_array_cfdi($ini_string){
		$renglones=explode("\n",$ini_string);
		$posicion='';
		foreach($renglones AS $renglon){
			//detecta posicion arreglo
			list($x1,$x2)=explode('[',$renglon);
			if($x2!=''){
				//nueva posicion
				$posicion=str_replace(']','',$x2); 
			}else{
                list($nombre,$valor)=explode('=',$renglon);
                list($tmp1,$tmp2,$tmp3)=explode('.',$posicion);
                $tmp1= str_replace("\n",'',$tmp1);
                $tmp2= str_replace("\n",'',$tmp2);
                $tmp3= str_replace("\n",'',$tmp3);
                $tmp1= str_replace("\r",'',$tmp1);
                $tmp2= str_replace("\r",'',$tmp2);
                $tmp3= str_replace("\r",'',$tmp3);
                $tmp1= str_replace("\t",'',$tmp1);
                $tmp2= str_replace("\t",'',$tmp2);
                $tmp3= str_replace("\t",'',$tmp3);
                $valor= str_replace("\n",'',$valor);
                $valor= str_replace("\r",'',$valor);
                if($tmp1!='' AND $tmp2!=''  AND $tmp3!='' ){
                    if($nombre!='') 
						$arreglo[$tmp1][$tmp2][$tmp3][$nombre]=$valor;
                }
                elseif($tmp1!='' AND $tmp2!='' ){
                    if($nombre!='')
						$arreglo[$tmp1][$tmp2][$nombre]=$valor;
                }
                elseif($tmp1!='' ){
                    if($nombre!='')
                        $arreglo[$tmp1][$nombre]=$valor;
                }
                elseif($tmp1=='' ){
                    if($nombre!='')
                        $arreglo[$nombre]=$valor;
                }        
			}
		}
		return $arreglo;
	}
	////////////////////////////////////////////////////////////////////////////////
	function arr2ini(array $a, array $parent = array()){
		$out='';
		foreach ($a as $k => $v){
			if (is_array($v)){
				//subsection case
				//merge all the sections into one array...
				$sec = array_merge((array) $parent, (array) $k);
				//add section information to the output
				$out .= '[' . join('.', $sec) . ']' . PHP_EOL;
				//recursively traverse deeper
				$out .= arr2ini($v, $sec);
			}else{
				//plain key->value case
				$out .= "$k=$v" . PHP_EOL;
			}
		}
		return $out;
	}
	//////////////////////////////////////////////////////////////////////
	function cfdi_timbrar($xml){
		$datos=$this->datos;
		$cfdi = utf8_encode((string)$xml);
		if(!class_exists('nusoap_client')){
			if(function_exists('libreria_mash')){
				libreria_mash('nusoap');
			} else {
				include $datos['SDK']['ruta']."lib/nusoap/nusoap.php";
			}
		}
		$usuario = $datos['PAC']['usuario'];
		$clave   = $datos['PAC']['pass'];
		$produccion=$datos['PAC']['produccion'];
		$pac=rand(1,10);
		$soapclient = new nusoap_client("http://pac$pac.multifacturas.com/pac/?wsdl",$esWSDL = true);
		//Generamos el arreglo con los parametros para timbrado
		$tim = array('rfc' => $usuario, 'clave' => $clave,'xml' => $cfdi,'produccion' => $produccion);
		//Generamos el llamado al servicio de timbrado
		$soap_timbrado = $soapclient->call('timbrar', $tim);
		$valor=$soap_timbrado;
		if ($soap_timbrado == false){
			//ERROR TIMEOUT
			$status = "12345 - TimeOut Falla de Conexion SERVIDOR PAC$pac.MULTIFACTURAS.COM";
			list($codigo_numero, $codigo_txt) = explode('-', $status);
			$codigo_numero = intval(trim($codigo_numero));
		} else {
			$timbre = $soap_timbrado['cfdi'];
			$codigo = $soap_timbrado['png'];
			$codigo_numero = $soap_timbrado['codigo_mf_numero'];
			$codigo_txt = $soap_timbrado['codigo_mf_texto'];

			//elimina definiciones escritas 2 veces
			$original = 'xsi:schemaLocation="http://www.sat.gob.mx/cfd/3                           http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd                           http://www.sat.gob.mx/TimbreFiscalDigital                           http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd"';
			$nuevo = 'xsi:schemaLocation="http://www.sat.gob.mx/cfd/3 http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd http://www.sat.gob.mx/TimbreFiscalDigital http://www.sat.gob.mx/sitio_internet/TimbreFiscalDigital/TimbreFiscalDigital.xsd"';
			$timbre = str_replace($original, $nuevo, $timbre);

			if($codigo_numero==0){
				$datos['cfdi'].=$soap_timbrado['uuid'].".xml";
				$ruta_xml = $datos['cfdi'];
				if($ruta_xml==''){
					$ruta_xml=time().'.xml';
				}
				
				//almacena xml
				$timbreutf8 = utf8_encode($timbre);
				$file_target = $ruta_xml;
				if(strlen($timbreutf8)>10){
					@unlink($file_target);
					if (file_exists($file_target)) {
						@chmod($file_target, 0777);
					} // add write permission
					if (($wh = fopen($file_target, 'wb')) === false) {
						return "ERROR ESCRITURA EN  $ruta_xml";
					} // error messages.
					if (fwrite($wh, $timbreutf8) === false) {
						fclose($wh);
						return "ERROR ESCRITURA EN  $ruta_xml";
					}
					fclose($wh);
					@chmod($file_target, 0777);
                }
				//almacena PNG
				if(strlen($codigo)>10){
					$ruta_png = str_replace('xml', 'png', $ruta_xml);
					$ruta_png = str_replace('XML', 'png', $ruta_png);
					$file_target = $ruta_png;
					@unlink($file_target);
					if (file_exists($file_target)) {
						@chmod($file_target, 0777);
					} 
					if (($wh = fopen($file_target, 'wb')) === false) {
						return "ERROR ESCRITURA EN  $ruta_png";
					} 
					if (fwrite($wh, base64_decode($codigo)) === false) {
						fclose($wh);
						return "ERROR ESCRITURA EN  $ruta_png";
					}
					fclose($wh);
					@chmod($file_target, 0777);
				}
				@unlink($datos['xml_debug']);
				//UUID
				if(file_exists($datos['cfdi'])){
					$xml = simplexml_load_file($datos['cfdi']);
					$ns = $xml->getNamespaces(true);
					$xml->registerXPathNamespace('c', $ns['cfdi']);
					$xml->registerXPathNamespace('t', $ns['tfd']);
					foreach ($xml->xpath('//t:TimbreFiscalDigital') as $tfd){
						$uuid=$tfd['UUID'];
					}
				} else $valor['uuid']=null;
				if($valor['uuid']!=''){
					$valor['archivo_png'] = $ruta_png;
					$valor['archivo_xml'] = $ruta_xml;
				}
			}
		}
		return $valor;
	}
	private function fileToDOMDoc($filename) {
		$dom= new DOMDocument;
		$xmldata = file_get_contents($filename);
		$xmldata = str_replace("&", "&amp;", $xmldata);  // disguise &s going IN to loadXML()
		$dom->substituteEntities = true;  // collapse &s going OUT to transformToXML()
		$dom->loadXML($xmldata);
		return $dom;
	} 
}

