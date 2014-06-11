<?php if ( ! defined('BASEPATH')) exit('No se permite el acceso directo al script');

class Catalogos_model extends CI_Model {
    function __construct(){
        parent::__construct();
    }
    //Almacenamos en base de datos
	function save($table='',$id='',$post=array()){
		if(!count($post)) return false;
		if(empty($table)) return false;
		if(empty($id)) return false;
		if($post[$id]=='_empty' || $post[$id]=='null') $post[$id]='';
		
		$this->load->library('mydb');
		$db = $this->mydb;
		$sql="SELECT COUNT(*) AS count FROM $table WHERE $id='{$post[$id]}'";
		$result = $db->query($sql);
		$row = $result->fetchArray(SQLITE3_ASSOC);
		$count = (int)$row['count'];
		if(!$count){
			$filset="";
			$set="";
			foreach($post as $key=>$val){
				if($key==$id) continue;
				$filset.=(empty($filset))?"$key":",$key";
				$set.=(empty($set))?"'$val'":",'$val'";
			}
			$sql="INSERT INTO $table ($filset) VALUES ($set)";
		} else {
			$filset="";
			foreach($post as $key=>$val){
				if($key==$id) continue;
				$filset.=(empty($filset))?"$key='$val'":",$key='$val'";
			}
			$sql="UPDATE $table SET $filset WHERE $id='{$post[$id]}'";
		}
		if($db->exec($sql)){
			$return = true;
		} else {
			$this->plantillas->set_message(5001,"Fallo en catalogos");
			$return = false;
		}
		return $return;
	}
	//Elimina de la base de datos
	function delete($table='',$id='',$post=array()){
		if(!count($post)) return false;
		if(empty($table)) return false;
		if(empty($id)) return false;
		if($post[$id]=='_empty' || !isset($post[$id]) || $post[$id]=='null') $post[$id]='';
		$this->load->library('mydb');
		$db = $this->mydb;
		$sql="DELETE FROM $table WHERE $id='{$post[$id]}'";
		if($db->exec($sql)){
			$return = true;
		} else {
			$this->plantillas->set_message(5002,"Fallo en catalogos");
			$return = false;
		}
		return $return;
	}
	//Buscamos registro
	function row($table='',$id='',$post=array()){
		if(!count($post)) return false;
		if(empty($table)) return false;
		if(empty($id)) return false;
		if($post[$id]=='_empty' || !isset($post[$id])) $post[$id]=0;
		
		$this->load->library('mydb');
		$db = $this->mydb;
		$sql="SELECT COUNT(*) AS count,* FROM $table WHERE $id='{$post[$id]}'";
		$result=$db->query($sql);
		while($row=$result->fetchArray(SQLITE3_ASSOC)){
			if(empty($return['count']) || !isset($return['count'])) $return['count']=$row['count'];
			$return['cell'][]=$row;
		}
		return $return;
	}
}
