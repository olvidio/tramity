<?php
namespace core;

class dbConnection {

	private $config;
	private $esquema;
	
	public function setEsquema($esquema) {
		$this->esquema = $esquema;
	}

	public function setConfig($config) {
		$this->config = $config;
	}

	public function __construct($config){
		$this->config = $config;
    }

	public function getPDO() {
		$config = $this->config;

		$host = $config['host'];
		$port = $config['port'];
		$dbname = $config['dbname'];
		$user = $config['user'];
		$password = $config['password'];
		//opcionales
		$str_conexio = '';
		if (!empty($config['sslmode'])) {
		    $str_conexio .= empty($str_conexio)? '' : ';';
		    $str_conexio .= "sslmode=".$config['sslmode'];
		}
		if (!empty($config['sslcert'])) {
		    $str_conexio .= empty($str_conexio)? '' : ';';
		    $str_conexio .= "sslcert=".$config['sslcert'];
		}
		if (!empty($config['sslkey'])) {
		    $str_conexio .= empty($str_conexio)? '' : ';';
		    $str_conexio .= "sslkey=".$config['sslkey'];
		}
		if (!empty($config['sslrootcert'])) {
		    $str_conexio .= empty($str_conexio)? '' : ';';
		    $str_conexio .= "sslrootcert=".$config['sslrootcert'];
		}
		
		// OJO Con las comillas dobles para algunos caracteres del password ($...)
		//$dsn = 'pgsql:host='.$host.' port='.$port.' dbname=\''.$dbname.'\' user=\''.$user.'\' password=\''.$password.'\'';
		$dsn = 'pgsql:host='.$host.';port='.$port.';dbname=\''.$dbname.'\';user=\''.$user.'\';password=\''.$password.'\';'.$str_conexio;
		
		$esquema =$this->config['schema']; 
		$oDB = new \PDO($dsn);
		$oDB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$oDB->exec("SET search_path TO \"$esquema\"");
		return $oDB;
	}
	
	public function getURI() {
		$config = $this->config;
		
        $host = $config['host'];
        $port = $config['port'];
        $dbname = $config['dbname'];
        $user = $config['user'];
        $password = $config['password'];
        //opcionales
        $str_conexio = '';
        if (!empty($config['sslmode'])) {
            $str_conexio .= empty($str_conexio)? '' : '&';
            $str_conexio .= "sslmode=".$config['sslmode'];
        }
        if (!empty($config['sslcert'])) {
            $str_conexio .= empty($str_conexio)? '' : '&';
            $str_conexio .= "sslcert=".$config['sslcert'];
        }
        if (!empty($config['sslkey'])) {
            $str_conexio .= empty($str_conexio)? '' : '&';
            $str_conexio .= "sslkey=".$config['sslkey'];
        }
        if (!empty($config['sslrootcert'])) {
            $str_conexio .= empty($str_conexio)? '' : '&';
            $str_conexio .= "sslrootcert=".$config['sslrootcert'];
        }
        if (!empty($str_conexio)) {
            $str_conexio = '?'.$str_conexio;
        }
        
        $password_encoded = urlencode ($password);
        return "postgresql://$user:$password_encoded@$host:$port/".$dbname.$str_conexio;
    }
	
	public function getPDOListas() {
		$config = $this->config;

        $str_conexio = $config['driver'].":".$config['dbname'];
		/* No se porque no va todo en una variable...
		$str_conexio .= ", user='".$config['user']."'";
		$str_conexio .= ", password='".$config['password']."'";
		$str_conexio .= ", array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING)";
		$oDB = new \PDO($str_conexio);
		*/
		return new \PDO($str_conexio,$config['user'], $config['password'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING, \PDO::ATTR_TIMEOUT => 3));
	}
}