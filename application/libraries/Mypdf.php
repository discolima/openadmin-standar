<?php if ( ! defined('BASEPATH')) exit('No se permite el acceso directo al script');
// Incluimos el archivo fpdf
require_once APPPATH."libraries/fpdf.php";

class Mypdf extends FPDF {
	protected $CI;
	private $angle=0;
	function Rotate($angle,$x=-1,$y=-1){
		if($x==-1)
			$x=$this->x;
		if($y==-1)
			$y=$this->y;
		if($this->angle!=0)
			$this->_out('Q');
		$this->angle=$angle;
		if($angle!=0){
			$angle*=M_PI/180;
			$c=cos($angle);
			$s=sin($angle);
			$cx=$x*$this->k;
			$cy=($this->h-$y)*$this->k;
			$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
		}
	}
	public function __construct($v=array()){
		if(!isset($v['P']) || empty($v['P'])) $v['P']='p';
		if(!isset($v['M']) || empty($v['M'])) $v['M']='mm';
		if(!isset($v['T']) || empty($v['T'])) $v['T']='A4';
		$this->CI =& get_instance();
		parent::__construct($v['P'],$v['M'],$v['T']);
    }
    // El encabezado del PDF
    public function Header(){
		if(isset($this->CI->mydb)) $db = $this->CI->mydb;
		else{
			$vars = array('anio'=>$anio);
			$this->CI->load->library('mydb',$vars);
			$db = $this->CI->mydb;
		}
		$sql="SELECT value FROM config WHERE name='emisor'";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$emisor= json_decode($row['value'],true);
		$img = (isset($row['logo']) && !empty($row['logo']))?$row['logo']:"lib/images/logo_oa_small.png";
		if(is_file(ROOT.$img))
			$this->Image($img,10,5,40);
		$this->SetFont('Arial','I',8);
		$this->SetTextColor(0);
        $this->Cell(0,0,"Creado:   " .utf8_decode(dateLong(date('Y-m-d'))),0,0,'R');
        $this->Ln(3);
        $this->Cell(0,0,'Contacto: 160-4926',0,1,'R');
        $this->Ln(2);
        $this->SetFont('Arial','B',13);
        $this->Cell(0,0,utf8_decode($emisor['nombre']));
        $this->Ln(5);
        $this->SetFont('Arial','',8);
        $this->Cell(0,0,utf8_decode($emisor['rfc']));
        $this->Ln(3);
        $calle = (!empty($emisor['DomicilioFiscal']['calle']))?$emisor['DomicilioFiscal']['calle']:'Domicilio conocido';
        $calle .= ($calle=='Domicilio conocido' || empty($emisor['DomicilioFiscal']['noExterior']))?'':" #{$emisor['DomicilioFiscal']['noExterior']}";
        $calle .= ($calle=='Domicilio conocido' || empty($emisor['DomicilioFiscal']['noInterior']))?'':" INT. {$emisor['DomicilioFiscal']['noInterior']}";
        $this->Cell(0,0,utf8_decode($calle));
        $this->Ln(3);
        $colonia = (!empty($emisor['DomicilioFiscal']['colonia']))?"COL. {$emisor['DomicilioFiscal']['colonia']}  ":'';
        $colonia .= (!empty($emisor['DomicilioFiscal']['CodigoPostal']))?"C.P. {$emisor['DomicilioFiscal']['CodigoPostal']}":'';
		if(!empty($colonia)){	
			$this->Cell(0,0,utf8_decode($colonia));
			$this->Ln(3);
		}
		if($emisor['DomicilioFiscal']['localidad']!=$emisor['DomicilioFiscal']['municipio'])
			$localidad = (!empty($emisor['DomicilioFiscal']['localidad']))?"LOC. {$emisor['DomicilioFiscal']['localidad']}  ":'';
		$localidad = (!empty($emisor['DomicilioFiscal']['municipio']))?"{$emisor['DomicilioFiscal']['municipio']}  ":'';
		if(!empty($localidad)){		
			$this->Cell(0,0,utf8_decode($localidad));
			$this->Ln(3);
		}
		$estado = (!empty($emisor['DomicilioFiscal']['estado']))?"{$emisor['DomicilioFiscal']['estado']}":'';
		$estado .= (!empty($estado) && !empty($emisor['DomicilioFiscal']['pais']))?", {$emisor['DomicilioFiscal']['pais']}  ":$emisor['DomicilioFiscal']['pais'];
        if(!empty($localidad)){		
			$this->Cell(0,0,utf8_decode($estado));
		}
        $this->Ln(4);
	}
	//Rotar texto
	public function RotatedText($x,$y,$txt,$angle){
		$this->Rotate($angle,$x,$y);
		$this->Text($x,$y,$txt);
		$this->Rotate(0);
	}
	// El pie del pdf
	public function Footer(){
		$this->SetY(-10);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(0);
		$this->SetDrawColor(0);
		$this->SetLineWidth(.3);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}','T',0,'C');
	}
	function _endpage(){
		if($this->angle!=0){
			$this->angle=0;
			$this->_out('Q');
		}
		parent::_endpage();
	}
}
