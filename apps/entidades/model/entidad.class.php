<?php
namespace entidades\model;

use core\ConfigGlobal;
use entidades\model\entity\EntidadDB;
use usuarios\model\entity\Cargo;


class Entidad Extends EntidadDB {
    
    /* CONST -------------------------------------------------------------- */
	
	/* Utilizar los mismos que en cargo
	const AMBITO_CG  = 1;
	const AMBITO_CR  = 2;
	const AMBITO_DL  = 3;  //"dl"
	const AMBITO_CTR = 4;
    */
    
    private $sdir;
    private $sfileLog;
    private $snameFileLog;
    private $ssql_txt;
    
    /* CONSTRUCTOR -------------------------------------------------------------- */
    
    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_entrada
     * 						$a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id='') {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach($a_id as $nom_id=>$val_id) {
                if (($nom_id == 'id_entidad') && $val_id !== '') { $this->iid_entidad = (int)$val_id; } // evitem SQL injection fent cast a integer
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_entidad = intval($a_id); // evitem SQL injection fent cast a integer
                $this->aPrimary_key = array('iid_entidad' => $this->iid_entidad);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('entidades');
    }
    
    /* METODES PUBLICS ----------------------------------------------------------*/
    
    public function getArrayTipo() {
        return [
            Cargo::AMBITO_CTR => _("ctr"),
            Cargo::AMBITO_DL  => _("dl"),
            Cargo::AMBITO_CR  => _("cr"),
            Cargo::AMBITO_CG  => _("cg"),
        ];
    }
        
        
    public function eliminarEsquema() {
        $err = ''; 
        $err .= $this->dropEsquema();
        
        return $err;
    }
        
    public function nuevoEsquema() {
        $err = ''; 
        $err .= $this->crearEsquema();
        $err .= $this->crearTablas();
        
        return $err;
    }
        
        
    /**
     * Tablas actuales
     * global:
     *    lugares              
     *    entidades
     *    x_locales
     *
     *  cada esquema:
     *    acciones
     *    aux_cargos
     *    aux_usuarios
     *    documentos
     *    entrada_adjuntos
     *    entrada_doc
     *    entrada_doc_bytea
     *    entrada_doc_json
     *    entrada_doc_txt
     *    entradas
     *    escrito_adjuntos
     *    escritos
     *    etiquetas
     *    etiquetas_documento
     *    etiquetas_expediente
     *    expediente_firmas
     *    expedientes
     *    pendientes
     *    plantillas
     *    tramite_cargo
     *    usuario_preferencias
     *    x_config
     *    x_tramites
     *
     *   sólo dl:
     *    cargos_grupos
     *    entradas_bypass
     *    lugares_grupos
     *    x_oficinas
     *    
     */
    private function crearTablas() {
        $err = ''; 
        // entradas:
        $err .= $this->ejecutarPsqlCrear('entradas');
        // para las dl:
        $tipo_entidad = $this->getTipo();
        if ($tipo_entidad === Cargo::AMBITO_DL) {
            // entradas_bypass
            $err .= $this->ejecutarPsqlCrear('entradas',TRUE);
        }
        // escritos:
        $err .= $this->ejecutarPsqlCrear('escritos');
        // expedientes:
        $err .= $this->ejecutarPsqlCrear('expedientes');
        // etiquetas:
        $err .= $this->ejecutarPsqlCrear('etiquetas');
        // documentos:
        $err .= $this->ejecutarPsqlCrear('documentos');
        // pendientes:
        $err .= $this->ejecutarPsqlCrear('pendientes');
        // plantillas:
        $err .= $this->ejecutarPsqlCrear('plantillas');
        // config:
        $err .= $this->ejecutarPsqlCrear('config');
        // usuarios:
        $err .= $this->ejecutarPsqlCrear('usuarios');
        // tramites (tiene que estar después de usuarios, porque depende de la tabla aux_cargos):
        $err .= $this->ejecutarPsqlCrear('tramites');

        // para las dl:
        if ($tipo_entidad === Cargo::AMBITO_DL) {
            // x_oficinas y cargos_grupos
            $err .= $this->ejecutarPsqlCrear('usuarios', TRUE);
            // lugares_grupos
            $err .= $this->ejecutarPsqlCrear('lugares', TRUE);
        }
        // INSERTS
        if ($this->getTipo() === Cargo::AMBITO_DL) {
            // insert cargos mínimos usuarios:
            $err .= $this->ejecutarPsqlInsert('usuarios', TRUE);
            $err .= $this->ejecutarPsqlInsert('tramites', TRUE);
            $err .= $this->ejecutarPsqlInsert('config', TRUE);
        } else {
            // insert cargos mínimos usuarios:
            $err .= $this->ejecutarPsqlInsert('usuarios');
            $err .= $this->ejecutarPsqlInsert('tramites');
            $err .= $this->ejecutarPsqlInsert('config');
        }
		// añadir la sigla en config:
		$err .= $this->ejecutarSql("INSERT INTO public.x_config (parametro, valor) VALUES ('sigla', '$this->snombre')");

        return $err;
    }
    
    private function ejecutarPsqlInsert($app,$dl=FALSE) {
        if ($dl) {
            $this->ssql_txt = file_get_contents("../../$app/db/insert_$app"."_dl.sql");
        } else {
            $this->ssql_txt = file_get_contents("../../$app/db/insert_$app.sql");
        }
        $this->snameFileLog = $this->getFileLog("$app");
        
        return $this->ejecutarPsql();
    }
    
    private function ejecutarPsqlCrear($app, $dl=FALSE) {
        if ($dl) {
            $this->ssql_txt = file_get_contents("../../$app/db/$app"."_dl.sql");
        } else {
            $this->ssql_txt = file_get_contents("../../$app/db/$app.sql");
        }
        $this->snameFileLog = $this->getFileLog("$app");
        
        return $this->ejecutarPsql();
    }
        
    private function ejecutarPsql() {
        $err_txt = '';
        $sql_txt = $this->ssql_txt;
        $file_log = $this->snameFileLog;
               
        // cambiar nombre esquema
        $nom_schema ="\\\"". $this->getSchema()."\\\"".'.';
        
        $sql_txt_nou = str_replace('public.',$nom_schema,$sql_txt);
        
        $command = "/usr/bin/psql -U tramity -d tramity -c << EOF \" $sql_txt_nou \" ";
        $command .= " 2> ".$file_log;
        //passthru($command); // no output to capture so no need to store it
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);
        // read the file, if empty all's well
        $error = file_get_contents($file_log);
        if(trim($error) != ''
            && stripos($error, 'error') !== FALSE // evitar los NOTICE y otros
            && ConfigGlobal::is_debug_mode()) {
           $err_txt .= sprintf("PSQL ERROR IN COMMAND(1): %s<br> mirar: %s<br>",$command,$file_log);
        }
        return $err_txt;
    }
    
    private function ejecutarSql($sql) {
        $oDbl = $this->getoDbl();
        $err_txt = '';
        
		// cambiar nombre esquema
		$nom_schema ="\"". $this->getSchema()."\"".'.';
		$sql_txt_nou = str_replace('public.',$nom_schema,$sql);
        
        if (($oDblSt = $oDbl->prepare($sql_txt_nou)) === false) {
            $sClauError = 'Entidad.sql.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err_txt .= sprintf("ERROR AL EJECUTAR SQL: $sClauError");
        } else {
            if ($oDblSt->execute() === false) {
                $sClauError = 'Entidad.sql.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                $err_txt .= sprintf("ERROR AL EJECUTAR SQL: $sClauError");
            }
        }
        return $err_txt;
    }
    
    private function dropEsquema() {
        $oDbl = $this->getoDbl();
        $nom_schema = $this->getSchema();
        $err_txt = '';
        $sql = "DROP SCHEMA IF EXISTS \"$nom_schema\" CASCADE ";
        
        if (($oDblSt = $oDbl->prepare($sql)) === false) {
            $sClauError = 'Entidad.eliminarSchema.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err_txt .= sprintf("ERROR AL ELIMINAR EL ESQUEMA: $sClauError");
        } else {
            if ($oDblSt->execute() === false) {
                $sClauError = 'Entidad.eliminarSchema.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                $err_txt .= sprintf("ERROR AL ELIMINAR EL ESQUEMA: $sClauError");
            }
        }
        return $err_txt;
    }
    
    private function crearEsquema() {
        $oDbl = $this->getoDbl();
        $nom_schema = $this->getSchema();
        $err_txt = '';
        $sql = "CREATE SCHEMA IF NOT EXISTS \"$nom_schema\" ";
        
        if (($oDblSt = $oDbl->prepare($sql)) === false) {
            $sClauError = 'Entidad.crearSchema.prepare';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err_txt .= sprintf("ERROR AL CREAR EL ESQUEMA: $sClauError");
        } else {
            if ($oDblSt->execute() === false) {
                $sClauError = 'Entidad.crearSchema.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                $err_txt .= sprintf("ERROR AL CREAR EL ESQUEMA: $sClauError");
            }
        }
        return $err_txt;
    }
    
    private function getDir() {
        $this->sdir = empty($this->sdir)? ConfigGlobal::$directorio.'/log/db' : $this->sdir;
        return $this->sdir;
    }
    private function getFileLog($filename='tramity') {
        $this->sfileLog = $this->getDir().'/'.$filename.'.pg_error.sql';

        $command = "touch ".$this->sfileLog;
        passthru($command); // no output to capture so no need to store it
        
        return $this->sfileLog;
    }
}