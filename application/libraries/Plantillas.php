<?php if ( ! defined('BASEPATH')) exit('No se permite el acceso directo al script');

class Plantillas {
	protected $CI;
	function __construct(){
		$this->CI =& get_instance();
		date_default_timezone_set('America/Mexico_City');
		setlocale(LC_MONETARY, 'es_MX');
	}
	public function show_tpl($view,$data){
		if(isset($data['top'])) $top = $data['top'];
		$top['user']['name'] = $user = $this->CI->session->userdata('username');
		if(!isset($top['title'])) $top['title']="OpenAdmin";
		else $top['title'].=" - OpenAdmin";
		$top['menu']=$this->CI->router->fetch_class();
		$top['submenu']=$this->CI->router->fetch_method();
		
		$this->CI->load->view('plantilla/top', $top);
		if(isset($data['top'])) unset($data['top']);
		$this->CI->load->view($view, $data);
		$this->CI->load->view('plantilla/bottom');
	}
	function set_message($num=1000,$msg=null,$type='error'){
		$detalles['message'] = $this->CI->session->userdata('message');
		if(is_array($detalles['message'])) $x = count($detalles['message']);
		else $x=0;
		
		if($msg=='error' || $msg=='alert' || $msg=='warning' || $msg=='success' || $msg=='information'){
			$type=$msg;
			$msg=null;
		}
		if(is_string($num) || empty($num)){
			if($num=='error' || $num=='alert' || $num=='warning' || $num=='success' || $num=='information'){
				$type=$num;
				$num=0;
			} else {
				$msg=$num;
				$num=0;	
			}
		} elseif (!is_integer($num)) $num=0;
		
		$code=$this->show_numErr($num,$msg);
		if($code=="Error desconosido, llame al administrador.") $type="error";
		
		$detalles['message'][$x] = array(
        	'message'  => utf8_encode($code),
            'type'     => $type
        );
		$this->CI->session->set_userdata($detalles);
		return $code;
	}
	/* Muestra los errores de la session */
	function show_message(){
		$detalles = $this->CI->session->userdata('message');
		$this->CI->session->unset_userdata('message');
		return $detalles;
	}
	/*Interpreta los errores*/
	function show_numErr($num=1000,$txt=null){
		$place=$this->CI->router->fetch_class().'/'.$this->CI->router->fetch_method();
		$err[100]='Datos de usuario vac&iacute;o en '.$place;
		$err[101]='El usuario ya existe en el sistema';
		$err[102]='Se enviaron datos vacios en '.$place;
		$err[103]='Datos de proveedor vac&iacute;o en '.$place;
		$err[104]='Datos de acceso incorrectos';
		
		$err[1000]='Token inv&aacute;lido o vac&iacute;o en '.$place;
		$err[1001]='Inicie sesi&oacute;n en el sistema';
		$err[1100]='Se perdio la conexion con el servidor en '.$place;
		
		$err[5001]='Error al insertar registro en la DB en '.$place;
		$err[5002]='Error al actualizar registro en la DB en '.$place;
		$err[5003]='Error de conexion con la DB en '.$place;
		
		$err[6000]='Error al cargar XML en '.$place;
		$err[6001]='Error de comunicacion con el SAT';
		$err[6002]='Error de facturacion en '.$place;
		
		if(isset($err[$num])){
			$return=$num.': '.$err[$num];
			if(!empty($txt)) $return.='. ('.$txt.')';
		} else $return=$txt;
		
		if(empty($return)) $return="Error desconosido, llame al administrador.";
		return $return;
	}
	//Devuelve el id del usuario.
	public function getUser(){
		return $this->CI->session->userdata('iduser');
	}
	//Verificamos sesion
	public function is_session(){
		$user_id = $this->getUser();
		if (!$user_id){
			$this->set_message(1001,'warning');
			redirect(base_url('home/autho'),'refresh');
		} else
			return true;
	}
}
