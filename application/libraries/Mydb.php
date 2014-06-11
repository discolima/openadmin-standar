<?php if ( ! defined('BASEPATH')) exit('No se permite el acceso directo al script');

class Mydb extends sqlite3 {
	protected $CI;
	public $cnx = false;
	function __construct($v=array()){
		$this->CI =& get_instance();
		$this->opendb($v);
	}
	private function opendb($v=array()){
		if($this->cnx) return 2;
		try {
			$this->open("lib/sqlite/data.sqlite");
			$this->tables();
			if(isset($v['anio'])){
				if(empty($v['anio'])) $v['anio']=date('Y');
				$this->exec("ATTACH DATABASE 'lib/sqlite/contable/db_{$v['anio']}.sqlite' AS {$v['anio']}");
				$this->mesContable($v['anio']);
			} else {
				$anio=date('Y');
				$this->exec("ATTACH DATABASE 'lib/sqlite/contable/db_$anio.sqlite' AS $anio");
				$this->mesContable($anio);
			}
			$this->cnx = true;
		} catch (Exception $e){
			$this->CI->plantilla->set_message(5003,'Problema al abrir la db.');
			$this->close();
		}
		return $this->cnx;
	}
	function close(){
		$this->cnx = false;
		parent::__construct();
	}
	function serie($anio=''){
		$anio = date("Y",strtotime($anio));
		$this->exec("ATTACH DATABASE 'lib/sqlite/contable/db_$anio.sqlite' AS $anio");
		$this->mesContable($anio);
	}
	private function tables($db='main'){
		try {
			$this->exec('CREATE TABLE IF NOT EXISTS "'.$db.'"."contactos" (
			"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL,
			"name" TEXT,
			"rfc" TEXT,
			"type" TEXT DEFAULT cliente,
			"domicilioFiscal" TEXT,
			"email" TEXT,
			"telefono" TEXT,
			"contacto" TEXT,
			"registro" TEXT, 
			"fecha" DATETIME)');
			$this->exec('CREATE TABLE IF NOT EXISTS "'.$db.'"."config" (
			"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
			"name" VARCHAR,
			"value" TEXT,
			"user" VARCHAR)');
			$this->exec('CREATE TABLE IF NOT EXISTS "'.$db.'"."users" (
			"iduser" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
			"lasttime" DATETIME,
			"username" VARCHAR,
			"password" VARCHAR,
			"permissions" TEXT DEFAULT "{}")');
			$this->exec('CREATE TABLE IF NOT EXISTS "'.$db.'"."impuestos" (
			"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
			"type" VARCHAR,
			"name" VARCHAR, 
			"value" FLOAT)');
			$this->exec('CREATE TABLE IF NOT EXISTS "'.$db.'"."store" (
			"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL ,
			"user" VARCHAR,
			"fecha" DATETIME,
			"type" VARCHAR DEFAULT producto,
			"code" VARCHAR,
			"unidad" VARCHAR,
			"name" VARCHAR,
			"precio" FLOAT,
			"cantidad" FLOAT)');
		} catch (Exception $e){
			$this->CI->plantilla->set_message(5003,'Tablas en db');
		}
	}
	private function mesContable($db='main'){
		try {
			$this->exec('CREATE TABLE IF NOT EXISTS "'.$db.'"."gastos" (
			"id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL,
			"user" TEXT,
			"catid" TEXT,
			"uuid" TEXT,
			"fecha" DATETIME,
			"nombre" TEXT,
			"metodoDePago" TEXT,
			"subtotal" NUMERIC,
			"impuestos" NUMERIC,
			"transladados" TEXT,
			"retenciones" TEXT,
			"total" NUMERIC,
			"moneda" TEXT,
			"cambio" NUMERIC)');
			$this->exec('CREATE TABLE IF NOT EXISTS "'.$db.'"."ingresos" (
			"folio" INTEGER PRIMARY KEY NOT NULL,
			"serie" VARCHAR,
			"uuid" VARCHAR,
			"user" TEXT,
			"fecha" DATETIME,
			"factura" TEXT,
			"receptor" TEXT,
			"conceptos" TEXT,
			"subtotal" NUMERIC,
			"impuestos" NUMERIC,
			"total" NUMERIC,
			"sat" TEXT,
			"status" NUMERIC)');
		} catch (Exception $e){
			$this->CI->plantilla->set_message(5003,'Tabla contables de gastos');
		}
	}
}
