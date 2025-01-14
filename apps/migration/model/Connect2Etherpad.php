<?php

namespace migration\model;

class Connect2Etherpad
{

    private $config;

    public function __construct()
    {
        $config = [
            'datestyle' => 'ISO,YMD',
            'host' => 'db',
            'sslmode' => 'disable',
            'port' => 5432,
            'dbname' => 'etherpad',
            'schema' => 'public',
            'sslcert' => '/home/orbix/conf/postgresql.crt',
            'sslkey' => '/home/orbix/conf/postgresql.key',
            'sslrootcert' => '/home/orbix/conf/root.crt',
            'ssh_user' => 'postgres',
            'user' => 'postgres',
            'password' => 'example',
        ];

        $this->config = $config;
    }

    public function getHost()
    {
        return $this->config['host'];
    }
    public function getPDO()
    {
        $config = $this->config;

        $host = $config['host'];
        $port = $config['port'];
        $dbname = $config['dbname'];
        $user = $config['user'];
        $password = $config['password'];
        //opcionales
        $str_conexio = '';
        if (!empty($config['sslmode'])) {
            $str_conexio .= empty($str_conexio) ? '' : ';';
            $str_conexio .= "sslmode=" . $config['sslmode'];
        }
        if (!empty($config['sslcert'])) {
            $str_conexio .= empty($str_conexio) ? '' : ';';
            $str_conexio .= "sslcert=" . $config['sslcert'];
        }
        if (!empty($config['sslkey'])) {
            $str_conexio .= empty($str_conexio) ? '' : ';';
            $str_conexio .= "sslkey=" . $config['sslkey'];
        }
        if (!empty($config['sslrootcert'])) {
            $str_conexio .= empty($str_conexio) ? '' : ';';
            $str_conexio .= "sslrootcert=" . $config['sslrootcert'];
        }

        // OJO Con las comillas dobles para algunos caracteres del password ($...)
        //$dsn = 'pgsql:host='.$host.' port='.$port.' dbname=\''.$dbname.'\' user=\''.$user.'\' password=\''.$password.'\'';
        $dsn = 'pgsql:host=' . $host . ';port=' . $port . ';dbname=\'' . $dbname . '\';user=\'' . $user . '\';password=\'' . $password . '\';' . $str_conexio;

        $esquema = $this->config['schema'];
        $oDB = new \PDO($dsn);
        $oDB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $oDB->exec("SET search_path TO \"$esquema\"");
        /* le paso la gestión a la clase web\datetimelocal */
        //$oDB->exec("SET DATESTYLE TO '$datestyle'");
        return $oDB;
    }

    public function getURI()
    {
        $config = $this->config;

        $host = $config['host'];
        $port = $config['port'];
        $dbname = $config['dbname'];
        $user = $config['user'];
        $password = $config['password'];
        //opcionales
        $str_conexio = '';
        if (!empty($config['sslmode'])) {
            $str_conexio .= empty($str_conexio) ? '' : '&';
            $str_conexio .= "sslmode=" . $config['sslmode'];
        }
        if (!empty($config['sslcert'])) {
            $str_conexio .= empty($str_conexio) ? '' : '&';
            $str_conexio .= "sslcert=" . $config['sslcert'];
        }
        if (!empty($config['sslkey'])) {
            $str_conexio .= empty($str_conexio) ? '' : '&';
            $str_conexio .= "sslkey=" . $config['sslkey'];
        }
        if (!empty($config['sslrootcert'])) {
            $str_conexio .= empty($str_conexio) ? '' : '&';
            $str_conexio .= "sslrootcert=" . $config['sslrootcert'];
        }
        if (!empty($str_conexio)) {
            $str_conexio = '?' . $str_conexio;
        }

        $password_encoded = urlencode($password);
        $dsn = "postgresql://$user:$password_encoded@$host:$port/" . $dbname . $str_conexio;
        if ($host === '/var/run/postgresql') {
            $dsn = "postgresql:///$dbname?host=$host";
        }

        return $dsn;
    }
}