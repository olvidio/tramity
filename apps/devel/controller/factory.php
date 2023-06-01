<?php

namespace devel\controller;

use core\ConfigGlobal;
use web\DateTimeLocal;
use function core\is_true;

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
	 private array $aPrimary_key;

	/**
	 * bLoaded de ' . $clase . '
	 *
	 * @var boolean
	 */
	 private bool $bLoaded = FALSE;

';
$add_convert = FALSE;
$a_use_txt = [];
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
$bytea_bind = '';
$bytea_dades = '';
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
    $null = (is_true($row['notnull']))? 'null' : '';

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
    //echo "{$_POST['ficha']}\n$nomcamp_post\n";

    switch ($tipo) {
        case '_int8':
        case '_int4':
        case '_int2':
            $tipo_db = 'array';
            $tip = 'a_';
            $tip_val = '';
            $a_use_txt['array_pg2php'] = "use function core\array_pg2php";
            $a_use_txt['array_php2pg'] = "use function core\array_php2pg";
            break;
        case 'int8':
        case 'int4':
        case 'int2':
            $tipo_db = 'int';
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
        case 'bpchar':
            $tipo_db = 'string';
            $tip = 's';
            $tip_val = '';
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $tipo_db = 'DateTimeLocal';
            $tip = 'd';
            $tip_val = '';
            $a_use_txt['DateTimeLocal'] = "use web\DateTimeLocal";
            $a_use_txt['NullDateTimeLocal'] = "use web\NullDateTimeLocal";
            $a_use_txt['ConverterDate'] = "use core\ConverterDate";
            break;
        case 'time':
            $tipo_db = 'string time';
            $tip = 't';
            $tip_val = '';
            break;
        case 'bool':
            $tipo_db = 'boolean';
            $tip = 'b';
            $tip_val = 'FALSE';
            $a_use_txt['is_true'] = "use function core\is_true";
            break;
        case 'json':
        case 'jsonb':
            $tipo_db = 'string';
            $tip = '';
            $tip_val = '';
            $a_use_txt['ConverterJson'] = "use core\ConverterJson";
            $a_use_txt['JsonException'] = "use JsonException";
            $a_use_txt['stdClass'] = "use stdClass";
            break;
        case 'bytea':
            $tipo_db = 'string';
            $tip = 's';
            $tip_val = '';
            $bytea_dades .= "\n\t\t\t";
            $bytea_dades .= '$aDades[\''.$nomcamp.'\'] = $'.$tip.$nomcamp.';';
            $bytea_bind .= "\n\t\t\t";
            $bytea_bind .= '$'.$tip.$nomcamp." = '';";
            $bytea_bind .= "\n\t\t\t";
            $bytea_bind .= '$oDblSt->bindColumn(\''.$nomcamp.'\', $'.$tip.$nomcamp.', PDO::PARAM_STR);';

            break;
    }
    if (empty($null)) {
        $tipo_db_txt = $tipo_db."|null";
        $tip_txt = "?".$tipo_db;
        $val_default = ' = null';
    } else {
        $tipo_db_txt = $tipo_db;
        $tip_txt = $tipo_db;
        $val_default = '';
    }
    $ATRIBUTOS .= '
	/**
	 * ' . $NomCamp . ' de ' . $clase . '
	 *
	 * @var ' . $tipo_db_txt .'
	 */
	 private ' . $tip_txt. ' $' . $tip . $nomcamp . $val_default . ';';

    switch ($tipo) {
        case '_int8':
        case '_int4':
        case '_int2':
            $gets .= '
	/**
	 *
	 * @return ' . $tipo_db_txt . ' $' . $tip . $nomcamp . '
	 */
	public function get' . $NomCamp . '(): '.$tipo_db_txt.'
	{
		if (!isset($this->' . $tip . $nomcamp . ') && !$this->bLoaded) {
			$this->DBCargar();
		}
        return array_pg2php($this->' . $tip . $nomcamp . ');
	}';
            break;
        case 'json':
        case 'jsonb':
            $gets .= '
	/**
	 *
	 * @param boolean $bArray si hay que devolver un array en vez de un objeto.
	 * @return ' . $tipo_db_txt . ' $' . $tip . $nomcamp . '
	 * @throws JsonException
	 */
	public function get' . $NomCamp . '($bArray=FALSE): '.$tipo_db_txt.'
	{
		if (!isset($this->' . $tip . $nomcamp . ') && !$this->bLoaded) {
			$this->DBCargar();
		}
		return (new ConverterJson($this->'. $tip . $nomcamp .'json_visto, $bArray))->fromPg();
	}';
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $gets .= '
	/**
	 *
	 * @return DateTimeLocal|NullDateTimeLocal' . ' $' . $tip . $nomcamp . '
	 * @throws JsonException
	 */
	public function get' . $NomCamp . '(): DateTimeLocal|NullDateTimeLocal
	{
		if (!isset($this->' . $tip . $nomcamp . ') && !$this->bLoaded) {
			$this->DBCargar();
		}
		if (empty($this->' . $tip . $nomcamp . ')) {
			return new NullDateTimeLocal();
		}
        $oConverter = new ConverterDate(\'' . $tipo . '\', $this->' . $tip . $nomcamp . ');
		return $oConverter->fromPg();
	}';
            break;
        default:
            $gets .= '
	/**
	 *
	 * @return ' . $tipo_db_txt . ' $' . $tip . $nomcamp . '
	 */
	public function get' . $NomCamp . '(): '.$tip_txt.'
	{
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
	 *
	 * @param ' . $tipo_db_txt . ' $'.$tip.$nomcamp . '
	 */
	public function set' . $NomCamp . '('.$tip_txt.' $' . $tip . $nomcamp . '): void
	{
		$this->' . $tip . $nomcamp . ' = $' . $tip . $nomcamp . ';
	}';
    } else {
        switch ($tipo) {
            case '_int8':
            case '_int4':
            case '_int2':
                $gets .= '
	/**
	 * 
	 * @param ' . $tipo_db_txt . ' $' . $tip . $nomcamp . '
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
	 *  o es una variable de php hay que convertirlo.
	 */
	public function set' . $NomCamp . '($' . $tip . $nomcamp . '=\'' . $tip_val . '\',$db=FALSE): void
	{
        if ($db === FALSE) {
	        $postgresArray = array_php2pg($' . $tip . $nomcamp . ');
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
	 * 
	 * @param ' . $tipo_db_txt . ' $' . $tip . $nomcamp . '
     * @param boolean $db=FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
	 *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
	 * @throws JsonException
	 */
	public function set' . $NomCamp . '(' . $tipo_db_txt . ' $oJSON, bool $db=FALSE): void
	{
        $this->' . $tip . $nomcamp . ' = (new ConverterJson($oJSON, FALSE))->toPg($db);
	}';
                break;
            case 'date':
            case 'timestamp':
            case 'timestamptz';
                $gets .= '
	/**
	 * Si $' . $tip . $nomcamp . ' es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
	 * Si convert es FALSE, $' . $tip . $nomcamp . ' debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
	 * 
	 * @param DateTimeLocal|string|null $' . $tip . $nomcamp.'
     * @param boolean $convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
	 */
	public function set' . $NomCamp . '(DateTimeLocal|string|null $' . $tip . $nomcamp . '=\'\', bool $convert=TRUE): void
	{
        if ($convert === TRUE  && !empty($' . $tip . $nomcamp . ')) {
            $oConverter = new ConverterDate(\'' . $tipo . '\', $' . $tip . $nomcamp . ');
            $this->' . $tip . $nomcamp . ' = $oConverter->toPg();
	    } else {
            $this->' . $tip . $nomcamp . ' = $' . $tip . $nomcamp . ';
	    }
	}';
                break;
            default:
                $gets .= '
	/**
	 *
	 * @param ' . $tipo_db_txt . ' $' . $tip . $nomcamp .'
	 */
	public function set' . $NomCamp . '( '.$tip_txt.' $' . $tip . $nomcamp . $val_default . '): void
	{
		$this->' . $tip . $nomcamp . ' = $' . $tip . $nomcamp . ';
	}';

        }

        $altres_gets .= '
	/**
	 *
	 * @return DatosCampo
	 */
	public function getDatos' . $NomCamp . '(): DatosCampo
	{
		$nom_tabla = $this->getNomTabla();
		$oDatosCampo = new DatosCampo(array(\'nom_tabla\'=>$nom_tabla,\'nom_camp\'=>\'' . $nomcamp . '\'));
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
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDades))';
            $exists .= "\n\t\t{";
            $exists .= "\n\t\t\t" . '$this->set' . $NomCamp . '($aDades[\'' . $nomcamp . '\'],TRUE);';
            $exists .= "\n\t\t}";
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $add_convert = TRUE;
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDades))';
            $exists .= "\n\t\t{";
            $exists .= "\n\t\t\t" . '$this->set' . $NomCamp . '($aDades[\'' . $nomcamp . '\'],$convert);';
            $exists .= "\n\t\t}";
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
            break;
        default:
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDades))';
            $exists .= "\n\t\t{";
            $exists .= "\n\t\t\t" . '$this->set' . $NomCamp . '($aDades[\'' . $nomcamp . '\']);';
            $exists .= "\n\t\t}";
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
    }

    if (!in_array($nomcamp, $aClaus)) {
        if ($auto != 1) { // si tiene secuencia no pongo el campo en el update.
            if ($tip === 'b') {
                $err_bool .= "\n\t\t" . 'if ( is_true($aDades[\'' . $nomcamp . '\']) ) { $aDades[\'' . $nomcamp . '\']=\'true\'; } else { $aDades[\'' . $nomcamp . '\']=\'false\'; }';
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
    if ($auto != 1) { // si tiene secuencia no pongo el campo en el insert.
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

use core\ClasePropiedades;
use core\DatosCampo;
use core\Set;
use PDO;
use PDOException;";

$use_txt = '';
foreach ($a_use_txt as $use) {
    $txt .= "\n".$use.";";
}
$txt .= "\n".$use_txt;

$txt .= "

/**
 * Fichero con la Clase que accede a la tabla $tabla
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created $hoy
 */
/**
 * Clase que implementa la entidad $tabla
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created $hoy
 */
class $clase Extends ClasePropiedades {
	/* ATRIBUTOS ----------------------------------------------------------------- */

";
$txt .= $ATRIBUTOS;
$txt .= "\n\t" . '/* ATRIBUTOS QUE NO SON CAMPOS------------------------------------------------- */';
$txt .= "\n";

$i = 0;
$claus_txt = '';
$claus_txt2 = '';
$claus_if = '';
$guardar_if = '';
$where = '';
$claus_isset = '';
$claus_query = "";
$claus_getPrimary = "";

$txt .= '
	/* CONSTRUCTOR -------------------------------------------------------------- */
';
$txt .= "\n\t/**";
// si només hi ha una clau primària
if (count($aClaus2) === 1) {
    $nom_clau = current($aClaus2);
    $clau = key($aClaus2);
    switch (substr($nom_clau, 0, 1)) {
        case 'i':
            $txt .= "\n\t * @param integer|null  \$$nom_clau";
            $txt .= "\n\t */";
            $txt .= "\n\t" . 'public function __construct(int $'.$nom_clau.' = null)'."\n\t" . '{';
            break;
        case 's':
            $txt .= "\n\t * @param string|null  \$$nom_clau";
            $txt .= "\n\t */";
            $txt .= "\n\t" . 'public function __construct(string $'.$nom_clau.' = null)'."\n\t" . '{';
            break;
        case 'b':
            $txt .= "\n\t * @param bool|null  \$$nom_clau";
            $txt .= "\n\t */";
            $txt .= "\n\t" . 'public function __construct(bool $'.$nom_clau.' = null)'."\n\t" . '{';
            break;
    }
    $txt .= "\n\t\t" . '$oDbl = $GLOBALS[\'' . $oDB_txt . '\'];';

    $claus_txt2 .= "'$clau' => " . '$aDades[\'' . $clau . '\']';
    $txt .= "\n\t\t" . 'if ($'.$nom_clau.' !== null)';
    $txt .= "\n\t\t" . '{';
    $txt .= "\n\t\t\t" . '$this->' . $nom_clau . " = \$$nom_clau;";
    $txt .= "\n\t\t\t" . '$this->aPrimary_key = array(\''.$nom_clau.'\' => $this->'.$nom_clau.');';
    $txt .= "\n\t\t" . '}';

    $where .= $clau . '=\'$this->' . $nom_clau . '\'';

    $claus_isset .= 'isset($this->' . $nom_clau . ')';
    $claus_getPrimary .= '\'' . $clau . '\' => $this->' . $nom_clau;

} else {
    // si n'hi ha més d'una
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
                $claus_if .= "\t\t\t\t" . 'if (($nom_id === \'' . $clau . '\') && $val_id !== \'\') { $this->' . $nom_clau . ' = (string)$val_id; }';
                break;
            case 'b':
                $claus_if .= "\t\t\t\t" . 'if (($nom_id === \'' . $clau . '\') && $val_id !== \'\') { $this->' . $nom_clau . ' = (bool)$val_id; }';
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

    $txt .= "\n\t\t" . $sForPrimaryK;
}

$txt .= "\n\t\t" . '$this->setoDbl($oDbl);
		$this->setNomTabla(\'' . $tabla . '\');
	}';


$txt .= '

	/* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

	/**
	 * Si no existe el registro, hace un insert, si existe, se hace el update.
	 */
	public function DBGuardar(): bool
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if ($this->DBCargar(\'guardar\') === FALSE)
		{
		    $bInsert=TRUE;
		} else {
		    $bInsert=FALSE;
		}
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
				$sClaveError = \'' . $clase . '.update.prepare\';
				$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
				
            try {
                $oDblSt->execute($aDades);
            }
            catch ( PDOException $e) {
                $err_txt=$e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = \'' . $clase . '.update.execute\';
                $_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
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
				$sClaveError = \'' . $clase . '.insertar.prepare\';
				$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
            try {
                $oDblSt->execute($aDades);
            }
            catch ( PDOException $e) {
                $err_txt=$e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = \'' . $clase . '.insertar.execute\';
                $_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
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
	 * Carga los campos de la base de datos como ATRIBUTOS de la clase.
	 */
	public function DBCargar($que=null): bool
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (' . $claus_isset . ') {
			if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE ' . $where . '")) === FALSE) {
				$sClaveError = \'' . $clase . '.cargar\';
				$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}';
// para los bytea
if (!empty($bytea_bind)) {
    $txt .= "\n\t\t\t" . '// para los bytea:';
    $txt .= $bytea_bind;
}
$txt .= "\n\t\t\t" . '$aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);';
if (!empty($bytea_bind)) {
    $txt .= "\n\t\t\t" . '// para los bytea, sobre escribo los valores:';
    $txt .= $bytea_dades;
}
$txt .= "\n\t\t\t" . '
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
			switch ($que) {
				case \'tot\':
                    $this->setAllAtributes($aDades);
					break;
				case \'guardar\':
					if (!$oDblSt->rowCount()){
					    return FALSE;
					}
					break;
                default:
					// En el caso de no existir esta fila, $aDades = FALSE:
					if ($aDades === FALSE) {
						return FALSE;
					}
					$this->setAllAtributes($aDades);
			}
			return TRUE;
		}
        return FALSE;
	}

	public function DBEliminar(): bool
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		if (($oDbl->exec("DELETE FROM $nom_tabla WHERE ' . $where . '")) === FALSE) {
			$sClaveError = \'' . $clase . '.eliminar\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
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
    $txt .= "\n\t" . 'private function setAllAtributes(array $aDades, $convert=FALSE): void'. "\n\t" . '{';
} else {
    $txt .= "\n\t" . 'private function setAllAtributes(array $aDades): void' . "\n\t" . '{';
}

$txt .= $exists;
$txt .= "\n\t" . '}';

$txt .='
	/* MÉTODOS GET y SET --------------------------------------------------------*/

';

$txt .= '
	/**
	 * Recupera las claves primarias de ' . $clase . ' en un array
	 *
	 * @return array aPrimary_key
	 */
	public function getPrimary_key(): array
	{
		if (!isset($this->aPrimary_key )) {
			$this->aPrimary_key = array(' . $claus_getPrimary . ');
		}
		return $this->aPrimary_key;
	}
	/**
	 * Establece las claves primarias de ' . $clase . ' en un array
	 *
	 */
	public function setPrimary_key(array $aPrimaryKey): void
	{
		$this->aPrimary_key = $aPrimaryKey;
	}
	
';

$txt .= $gets;

$txt .= '
	/* MÉTODOS GET y SET DE ATRIBUTOS QUE NO SON CAMPOS -----------------------------*/

	/**
	 * Devuelve una colección de objetos del tipo DatosCampo
	 */
	public function getDatosCampos(): array
	{
		$o' . $clase . 'Set = new Set();
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
$filename = ConfigGlobal::DIR . '/apps/' . $grupo . '/model/entity/' . $Q_clase . '.php';

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

use core\\ClaseGestor;
use core\\Condicion;
use core\\Set;

/**
 * $gestor
 *
 * Clase para gestionar la lista de objetos de la clase $clase
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created $hoy
 */

class $gestor Extends ClaseGestor {
	/* ATRIBUTOS ----------------------------------------------------------------- */

	/* CONSTRUCTOR -------------------------------------------------------------- */
	
";

$txt2 .= '
	public function __construct() {
		$oDbl = $GLOBALS[\'' . $oDB_txt . '\'];
		$this->setoDbl($oDbl);
		$this->setNomTabla(\'' . $tabla . '\');
	}


	/* MÉTODOS PÚBLICOS -----------------------------------------------------------*/
';

$txt2 .= '
	/**
	 * devuelve una colección (array) de objetos de tipo ' . $clase . '
	 *
	 * @param string $sQuery la query a ejecutar.
	 * @return array|FALSE Una colección de objetos de tipo ' . $clase . '
	 */
	public function get' . $clase_plural . 'Query(string $sQuery=\'\'): array|FALSE
	{
		$oDbl = $this->getoDbl();
		$o' . $clase . 'Set = new Set();
		if (($oDbl->query($sQuery)) === FALSE) {
			$sClaveError = \'' . $gestor . '.query\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDbl->query($sQuery) as $aDades) {';
// si només hi ha una clau primària
if (count($aClaus2) === 1) {
    $txt2 .= "\n\t\t\t" . '$o' . $clase . ' = new ' . $clase . '($aDades[\''.$clau.'\']);';
} else {
    $txt2 .= "\n\t\t\t" . '$a_pkey = array(' . $claus_txt2 . ');';
    $txt2 .= "\n\t\t\t" . '$o' . $clase . ' = new ' . $clase . '($a_pkey);';
}
$txt2 .= '
			$o' . $clase . 'Set->add($o' . $clase . ');
		}
		return $o' . $clase . 'Set->getTot();
	}
';

$txt2 .= '
	/**
	 * devuelve una colección (array) de objetos de tipo ' . $clase . '
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo ' . $clase . '
	 */
	public function get' . $clase_plural . '(array $aWhere=[], array $aOperators=[]): array|FALSE
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$o' . $clase . 'Set = new Set();
		$oCondicion = new Condicion();
		$aCondicion = array();';
$txt2 .= '
		foreach ($aWhere as $camp => $val) {
			if ($camp === \'_ordre\') { continue; }
			if ($camp === \'_limit\') { continue; }
			$sOperador = $aOperators[$camp] ?? \'\';
			if ($a = $oCondicion->getCondicion($camp,$sOperador,$val)) { $aCondicion[]=$a; }
			// operadores que no requieren valores
			if ($sOperador === \'BETWEEN\' || $sOperador === \'IS NULL\' || $sOperador === \'IS NOT NULL\' || $sOperador === \'OR\') { unset($aWhere[$camp]); }
            if ($sOperador === \'IN\' || $sOperador === \'NOT IN\') { unset($aWhere[$camp]); }
            if ($sOperador === \'TXT\') { unset($aWhere[$camp]); }
		}';

$txt2 .= "\n\t\t" . '$sCondicion = implode(\' AND \',$aCondicion);
		if ($sCondicion !==\'\') { $sCondicion = " WHERE ".$sCondicion; }
		$sOrdre = \'\';
        $sLimit = \'\';
		if (isset($aWhere[\'_ordre\']) && $aWhere[\'_ordre\'] !== \'\') { $sOrdre = \' ORDER BY \'.$aWhere[\'_ordre\']; }
		if (isset($aWhere[\'_ordre\'])) { unset($aWhere[\'_ordre\']); }
		if (isset($aWhere[\'_limit\']) && $aWhere[\'_limit\'] !== \'\') { $sLimit = \' LIMIT \'.$aWhere[\'_limit\']; }
		if (isset($aWhere[\'_limit\'])) { unset($aWhere[\'_limit\']); }
		$sQry = "SELECT * FROM $nom_tabla ".$sCondicion.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClaveError = \'' . $gestor . '.listar.prepare\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClaveError = \'' . $gestor . '.listar.execute\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		foreach ($oDblSt as $aDades) {';
// si només hi ha una clau primària
if (count($aClaus2) === 1) {
    $txt2 .= "\n\t\t\t" . '$o' . $clase . ' = new ' . $clase . '($aDades[\''.$clau.'\']);';
} else {
    $txt2 .= "\n\t\t\t" . '$a_pkey = array(' . $claus_txt2 . ');';
    $txt2 .= "\n\t\t\t" . '$o' . $clase . ' = new ' . $clase . '($a_pkey);';
}
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
$filename = ConfigGlobal::DIR . '/apps/' . $grupo . '/model/entity/Gestor' . $Q_clase . '.php';


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
