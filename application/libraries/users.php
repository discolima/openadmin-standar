<?php if ( ! defined('BASEPATH')) exit('No se permite el acceso directo al script');

class users {
	protected $CI;
    
	function __construct(){
		$this->CI =& get_instance();
		$this->CI->load->helper('url');
		$this->CI->config->load('permissions',FALSE);
	}
	//Devuelve el id del usuario.
	public function getUser(){
		return $this->CI->session->userdata('iduser');
	}
	//Verificamos sesion
	public function is_session(){
		$user_id = $this->getUser();
		if (!$user_id){
			$this->CI->plantilla->set_message(1001,'warning');
			redirect(base_url('usuarios/login'),'refresh');
		} else
			return true;
	}
	/* Devuelve array con datos de la configuracion
	 * de permisos
	 */
	public function getPermissions(){
		$return['controllers']=$this->CI->config->item('controllers');
		$return['translate']=$this->CI->config->item('translate');
		return $return;
	}
	/*Devuelve los permisos del usuario activo*/
	public function getUserAcl($controller=null){
		if(empty($controller)) $controller=$this->CI->router->fetch_class();
		$uacl=$this->CI->session->userdata('permissions');
		return $uacl[$controller]; 
	}
	/*Procesa la peticion ACL*/
	public function onAcl($action=''){
		$uacl=$this->getUserAcl();
		if(empty($action)){
			if(!count($uacl)) redirect(base_url('home/home'),'refresh');
		} else 		
			if(!isset($uacl[$action])) redirect(base_url('home/home'),'refresh');
	}
	/*Devuelve los datos de la session*/
	public function getUserdata($fiel=null){
		if(empty($fiel)) $return = $this->CI->session->all_userdata();
		else $return = $this->CI->session->userdata($fiel);
		return $return;
	}
	/*Elimina la session.*/
	public function destroySession(){
		return $this->CI->session->userdata->destroySession();
	}
	/*Comprueba el token*/
	public function getValidtoken($token=null){
		$return = FALSE;
		if(!empty($token)){
			$query = $this->CI->db->query("SELECT session_id  FROM ads_sessions WHERE session_id ='$token'");
			if($query->num_rows()>0){
				$row=$query->row_array();
				$uid=$this->getUserdata('session_id');
				if($row['session_id']==$uid) $return=TRUE;
			}
		}
		return $return;
	}
}
