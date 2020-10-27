<?php
namespace core;

/**
 * Básicamente la conexión a la base de datos, con los passwd para cada esquema.
 * @author dani
 *
 */
class ConfigDB {
	
	private $data;
	
	public function __construct($database) {
	    $this->setDataBase($database);
	}
	
	public function getEsquema($esquema) {
		$data = $this->data['default'];
		$data['schema'] = $esquema;
		//sobreescribir los valores default
		foreach ($this->data[$esquema] as $key => $value){
			$data[$key] = $value;
		}
		return $data;
	}

	public function setDataBase($database) {
		if (ConfigGlobal::WEBDIR == 'pruebas') {
			$database = 'pruebas-'.$database;
		}
		$this->data = include ConfigGlobal::DIR_PWD.'/'.$database.'.inc';
	}
	
	public function addEsquema($database,$esquema,$esquema_pwd){
        // Las bases de datos de pruebas y producción están en el mismo cluster, y 
        // por tanto los usuarios son los mismos. Hay que ponerlo en los dos ficheros:
        // Pero OJO: la parte de definicion de host y dbname son diferentes!!
        
        $this->addEsquemaProduccion($database,$esquema,$esquema_pwd);
        $this->addEsquemaPruebas($database,$esquema,$esquema_pwd);
	}
	
	public function addEsquemaProduccion($database,$esquema,$esquema_pwd){
		$this->data = include ConfigGlobal::DIR_PWD.'/'.$database.'.inc';
		
        $this->data[$esquema] = ['user' => $esquema, 'password' => $esquema_pwd ];
        
		$filename = ConfigGlobal::DIR_PWD.'/'.$database.'.inc';
	    file_put_contents($filename, '<?php return ' . var_export($this->data, true) . ' ;');
	}
	public function addEsquemaPruebas($database,$esquema,$esquema_pwd){
		$database = 'pruebas-'.$database;
		$this->data = include ConfigGlobal::DIR_PWD.'/'.$database.'.inc';
		
        $this->data[$esquema] = ['user' => $esquema, 'password' => $esquema_pwd ];
        
		$filename_pruebas = ConfigGlobal::DIR_PWD.'/pruebas-'.$database.'.inc';
	    file_put_contents($filename_pruebas, '<?php return ' . var_export($this->data, true) . ' ;');
	}
}