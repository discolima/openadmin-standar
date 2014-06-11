<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ingresos extends CI_Controller {
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
		$this->load->model('ingresos_model','ingresos');
		$this->plantillas->is_session();
	}
	//Pagina principal
	public function index(){
		$data['top']['title']='Ingresos';
		$data['top']['cssf'][]['href']=base_url('lib/css/view/ingresos/home.css');
		$data['top']['scripts'][]['src']=base_url('lib/js/view/ingresos/home.js');
		$data['top']['main'][]=array('click'=>'buscar()','class'=>'search','label'=>'Buscar');
		$data['top']['main'][]=array('click'=>'edit()','class'=>'edit','label'=>'Editar');
		$data['top']['main'][]=array('click'=>'add()','class'=>'pagenew','label'=>'Nuevo');
		$data['top']['main'][]=array('label'=>'sp');
		$data['top']['main'][]=array('click'=>'eliminar()','class'=>'page_delete','label'=>'Eliminar');
		$data['top']['main'][]=array('label'=>'sp');
		$data['top']['main'][]=array('click'=>'toreport()','class'=>'report','label'=>'Reporte');
		$data['top']['main'][]=array('click'=>'tomail()','class'=>'email','label'=>'Enviar');
		
		$data['mes']=date('m');
		$data['anio']=date('Y');
		$data['start'] = "2014";
		$more = strtotime("+1 year", time());
		$data['end'] = date("Y", $more);
		
		$this->plantillas->show_tpl('ingresos/home',$data);
	}
	//Formulario de nuevo ingreso
	public function formIngreso(){
		$data['top']['title']='Ingresos';
		$data['top']['cssf'][]['href']=base_url('lib/css/view/ingresos/form.css');
		$data['top']['scripts'][]['src']=base_url('lib/js/view/ingresos/form.js');
		$data['top']['main'][]=array('click'=>'back()','class'=>'left','label'=>'Volver');
		$data['top']['main'][]=array('click'=>'topdf()','class'=>'report_seo','label'=>'PDF');
		$data['anio']=date('Y');
		$sf = $this->input->post('sf');
		if($sf) $sfa = explode("_",$sf);
		else $sfa = array();
		$data['sf']=$sfa;
		
		$this->load->library('mydb',array('anio'=>$data['anio']));
		$db = $this->mydb;
		if(count($sfa)){
			$result = $db->query("SELECT * FROM ingresos WHERE folio={$sfa[1]}");
			$data['row']=$result->fetchArray(SQLITE3_ASSOC);
			$data['sequence']=0;
			$data['row']['receptor']=(isset($data['row']['receptor']))?json_decode($data['row']['receptor'],true):array();
			if(isset($data['row']['status']) && ($data['row']['status']=='sin timbrar' || $data['row']['status']==''))
				$data['top']['main'][]=array('click'=>'timbrar()','class'=>'timbre','label'=>'Timbrar');
			if(isset($data['row']['status']) && $data['row']['status']!='cancelada')
				$data['top']['main'][]=array('click'=>'toMail()','class'=>'email','label'=>'Enviar');
			if(isset($data['row']['status']) && ($data['row']['status']=='timbrada' || $data['row']['status']==''))
				$data['top']['main'][]=array('click'=>'cancelar()','class'=>'cancelar','label'=>'Cancelar');
			$result = $db->query("SELECT email,contacto FROM contactos WHERE rfc='{$data['row']['receptor']['rfc']}'");
			$row = $result->fetchArray(SQLITE3_ASSOC);
			$data['row']['receptor']['email']=$row['email'];
			$data['row']['receptor']['contacto']=$row['contacto'];
		} else {
			$result = $db->query("SELECT COUNT(*) as count FROM ingresos");
			$row=$result->fetchArray(SQLITE3_ASSOC);
			$data['sequence']=(isset($row['count']))?$row['count']+1:1;
			$data['row']=array();
		}
		$this->plantillas->show_tpl('ingresos/formIngresos',$data);
	}
	public function save(){
		if(isset($_POST['factura']['fecha_expedicion']))
			$_POST['factura']['fecha_expedicion']=date("Y-m-d H:i:s",strtotime($_POST['factura']['fecha_expedicion']));
		else
			$_POST['factura']['fecha_expedicion']=date("Y-m-d H:i:s");
		$_POST['fecha'] = date("Y-m-d",strtotime($_POST['factura']['fecha_expedicion']));
		$_POST['serie'] = $_POST['factura']['serie'];
		$_POST['folio'] = $_POST['factura']['folio'];
		$_POST['subtotal'] = $_POST['factura']['subtotal'];
		$_POST['total'] = $_POST['factura']['total'];
		$_POST['factura'] = json_encode($_POST['factura']);
		unset($_POST['domicilio']);
		$_POST['receptor']['Domicilio'] = json_decode($_POST['receptor']['Domicilio'],true);
		$_POST['receptor'] = json_encode($_POST['receptor']);
		if(isset($_POST['impuestos']['retenidos']) && count($_POST['impuestos']['retenidos'])){
			foreach($_POST['impuestos']['retenidos'] as $key=>$val){
				$_POST['impuestos']['retenidos'][$key]['importe'] = $val['importe']*-1;

			}
		}
		$_POST['impuestos'] = (isset($_POST['impuestos']))?json_encode($_POST['impuestos']):'{}';
		$_POST['user'] = $_SERVER['PHP_AUTH_USER'];
		$result = $this->ingresos->save($this->input->post());
		redirect("ingresos", 'refresh');
	}
	public function editRows($anio=''){
		$anio = (empty($anio))?date('Y'):$anio;
		$oper = $this->input->post('oper');
		$id = $this->input->post('id');
		if($id) $sf = explode("_",$id);
		else $sf = array();
		$folio=(int)$sf[1];
		$r['data']=0;
		
		$this->load->library('mydb',array('anio'=>$anio));
		$db = $this->mydb;
		
		if($oper=='del'){
			$sql="DELETE FROM ingresos WHERE folio=$folio";
			if($db->exec($sql)){
				//unlink($_SERVER['DOCUMENT_ROOT']."/files/$anio/$mes/gastos/$val.xml");
				$this->plantillas->set_message('Factura eliminada','success');
				$r['data']=1;
			} else $this->plantillas->set_message(5002,'Al eliminar factura SQLite');
		}
		if($oper=='edit'){
			
		}
		$this->output
		->set_content_type('application/json')
		->set_output(json_encode($r));
	}
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
		
		$this->load->library('mydb',array('anio'=>$anio));

		$db = $this->mydb;
		$result = $db->query("SELECT strftime('%m',fecha) AS mes,COUNT(*) AS count FROM ingresos$where");
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$count = $row['count'];
		if( $count >0 )
			$total_pages = ceil($count/$limit);
		else
			$total_pages = 0;
	
		if ($page > $total_pages)$page=$total_pages; 
		$start = $limit*$page - $limit; // do not put $limit*($page - 1)
		
		$result = $db->query("SELECT strftime('%m',fecha) AS mes,* FROM ingresos$where ORDER BY $sidx $sord LIMIT $start,$limit");
		
		$responce = (object) array();
		$responce->page = $page; 
		$responce->total = $total_pages; 
		$responce->records = $count;
		$i=0;
		while($row = $result->fetchArray(SQLITE3_ASSOC)){
			$responce->rows[$i]['id']=$row['serie'].'_'.$row['folio'];
			foreach($row as $key=>$val){
				if($key=='receptor'){
					$receptor = json_decode($val,true);
					$responce->rows[$i]['cell']['nombre']=$receptor['nombre'];
				} elseif($key=='impuestos'){
					if($row['status']=='timbrada')
						$responce->rows[$i]['cell']['impuestos']=(float)$row['total']-$row['subtotal'];
					else
						$responce->rows[$i]['cell']['impuestos']=(float)0.00;
				} elseif($key=='status'){
					$responce->rows[$i]['cell']['status']=(empty($row['status']))?'sin timbrar':$row['status'];
				} elseif($key=='subtotal' || $key=='total') {
					if($row['status']!='timbrada')
						$responce->rows[$i]['cell'][$key]=(float)0.00;
					else
						$responce->rows[$i]['cell'][$key]=(float)$val;
				} else
					$responce->rows[$i]['cell'][$key]=$val;
			}
			$responce->rows[$i]['cell']['id']=$row['serie'].'_'.$row['folio'];
			$i++; 
		}
		$this->output
		->set_content_type('application/json')
		->set_output(json_encode($responce));
	}
	public function timbrar(){
		$_POST['conceptos'] = json_decode($_POST['conceptos'],true);
		$_POST['receptor']['Domicilio'] = json_decode($_POST['receptor']['Domicilio'],true);
		foreach($_POST['receptor']['Domicilio'] as $key => $val)
			$_POST['receptor']['Domicilio'][$key]= htmlentities($val);
		if(!isset($_POST['factura']['descuento'])) $_POST['factura']['descuento'] = 0.0;
		if(isset($_POST['factura']['fecha_expedicion']))
			$_POST['factura']['fecha_expedicion']=date("Y-m-d H:i:s",strtotime($_POST['factura']['fecha_expedicion']));
		else
			$_POST['factura']['fecha_expedicion']=date("Y-m-d H:i:s");
		unset($_POST['domicilio']);
		unset($_POST['status']);
		$data = $this->input->post();
		
		$this->load->library('mydb',array('anio'=>date('Y',strtotime($data['factura']['fecha_expedicion']))));
		$db = $this->mydb;
		
		//Emisor
		$sql="SELECT value FROM config WHERE name='emisor'";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$data['emisor']= json_decode($row['value'],true);
		$data['factura']['RegimenFiscal'] = (isset($data['emisor']['RegimenFiscal']))?$data['emisor']['RegimenFiscal']:'';
		unset($data['emisor']['RegimenFiscal']);
		$data['factura']['LugarExpedicion'] = $data['emisor']['ExpedidoEn']['municipio'].', '.$data['emisor']['ExpedidoEn']['estado'];
		if(empty($data['emisor']['DomicilioFiscal']['noInterior'])) unset($data['emisor']['DomicilioFiscal']['noInterior']);
		if(empty($data['emisor']['ExpedidoEn']['noInterior'])) unset($data['emisor']['ExpedidoEn']['noInterior']);
		//PAC
		$sql="SELECT value FROM config WHERE name='PAC'";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$data['PAC']= json_decode($row['value'],true);
		$data['conf']['cer'] = $data['PAC']['cer'];
		unset($data['PAC']['cer']);
		$data['conf']['key'] = $data['PAC']['key'];
		unset($data['PAC']['key']);
		$data['conf']['pass'] = $data['PAC']['SAT']['pass'];
		unset($data['PAC']['SAT']);
		$this->load->library('xml',$data);
		
		$file = $this->xml->cfdi_generar_xml();
		if($file['return']){
			$res = $this->xml->cfdi_timbrar($file['xml']);
			if($res['codigo_mf_numero']==0){
				$xml = simplexml_load_string((string)$res['cfdi']);
				$ns = $xml->getNamespaces(true);
				$xml->registerXPathNamespace('c', $ns['cfdi']);
				$xml->registerXPathNamespace('t', $ns['tfd']);
				$sat = $xml->xpath('//t:TimbreFiscalDigital');	
				foreach($sat[0]->attributes() as $key => $val)
					$timbre[$key]=(string)$val[0];
				$jt = json_encode($timbre);
				if(isset($data['impuestos']['retenidos']) && count($data['impuestos']['retenidos'])){
					foreach($data['impuestos']['retenidos'] as $key=>$val){
						$data['impuestos']['retenidos'][$key]['importe'] = $val['importe']*-1;
					}
				}
				$db = array(
					'folio'=>$data['factura']['folio'],
					'uuid'=>$res['uuid'],
					'status'=>'timbrada',
					'fecha'=>date("Y-m-d",strtotime($data['factura']['fecha_expedicion'])),
					'factura'=>json_encode($data['factura']),
					'receptor'=>json_encode($data['receptor']),
					'conceptos'=>json_encode($data['conceptos']),
					'impuestos'=>json_encode($data['impuestos']),
					'subtotal'=>$data['factura']['subtotal'],
					'total'=>$data['factura']['total'],
					'sat'=>$jt
				);
				$result=$this->ingresos->save($db);
				if($result) $this->plantillas->set_message($res['codigo_mf_texto'],'success');
			} else $this->plantillas->set_message(6000,$res['codigo_mf_texto']);
		} else $this->plantillas->set_message(6000,"Fallo al crear XML");
		redirect("ingresos", 'refresh');
	}
	//Cancelar factura
	public function cancelar(){
		$data = $this->input->post();
		
		$this->load->library('mydb',array('anio'=>$data['anio']));
		$db = $this->mydb;
		
		$result = $db->query("SELECT COUNT(*) AS count,status,uuid FROM ingresos WHERE folio={$data['folio']}");
		$row=$result->fetchArray(SQLITE3_ASSOC);
		if($row['count'] && $row['status']=='timbrada'){
			//PAC
			$sql="SELECT value FROM config WHERE name='PAC'";
			$result = $db->query($sql);
			$pac = $result->fetchArray(SQLITE3_ASSOC);
			$data['PAC']= json_decode($pac['value'],true);
			$data['conf']['cer'] = $data['PAC']['cer'];
			unset($data['PAC']['cer']);
			$data['conf']['key'] = $data['PAC']['key'];
			unset($data['PAC']['key']);
			$data['conf']['pass'] = $data['PAC']['SAT']['pass'];
			unset($data['PAC']['SAT']);
			$data['cfdi']=ROOT."files".DS.$data['anio'].DS.$data['mes'].DS."ingresos".DS.$row['uuid'].".xml";
						
			$this->load->library('xml',$data);
			if(file_exists($data['cfdi'])){
				$error=array();
				$res=$this->xml->cfdi_cancelar((string)$row['uuid']);
				if(isset($res['return'])) preg_match('/^[0-9]*/',$res['return'],$error, PREG_OFFSET_CAPTURE);
				
				if(!count($error) || $error[0]=="402"){
					$db = array(
						'folio'=>$data['folio'],
						'status'=>'cancelada',
					);
					$result=$this->ingresos->save($db);
					$msg = (isset($res['codigo_mf_texto']))?$res['codigo_mf_texto']:'';
					if($result) $this->plantillas->set_message($msg,'success');
				} else $this->plantillas->set_message(6001,$res['return']);
			} else $this->plantillas->set_message(6002,"EL archivo CFDI no existe");
		} else $this->plantillas->set_message(6002,"EL CFDI no esta registrado");
		
		redirect("ingresos", 'refresh');
	}
	//Descarga un registro en PDF
	public function toPdf($file=''){
		$id=$this->input->post('folio');
		$mes=$this->input->post('mes');
		$anio=$this->input->post('anio');
		
		if(empty($id) || empty($mes) || empty($anio)) die('Parametros no enviados');
		$this->load->library('mydb',array('anio'=>$anio));
		$db = $this->mydb;
		$result = $db->query("SELECT * FROM ingresos WHERE folio=$id");
		$row=$result->fetchArray(SQLITE3_ASSOC);
		$factura = json_decode($row['factura'],true);
		$receptor = json_decode($row['receptor'],true);
		$conceptos = json_decode($row['conceptos'],true);
		$impuestos = json_decode($row['impuestos'],true);
		$timbre = json_decode($row['sat'],true);
		if(empty($file))$file=ROOT."files/$anio/$mes/ingresos/".$row['uuid'].".pdf";
		$result = $db->query("SELECT value FROM config WHERE name='emisor'");
		$conf = $result->fetchArray(SQLITE3_ASSOC);
		$emisor = json_decode($conf['value'],true);
		
        $this->load->library('mypdf');
        $this->mypdf->AddPage();
        $this->mypdf->AliasNbPages();
        $this->mypdf->SetFont('Arial','B',10);
        $this->mypdf->SetTextColor(0);
        $this->mypdf->SetFillColor(247,246,240);
		$this->mypdf->SetDrawColor(247,246,240);
		$this->mypdf->SetLineWidth(.3);
        //Expedido en
		$this->mypdf->Cell(0,0,'Expedido en','',0,'R');
		$this->mypdf->Ln(4);
        if(!count($emisor['ExpedidoEn']) && count($emisor['DomicilioFiscal']))
        $emisor['ExpedidoEn']=$emisor['DomicilioFiscal'];
        
        $this->mypdf->SetFont('Arial','I',8);
        $calle = (!empty($emisor['ExpedidoEn']['calle']))?$emisor['ExpedidoEn']['calle']:'Domicilio conocido';
        $calle .= ($calle=='Domicilio conocido' || empty($emisor['ExpedidoEn']['noExterior']))?'':" #{$emisor['ExpedidoEn']['noExterior']}";
        $calle .= ($calle=='Domicilio conocido' || empty($emisor['ExpedidoEn']['noInterior']))?'':" INT. {$emisor['ExpedidoEn']['noInterior']}";
        $this->mypdf->Cell(0,0,utf8_decode($calle),'',0,'R');
        $this->mypdf->Ln(3);
        $colonia = (!empty($emisor['ExpedidoEn']['colonia']))?"COL. {$emisor['ExpedidoEn']['colonia']}  ":'';
        $colonia .= (!empty($emisor['ExpedidoEn']['CodigoPostal']))?"C.P. {$emisor['ExpedidoEn']['CodigoPostal']}":'';
		if(!empty($colonia)){	
			$this->mypdf->Cell(0,0,utf8_decode($colonia),'',0,'R');
			$this->mypdf->Ln(3);
		}
		$localidad="";
		if($emisor['ExpedidoEn']['localidad']!=$emisor['ExpedidoEn']['municipio'])
			$localidad = (!empty($emisor['ExpedidoEn']['localidad']))?"LOC. {$emisor['ExpedidoEn']['localidad']}  ":'';
		$localidad .= (!empty($emisor['ExpedidoEn']['municipio']))?"{$emisor['ExpedidoEn']['municipio']}":'';
		if(!empty($localidad)){		
			$this->mypdf->Cell(0,0,utf8_decode($localidad),'',0,'R');
			$this->mypdf->Ln(3);
		}
		$estado = (!empty($emisor['ExpedidoEn']['estado']))?"{$emisor['ExpedidoEn']['estado']}":'';
		$estado .= (!empty($estado) && !empty($emisor['ExpedidoEn']['pais']))?", {$emisor['ExpedidoEn']['pais']}":$emisor['ExpedidoEn']['pais'];
        if(!empty($localidad)){		
			$this->mypdf->Cell(0,0,utf8_decode($estado),'',0,'R');
		}
		$this->mypdf->Ln(-8);
        
        $serie=(empty($row['serie']))?"-":$row['serie']." ";
		$folio=(empty($row['folio']))?"-":$row['folio'];
        $this->mypdf->SetFont('Arial','',10);
        $this->mypdf->Cell(20,0,'Serie: '.$serie);
        $this->mypdf->Cell(0,0,'Folio: '.$folio);
		$this->mypdf->Ln(11);
		
        $this->mypdf->SetFont('Arial','B',12);
        $this->mypdf->Cell(0,0,"Resumen");
        $this->mypdf->Ln(4);
        $this->mypdf->SetFont('Arial','B',10);
        $this->mypdf->Cell(12,7,'UUID','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(75,7,strtoupper($row['uuid']),'TBLR',0,'L',0);
        $this->mypdf->SetFont('Arial','B',10);
        $this->mypdf->Cell(13,7,'Fecha','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(30,7,$row['fecha'],'TBLR',0,'L',0);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(30,7,'T. comprobante','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(30,7,utf8_decode($factura['tipocomprobante']),'TBLR',0,'L',0);
        $this->mypdf->Ln(8);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(20,7,'T. cambio','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $tcambio=(empty($factura['tipocambio']))?"1.00":number_format((float)$factura['tipocambio'],2);
        $this->mypdf->Cell(13,7,$tcambio,'TBLR',0,'L',0);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(20,7,'F. de pago','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(50,7,strtolower(utf8_decode($factura['forma_pago'])),'TBLR',0,'L',0);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(20,7,'M. de pago','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(30,7,strtolower(utf8_decode($factura['metodo_pago'])),'TBLR',0,'L',0);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(12,7,'N. cta','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $ncta = (empty($factura['NumCtaPago']))?"-":str_pad($factura['NumCtaPago'],16,'*',STR_PAD_LEFT);
        $this->mypdf->Cell(25,7,utf8_decode($ncta),'TBLR',0,'L',0);
        $this->mypdf->Ln(11);
		/////////////EMISOR//////////////////////
		$this->mypdf->SetFont('Arial','B',12);
        $this->mypdf->Cell(0,0,"Receptor");
        $this->mypdf->Ln(4);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(10,7,'RFC','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(30,7,strtoupper(utf8_decode($receptor['rfc'])),'TBLR',0,'L',0);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(17,7,'Nombre','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $this->mypdf->Cell(0,7,strtoupper(utf8_decode($receptor['nombre'])),'TBLR',0,'L',0);
        $this->mypdf->Ln(8);
        $this->mypdf->SetFont('Arial','B',10);
		$this->mypdf->Cell(30,7,'Domicilio fiscal','TBLR',0,'L',1);
        $this->mypdf->SetFont('Arial','I',8);
        $street=(empty($receptor['Domicilio']['calle']))?"Domicilio conocido":html_entity_decode($receptor['Domicilio']['calle']);
		$street.=(empty($receptor['Domicilio']['noExterior']))?" SN":" ".html_entity_decode($receptor['Domicilio']['noExterior']);
		$street.=(empty($receptor['Domicilio']['noInterior']))?"":" (".html_entity_decode($receptor['Domicilio']['noInterior']).")";
		$street.=(empty($receptor['Domicilio']['colonia']))?"":", ".html_entity_decode($receptor['Domicilio']['colonia']);
		$street.=(empty($receptor['Domicilio']['localidad']))?"":", Loc. ".html_entity_decode($receptor['Domicilio']['localidad']);
		$street.=(empty($receptor['Domicilio']['municipio']))?"":", ".html_entity_decode($receptor['Domicilio']['municipio']);
		$street.=(empty($receptor['Domicilio']['estado']))?"":", ".html_entity_decode($receptor['Domicilio']['estado']);
		$street.=(empty($receptor['Domicilio']['pais']))?"":", ".html_entity_decode($receptor['Domicilio']['pais']);
		$street.=(empty($receptor['Domicilio']['codigoPostal']))?"":", C.P.".html_entity_decode($receptor['Domicilio']['codigoPostal']);
        $this->mypdf->Cell(0,7,strtolower(utf8_decode($street)),'TBLR',0,'L',0);
        $this->mypdf->Ln(11);
		$this->mypdf->SetFont('Arial','B',12);
        $this->mypdf->Cell(0,0,"Conceptos");
        $this->mypdf->Ln(3);
        $this->mypdf->SetFillColor(247,246,240);
		$this->mypdf->SetDrawColor(247,246,240);
        $this->mypdf->SetFont('Arial','TBL',10);
        $this->mypdf->Cell(15,7,'CANT','TBL',0,'L','1');
        $this->mypdf->Cell(17,7,'UNIDAD','TBL',0,'R','1');
        $this->mypdf->Cell(96,7,'DESCRIPCION','TBL',0,'L','1');
        $this->mypdf->Cell(31,7,'UNITARIO','TBL',0,'R','1');
        $this->mypdf->Cell(31,7,'IMPORTE','TBR',0,'R','1');
        $this->mypdf->Ln(8);
        $importe=0;
        $moneda=(empty($factura['Moneda']))?"MXN":$factura['Moneda'];
        $moneda=(strlen($moneda)>3)?"MXN":$moneda;
		$this->mypdf->SetFont('Arial','',8);
		// Datos
        foreach($conceptos as $item){
			$importe+=(float)$item['importe'];
            $this->mypdf->Cell(15,5,number_format((float)$item['cantidad'],2),'LBR',0,'L');
            $this->mypdf->Cell(17,5,utf8_decode($item['unidad']),'BR',0,'R');
            $this->mypdf->Cell(100,5,utf8_decode($item['descripcion']),'BR',0,'L');
            $this->mypdf->Cell(29,5,money_format('%.2n',(float)$item['valorunitario']) ." ".$moneda,'BR',0,'R');
            $this->mypdf->Cell(29,5,money_format('%.2n',(float)$item['importe']) ." ".$moneda,'BR',0,'R');
            $this->mypdf->Ln(5);
		}
		//Footer de conceptos
		$this->mypdf->SetFillColor(247,246,240);
		$this->mypdf->SetFont('Arial','B',9);
		$this->mypdf->Cell(161,7,"Subtotal",'LBR',0,'R',1);
		$this->mypdf->Cell(0,7,money_format('%.2n',(float)$importe)." ".$moneda,'LBR',0,'R',1);
		$this->mypdf->Ln(7);
		$impuesto=0;
		foreach($impuestos as $key=>$val){
			if(!count($val)) continue;
			$this->mypdf->Cell(161,7,"Impuesto $key",'LB',0,'R',1);
			$this->mypdf->Cell(0,7,"",'BR',0,'',1);
			$this->mypdf->Ln(7);
			foreach($val as $imp){
				if($key=='retenidos')
					$impuesto+=(float)$imp['importe'] * -1;
				else
					$impuesto+=(float)$imp['importe'];
				$this->mypdf->Cell(161,7,"{$imp['impuesto']}: {$imp['tasa']}%",'LBR',0,'R',1);
				$this->mypdf->Cell(0,7,money_format('%.2n',(float)$imp['importe'])." ".$moneda,'BR',0,'R',1);
				$this->mypdf->Ln(7);
			}
		}
		$this->mypdf->Cell(161,7,"Total",'LBR',0,'R',1);
		$this->mypdf->Cell(0,7,money_format('%.2n',$importe+$impuesto)." ".$moneda,'LBR',0,'R',1);
		$this->mypdf->Ln(8);
		$this->mypdf->SetFont('Arial','',12);
		$this->mypdf->Cell(0,7,num2letras(number_format((float)$importe+$impuesto,2),$moneda),'',0,'C');
		if($row['status']=='timbrada'){
			$this->mypdf->Ln(10);
			$img = "files/$anio/".date('m',strtotime($row['fecha']))."/ingresos/{$row['uuid']}.png";
			if(is_file(ROOT.$img)){
				$this->mypdf->Image($img);
				$this->mypdf->Ln(-47);
			}
			$this->mypdf->SetFont('Arial','B',10);
			$this->mypdf->Cell(50,6,"");
			$this->mypdf->Cell(0,6,"Sello digital del CFDI:");
			$this->mypdf->Ln(6);
			$this->mypdf->SetFont('Arial','',9);
			$this->mypdf->Cell(50,4,"");
			$this->mypdf->MultiCell(0,4,$timbre['selloCFD']);
			///////
			$this->mypdf->Ln(3);
			$this->mypdf->SetFont('Arial','B',10);
			$this->mypdf->Cell(50,6,"");
			$this->mypdf->Cell(0,6,"Sello del SAT:");
			$this->mypdf->Ln(6);
			$this->mypdf->SetFont('Arial','',9);
			$this->mypdf->Cell(50,4,"");
			$this->mypdf->MultiCell(0,4,$timbre['selloSAT']);
			/////
			$this->mypdf->Ln(4);
			$this->mypdf->SetFont('Arial','B',10);
			$this->mypdf->Cell(62,6,"No de Serie del Certificado del SAT:");
			$this->mypdf->SetFont('Arial','',10);
			$this->mypdf->MultiCell(0,6,$timbre['noCertificadoSAT']);
			///////
			$this->mypdf->Ln(1);
			$this->mypdf->SetFont('Arial','B',10);
			$this->mypdf->Cell(40,6,"Fecha de certificacion:");
			$this->mypdf->SetFont('Arial','',10);
			$this->mypdf->MultiCell(0,6,dateLong(date("Y-m-d",strtotime(str_replace("T", " ",$timbre['FechaTimbrado'])))));
			$this->mypdf->SetY(-30);
			$this->mypdf->SetFont('Arial','',11);
			$this->mypdf->Cell(0,0,"Representacion impresa del XML {$row['uuid']}",0,'','C');
		}
		if($row['status']=='sin timbrar'){
			//Si esta timbrada
			$this->mypdf->SetFont('Arial','B',50);
			$this->mypdf->SetTextColor(255,192,203);
			$this->mypdf->RotatedText(60,190,'S i n   t i m b r a r',45);
		}
		
		
        if(!empty($row['uuid'])) $out="F"; else $out="I";
        $this->mypdf->Output($file,$out);
        if(file_exists($file)){
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="reporte_mensual.pdf"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			@readfile($file);
		} else die('Error al procesar archivo PDF'); 
	}
}
