<?php if ( ! defined('BASEPATH')) exit('No se permite el acceso directo al script');

class Ingresos_model extends CI_Model {
    function __construct(){
        parent::__construct();
    }
    //Almacenamos en base de datos
	function save($post=array()){
		if(!count($post)) return false;
		if(isset($post['fecha']))
			$anio=date("Y",strtotime($post['fecha']));
		else
			$anio=date("Y");
		$this->load->library('mydb',array('anio'=>$anio));
		$db = $this->mydb;
		$sql="SELECT COUNT(*) AS count FROM ingresos WHERE folio={$post['folio']}";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$count = (int)$row['count'];
		if(!$count){
			$filset="";
			$set="";
			foreach($post as $key=>$val){
				$filset.=(empty($filset))?"$key":",$key";
				$set.=(empty($set))?"'$val'":",'$val'";
			}
			$sql="INSERT INTO ingresos ($filset) VALUES ($set)";
			$msg="Factura ingresada con exito";
		} else {
			$filset="";
			foreach($post as $key=>$val){
				$filset.=(empty($filset))?"$key='$val'":",$key='$val'";
			}
			$sql="UPDATE ingresos SET $filset WHERE folio='{$post['folio']}'";
			$msg="Factura actualizada con exito";
		}
		
		if($db->exec($sql)){
			$this->plantillas->set_message($msg,'success');
			$return = true;
		} else {
			$this->plantillas->set_message(5001,"Fallo en facturas");
			$return = false;
		}
		return $return;
	}
}
