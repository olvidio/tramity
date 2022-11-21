<?php

namespace devel\controller;

use core\ConfigGlobal;
use web\DateTimeLocal;

/**
 * programa per generar les classes a partir de la taula
 *
 */
/**
 * Para asegurar que inicia la sesión, y poder acceder a los permisos
 */
// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
require_once("apps/devel/controller/func_factory.php");
// FIN de  Cabecera global de URL de controlador ********************************

$Q_db = (string)filter_input(INPUT_POST, 'db');
$Q_tabla = (string)filter_input(INPUT_POST, 'tabla');
$Q_clase = (string)filter_input(INPUT_POST, 'clase');
$Q_clase_plural = (string)filter_input(INPUT_POST, 'clase_plural');
$Q_grupo = (string)filter_input(INPUT_POST, 'grupo');
$Q_aplicacion = (string)filter_input(INPUT_POST, 'aplicacion');

if (empty($Q_tabla)) {
    exit("Ha de dir quina taula");
}
// si la tabla tiene el schema, hay que separalo:
$schema_sql = '';
$tabla = $Q_tabla;
$schema = strtok($tabla, '.');
if ($schema !== $tabla) {
    $tabla = strtok('.');
    $schema_sql = "and n.nspname='$schema' ";
} else {
    $schema = 'public';
}


if (isset($Q_db)) {
    switch ($Q_db) {
        case "tramity":
            $oDbl = $oDBT;
            $oDB_txt = 'oDBT';
            $prefix = '';
            break;
        case "davical":
            $oDbl = $oDBDavical;
            $oDB_txt = 'oDBDavical';
            $prefix = '';
            break;
        default:
            exit("Ha de dir quina base de dades");
    }
} else {
    exit("Ha de dir quina base de dades");
}


$clase = !empty($Q_clase) ? $Q_clase : $tabla;
if (!empty($Q_clase_plural)) {
    $clase_plural = $Q_clase_plural;
} else {
    //plural de la clase
    if (preg_match('/[aeiou]$/', $clase)) {
        $clase_plural = $clase . 's';
    } else {
        $clase_plural = $clase . 'es';
    }
}

$grupo = !empty($Q_grupo) ? $Q_grupo : "actividades";
$aplicacion = !empty($Q_aplicacion) ? $Q_aplicacion : "delegación";

//busco les claus primaries
$aClaus = primaryKey($oDbl, $Q_tabla);

$sql = "SELECT 
				a.attnum,
				a.attname AS field, 
				t.typname AS type, 
				a.attlen AS length,
				a.atttypmod AS lengthvar,
				a.attnotnull AS notnull
			FROM 
				pg_catalog.pg_class c,
				pg_catalog.pg_attribute a,
				pg_catalog.pg_type t,
				pg_catalog.pg_namespace n
			WHERE 
				c.relname = '$tabla'
				and a.attnum > 0
				and a.attrelid = c.oid
				and a.atttypid = t.oid
				and n.oid = c.relnamespace
				and n.nspname='$schema'
			ORDER BY a.attnum
";

$ATRIBUTOS = '
	/**
	 * aPrimary_key de ' . $clase . '
	 *
	 * @var array
	 */
	 private $aPrimary_key;

	/**
	 * aDades de ' . $clase . '
	 *
	 * @var array
	 */
	 private $aDades;

	/**
	 * bLoaded de ' . $clase . '
	 *
	 * @var boolean
	 */
	 private $bLoaded = FALSE;

	/**
	 * Id_schema de ' . $clase . '
	 *
	 * @var integer
	 */
	 private $iid_schema;
';
$add_convert = FALSE;
$add_JSON = FALSE;
$c = 0;
$cl = 0;
$id_seq = "";
$id_seq2 = "";
$guardar = "";
$update = "";
$campos = "";
$valores = "";
$exists = "";
$ToEmpty = "";
$gets = "";
$altres_gets = "";
$altres_gets_set = "";
$query_if = "";
$err_bool = "";
$a_auto = array();
foreach ($oDbl->query($sql) as $row) {
    $nomcamp = $row['field'];
    if ($nomcamp === 'id_schema') {
        continue;
    }
    $NomCamp = ucwords($nomcamp);
    $tipo = $row['type'];
    $not_null = $row['notnull'];

    $sql_get_default = "SELECT pg_get_expr(adbin, adrelid) AS rowdefault
				FROM pg_catalog.pg_attrdef d,
					 pg_catalog.pg_class c,
					 pg_catalog.pg_namespace n
				WHERE 
					c.relname = '$tabla'
					and c.oid = d.adrelid
					and n.oid = c.relnamespace
					and n.nspname='$schema'
					and d.adnum =" . $row['attnum'];

    //echo "sql_def: $sql_get_default<br>";
    $default = $oDbl->query($sql_get_default)->fetchColumn();
    $auto = 0;
    if (!empty($default)) { //nomes agafo un. li dono preferencia al id_local
        $matches = [];
        if (preg_match("/id_local\('(\w+)'.*$/", $default, $matches) || preg_match("/id_ubi\('(\w+)'.*$/", $default, $matches)) {
            $id_seq = $matches[1];
            $auto = 1;
            $a_auto[] = $nomcamp;
        } else {
            if (preg_match("/nextval\('(\w+)'.*$/", $default, $matches)) {
                $id_seq2 = $matches[1];
                $auto = 1;
                $a_auto[] = $nomcamp;
            }
        }
    }
    //echo "${_POST['ficha']}\n$nomcamp_post\n";

    switch ($tipo) {
        case '_int8':
        case '_int4':
        case '_int2':
            $tipo_db = 'array';
            $tip = 'a_';
            $tip_val = '';
            break;
        case 'int8':
        case 'int4':
        case 'int2':
            $tipo_db = 'integer';
            $tip = 'i';
            $tip_val = '';
            break;
        case 'float4':
        case 'double':
        case 'numeric':
            $tipo_db = 'float';
            $tip = 'i';
            $tip_val = '';
            break;
        case 'text':
        case 'varchar':
            $tipo_db = 'string';
            $tip = 's';
            $tip_val = '';
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $tipo_db = 'web\\DateTimeLocal';
            $tip = 'd';
            $tip_val = '';
            break;
        case 'time':
            $tipo_db = 'string time';
            $tip = 't';
            $tip_val = '';
            break;
        case 'bool':
            $tipo_db = 'boolean';
            $tip = 'b';
            $tip_val = 'f';
            break;
        case 'json':
        case 'jsonb':
            $tipo_db = 'object JSON';
            $tip = '';
            $tip_val = '';
            break;
    }
    $ATRIBUTOS .= '
	/**
	 * ' . $NomCamp . ' de ' . $clase . '
	 *
	 * @var ' . $tipo_db . '
	 */
	 private $' . $tip . $nomcamp . ';';

    switch ($tipo) {
        case '_int8':
        case '_int4':
        case '_int2':
            $gets .= '
	/**
	 * Recupera l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 *
	 * @return ' . $tipo_db . ' ' . $tip . $nomcamp . '
	 */
	function get' . $NomCamp . '() {
		if (!isset($this->' . $tip . $nomcamp . ') && !$this->bLoaded) {
			$this->DBCargar();
		}
        return core\array_pg2php($this->' . $tip . $nomcamp . ');
	}';
            break;
        case 'json':
        case 'jsonb':
            $gets .= '
	/**
	 * Recupera l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 *
	 * @param boolean $bArray si hay que devolver un array en vez de un objeto.
	 * @return ' . $tipo_db . ' ' . $tip . $nomcamp . '
	 */
	function get' . $NomCamp . '($bArray=FALSE) {
		if (!isset($this->' . $tip . $nomcamp . ') && !$this->bLoaded) {
			$this->DBCargar();
		}
        $oJSON = json_decode($this->' . $tip . $nomcamp . ',$bArray);
	    if (empty($oJSON) || $oJSON == \'[]\') {
	        if ($bArray) {
	            $oJSON = [];
	        } else {
	            $oJSON = new stdClass;
	        }
	    }
	    return $oJSON;
	}';
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $gets .= '
	/**
	 * Recupera l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 *
	 * @return ' . $tipo_db . ' ' . $tip . $nomcamp . '
	 */
	function get' . $NomCamp . '() {
		if (!isset($this->' . $tip . $nomcamp . ') && !$this->bLoaded) {
			$this->DBCargar();
		}
		if (empty($this->' . $tip . $nomcamp . ')) {
			return new web\NullDateTimeLocal();
		}
        $oConverter = new core\Converter(\'' . $tipo . '\', $this->' . $tip . $nomcamp . ');
		return $oConverter->fromPg();
	}';
            break;
        default:
            $gets .= '
	/**
	 * Recupera l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 *
	 * @return ' . $tipo_db . ' ' . $tip . $nomcamp . '
	 */
	function get' . $NomCamp . '() {
		if (!isset($this->' . $tip . $nomcamp . ') && !$this->bLoaded) {
			$this->DBCargar();
		}
		return $this->' . $tip . $nomcamp . ';
	}';
    }

    if (in_array($nomcamp, $aClaus)) {
        $aClaus2[$nomcamp] = $tip . $nomcamp;
        $gets .= '
	/**
	 * estableix el valor de l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 *
	 * @param ' . $tipo_db . ' ' . $tip . $nomcamp . '
	 */
	function set' . $NomCamp . '($' . $tip . $nomcamp . ') {
		$this->' . $tip . $nomcamp . ' = $' . $tip . $nomcamp . ';
	}';
    } else {
        switch ($tipo) {
            case '_int8':
            case '_int4':
            case '_int2':
                $gets .= '
	/**
	 * estableix el valor de l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 * 
	 * @param ' . $tipo_db . ' ' . $tip . $nomcamp . '
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
	 *  o es una variable de php hay que convertirlo.
	 */
	function set' . $NomCamp . '($' . $tip . $nomcamp . '=\'' . $tip_val . '\',$db=FALSE) {
        if ($db === FALSE) {
	        $postgresArray = core\array_php2pg($' . $tip . $nomcamp . ');
	    } else {
	        $postgresArray = $' . $tip . $nomcamp . ';
	    }
        $this->' . $tip . $nomcamp . ' = $postgresArray;
	}';
                break;
            case 'json':
            case 'jsonb':
                $gets .= '
	/**
	 * estableix el valor de l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 * 
	 * @param ' . $tipo_db . ' ' . $tip . $nomcamp . '
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
	 *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
	 */
	function set' . $NomCamp . '($oJSON,$db=FALSE) {
        if ($db === FALSE) {
	        $json = json_encode($oJSON);
	    } else {
	        $json = $oJSON;
	    }
        $this->' . $tip . $nomcamp . ' = $json;
	}';
                break;
            case 'date':
            case 'timestamp':
            case 'timestamptz';
                $gets .= '
	/**
	 * estableix el valor de l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 * Si ' . $tip . $nomcamp . ' es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
	 * Si convert es FALSE, ' . $tip . $nomcamp . ' debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param ' . $tipo_db . '|string ' . $tip . $nomcamp . '=\'' . $tip_val . '\' optional.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	function set' . $NomCamp . '($' . $tip . $nomcamp . '=\'' . $tip_val . '\',$convert=TRUE) {
        if ($convert === TRUE  && !empty($' . $tip . $nomcamp . ')) {
            $oConverter = new core\Converter(\'' . $tipo . '\', $' . $tip . $nomcamp . ');
            $this->' . $tip . $nomcamp . ' = $oConverter->toPg();
	    } else {
            $this->' . $tip . $nomcamp . ' = $' . $tip . $nomcamp . ';
	    }
	}';
                break;
            default:
                $gets .= '
	/**
	 * estableix el valor de l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 *
	 * @param ' . $tipo_db . ' ' . $tip . $nomcamp . '=\'' . $tip_val . '\' optional
	 */
	function set' . $NomCamp . '($' . $tip . $nomcamp . '=\'' . $tip_val . '\') {
		$this->' . $tip . $nomcamp . ' = $' . $tip . $nomcamp . ';
	}';

        }

        $altres_gets .= '
	/**
	 * Recupera les propietats de l\'atribut ' . $tip . $nomcamp . ' de ' . $clase . '
	 * en una clase del tipus DatosCampo
	 *
	 * @return core\DatosCampo
	 */
	function getDatos' . $NomCamp . '() {
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new core\\DatosCampo(array(\'nom_tabla\'=>$nom_tabla,\'nom_camp\'=>\'' . $nomcamp . '\'));
		$oDatosCampo->setEtiqueta(_("' . $nomcamp . '"));
		return $oDatosCampo;
	}';
        $altres_gets_set .= "\n\t\t" . '$o' . $clase . 'Set->add($this->getDatos' . $NomCamp . '());';

    }

    switch ($tipo) {
        case '_int8':
        case '_int4':
        case '_int2':
        case 'json':
        case 'jsonb':
            $add_JSON = TRUE;
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDades)) { $this->set' . $NomCamp . '($aDades[\'' . $nomcamp . '\'],TRUE); }';
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $add_convert = TRUE;
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDades)) { $this->set' . $NomCamp . '($aDades[\'' . $nomcamp . '\'],$convert); }';
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
            break;
        default:
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDades)) { $this->set' . $NomCamp . '($aDades[\'' . $nomcamp . '\']); }';
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
    }

    if (!in_array($nomcamp, $aClaus)) {
        if ($auto != 1) { // si tiene secuencia no pongo el campo en el update.
            if ($tip === 'b') {
                $err_bool .= "\n\t\t" . 'if ( core\is_true($aDades[\'' . $nomcamp . '\']) ) { $aDades[\'' . $nomcamp . '\']=\'true\'; } else { $aDades[\'' . $nomcamp . '\']=\'false\'; }';
            }
            $guardar .= "\n\t\t" . '$aDades[\'' . $nomcamp . '\'] = $this->' . $tip . $nomcamp . ';';
            if ($cl > 0) $update .= ",\n";
            $update .= "\t\t\t\t\t" . $nomcamp;
            // para intentar que los = salgan en la misma columna
            $n = strlen($nomcamp);
            for ($s = $n; $s < 25; $s++) {
                $update .= " ";
            }
            $update .= '= :' . $nomcamp;
            $cl++;
        }
    }
    if ($auto != 1) { // si tiene sequencia no pongo el campo en el insert.
        if ($c > 0) $campos .= ",";
        $campos .= $nomcamp;
        if ($c > 0) $valores .= ",";
        $valores .= ':' . $nomcamp;
        $c++;
    }
}
$oHoy = new DateTimeLocal();
$hoy = $oHoy->getFromLocal();

$txt = "<?php
namespace $grupo\\model\\entity;
use core;";

if ($add_convert === TRUE) {
    $txt .= "\nuse web;";
}
if ($add_JSON === TRUE) {
    $txt .= "\nuse stdClass;";
}

$txt .= "
/**
 * Fitxer amb la Classe que accedeix a la taula $tabla
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created $hoy
 */
/**
 * Classe que implementa l'entitat $tabla
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created $hoy
 */
class $clase Extends core\ClasePropiedades {
	/* ATRIBUTOS ----------------------------------------------------------------- */
";
$txt .= $ATRIBUTOS;
$txt .= "\n\t" . '/* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */';

$txt .= '
	/**
	 * oDbl de ' . $clase . '
	 *
	 * @var object
	 */
	 protected $oDbl;
	/**
	 * NomTabla de ' . $clase . '
	 *
	 * @var string
	 */
	 protected $sNomTabla;';

$i = 0;
$claus_txt = '';
$claus_txt2 = '';
$claus_if = '';
$guardar_if = '';
$where = '';
$claus_isset = '';
$claus_query = "";
$claus_getPrimary = "";
foreach ($aClaus2 as $clau => $nom_clau) {
    //$nom_clau="i".$clau;
    if (!empty($claus_txt)) $claus_txt .= ",";
    $claus_txt .= $nom_clau;
    if ($i > 0) $claus_txt2 .= ",\n\t\t\t\t\t\t\t";
    $claus_txt2 .= "'$clau' => " . '$aDades[\'' . $clau . '\']';
    if ($i > 0) $claus_if .= "\n";
    switch (substr($nom_clau, 0, 1)) {
        case 'i':
            $claus_if .= "\t\t\t\t" . 'if (($nom_id === \'' . $clau . '\') && $val_id !== \'\') { $this->' . $nom_clau . ' = (int)$val_id; }';
            break;
        case 's':
            $claus_if .= "\t\t\t\t" . 'if (($nom_id === \'' . $clau . '\') && $val_id !== \'\') { $this->' . $nom_clau . ' = (string)$val_id; } // evitem SQL injection fent cast a string';
            break;
        case 'b':
            $claus_if .= "\t\t\t\t" . 'if (($nom_id === \'' . $clau . '\') && $val_id !== \'\') { $this->' . $nom_clau . ' = (bool)$val_id; } // evitem SQL injection fent cast a boolean';
            break;
    }
    // si no es auto
    if (!in_array($clau, $a_auto)) {
        if (!empty($guardar_if)) $guardar_if .= ", ";
        $guardar_if .= '$this->' . $nom_clau;
    }
    if ($i > 0) $where .= " AND ";
    $where .= $clau . '=\'$this->' . $nom_clau . '\'';
    if ($i > 0) $claus_isset .= " && ";
    $claus_isset .= 'isset($this->' . $nom_clau . ')';
    $claus_query .= "\n\t\t\t" . '$' . $nom_clau . ' = $aDades[\'' . $clau . '\'];';
    if (!empty($claus_getPrimary)) $claus_getPrimary .= ",";
    $claus_getPrimary .= '\'' . $clau . '\' => $this->' . $nom_clau;
    $i++;
}
$txt .= '
	/* CONSTRUCTOR -------------------------------------------------------------- */

	/**
	 * Constructor de la classe.
	 * Si només necessita un valor, se li pot passar un integer.
	 * En general se li passa un array amb les claus primàries.
	 *
	 * @param integer|array ' . $claus_txt . '
	 * 						$a_id. Un array con los nombres=>valores de las claves primarias.
	 */';

$sForPrimaryK = 'if (is_array($a_id)) { 
			$this->aPrimary_key = $a_id;
			foreach($a_id as $nom_id=>$val_id) {
' . $claus_if . '
			}';
if (count($aClaus2) > 1) { // per el cas de només una clau.
    $sForPrimaryK .= "\n\t\t}";
} else {
    $sForPrimaryK .= "\n\t\t" . '} else {
			if (isset($a_id) && $a_id !== \'\') {
				$this->' . $claus_txt . ' = (int)$a_id;
				$this->aPrimary_key = array(\'' . $claus_txt . '\' => $this->' . $claus_txt . ');
			}
		}';
}

$txt .= "\n\t" . 'function __construct($a_id=\'\') {
		$oDbl = $GLOBALS[\'' . $oDB_txt . '\'];';
$txt .= "\n\t\t" . $sForPrimaryK;

$txt .= "\n\t\t" . '$this->setoDbl($oDbl);
		$this->setNomTabla(\'' . $tabla . '\');
	}';


$txt .= '

	/* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

	/**
	 * Desa els ATRIBUTOS de l\'objecte a la base de dades.
	 * Si no hi ha el registre, fa el insert, si hi es fa el update.
	 *
	 */
	public function DBGuardar() {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if ($this->DBCargar(\'guardar\') === FALSE) { $bInsert=TRUE; } else { $bInsert=FALSE; }
		$aDades=array();';
$txt .= $guardar;
$txt .= '
		array_walk($aDades, \'core\\poner_null\');';
if ($err_bool) {
    $txt .= "\n\t\t//para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:";
    $txt .= $err_bool;
}
$txt .= "\n\n\t\t" . 'if ($bInsert === FALSE) {
			//UPDATE
			$update="
';
$txt .= $update . '";';
$txt .= '
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE ' . $where . '")) === FALSE) {
				$sClauError = \'' . $clase . '.update.prepare\';
				$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = \'' . $clase . '.update.execute\';
					$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}
		} else {
			// INSERT';
if (!empty($guardar_if)) {
    $txt .= "\n\t\t\t" . 'array_unshift($aDades, ' . $guardar_if . ');';
}
$txt .= "\n\t\t\t" . '$campos="(';
$txt .= $campos . ')";' . "\n";
$txt .= "\t\t\t" . '$valores="(';
$txt .= $valores . ')";';
$txt .= '		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClauError = \'' . $clase . '.insertar.prepare\';
				$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			} else {
				try {
					$oDblSt->execute($aDades);
				}
				catch ( \PDOException $e) {
					$err_txt=$e->errorInfo[2];
					$this->setErrorTxt($err_txt);
					$sClauError = \'' . $clase . '.insertar.execute\';
					$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
					return FALSE;
				}
			}';
if ($id_seq || $id_seq2) {
    if (empty($id_seq2)) {
        $id_seq = $id_seq;
        $ccc = 'i' . end($a_auto);
    } else {
        $id_seq = $id_seq2;
        $ccc = 'i' . end($a_auto);
    }
    $txt .= "\n\t\t\t" . '$this->' . $ccc . ' = $oDbl->lastInsertId(\'' . $id_seq . '\');';
}
$txt .= "\n\t\t" . '}
		$this->setAllAtributes($aDades);
		return TRUE;
	}

	/**
	 * Carrega els camps de la base de dades com ATRIBUTOS de l\'objecte.
	 *
	 */
	public function DBCargar($que=null) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (' . $claus_isset . ') {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE ' . $where . '")) === FALSE) {
				$sClauError = \'' . $clase . '.carregar\';
				$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
				return FALSE;
			}
			$aDades = $oDblSt->fetch(\\PDO::FETCH_ASSOC);
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
			switch ($que) {
				case \'tot\':
                    $this->setAllAtributes($aDades);
					break;
				case \'guardar\':
					if (!$oDblSt->rowCount()) return FALSE;
					break;
                default:
					// En el caso de no existir esta fila, $aDades = FALSE:
					if ($aDades === FALSE) {
						return FALSE;
					}
					$this->setAllAtributes($aDades);
			}
			return TRUE;
		} else {
		   	return FALSE;
		}
	}

	/**
	 * Elimina el registre de la base de dades corresponent a l\'objecte.
	 *
	 */
	public function DBEliminar() {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE ' . $where . '")) === FALSE) {
			$sClauError = \'' . $clase . '.eliminar\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		return TRUE;
	}
	
	/* OTOS MÉTODOS  ----------------------------------------------------------*/
';

$txt .= '	/* MÉTODOS PRIVADOS ----------------------------------------------------------*/

	/**
	 * Establece el valor de todos los atributos
	 *
	 * @param array $aDades
	 */';
if ($add_convert === TRUE) {
    $txt .= "\n\t" . 'function setAllAtributes($aDades,$convert=FALSE) {';
} else {
    $txt .= "\n\t" . 'private function setAllAtributes($aDades) {';
}
$txt .= "\n\t\t" . 'if (!is_array($aDades)) { return; }
		if (array_key_exists(\'id_schema\',$aDades)) { $this->setId_schema($aDades[\'id_schema\']); }';

$txt .= $exists;
$txt .= "\n\t" . '}';

	/* MÉTODOS GET y SET --------------------------------------------------------*/

	/**
	 * Recupera tots els ATRIBUTOS de ' . $clase . ' en un array
	 *
	 * @return array aDades
	 */
	function getTot() {
		if (!is_array($this->aDades)) {
			$this->DBCargar(\'tot\');
		}
		return $this->aDades;
	}

	/**
	 * Recupera las claus primàries de ' . $clase . ' en un array
	 *
	 * @return array aPrimary_key
	 */
	function getPrimary_key() {
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array(' . $claus_getPrimary . ');
		}
		return $this->aPrimary_key;
	}
	/**
	 * Estableix las claus primàries de ' . $clase . ' en un array
	 *
	 */
	public function setPrimary_key($a_id=\'\') {
	    ' . $sForPrimaryK . '
	}
	
';

$txt .= $gets;

$txt .= '
	/* MÉTODOS GET y SET D\'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

	/**
	 * Retorna una col·lecció d\'objectes del tipus DatosCampo
	 *
	 */
	function getDatosCampos() {
		$o' . $clase . 'Set = new core\Set();
';
$txt .= $altres_gets_set;
$txt .= '
		return $o' . $clase . 'Set->getTot();
	}


';
$txt .= $altres_gets;
$txt .= '
}
';

/* ESCRIURE LA CLASSSE ------------------------------------------------ */
$filename = ConfigGlobal::DIR . '/apps/' . $grupo . '/model/entity/' . strtolower($Q_clase) . '.class.php';

if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}

// Write $somecontent to our opened file.
if (fwrite($handle, $txt) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}

echo "Success, wrote (somecontent) to file ($filename)";

fclose($handle);

chmod($filename, 0775);
//chown($filename, 'dani'); No se puede por falta de permisos
chgrp($filename, 'www-data');

/* CONSTRUIR EL GESTOR ------------------------------------------------ */
$gestor = "Gestor" . ucfirst($clase);
$txt2 = "<?php
namespace $grupo\\model\\entity;
use core;
/**
 * $gestor
 *
 * Classe per gestionar la llista d'objectes de la clase $clase
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created $hoy
 */

class $gestor Extends core\ClaseGestor {
	/* ATRIBUTOS ----------------------------------------------------------------- */

	/* CONSTRUCTOR -------------------------------------------------------------- */
";

$txt2 .= '

	/**
	 * Constructor de la classe.
	 *
	 * @return $gestor
	 *
	 */
	function __construct() {
		$oDbl = $GLOBALS[\'' . $oDB_txt . '\'];
		$this->setoDbl($oDbl);
		$this->setNomTabla(\'' . $tabla . '\');
	}


	/* MÉTODOS PÚBLICOS -----------------------------------------------------------*/
';

$txt2 .= '
	/**
	 * retorna l\'array d\'objectes de tipus ' . $clase . '
	 *
	 * @param string sQuery la query a executar.
	 * @return array Una col·lecció d\'objectes de tipus ' . $clase . '
	 */
	function get' . $clase_plural . 'Query($sQuery=\'\') {
		$oDbl = $this->getoDbl();
		$o' . $clase . 'Set = new core\Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClauError = \'' . $gestor . '.query\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {';
$txt2 .= "\n\t\t\t" . '$a_pkey = array(' . $claus_txt2 . ');';
$txt2 .= "\n\t\t\t" . '$o' . $clase . '= new ' . $clase . '($a_pkey);';
$txt2 .= '
			$o' . $clase . 'Set->add($o' . $clase . ');
		}
		return $o' . $clase . 'Set->getTot();
	}
';

$txt2 .= '
	/**
	 * retorna l\'array d\'objectes de tipus ' . $clase . '
	 *
	 * @param array aWhere associatiu amb els valors de les variables amb les quals farem la query
	 * @param array aOperators associatiu amb els valors dels operadors que cal aplicar a cada variable
	 * @return array Una col·lecció d\'objectes de tipus ' . $clase . '
	 */
	function get' . $clase_plural . '($aWhere=array(),$aOperators=array()) {
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$o' . $clase . 'Set = new core\Set();
		$oCondicion = new core\Condicion();
		$aCondi = array();';
$txt2 .= '
		foreach ($aWhere as $camp => $val) {
			if ($camp == \'_ordre\') { continue; }
			if ($camp == \'_limit\') { continue; }
			$sOperador = isset($aOperators[$camp])? $aOperators[$camp] : \'\';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondi[]=$a; }
			// operadores que no requieren valores
			if ($sOperador == \'BETWEEN\' || $sOperador == \'IS NULL\' || $sOperador == \'IS NOT NULL\' || $sOperador == \'OR\') { unset($aWhere[$camp]); }
            if ($sOperador == \'IN\' || $sOperador == \'NOT IN\') { unset($aWhere[$camp]); }
            if ($sOperador == \'TXT\') { unset($aWhere[$camp]); }
		}';

$txt2 .= "\n\t\t" . '$sCondi = implode(\' AND \',$aCondi);
		if ($sCondi!=\'\') { $sCondi = " WHERE ".$sCondi; }
		$sOrdre = \'\';
        $sLimit = \'\';
		if (isset($aWhere[\'_ordre\']) && $aWhere[\'_ordre\']!=\'\') { $sOrdre = \' ORDER BY \'.$aWhere[\'_ordre\']; }
		if (isset($aWhere[\'_ordre\'])) { unset($aWhere[\'_ordre\']); }
		if (isset($aWhere[\'_limit\']) && $aWhere[\'_limit\']!=\'\') { $sLimit = \' LIMIT \'.$aWhere[\'_limit\']; }
		if (isset($aWhere[\'_limit\'])) { unset($aWhere[\'_limit\']); }
		$sQry = "SELECT * FROM $nom_tabla ".$sCondi.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClauError = \'' . $gestor . '.llistar.prepare\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClauError = \'' . $gestor . '.llistar.execute\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {';
$txt2 .= "\n\t\t\t" . '$a_pkey = array(' . $claus_txt2 . ');';
$txt2 .= "\n\t\t\t" . '$o' . $clase . ' = new ' . $clase . '($a_pkey);';
$txt2 .= '
			$o' . $clase . 'Set->add($o' . $clase . ');
		}
		return $o' . $clase . 'Set->getTot();
	}
';
$txt2 .= '
	/* MÉTODOS PROTECTED --------------------------------------------------------*/

	/* MÉTODOS GET y SET --------------------------------------------------------*/
}
';
/* ESCRIURE LA CLASSSE ------------------------------------------------ */
$filename = ConfigGlobal::DIR . '/apps/' . $grupo . '/model/entity/gestor' . strtolower($Q_clase) . '.class.php';


if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}

// Write $somecontent to our opened file.
if (fwrite($handle, $txt2) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}

echo "<br>Success, wrote gestor to file ($filename)";

fclose($handle);

chmod($filename, 0775);
//chown($filename, 'dani'); No se puede por falta de permisos
chgrp($filename, 'www-data');
