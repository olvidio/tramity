<?php

namespace devel\controller;

use core\ConfigGlobal;
use core\ServerConf;
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

// crear el directorio legacy si no existe
$dir_legacy = ServerConf::DIR . '/apps/' . $grupo . '/legacy';
if (!is_dir($dir_legacy)) {
    mkdir($dir_legacy);
}

/* rename file of class to old if exists */
$grupo = !empty($Q_grupo) ? $Q_grupo : "actividades";
$filename = ServerConf::DIR . '/apps/' . $grupo . '/model/entity/' . $Q_clase . '.php';
$filenameOld = ServerConf::DIR . '/apps/' . $grupo . '/legacy/zz' . $Q_clase . 'Old.php';
if (file_exists($filename)) {
    rename($filename, $filenameOld);
    /* rename class if exists */
    $content = file_get_contents($filenameOld);
    $pattern = '/^class\s+' . $Q_clase . '/im';
    $replacement = 'class zzz' . $Q_clase . 'Old';
    $new_content = preg_replace($pattern, $replacement, $content);
    // también el namespace:
    $pattern2 = '/^namespace\s+(.*)/im';
    $replacement2 = "namespace $grupo\\legacy;";
    $new_content2 = preg_replace($pattern2, $replacement2, $new_content);

    if (file_put_contents($filenameOld, $new_content2) === FALSE) {
        echo "No puedo cambiar el nombre de la clase en  ($filenameOld)";
        die();
    }
}
/* rename file of gestor to old if exists */
$gestor = "Gestor" . ucfirst($Q_clase);
$filename = ServerConf::DIR . '/apps/' . $grupo . '/model/entity/Gestor' . $Q_clase . '.php';
$filenameOld = ServerConf::DIR . '/apps/' . $grupo . '/legacy/zzzGestor' . $Q_clase . 'Old.php';
if (file_exists($filename)) {
    rename($filename, $filenameOld);
    /* rename class if exists */
    $content = file_get_contents($filenameOld);
    $pattern = '/^class\s+' . $gestor . '/im';
    $replacement = 'class zzz' . $gestor . 'Old';
    $new_content = preg_replace($pattern, $replacement, $content);
    // también el namespace:
    $pattern2 = '/^namespace\s+(.*)/im';
    $replacement2 = "namespace $grupo\\legacy;";
    $new_content2 = preg_replace($pattern2, $replacement2, $new_content);

    if (file_put_contents($filenameOld, $new_content2) === FALSE) {
        echo "No puedo cambiar el nombre de la clase en  ($filenameOld)";
        die();
    }
}

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


$ATRIBUTOS = '';

$a_use_txt = [];
$c = 0;
$cl = 0;
$id_seq = "";
$id_seq2 = "";
$aClaus2 = [];
$guardar = "";
$update = "";
$campos = "";
$a_add_campos = [];
$valores = "";
$exists = "";
$ToEmpty = "";
$bytea_bind = '';
$bytea_dades = '';
$array_dades = '';
$fechas_dades = '';
$gets = "";
$altres_gets = "";
$altres_gets_set = "";
$query_if = "";
$guardar_array = "";
$guardar_fechas = "";
$err_bool = "";
$a_auto = array();
// una primera vuelta para cargar excepciones...
foreach ($oDbl->query($sql) as $row) {
    $nomcamp = $row['field'];
    if ($nomcamp === 'id_schema') {
        continue;
    }
    $tipo = $row['type'];

    switch ($tipo) {
        case '_int8':
        case '_int4':
        case '_int2':
            $a_use_txt['array_pg2php'] = "use function core\array_pg2php";
            $a_use_txt['array_php2pg'] = "use function core\array_php2pg";
            break;
        case 'int8':
        case 'int4':
        case 'int2':
            break;
        case 'float4':
        case 'double':
        case 'numeric':
            break;
        case 'text':
        case 'varchar':
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $a_use_txt['DateTimeLocal'] = "use web\DateTimeLocal";
            $a_use_txt['NullDateTimeLocal'] = "use web\NullDateTimeLocal";
            $a_use_txt['ConverterDate'] = "use core\ConverterDate";
            break;
        case 'time':
            break;
        case 'bool':
            $a_use_txt['is_true'] = "use function core\is_true";
            break;
        case 'json':
        case 'jsonb':
            $a_use_txt['ConverterJson'] = "use core\ConverterJson";
            $a_use_txt['JsonException'] = "use JsonException";
            $a_use_txt['stdClass'] = "use stdClass";
            break;
        case 'bytea':
            break;
    }
}

foreach ($oDbl->query($sql) as $row) {
    $nomcamp = $row['field'];
    if ($nomcamp === 'id_schema') {
        continue;
    }
    $NomCamp = ucwords($nomcamp);
    $tipo = $row['type'];
    $null = (is_true($row['notnull'])) ? 'null' : '';

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
            if (preg_match("/nextval\(\'(.*)\'.*$/", $default, $matches)) {
                $id_seq_con_esquema = $matches[1];
                // quitar el esquema (si existe)
                if (preg_match("/(.*)\.(.*)$/", $id_seq_con_esquema, $matches2)) {
                    $id_seq2 = $matches2[2];
                } else {
                    $id_seq2 = $matches[1];
                }
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
            $array_dades .= "\n\t\t\t";
            $array_dades .= '$aDatos[\'' . $nomcamp . '\'] = array_pg2php($aDatos[\'' . $nomcamp . '\']);';
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
            $fechas_dades .= "\n\t\t\t";
            $fechas_dades .= '$aDatos[\'' . $nomcamp . '\'] = (new ConverterDate(\'' . $tipo . '\', $aDatos[\'' . $nomcamp . '\']))->fromPg();';
            break;
        case 'time':
            $tipo_db = 'string time';
            $tip = 't';
            $tip_val = '';
            break;
        case 'bool':
            $tipo_db = 'bool';
            $tip = 'b';
            $tip_val = 'FALSE';
            break;
        case 'json':
        case 'jsonb':
            $tipo_db = 'string';
            $tip = '';
            $tip_val = '';
            break;
        case 'bytea':
            $tipo_db = 'string';
            $tip = 's';
            $tip_val = '';
            $bytea_dades .= "\n\t\t\t";
            $bytea_dades .= '$handle = $aDatos[\'' . $nomcamp . '\'];';
            $bytea_dades .= 'if ($handle !== null) {';
            $bytea_dades .= "\n\t\t\t";
            $bytea_dades .= '$contents = stream_get_contents($handle);';
            $bytea_dades .= "\n\t\t\t";
            $bytea_dades .= 'fclose($handle);';
            $bytea_dades .= "\n\t\t\t";
            $bytea_dades .= '$' . $nomcamp . ' = $contents;';
            $bytea_dades .= "\n\t\t\t";
            $bytea_dades .= '$aDatos[\'' . $nomcamp . '\'] = $' . $nomcamp . ';';
            $bytea_dades .= "\n\t\t\t";
            $bytea_dades .= "}";

            $bytea_bind .= "\n\t\t";
            $bytea_bind .= '$' . $tip . $nomcamp . " = '';";
            $bytea_bind .= "\n\t\t";
            $bytea_bind .= '$oDblSt->bindColumn(\'' . $nomcamp . '\', $' . $tip . $nomcamp . ', PDO::PARAM_STR);';
            $bytea_bind .= "\n\t\t";
            $bytea_bind .= '$aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);';
            $bytea_bind .= "\n\t\t";
            $bytea_bind .= '$aDatos[\'' . $nomcamp . '\'] = $' . $tip . $nomcamp . ';';
            break;
    }
    if (empty($null)) {
        $tipo_db_txt = $tipo_db . "|null";
        $tip_txt = "?" . $tipo_db;
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
	 * @var ' . $tipo_db_txt . '
	 */
	 private ' . $tip_txt . ' $' . $tip . $nomcamp . $val_default . ';';

    switch ($tipo) {
        case 'bool':
            $metodo_get = 'is' . $NomCamp . '()';
            $gets .= '
	/**
	 *
	 * @return ' . $tipo_db_txt . ' $' . $tip . $nomcamp;
            if (!empty($a_use_txt['JsonException'])) {
                $gets .= "\n\t" . ' * @throws JsonException';
            }
            $gets .= "\n\t" . ' */
	public function is' . $NomCamp . '(): ' . $tip_txt . '
	{
		return $this->' . $tip . $nomcamp . ';
	}';
            break;
        case '_int8':
        case '_int4':
        case '_int2':
            $metodo_get = 'get' . $NomCamp . '()';
            $gets .= '
	/**
	 *
	 * @return ' . $tipo_db_txt . ' $' . $tip . $nomcamp;
            if (!empty($a_use_txt['JsonException'])) {
                $gets .= "\n\t" . ' * @throws JsonException';
            }
            $gets .= "\n\t" . ' */
	public function get' . $NomCamp . '(): ' . $tipo_db_txt . '
	{
        return $this->' . $tip . $nomcamp . ';
	}';
            break;
        case 'json':
        case 'jsonb':
            $metodo_get = 'get' . $NomCamp . '()';
            $gets .= '
	/**
	 *
	 * @param bool $bArray si hay que devolver un array en vez de un objeto.
	 * @return array|stdClass|null $' . $tip . $nomcamp . '
	 * @throws JsonException
	 */
	public function get' . $NomCamp . '(bool $bArray=FALSE): array|stdClass|null
	{
		if (!isset($this->' . $tip . $nomcamp . ') && !$this->bLoaded) {
			$this->DBCargar();
		}
		return (new ConverterJson($this->' . $tip . $nomcamp . ', $bArray))->fromPg();
	}';
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $metodo_get = 'get' . $NomCamp . '()';
            $gets .= '
	/**
	 *
	 * @return DateTimeLocal|null' . ' $' . $tip . $nomcamp;
            $gets .= "\n\t" . ' */
	public function get' . $NomCamp . '(): DateTimeLocal|null
	{
        return $this->' . $tip . $nomcamp . ';
	}';
            break;
        default:
            $metodo_get = 'get' . $NomCamp . '()';
            $gets .= '
	/**
	 *
	 * @return ' . $tipo_db_txt . ' $' . $tip . $nomcamp;
            if (!empty($a_use_txt['JsonException'])) {
                $gets .= "\n\t" . ' * @throws JsonException';
            }
            $gets .= "\n\t" . ' */
	public function get' . $NomCamp . '(): ' . $tip_txt . '
	{
		return $this->' . $tip . $nomcamp . ';
	}';
    }

    if (in_array($nomcamp, $aClaus)) {
        $a_add_campos[$nomcamp] = '$aDatos[\'' . $nomcamp . '\'] = $' . $Q_clase . '->' . $metodo_get . ';';
        $aClaus2[$nomcamp] = ['tip_nomcamp' => $tip . $nomcamp, 'tip_txt' => $tip_txt];
        $gets .= '
	/**
	 *
	 * @param ' . $tipo_db_txt . ' $' . $tip . $nomcamp . '
	 */
	public function set' . $NomCamp . '(' . $tip_txt . ' $' . $tip . $nomcamp . '): void
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
	 * @param array|null $' . $tip . $nomcamp . '
	 */
	public function set' . $NomCamp . '(array $' . $tip . $nomcamp . '= null): void
	{
        $this->' . $tip . $nomcamp . ' = $' . $tip . $nomcamp . ';
	}';
                break;
            case 'json':
            case 'jsonb':
                $gets .= '
	/**
	 * 
	 * @param string|array|null $' . $tip . $nomcamp . '
     * @param bool $db=FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
	 *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
	 * @throws JsonException
	 */
	public function set' . $NomCamp . '(string|array|null $' . $tip . $nomcamp . ', bool $db=FALSE): void
	{
        $this->' . $tip . $nomcamp . ' = (new ConverterJson($' . $tip . $nomcamp . ', FALSE))->toPg($db);
	}';
                break;
            case 'date':
            case 'timestamp':
            case 'timestamptz';
                $gets .= '
	/**
	 * 
	 * @param DateTimeLocal|null $' . $tip . $nomcamp . '
	 */
	public function set' . $NomCamp . '(DateTimeLocal|null $' . $tip . $nomcamp . ' = null): void
	{
        $this->' . $tip . $nomcamp . ' = $' . $tip . $nomcamp . ';
	}';
                break;
            default:
                $gets .= '
	/**
	 *
	 * @param ' . $tipo_db_txt . ' $' . $tip . $nomcamp . '
	 */
	public function set' . $NomCamp . '(' . $tip_txt . ' $' . $tip . $nomcamp . $val_default . '): void
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
        case 'bool':
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDatos))';
            $exists .= "\n\t\t{";
            $exists .= "\n\t\t\t" . '$this->set' . $NomCamp . '(is_true($aDatos[\'' . $nomcamp . '\']));';
            $exists .= "\n\t\t}";
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
            break;
        case '_int8':
        case '_int4':
        case '_int2':
        case 'json':
        case 'jsonb':
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDatos))';
            $exists .= "\n\t\t{";
            $exists .= "\n\t\t\t" . '$this->set' . $NomCamp . '($aDatos[\'' . $nomcamp . '\']);';
            $exists .= "\n\t\t}";
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
            break;
        case 'date':
        case 'timestamp':
        case 'timestamptz';
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDatos))';
            $exists .= "\n\t\t{";
            $exists .= "\n\t\t\t" . '$this->set' . $NomCamp . '($aDatos[\'' . $nomcamp . '\'], FALSE);';
            $exists .= "\n\t\t}";
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
            break;
        default:
            $exists .= "\n\t\t" . 'if (array_key_exists(\'' . $nomcamp . '\',$aDatos))';
            $exists .= "\n\t\t{";
            $exists .= "\n\t\t\t" . '$this->set' . $NomCamp . '($aDatos[\'' . $nomcamp . '\']);';
            $exists .= "\n\t\t}";
            $ToEmpty .= "\n\t\t" . '$this->set' . $NomCamp . '(\'\');';
    }

    if (!in_array($nomcamp, $aClaus)) {
        if ($auto != 1) { // si tiene secuencia no pongo el campo en el update.
            if ($tip === 'b') {
                $err_bool .= "\n\t\t" . 'if ( is_true($aDatos[\'' . $nomcamp . '\']) ) { $aDatos[\'' . $nomcamp . '\']=\'true\'; } else { $aDatos[\'' . $nomcamp . '\']=\'false\'; }';
            }
            if ($tipo_db === 'array') {
                $guardar_array = "\n\t\t" . '$aDatos[\'' . $nomcamp . '\'] = array_php2pg($' . $Q_clase . '->' . $metodo_get . ');';
            }
            if ($tipo_db === 'DateTimeLocal') {
                $guardar_fechas = "\n\t\t" . '$aDatos[\'' . $nomcamp . '\'] = (new ConverterDate(\'' . $tipo . '\', $' . $Q_clase . '->' . $metodo_get . '))->toPg();';
            }

            if ($tipo_db !== 'array' && $tipo_db !== 'DateTimeLocal') {
                $guardar .= "\n\t\t" . '$aDatos[\'' . $nomcamp . '\'] = $' . $Q_clase . '->' . $metodo_get . ';';
            }

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
    if ($c > 0) $campos .= ",";
    $campos .= $nomcamp;
    if ($c > 0) $valores .= ",";
    $valores .= ':' . $nomcamp;
    $c++;
}
$oHoy = new DateTimeLocal();
$hoy = $oHoy->getFromLocal();

//------------------------------------ CLASE ENTIDAD -----------------------------------------------
$txt_entidad = "<?php

namespace $grupo\\domain\\entity;";
if (!empty($a_use_txt['is_true'])) {
    $txt_entidad .= "\n\t" . 'use function core\is_true;';
}
if (!empty($a_use_txt['DateTimeLocal'])) {
    $txt_entidad .= "\n\t" . 'use web\DateTimeLocal;';
}

$txt_entidad .= "
/**
 * Clase que implementa la entidad $tabla
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created $hoy
 */
class $clase {

	/* ATRIBUTOS ----------------------------------------------------------------- */
";
$txt_entidad .= $ATRIBUTOS;
$txt_entidad .= "\n";
$txt_entidad .= "\n\t";
$txt_entidad .= '/* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

	/**
	 * Establece el valor de todos los atributos
	 *
	 * @param array $aDatos';
if (!empty($a_use_txt['JsonException'])) {
    $txt_entidad .= "\n\t" . ' * @throws JsonException';
}
$txt_entidad .= "\n\t" . ' * return ' . $Q_clase;
$txt_entidad .= "\n\t" . ' */';
$txt_entidad .= "\n\t" . 'public function setAllAttributes(array $aDatos): ' . $Q_clase . "\n\t" . '{';

$txt_entidad .= $exists;
$txt_entidad .= "\n\t\t" . 'return $this;';
$txt_entidad .= "\n\t" . '}';
$txt_entidad .= $gets;
$txt_entidad .= "\n" . '}';

// ESCRIURE LA CLASSSE ---------  ENTIDAD

// crear el directorio domain/entity si no existe
$dir_entity = ServerConf::DIR . '/apps/' . $grupo . '/domain/entity';
if (!is_dir($dir_entity)) {
    if (!mkdir($dir_entity, 0774, TRUE) && !is_dir($dir_entity)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir_entity));
    }
}
$filename = ConfigGlobal::DIR . '/apps/' . $grupo . '/domain/entity/' . $Q_clase . '.php';
if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}
// Write $somecontent to our opened file.
if (fwrite($handle, $txt_entidad) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}
echo "<br>Success, wrote gestor to file ($filename)";
fclose($handle);

// ---------------------- REPOSITORIO ------------------------------------------------

$pg_clase = "Pg" . $Q_clase . "Repository";
$clase_interface = $Q_clase . "RepositoryInterface";
$clase_repository = $Q_clase . "Repository";

$where = '';
$claus_getPrimary = '';
$getClau = '';
$claus_txt = '';
$claus_txt2 = '';
if (count($aClaus2) === 1) {
    $a_nom_clau = current($aClaus2);
    $nom_clau = $a_nom_clau['tip_nomcamp'];
    $clau_tip_txt = $a_nom_clau['tip_txt'];
    $clau = key($aClaus2);
    // si es integer quito las comillas del where
    if ($nom_clau[0] === 'i') {
        $where .= $clau . ' = $' . $clau;
    } else {
        $where .= $clau . ' = \'$' . $clau . '\'';
    }

    $claus_getPrimary .= '\'' . $clau . '\' => $this->' . $nom_clau;

    $getClau .= '$' . $clau . ' = $' . $Q_clase . '->get' . ucfirst($clau) . '();';

} else {
    // si n'hi ha més d'una
    $i = 0;
    foreach ($aClaus2 as $clau => $nom_clau) {
        //$nom_clau="i".$clau;
        $i++;
        if ($i > 0) {
            $where .= " AND ";
        }
        // si es integer quito las comillas del where
        if ($nom_clau[0] === 'i') {
            $where .= $clau . ' = $' . $clau;
        } else {
            $where .= $clau . ' = \'$' . $clau . '\'';
        }

        if (!empty($claus_txt)) $claus_txt .= ",";
        $claus_txt .= $nom_clau;
        if ($i > 0) $claus_txt2 .= ",\n\t\t\t\t\t\t\t";
        $claus_txt2 .= "'$clau' => " . '$aDatos[\'' . $clau . '\']';
    }
}

$txt_repository = "<?php

namespace $grupo\\domain\\repositories;

use PDO;
use $grupo\\domain\\entity\\$Q_clase;
use $grupo\\infrastructure\\$pg_clase;
use web\Desplegable;

/**
 *
 * Clase para gestionar la lista de objetos tipo $Q_clase
 * 
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created $hoy
 */
class $clase_repository implements $clase_interface
{

    /**$
     * @var $clase_interface
     */
    private $clase_interface \$repository;

    public function __construct()
    {
        \$this->repository = new $pg_clase();
    }
";

$txt_interface = "<?php

namespace $grupo\\domain\\repositories;

use PDO;
use $grupo\\domain\\entity\\$Q_clase;
use web\\Desplegable;

/**
 * Interfaz de la clase $Q_clase y su Repositorio
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created $hoy
 */
interface $clase_interface
{
";

$txt_pgRepositorio = "<?php

namespace $grupo\\infrastructure;

use core\\ClaseRepository;
use core\\Condicion;
use core\\Set;
use PDO;
use PDOException;
";

$txt_pgRepositorio .= "
use $grupo\\domain\\entity\\$Q_clase;
use $grupo\\domain\\repositories\\$clase_interface;
use web\\Desplegable;
";

$use_txt = '';
foreach ($a_use_txt as $use) {
    $use_txt .= "\n" . $use . ";";
}
$txt_pgRepositorio .= "\n" . $use_txt;

$txt_pgRepositorio .= "
/**
 * Clase que adapta la tabla $tabla a la interfaz del repositorio
 *
 * @package $aplicacion
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created $hoy
 */
class $pg_clase extends ClaseRepository implements $clase_interface
{";

$txt_pgRepositorio .= '
    public function __construct()
    {
        $oDbl = $GLOBALS[\'oDBT\'];
        $this->setoDbl($oDbl);
        $this->setNomTabla(\'' . $tabla . '\');
    }
';

$txt_repository .= "\n";
$txt_repository .= '/* -------------------- GESTOR BASE ---------------------------------------- */';
$txt_repository .= "\n";
$txt_repository .= '
	/**
	 * devuelve una colección (array) de objetos de tipo ' . $Q_clase . '
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo ' . $Q_clase . '
	 */
	public function get' . $clase_plural . '(array $aWhere=[], array $aOperators=[]): array|FALSE
	{
	    return $this->repository->get' . $clase_plural . '($aWhere, $aOperators);
	}
	';

$txt_interface .= "\n";
$txt_interface .= '/* -------------------- GESTOR BASE ---------------------------------------- */';
$txt_interface .= "\n";
$txt_interface .= '
	/**
	 * devuelve una colección (array) de objetos de tipo ' . $Q_clase . '
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo ' . $Q_clase . '
	 */
	public function get' . $clase_plural . '(array $aWhere=[], array $aOperators=[]): array|FALSE;
	';

$txt_pgRepositorio .= "\n";
$txt_pgRepositorio .= '/* -------------------- GESTOR BASE ---------------------------------------- */';
$txt_pgRepositorio .= "\n";

$txt_pgRepositorio .= '
	/**
	 * devuelve una colección (array) de objetos de tipo ' . $Q_clase . '
	 *
	 * @param array $aWhere asociativo con los valores para cada campo de la BD.
	 * @param array $aOperators asociativo con los operadores que hay que aplicar a cada campo
	 * @return array|FALSE Una colección de objetos de tipo ' . $Q_clase . '
	 */
	public function get' . $clase_plural . '(array $aWhere=[], array $aOperators=[]): array|FALSE
	{
		$oDbl = $this->getoDbl();
		$nom_tabla = $this->getNomTabla();
		$' . $Q_clase . 'Set = new Set();
		$oCondicion = new Condicion();
		$aCondicion = array();';
$txt_pgRepositorio .= '
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

$txt_pgRepositorio .= "\n\t\t" . '$sCondicion = implode(\' AND \',$aCondicion);
		if ($sCondicion !==\'\') { $sCondicion = " WHERE ".$sCondicion; }
		$sOrdre = \'\';
        $sLimit = \'\';
		if (isset($aWhere[\'_ordre\']) && $aWhere[\'_ordre\'] !== \'\') { $sOrdre = \' ORDER BY \'.$aWhere[\'_ordre\']; }
		if (isset($aWhere[\'_ordre\'])) { unset($aWhere[\'_ordre\']); }
		if (isset($aWhere[\'_limit\']) && $aWhere[\'_limit\'] !== \'\') { $sLimit = \' LIMIT \'.$aWhere[\'_limit\']; }
		if (isset($aWhere[\'_limit\'])) { unset($aWhere[\'_limit\']); }
		$sQry = "SELECT * FROM $nom_tabla ".$sCondicion.$sOrdre.$sLimit;
		if (($oDblSt = $oDbl->prepare($sQry)) === FALSE) {
			$sClaveError = \'' . $pg_clase . '.listar.prepare\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		if (($oDblSt->execute($aWhere)) === FALSE) {
			$sClaveError = \'' . $pg_clase . '.listar.execute\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
			return FALSE;
		}
		
		$filas = $oDblSt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $aDatos) {';
if (!empty($bytea_dades)) {
    $txt_pgRepositorio .= '// para los bytea: (resources)';
    $txt_pgRepositorio .= $bytea_dades;
}

if (!empty($array_dades)) {
    $txt_pgRepositorio .= "\n\t\t\t// para los array del postgres";
    $txt_pgRepositorio .= $array_dades;
}

if (!empty($fechas_dades)) {
    $txt_pgRepositorio .= "\n\t\t\t// para las fechas del postgres (texto iso)";
    $txt_pgRepositorio .= $fechas_dades;
}

$txt_pgRepositorio .= '
            $' . $Q_clase . ' = new ' . $Q_clase . '();
            $' . $Q_clase . '->setAllAttributes($aDatos);
			$' . $Q_clase . 'Set->add($' . $Q_clase . ');
		}
		return $' . $Q_clase . 'Set->getTot();
	}
';


$txt_repository .= "\n";
$txt_repository .= '/* -------------------- ENTIDAD --------------------------------------------- */';
$txt_repository .= "\n";
$txt_repository .= "\n\t";
$txt_repository .= 'public function Eliminar(' . $Q_clase . ' $' . $Q_clase . '): bool
    {
        return $this->repository->Eliminar($' . $Q_clase . ');
    }';

$txt_interface .= "\n";
$txt_interface .= '/* -------------------- ENTIDAD --------------------------------------------- */';
$txt_interface .= "\n";
$txt_interface .= "\n\t";
$txt_interface .= 'public function Eliminar(' . $Q_clase . ' $' . $Q_clase . '): bool;';

$txt_pgRepositorio .= "\n";
$txt_pgRepositorio .= '/* -------------------- ENTIDAD --------------------------------------------- */';
$txt_pgRepositorio .= "\n";

$txt_pgRepositorio .= "\n\t";
$txt_pgRepositorio .= 'public function Eliminar(' . $Q_clase . ' $' . $Q_clase . '): bool
    {
        ' . $getClau . '
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE ' . $where . '")) === FALSE) {
            $sClaveError = \'' . $clase . '.eliminar\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }
';

$txt_repository .= "\n";
$txt_repository .= "\n\t";
$txt_repository .= 'public function Guardar(' . $Q_clase . ' $' . $Q_clase . '): bool
    {
        return $this->repository->Guardar($' . $Q_clase . ');
    }';

$txt_repository .= "\n";
$txt_repository .= "\n\t";
$txt_repository .= 'public function getErrorTxt(): string
    {
        return $this->repository->getErrorTxt();
    }';

$txt_repository .= "\n";
$txt_repository .= "\n\t";
$txt_repository .= 'public function getoDbl(): PDO
    {
        return $this->repository->getoDbl();
    }';

$txt_repository .= "\n";
$txt_repository .= "\n\t";
$txt_repository .= 'public function setoDbl(PDO $oDbl): void
    {
        $this->repository->setoDbl($oDbl);
    }';

$txt_repository .= "\n";
$txt_repository .= "\n\t";
$txt_repository .= 'public function getNomTabla(): string
    {
        return $this->repository->getNomTabla();
    }';

$txt_interface .= "\n";
$txt_interface .= "\n\t";
$txt_interface .= 'public function Guardar(' . $Q_clase . ' $' . $Q_clase . '): bool;';

$txt_interface .= "\n";
$txt_interface .= "\n\t";
$txt_interface .= 'public function getErrorTxt(): string;';

$txt_interface .= "\n";
$txt_interface .= "\n\t";
$txt_interface .= 'public function getoDbl(): PDO;';

$txt_interface .= "\n";
$txt_interface .= "\n\t";
$txt_interface .= 'public function setoDbl(PDO $oDbl): void;';

$txt_interface .= "\n";
$txt_interface .= "\n\t";
$txt_interface .= 'public function getNomTabla(): string;';

$txt_pgRepositorio .= "\n\t";
$txt_pgRepositorio .= '
	/**
	 * Si no existe el registro, hace un insert, si existe, se hace el update.
	 */
	public function Guardar(' . $Q_clase . ' $' . $Q_clase . '): bool
    {
        ' . $getClau . '
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        $bInsert = $this->isNew($' . $clau . ');

		$aDatos = [];';

$txt_pgRepositorio .= $guardar;
if ($guardar_array) {
    $txt_pgRepositorio .= "\n\t\t// para los array";
    $txt_pgRepositorio .= $guardar_array;
}
if ($guardar_fechas) {
    $txt_pgRepositorio .= "\n\t\t// para las fechas";
    $txt_pgRepositorio .= $guardar_fechas;
}
$txt_pgRepositorio .= '
		array_walk($aDatos, \'core\\poner_null\');';
if ($err_bool) {
    $txt_pgRepositorio .= "\n\t\t//para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:";
    $txt_pgRepositorio .= $err_bool;
}
$txt_pgRepositorio .= "\n\n\t\t" . 'if ($bInsert === FALSE) {
			//UPDATE
			$update="
';
$txt_pgRepositorio .= $update . '";';
$txt_pgRepositorio .= '
			if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE ' . $where . '")) === FALSE) {
				$sClaveError = \'' . $clase . '.update.prepare\';
				$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
				
            try {
                $oDblSt->execute($aDatos);
            } catch ( PDOException $e) {
                $err_txt=$e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = \'' . $clase . '.update.execute\';
                $_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
		} else {
			// INSERT';
foreach ($a_add_campos as $add_campo) {
    $txt_pgRepositorio .= "\n\t\t\t" . $add_campo;
}
$txt_pgRepositorio .= "\n\t\t\t" . '$campos="(';
$txt_pgRepositorio .= $campos . ')";' . "\n";
$txt_pgRepositorio .= "\t\t\t" . '$valores="(';
$txt_pgRepositorio .= $valores . ')";';
$txt_pgRepositorio .= '		
			if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
				$sClaveError = \'' . $clase . '.insertar.prepare\';
				$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
				return FALSE;
			}
            try {
                $oDblSt->execute($aDatos);
            } catch ( PDOException $e) {
                $err_txt=$e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = \'' . $clase . '.insertar.execute\';
                $_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
			}';
$txt_pgRepositorio .= "\n\t\t" . '}
		return TRUE;
	}';

$txt_pgRepositorio .= "\n\t";
$txt_pgRepositorio .= '
    private function isNew(' . $clau_tip_txt . ' $' . $clau . '): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE ' . $where . '")) === FALSE) {
			$sClaveError = \'' . $clase . '.isNew\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        if (!$oDblSt->rowCount()) {
            return TRUE;
        }
        return FALSE;
    }';

if ($nom_clau[0] === 'i') {
    $tip_txt = 'int';
} else {
    $tip_txt = 'string';
}
$txt_repository .= "\n\t";
$txt_repository .= '
    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     * 
     * @param ' . $clau_tip_txt . ' $' . $clau . '
     * @return array|bool
     */
    public function datosById(' . $clau_tip_txt . ' $' . $clau . '): array|bool
    {
        return $this->repository->datosById($' . $clau . ');
    }';

$txt_interface .= "\n\t";
$txt_interface .= '
    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     * 
     * @param ' . $clau_tip_txt . ' $' . $clau . '
     * @return array|bool
     */
    public function datosById(' . $clau_tip_txt . ' $' . $clau . '): array|bool;';

$txt_pgRepositorio .= "\n\t";
$txt_pgRepositorio .= '
    /**
     * Devuelve los campos de la base de datos en un array asociativo.
     * Devuelve false si no existe la fila en la base de datos
     * 
     * @param ' . $clau_tip_txt . ' $' . $clau . '
     * @return array|bool
     */
    public function datosById(' . $clau_tip_txt . ' $' . $clau . '): array|bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE ' . $where . '")) === FALSE) {
			$sClaveError = \'' . $clase . '.getDatosById\';
			$_SESSION[\'oGestorErrores\']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }';

if (!empty($bytea_bind)) {
    $txt_pgRepositorio .= "\n\t\t" . '// para los bytea, sobre escribo los valores:';
    $txt_pgRepositorio .= $bytea_bind;
} else {
    $txt_pgRepositorio .= "\n\t\t" . '$aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);';
}

if (!empty($array_dades)) {
    $txt_pgRepositorio .= "\n\t\t\t// para los array del postgres";
    $txt_pgRepositorio .= "\n\t\t\t" . 'if ($aDatos !== FALSE) {';
    $txt_pgRepositorio .= $array_dades;
    $txt_pgRepositorio .= "\n\t\t\t}";
}

if (!empty($fechas_dades)) {
    $txt_pgRepositorio .= "\n\t\t\t// para las fechas del postgres (texto iso)";
    $txt_pgRepositorio .= "\n\t\t\t" . 'if ($aDatos !== FALSE) {';
    $txt_pgRepositorio .= $fechas_dades;
    $txt_pgRepositorio .= "\n\t\t\t}";
}


$txt_pgRepositorio .= '
        return $aDatos;
    }
    ';

$txt_repository .= "\n\t";
$txt_repository .= '
    /**
     * Busca la clase con ' . $clau . ' en el repositorio.
     */
    public function findById(' . $clau_tip_txt . ' $' . $clau . '): ?' . $Q_clase . '
    {
        return $this->repository->findById($' . $clau . ');
    }';

$txt_interface .= "\n\t";
$txt_interface .= '
    /**
     * Busca la clase con ' . $clau . ' en el repositorio.
     */
    public function findById(' . $clau_tip_txt . ' $' . $clau . '): ?' . $Q_clase . ';';

$txt_pgRepositorio .= "\n\t";
$txt_pgRepositorio .= '
    /**
     * Busca la clase con ' . $clau . ' en la base de datos .
     */
    public function findById(' . $clau_tip_txt . ' $' . $clau . '): ?' . $Q_clase . '
    {
        $aDatos = $this->datosById($' . $clau . ');
        if (empty($aDatos)) {
            return null;
        }
        return (new ' . $Q_clase . '())->setAllAttributes($aDatos);
    }';

if ($id_seq || $id_seq2) {
    if (!empty($id_seq2)) {
        $id_seq = $id_seq2;
    }
    $nomcamp = $a_auto[0];

    $txt_repository .= "\n\t";
    $txt_repository .= '
    public function getNew' . ucfirst($nomcamp) . '()
    {
        return $this->repository->getNew' . ucfirst($nomcamp) . '();
    }';

    $txt_interface .= "\n\t";
    $txt_interface .= '
    public function getNew' . ucfirst($nomcamp) . '();';

    $txt_pgRepositorio .= "\n\t";
    $txt_pgRepositorio .= '
    public function getNew' . ucfirst($nomcamp) . '()
    {
        $oDbl = $this->getoDbl();
        $sQuery = "select nextval(\'' . $id_seq . '\'::regclass)";
        return $oDbl->query($sQuery)->fetchColumn();
    }';

}

$txt_repository .= "\n}";
$txt_interface .= "\n}";
$txt_pgRepositorio .= "\n}";

$txt = '';
// para los bytea
if (!empty($bytea_bind)) {
    $txt .= "\n\t\t" . '// para los bytea:';
    $txt .= $bytea_bind;
}
$txt .= "\n\t\t\t" . '$aDatos = $oDblSt->fetch(PDO::FETCH_ASSOC);';
if (!empty($bytea_bind)) {
    $txt .= "\n\t\t\t" . '// para los bytea, sobre escribo los valores:';
    $txt .= $bytea_dades;
}
$txt .= "\n\t\t\t" . '
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
			switch ($que) {
				case \'tot\':
                    $this->setAllAttributes($aDatos);
					break;
				case \'guardar\':
					if (!$oDblSt->rowCount()){
					    return FALSE;
					}
					break;
                default:
					// En el caso de no existir esta fila, $aDatos = FALSE:
					if ($aDatos === FALSE) {
						return FALSE;
					}
					$this->setAllAttributes($aDatos);
			}
			return TRUE;
		}
        return FALSE;
	}';


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


/* ESCRIURE LA CLASSSE  PG REPOSITORY  --------------------------------- */
// crear el directorio infrastructure si no existe
$dir_infra = ServerConf::DIR . '/apps/' . $grupo . '/infrastructure';
if (!is_dir($dir_infra)) {
    mkdir($dir_infra);
}
$filename = $dir_infra . '/' . $pg_clase . '.php';
if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}
// Write $somecontent to our opened file.
if (fwrite($handle, $txt_pgRepositorio) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}
echo "<br>Success, wrote (somecontent) to file ($filename)";
fclose($handle);

/* ESCRIURE LA CLASSE  REPOSITORYINTERFACE  --------------------------------- */
$dir_repositories = ServerConf::DIR . '/apps/' . $grupo . '/domain/repositories';
if (!is_dir($dir_repositories)) {
    mkdir($dir_repositories);
}
$filename = $dir_repositories . '/' . $clase_interface . '.php';
if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}
// Write $somecontent to our opened file.
if (fwrite($handle, $txt_interface) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}
echo "<br>Success, wrote (somecontent) to file ($filename)";
fclose($handle);

/* ESCRIURE LA CLASSE  REPOSITORY  --------------------------------- */
$dir_repositories = ServerConf::DIR . '/apps/' . $grupo . '/domain/repositories';
if (!is_dir($dir_repositories)) {
    mkdir($dir_repositories);
}
$filename = $dir_repositories . '/' . $clase_repository . '.php';
if (!$handle = fopen($filename, 'w')) {
    echo "Cannot open file ($filename)";
    die();
}
// Write $somecontent to our opened file.
if (fwrite($handle, $txt_repository) === FALSE) {
    echo "Cannot write to file ($filename)";
    die();
}
echo "<br>Success, wrote (somecontent) to file ($filename)";
fclose($handle);
