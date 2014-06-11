<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gastos extends CI_Controller {
	//array to translate the search type
	private $ops = array(
		'eq'=>'=', //equal
		'ne'=>'<>',//not equal
		'lt'=>'<', //less than
		'le'=>'<=',//less than or equal
		'gt'=>'>', //greater than
		'ge'=>'>=',//greater than or equal
		'bw'=>'LIKE', //begins with
		'bn'=>'NOT LIKE', //doesn't begin with
		'in'=>'LIKE', //is in
		'ni'=>'NOT LIKE', //is not in
		'ew'=>'LIKE', //ends with
		'en'=>'NOT LIKE', //doesn't end with
		'cn'=>'LIKE', // contains
		'nc'=>'NOT LIKE'  //doesn't contain
	);
	private function getWhereClause($col, $oper, $val){
        $ops = $this->ops;
        if($oper == 'bw' || $oper == 'bn') $val .= '%';
        if($oper == 'ew' || $oper == 'en' ) $val = '%'.$val;
        if($oper == 'cn' || $oper == 'nc' || $oper == 'in' || $oper == 'ni') $val = '%'.$val.'%';
        return " WHERE $col {$ops[$oper]} '$val'";
    }
    function __construct(){
		parent::__construct();
		$this->plantillas->is_session();
	}
	//Pagina principal
	public function index(){
		$data['top']['title']='Gastos';
		$data['top']['cssf'][]['href']=base_url('lib/css/view/gastos/home.css');
		$data['top']['scripts'][]['src']=base_url('lib/js/view/gastos/home.js');
		$data['top']['scripts'][]['src']=base_url('lib/js/jquery.html5form-1.5-min.js');
		
		$data['mes']=date('m');
		$data['anio']=date('Y');
		$data['start'] = "2014";
		$more = strtotime("+1 year", time());
		$data['end'] = date("Y", $more);
		$this->plantillas->show_tpl('gastos/home',$data);
	}
	//Muestra detalles de una entrada
	public function showRow(){
		$data['id']=$this->input->post('id');
		$data['fecha']=$this->input->post('fecha');
		if(empty($data['id']) || empty($data['fecha'])){
			 $this->plantillas->set_message(102,"Error: no se enviaron parametros.");
			 redirect('gastos', 'refresh');
		 }
		
		$t = strtotime($data['fecha']);
		$mes= date('m', $t);
		$anio= date('Y', $t);
		
		$vars = array('anio'=>$anio);
		$this->load->library('mydb',$vars);
		$db = $this->mydb;
		$result = $db->query("SELECT * FROM gastos WHERE uuid='{$data['id']}'");
		$data['rowdb']=$result->fetchArray(SQLITE3_ASSOC);
		$filexml = $_SERVER['DOCUMENT_ROOT']."/files/$anio/$mes/gastos/{$data['id']}.xml";
		$xml = simplexml_load_file($filexml);
		
		$data['Comprobante']=$xml->xpath('//cfdi:Comprobante');
		$data['Comprobante']['Emisor']=$xml->xpath('//cfdi:Comprobante//cfdi:Emisor');
		$data['Comprobante']['Emisor']['DomicilioFiscal']=$xml->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal');
		$data['Comprobante']['Emisor']['ExpedidoEn']=$xml->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:ExpedidoEn');
		$data['Comprobante']['Emisor']['RegimenFiscal']=$xml->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:RegimenFiscal');
		$data['Comprobante']['Receptor']=$xml->xpath('//cfdi:Comprobante//cfdi:Receptor');
		$data['Comprobante']['Receptor']['Domicilio']=$xml->xpath('//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio');
		$data['Comprobante']['Conceptos']=$xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto');
		$data['Comprobante']['Impuestos']['Traslados']=$xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado');
		$data['Comprobante']['Impuestos']['Retenciones']=$xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion');
		
		$result = $db->query("SELECT COUNT(*) AS count FROM contactos WHERE rfc='{$data['Comprobante']['Emisor'][0]['rfc']}'");
		$data['top']['reg']=$result->fetchArray(SQLITE3_ASSOC);
		$data['top']['title']=$data['Comprobante']['Emisor'][0]['nombre'];
		$data['top']['cssf'][]['href']=base_url('lib/css/view/gastos/row.css');
		$data['top']['scripts'][]['src']=base_url('lib/js/view/gastos/row.js');
		$data['top']['scripts'][]['src']=base_url('lib/js/jquery.html5form-1.5-min.js');
		$this->plantillas->show_tpl('gastos/row',$data);
	}
	//Descarga un registro en PDF
	public function toPdf($file=''){
		$id=$this->input->post('id');
		$mes=$this->input->post('mes');
		$anio=$this->input->post('anio');
		if(empty($id) || empty($mes) || empty($anio)) die('Parametros no enviados');
		if(empty($file))$file=$_SERVER['DOCUMENT_ROOT']."/files/$anio/$mes/gastos/".$id.".pdf";
		
		$filexml = $_SERVER['DOCUMENT_ROOT']."/files/$anio/$mes/gastos/$id.xml";
		$xml = simplexml_load_file($filexml);
		$Comprobante=$xml->xpath('//cfdi:Comprobante');
		$Comprobante['Emisor']=$xml->xpath('//cfdi:Comprobante//cfdi:Emisor');
		$Comprobante['Emisor']['DomicilioFiscal']=$xml->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:DomicilioFiscal');
		$Comprobante['Emisor']['ExpedidoEn']=$xml->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:ExpedidoEn');
		$Comprobante['Emisor']['RegimenFiscal']=$xml->xpath('//cfdi:Comprobante//cfdi:Emisor//cfdi:RegimenFiscal');
		$Comprobante['Receptor']=$xml->xpath('//cfdi:Comprobante//cfdi:Receptor');
		$Comprobante['Receptor']['Domicilio']=$xml->xpath('//cfdi:Comprobante//cfdi:Receptor//cfdi:Domicilio');
		$Comprobante['Conceptos']=$xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto');
		$Comprobante['Impuestos']['Traslados']=$xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado');
		$Comprobante['Impuestos']['Retenciones']=$xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion');
		
        $this->load->library('mypdf');
        $this->mypdf->AddPage();
        $this->mypdf->AliasNbPages();
        
        //Title
        $sf=(empty($Comprobante[0]['serie']))?"-":$Comprobante[0]['serie']." ";
		$sf.=(empty($Comprobante[0]['folio']))?"-":$Comprobante[0]['folio'];
        $this->mypdf->SetFont('Arial','B',14);
        $this->mypdf->Cell(0,0,'Representacion impresa de XML');
        $this->mypdf->Ln(5);
        $this->mypdf->SetFont('Arial','',10);
        $this->mypdf->Cell(0,0,'Serie y folio: '.$sf);
		$this->mypdf->SetFillColor(189,63,9);
		$this->mypdf->SetDrawColor(189,63,9);
		$this->mypdf->SetLineWidth(.3);
		$this->mypdf->Ln(10);
		
        $this->mypdf->SetFont('Arial','B',12);
        $this->mypdf->Cell(0,0,"Resumen");
        $this->mypdf->Ln(3);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','',10);
        $this->mypdf->Cell(12,7,'UUID','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(75,7,strtoupper($id),'TBLR',0,'L',0);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
        $this->mypdf->Cell(13,7,'Fecha','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $fecha = str_replace("T", " ",$Comprobante[0]['fecha']);
        $this->mypdf->Cell(30,7,$fecha,'TBLR',0,'L',0);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(30,7,'T. comprobante','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(30,7,utf8_decode($Comprobante[0]['tipoDeComprobante']),'TBLR',0,'L',0);
        $this->mypdf->Ln(8);
        ///////////
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(20,7,'T. cambio','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $tcambio=(empty($Comprobante[0]['TipoCambio']))?"1.00":number_format((float)$Comprobante[0]['TipoCambio'],2);
        $this->mypdf->Cell(13,7,$tcambio,'TBLR',0,'L',0);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(20,7,'F. de pago','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(50,7,strtolower(utf8_decode($Comprobante[0]['formaDePago'])),'TBLR',0,'L',0);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(20,7,'M. de pago','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(30,7,strtolower(utf8_decode($Comprobante[0]['metodoDePago'])),'TBLR',0,'L',0);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(12,7,'N. cta','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $ncta = (empty($Comprobante[0]['NumCtaPago']))?"-":str_pad($Comprobante[0]['NumCtaPago'],16,'*',STR_PAD_LEFT);
        $this->mypdf->Cell(25,7,utf8_decode($ncta),'TBLR',0,'L',0);
        $this->mypdf->Ln(8);
        //////////
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(24,7,'Expedido en','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        if(!count($Comprobante['Emisor']['ExpedidoEn']) && count($Comprobante['Emisor']['DomicilioFiscal']))
        $Comprobante['Emisor']['ExpedidoEn'][0]=$Comprobante['Emisor']['DomicilioFiscal'][0];
        $street=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['calle']))?"Domicilio conocido":trim($Comprobante['Emisor']['ExpedidoEn'][0]['calle']);
		$street.=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['noExterior']))?" SN":" ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['noExterior']);
		$street.=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['noInterior']))?"":" (".trim($Comprobante['Emisor']['ExpedidoEn'][0]['noInterior']).")";
		$street.=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['colonia']))?"":", ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['colonia']);
		$street.=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['localidad']))?"":", Loc. ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['localidad']);
		$street.=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['municipio']))?"":", ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['municipio']);
		$street.=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['estado']))?"":", ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['estado']);
		$street.=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['pais']))?"":", ".trim($Comprobante['Emisor']['ExpedidoEn'][0]['pais']);
		$street.=(empty($Comprobante['Emisor']['ExpedidoEn'][0]['codigoPostal']))?"":", C.P.".trim($Comprobante['Emisor']['ExpedidoEn'][0]['codigoPostal']);
        $this->mypdf->Cell(0,7,strtolower(utf8_decode($street)),'TBLR',0,'L',0);
		$this->mypdf->Ln(11);
		/////////////EMISOR//////////////////////
		$this->mypdf->SetFont('Arial','B',12);
        $this->mypdf->Cell(0,0,"Emisor");
        $this->mypdf->Ln(3);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(17,7,'Nombre','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(0,7,strtoupper(utf8_decode($Comprobante['Emisor'][0]['nombre'])),'TBLR',0,'L',0);
		$this->mypdf->Ln(8);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(10,7,'RFC','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(30,7,strtoupper(utf8_decode($Comprobante['Emisor'][0]['rfc'])),'TBLR',0,'L',0);
        $this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(30,7,'Regimen fiscal','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(0,7,strtoupper(utf8_decode($Comprobante['Emisor']['RegimenFiscal'][0]['Regimen'])),'TBLR',0,'L',0);
		$this->mypdf->Ln(8);
		$this->mypdf->SetTextColor(255);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(30,7,'Domicilio fiscal','TBLR',0,'L',1);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','I',8);
        $street=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['calle']))?"Domicilio conocido":trim($Comprobante['Emisor']['DomicilioFiscal'][0]['calle']);
		$street.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['noExterior']))?" SN":" ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['noExterior']);
		$street.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['noInterior']))?"":" (".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['noInterior']).")";
		$street.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['colonia']))?"":", ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['colonia']);
		$street.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['localidad']))?"":", Loc. ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['localidad']);
		$street.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['municipio']))?"":", ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['municipio']);
		$street.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['estado']))?"":", ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['estado']);
		$street.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['pais']))?"":", ".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['pais']);
		$street.=(empty($Comprobante['Emisor']['DomicilioFiscal'][0]['codigoPostal']))?"":", C.P.".trim($Comprobante['Emisor']['DomicilioFiscal'][0]['codigoPostal']);
        $this->mypdf->Cell(0,7,strtolower(utf8_decode($street)),'TBLR',0,'L',0);
        $this->mypdf->Ln(11);
		
		$this->mypdf->SetFont('Arial','B',12);
        $this->mypdf->Cell(0,0,"Conceptos");
        $this->mypdf->Ln(3);
        $this->mypdf->SetFillColor(247,246,240);
		$this->mypdf->SetDrawColor(247,246,240);
		$this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','TBL',10);
        $this->mypdf->Cell(15,7,'CANT','TBL',0,'L','1');
        $this->mypdf->Cell(17,7,'UNIDAD','TBL',0,'R','1');
        $this->mypdf->Cell(96,7,'DESCRIPCION','TBL',0,'L','1');
        $this->mypdf->Cell(31,7,'UNITARIO','TBL',0,'R','1');
        $this->mypdf->Cell(31,7,'IMPORTE','TBR',0,'R','1');
        $this->mypdf->Ln(7);
        $importe=0;
        $impuestos=0;
        $moneda=(empty($Comprobante[0]['Moneda']))?"MXN":$Comprobante[0]['Moneda'];
        $moneda=(strlen($moneda)>3)?"MXN":$moneda;
		$this->mypdf->SetFont('Arial','',8);
		// Datos
        foreach($Comprobante['Conceptos'] as $row){
			$importe+=(float)$row['importe'];
            $this->mypdf->Cell(15,5,number_format((float)$row['cantidad'],2),'LBR',0,'L');
            $this->mypdf->Cell(17,5,utf8_decode($row['unidad']),'BR',0,'R');
            $this->mypdf->Cell(100,5,utf8_decode($row['descripcion']),'BR',0,'L');
            $this->mypdf->Cell(29,5,money_format('%.2n',(float)$row['valorUnitario']) ." ".$moneda,'BR',0,'R');
            $this->mypdf->Cell(29,5,money_format('%.2n',(float)$row['importe']) ." ".$moneda,'BR',0,'R');
            //Se agrega un salto de linea
            $this->mypdf->Ln(5);
		}
		//Footer de conceptos
		$this->mypdf->SetFillColor(247,246,240);
		$this->mypdf->SetFont('Arial','B',9);
		$this->mypdf->Cell(161,7,"Subtotal",'LBR',0,'R',1);
		$this->mypdf->Cell(0,7,$importe." ".$moneda,'LBR',0,'R',1);
		$this->mypdf->Ln(7);
		foreach($Comprobante['Impuestos'] as $key=>$val){
			if(!count($val)) continue;
			$this->mypdf->Cell(161,7,"Impuesto $key",'LB',0,'R',1);
			$this->mypdf->Cell(0,7,"",'BR',0,'',1);
			$this->mypdf->Ln(7);
			foreach($val as $row){
				$impuestos+=(float)$row['importe'];
				$this->mypdf->Cell(161,7,"{$row['impuesto']}: {$row['tasa']}%",'LBR',0,'R',1);
				$this->mypdf->Cell(0,7,money_format('%.2n',(float)$row['importe'])." ".$moneda,'BR',0,'R',1);
				$this->mypdf->Ln(7);
			}
		}
		$this->mypdf->Cell(161,7,"Total",'LBR',0,'R',1);
		$this->mypdf->Cell(0,7,money_format('%.2n',$importe+$impuestos)." ".$moneda,'LBR',0,'R',1);
        $this->mypdf->Output($file,'F');
        if(file_exists($file)){
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="reporte_mensual.pdf"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			@readfile($file);
		} else die('Error al procesar archivo PDF'); 
	}
	//Envio de xml por email
	public function sendToemail(){
		$id=$this->input->post('id');
		$mes=$this->input->post('mes');
		$anio=$this->input->post('anio');
		$email=$this->input->post('email');
		$mensaje=$this->input->post('mensaje');
		$name = (!empty($id))? "$id" : "tmp";
		
		//if(empty($id) || empty($mes) || empty($anio)) die('Parametros no enviados');
		$config['protocol']    = 'smtp';
        $config['smtp_host']    = 'mail.discolima.com';
        $config['smtp_port']    = '26';
        $config['smtp_timeout'] = '5';
        $config['smtp_user']    = 'account@discolima.com';
        $config['smtp_pass']    = 'Pretty.01#';
        $config['mailtype']    = 'html';
        $config['charset']    = 'utf-8';
        $config['newline']    = "\r\n";
        $config['validation'] = TRUE;     
        $this->load->library('email', $config);
        
        $filepdf = $_SERVER['DOCUMENT_ROOT']."/files/$anio/$mes/gastos/$name.pdf";
        $filexml = $_SERVER['DOCUMENT_ROOT']."/files/$anio/$mes/gastos/$name.xml";
        if(!file_exists($filepdf) && file_exists($filexml)) $this->toPdf('F',$filepdf);
        if(file_exists($filepdf)){
			$this->email->attach($filepdf);
			$filepdf_date= date("Y-m-d H:i:s", filemtime($filepdf));
		} else $filepdf_date = date("Y-m-d");
		if(file_exists($filexml)) $this->email->attach($filexml);
		file_put_contents("email.log", $filepdf."\n", FILE_APPEND | LOCK_EX);
        $this->email->from('account@discolima.com', 'openAdmin');
        $this->email->to($email); 
        $this->email->subject("Factura openAdmin");
        
        $this->email->message("Correo generado automaticamente por openAdmin<br/>Fecha de archivo: $filepdf_date<br/>".$mensaje);  

        if($this->email->send()){
			$responce['data']="Email enviado a $email";
		} else {
			$responce['error']="Error al enviar email a $email";
		}
		$this->output
		->set_content_type('application/json')
		->set_output(json_encode($responce));
	}
	//Muestra PDF de reporte
	public function reportMes($file=''){
		$fecha = $this->input->post('fecha');
		$id = $this->input->post('id');
		if(empty($fecha) || empty($id)) die('Parametros no enviados');
		$t = strtotime($fecha);
		$mes= date('m', $t);
		$anio= date('Y', $t);
		if(empty($file))$file=$_SERVER['DOCUMENT_ROOT']."/files/$anio/$mes/gastos/reporte_mensual.pdf";
		
		$vars = array('anio'=>$anio);
		$this->load->library('mydb',$vars);
		$db = $this->mydb;
		$result = $db->query("SELECT strftime('%m',fecha) AS mes,* FROM gastos WHERE mes='$mes' ORDER by fecha,catid");
		
		$this->load->library('mypdf',array('P'=>'L'));
        $this->mypdf->AddPage();
        $this->mypdf->AliasNbPages();
        
        //Title
        $this->mypdf->SetFont('Arial','B',14);
        $this->mypdf->Cell(0,0,"Reporte mensual ".date('M - Y', $t));
        $this->mypdf->Ln(5);
        
        //Cabecera
        $this->mypdf->SetFillColor(247,246,240);
		$this->mypdf->SetDrawColor(247,246,240);
		$this->mypdf->SetTextColor(0);
        $this->mypdf->SetFont('Arial','B',10,'1');
        $this->mypdf->Cell(65,7,'UUID','TBL',0,'L','1');
        $this->mypdf->Cell(85,7,'RAZON SOCIAL','TBL',0,'L','1');
        $this->mypdf->Cell(55,7,'Metodo de pago','TBL',0,'L','1');
        $this->mypdf->Cell(25,7,'FECHA','TBL',0,'L','1');
        $this->mypdf->Cell(20,7,'SUBTOTAL','TBL',0,'C','1');
        $this->mypdf->Cell(0,7,'TOTAL','TBLR',0,'C','1');
        $this->mypdf->Ln(7);
        
        $importe=0;
        $impTrasladados=0;
        $impRetenidos=0;
        $total=0;
		$fill=0;
		// Datos
       while($row=$result->fetchArray(SQLITE3_ASSOC)){
			$it=null;
			$ir=null;
			$cambio=((float)$row['cambio']>0)?(float)$row['cambio']:1;
			$importe+=(float)$row['subtotal']*$cambio;
			$this->mypdf->SetFont('Arial','',8);
            $this->mypdf->Cell(65,5,$row['uuid'],'LB',0,'',$fill);
            $this->mypdf->Cell(85,5,utf8_decode($row['nombre']),'LB',0,'',$fill);
            $this->mypdf->Cell(55,5,utf8_decode($row['metodoDePago']),'LB',0,'',$fill);
            $this->mypdf->Cell(25,5,$row['fecha'],'LB',0,'',$fill);
            $this->mypdf->Cell(20,5,number_format($row['subtotal'],2)." ".$row['moneda'],'LB',0,'R',$fill);
            $this->mypdf->Cell(0,5,number_format($row['total'],2)." ".$row['moneda'],'LBR',0,'R',$fill);
            $this->mypdf->Ln(5);
            
            $this->mypdf->SetFont('Arial','BI',8);
            if($row['transladados']!="null"){
				$t=json_decode($row['transladados'],true);
				foreach($t as $w){
					$impTrasladados+=(float)$w['importe'];
					$it.="(";
					foreach($w as $key => $val){
						$val=($key=="importe" || $key=="tasa")?number_format($val,2):$val;
						$it.="$key:$val ";
					}
					$it.=")";
				}
			}
            $this->mypdf->Cell(133,5,"IT: ".$it,'LB',0,'L',$fill);
            
            if($row['retenciones']!="null"){
				$r=json_decode($row['retenciones'],true);
				foreach($r as $o){
					$impRetenidos+=(float)$o['importe'];
					$ir.="(";
					foreach($o as $key => $val){
						$val=($key=="importe" || $key=="tasa")?number_format($val,2):$val;
						$ir.="$key:$val ";
					}
					$ir.=")";
				}
			}
            $this->mypdf->Cell(0,5,"IR: ".$ir,'LB',0,'L',$fill);
            $this->mypdf->Ln(5);
            $fill=($fill)?0:1;
            
            $total += (float)$row['total'] * $cambio; 
		}
		$this->mypdf->SetFont('Arial','B',10,'1');
		$this->mypdf->Cell(70,5,"SubTotal",'LB',0,'R',1);
		$this->mypdf->Cell(70,5,'Traslados','LB',0,'R',1);
		$this->mypdf->Cell(70,5,'Retenidos','LB',0,'R',1);
		$this->mypdf->Cell(0,5,'Total','LB',0,'R',1);
		$this->mypdf->Ln(5);
		$this->mypdf->SetFont('Arial','',9,'1');
		$this->mypdf->Cell(70,5,number_format($importe,2)." MXN",'LB',0,'R',1);
		$this->mypdf->Cell(70,5,number_format($impTrasladados*$cambio,2)." MXN",'LB',0,'R',1);
		$this->mypdf->Cell(70,5,number_format($impRetenidos*$cambio,2)." MXN",'LB',0,'R',1);
		$this->mypdf->Cell(0,5,number_format($total,2)." MXN",'LBR',0,'R',1);
		$this->mypdf->Output($file,'F');
		
		if(file_exists($file)){
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="'.$id.'.pdf"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			@readfile($file);
		} else die('Error al procesar archivo PDF'); 
	}
	//funciones Ajax
	public function jsonRows($anio=''){
		$mes = $this->input->post('mes');
		if(empty($mes)) $mes=date('m');
		$page = $this->input->post('page');
		$page = (!$page)?1:$page;
		$limit = $this->input->post('rows');
		$limit = (!$limit)?12:$limit;
		$sidx =$this->input->post('sidx'); 
		$sidx = (!$sidx)?'fecha':$sidx; 
		$sord = $this->input->post('sord');
		$sord = (!$sord)?"":$sord;
		$anio = (empty($anio))?date('Y'):$anio;
		$search = $this->input->post('_search');
		$searchField = $this->input->post('searchField');
		$searchString = $this->input->post('searchString');
		$searchOper = $this->input->post('searchOper');
		$catid = $this->input->post('catid');
		
		if($search=='true' && !empty($search)){
			$where = $this->getWhereClause($searchField,$searchOper,$searchString);
			if(!empty($where)) $where .= " AND mes='$mes'";
		} elseif(!empty($catid))
			$where = " WHERE catid='$catid' AND mes='$mes'";
		else
			$where = " WHERE mes='$mes'";
		
		$vars = array('anio'=>$anio);
		$this->load->library('mydb',$vars);
		$db = $this->mydb;
		$sql="SELECT strftime('%m',fecha) AS mes,COUNT(*) AS count FROM gastos$where";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$count = (int)$row['count'];
		if( $count > 0 )
			$total_pages = ceil($count/$limit);
		else
			$total_pages = 0;
	
		if ($page > $total_pages)$page=$total_pages; 
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)
		
		$sql="SELECT strftime('%m',fecha) AS mes,* FROM gastos$where ORDER BY $sidx $sord LIMIT $start,$limit";
		$result = $db->query($sql);
		$responce = (object) array();
		$responce->page = $page; 
		$responce->total = $total_pages; 
		$responce->records = $count;
		$i=0;
		while($row = $result->fetchArray(SQLITE3_ASSOC)){
			if(isset($row['uuid'])) $responce->rows[$i]['id']=$row['uuid'];
			foreach($row as $key=>$val){
				$responce->rows[$i]['cell'][$key]=$val;
			}
			$i++; 
		}
		$this->output
		->set_content_type('application/json')
		->set_output(json_encode($responce));
	}
	
	public function uploadFile(){
	try {
		if (($_FILES["filexml"]["type"] == "text/xml") 
		&& ($_FILES["filexml"]["size"] < 20000) 
		&& ($_FILES["filexml"]["size"]>0)){
			if ($_FILES["filexml"]["error"] > 0){
				$this->plantillas->set_message(6000,"Error: " . $_FILES["filexml"]["error"]);
			} else {
				$xml = simplexml_load_file($_FILES["filexml"]["tmp_name"]);
				$ns = $xml->getNamespaces(true);
				$xml->registerXPathNamespace('c', $ns['cfdi']);
				$xml->registerXPathNamespace('t', $ns['tfd']);
				$comprobante = $xml->xpath('//cfdi:Comprobante');
				$sat = $xml->xpath('//t:TimbreFiscalDigital');	
				$nuCer = strtoupper($sat[0]['UUID']);
				$date = new DateTime(str_replace("T"," ",$comprobante[0]['fecha']));
				$dir = $_SERVER["DOCUMENT_ROOT"]."/files/".$date->format('Y');
		
				if(!file_exists($dir) && !is_dir($dir)){
					if(!mkdir($dir,0777,true))
					$this->plantillas->set_message(6000,"Error al crear el directorio: $dir");
				}
				$dir.= "/".$date->format('m');
				if(!file_exists($dir) && !is_dir($dir)){
					if(!mkdir($dir,0777,true))
					$this->plantillas->set_message(6000,"Error al crear el directorio: $dir");
				}
				$dir.="/gastos";
				if(!file_exists($dir) && !is_dir($dir)){
					if(!mkdir($dir,0777,true))
					$this->plantillas->set_message(6000,"Error al crear el directorio: $dir");
				}
				$dir.="/";
				$move_file=false;
				if (file_exists($dir . $nuCer . ".xml")){
					$this->plantillas->set_message(6000,"El archivo XML (". $nuCer . ") ya existe.");
					$move_file=true;
				} else {
					if(move_uploaded_file($_FILES["filexml"]["tmp_name"],$dir . $nuCer.".xml"))
						$move_file=true;
				}
				if($move_file){
					$vars = array('anio'=>$date->format('Y'));
					$this->load->library('mydb',$vars);
					$db = $this->mydb;
					$user = $_SERVER['PHP_AUTH_USER'];
					$result = $db->query("SELECT count(*) AS num FROM gastos WHERE uuid like '$nuCer'");
					$row = $result->fetchArray(SQLITE3_ASSOC);
					if(!$row['num']){
						$emisor = $xml->xpath('//cfdi:Comprobante//cfdi:Emisor');
						$traslados=$xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Traslados//cfdi:Traslado');
						$retenciones=$xml->xpath('//cfdi:Comprobante//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion');
						$moneda=(empty($comprobante[0]['Moneda']))?"MXN":$comprobante[0]['Moneda'];
						$moneda=(strlen($moneda)>3)?"MXN":$moneda;
						$tcambio=(empty($comprobante[0]['TipoCambio']) || $comprobante[0]['TipoCambio']==0)?1.00:number_format((float)$Comprobante[0]['TipoCambio'],2);
						
						$t=json_encode($traslados);
						$t=json_decode($t,true);
						foreach($t as $row){
							$temp[]=$row['@attributes'];
						}
						$impTras=json_encode($temp);
			
						$r=json_encode($retenciones);
						$r=json_decode($r,true);
						unset($temp);
						foreach($r as $row){
							$temp[]=$row['@attributes'];
						}
						$impRet=json_encode($temp);
						
						$fecha = $date->format('Y-m-d H:i');
						$total = (float)$comprobante[0]['total'];
						$subtotal = (float)$comprobante[0]['subTotal'];
						$impuestos = $total - $subtotal;
						$metodoDePago = strtoupper($comprobante[0]['metodoDePago']);
						$name = strtoupper($emisor[0]['nombre']);
						$catid = (!$this->input->post('catid'))?0:$this->input->post('catid');
				
						$sql="INSERT INTO gastos (uuid,nombre,fecha,metodoDePago,total,subtotal,impuestos,user,catid,transladados,retenciones,moneda,cambio) ".
						"VALUES ('$nuCer','$name','$fecha','$metodoDePago',$total,$subtotal,$impuestos,'$user','$catid','$impTras','$impRet','$moneda',$tcambio)";
						if($db->exec($sql)){
							echo "<form name='formxml'>".
							"<input type='hidden' name='anio' value='".$date->format('Y')."'/>".
							"<input type='hidden' name='mes' value='".$date->format('m')."'/>".
							"</form>";
						} else $this->plantillas->set_message(6000,"$sql");
					} else $this->plantillas->set_message(6000,"Este XML ya fue registrado.");	
				} else $this->plantillas->set_message(6000,"No se puedo mover el archivo XML {$_FILES["filexml"]["name"]}, a su ubicacion final.");
			}
		} else $this->plantillas->set_message(6000,"Tipo de archivo (".$_FILES["filexml"]["type"].") no es XML.");
	} catch (Exception $e){
		var_dump($e);
	}
	}
	
	public function editRows($anio=''){
		$anio = (empty($anio))?date('Y'):$anio;
		$oper = $this->input->post('oper');
		$id = $this->input->post('id');
		$id = explode(",",$id);
		$r['data']=0;
		
		$vars = array('anio'=>$anio);
		$this->load->library('mydb',$vars);
		$db = $this->mydb;
		
		if($oper=='del'){
			foreach($id as $val){
				$sql="DELETE FROM gastos WHERE uuid='$val'";
				if($db->exec($sql)){
					unlink($_SERVER['DOCUMENT_ROOT']."/files/$anio/$mes/gastos/$val.xml");
					$this->plantillas->set_message('Gasto eliminado','success');
					$r['data']=1;
				} else $this->plantillas->set_message(5002,'Al eliminar el gasto de la db');
			}
		}
		if($oper=='edit'){
			$catid = $this->input->post('catid');
			foreach($id as $val){
				$sql="UPDATE gastos SET catid='$catid' WHERE uuid='$val'";
				if($db->exec($sql)){
					$this->plantillas->set_message('Gasto cambiado de categoria','success');
					$r['data']=1;
				} else $this->plantillas->set_message(5002,'Al cambiar la categoria del gasto.');
			}
		}
		$this->output
		->set_content_type('application/json')
		->set_output(json_encode($r));
		/*$person="POST) ";
		foreach($_POST as $key=>$val){
			$person.="_($key:$val)_";
		}
		$person.="\nGET) ";
		foreach($_GET as $key=>$val){
			$person.="_($key:$val)_";
		}
		$person.="\n";
		file_put_contents("sqlite.log", $person, FILE_APPEND | LOCK_EX);*/
	}
}
