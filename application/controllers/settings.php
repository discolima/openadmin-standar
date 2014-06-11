<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Settings extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->plantillas->is_session();
	}
	//Pagina principal
	public function index(){
		$data['top']['scripts'][]['src']=base_url('lib/js/view/settings/home.js');
		$data['top']['cssf'][]['href']=base_url('lib/css/view/settings/home.css');
		
		$this->load->library('mydb');
		$db = $this->mydb;
		
		$sql="SELECT value FROM config WHERE name='emisor'";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$data['row']['emisor']= json_decode($row['value'],true);
		
		$sql="SELECT value FROM config WHERE name='PAC'";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$data['row']['PAC']= json_decode($row['value'],true);
		$this->plantillas->show_tpl('settings/home',$data);
	}
	//Guardar datos
	public function save(){
		if(!empty($_POST['emisor']['DomicilioFiscal']['calle']) && empty($_POST['emisor']['ExpedidoEn']['calle'])){
			$_POST['emisor']['ExpedidoEn']['calle'] = $_POST['emisor']['DomicilioFiscal']['calle'];
			$_POST['emisor']['ExpedidoEn']['noExterior'] = $_POST['emisor']['DomicilioFiscal']['noExterior'];
			$_POST['emisor']['ExpedidoEn']['noInterior'] = $_POST['emisor']['DomicilioFiscal']['noInterior'];
			$_POST['emisor']['ExpedidoEn']['colonia'] = $_POST['emisor']['DomicilioFiscal']['colonia'];
			$_POST['emisor']['ExpedidoEn']['localidad'] = $_POST['emisor']['DomicilioFiscal']['localidad'];
			$_POST['emisor']['ExpedidoEn']['municipio'] = $_POST['emisor']['DomicilioFiscal']['municipio'];
			$_POST['emisor']['ExpedidoEn']['estado'] = $_POST['emisor']['DomicilioFiscal']['estado'];
			$_POST['emisor']['ExpedidoEn']['pais'] = $_POST['emisor']['DomicilioFiscal']['pais'];
			$_POST['emisor']['ExpedidoEn']['CodigoPostal'] = $_POST['emisor']['DomicilioFiscal']['CodigoPostal'];
		}
		$this->load->library('mydb');
		$db = $this->mydb;
		
		$emisor = json_encode($this->input->post('emisor'));
		$sql="SELECT COUNT(*) AS count FROM config WHERE name='emisor'";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$count = (int)$row['count'];
		if(!$count){
			$sql="INSERT INTO config (name,value,user) VALUES ('emisor','$emisor','{$_SERVER['PHP_AUTH_USER']}')";
		} else {
			$sql="UPDATE config SET value='$emisor',user='{$_SERVER['PHP_AUTH_USER']}' WHERE name='emisor'";
		}
		if(!$db->exec($sql))
			$this->plantillas->set_message(5001,"Configuraciones emisor");
		
		$pac = json_encode($this->input->post('PAC'));
		$sql="SELECT COUNT(*) AS count FROM config WHERE name='PAC'";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$count = (int)$row['count'];
		if(!$count){
			$sql="INSERT INTO config (name,value,user) VALUES ('PAC','$pac','{$_SERVER['PHP_AUTH_USER']}')";
			$msg="Configuracion guardada";
		} else {
			$sql="UPDATE config SET value='$pac',user='{$_SERVER['PHP_AUTH_USER']}' WHERE name='PAC'";
			$msg="Configuracion actualizada";
		}
		if($db->exec($sql)){
			$this->plantillas->set_message($msg,'success');
		} else $this->plantillas->set_message(5001,"Configuraciones PAC");
		redirect("settings", 'refresh');
	}
}
