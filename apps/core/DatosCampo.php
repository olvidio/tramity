<?php

namespace core;
use PDO;

/**
 * DatosCampo
 *
 * Classe per a gestionar les dades referents a un camp de la Base de Dades
 *
 * @param array amb camp Nom del camp
 *                    tabla Nom de la Taula
 * @subpackage model
 * @author
 * @version 1.0
 * @created 22/9/2010
 *
 * @package delegación
 */
class DatosCampo
{
    /* ATRIBUTOS ----------------------------------------------------------------- */
    /**
     * Nom_tabla de DatosCampo
     *
     * @var string
     */
    private $snom_tabla;
    /**
     * Nom_camp de DatosCampo
     *
     * @var string
     */
    private $snom_camp;
    /**
     * Etiqueta de DatosCampo
     *
     * @var string
     */
    private $setiqueta;
    /**
     * Aviso de DatosCampo:
     * para indicar si debe salir en las opciones de avisar si hay un cambio.
     *
     * @var boolean
     */
    private $baviso;
    /**
     * Tipo de DatosCampo
     *
     * @var string
     */
    private $stipo;
    /**
     * Argument de DatosCampo
     *
     * @var string
     */
    private $sargument;
    /**
     * Argument2 de DatosCampo
     *
     * @var string
     */
    private $sargument2;
    /**
     * Argument3 de DatosCampo
     *
     * @var string
     */
    private $sargument3;
    /**
     * Accion de DatosCampo
     *
     * @var string
     */
    private $saccion;
    /**
     * Depende de DatosCampo
     *
     * @var string
     */
    private $sdepende;
    /**
     * Lista de DatosCampo
     *
     * @var array
     */
    private $alista;
    /**
     * RegExp de DatosCampo
     *
     * @var string
     */
    private $sRegExp;
    /**
     * RegExpText de DatosCampo
     *
     * @var string
     */
    private $sRegExpText;


    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     *
     *
     */
    function __construct($a_id = null)
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                $nom_id = 's' . $nom_id;
                if ($val_id !== '') {
                    $this->$nom_id = $val_id;
                }
            }
        }
    }
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/


    /**
     *
     * Esta función devuelve datos sobre el campo de una tabla
     *
     * $oDB es la conexión al Postgresql
     * $tabla es el nombre de la tabla
     * $camp es el nombre del campo
     * $que es el dato que queremos saber:
     *        "longitud"    longitud del campo
     *        "nulo"        si es permite nulo o no
     *        "tipo"        int, varchar, bool...
     *        "valor"        valor por defecto
     */
    function datos_campo($oDB, $que)
    {
        $tabla = $this->getNom_tabla();
        $camp = $this->getNom_camp();
        if ($tabla && $camp) {
            //tipo de campos
            $sql_get_fields = "
				SELECT 
					a.attrelid,
					a.attnum,
					a.attname AS field, 
					t.typname AS type, 
					a.attlen AS length,
					a.atttypmod AS lengthvar,
					a.attnotnull AS notnull,
					a.atthasdef
				FROM 
					pg_attribute a, 
					pg_type t
				WHERE 
					a.attnum > 0
					and a.attrelid = ('\"'|| current_schema() || '\"' || '.$tabla')::regclass
					and a.atttypid = t.oid
					and a.attname = '$camp'
				ORDER BY a.attnum
			";
            $oDBSt_res_fields = $oDB->query($sql_get_fields);
            if ($oDBSt_res_fields === false) {
                $sClauError = 'DatosCampo.datos_campo';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDB, $sClauError, __LINE__, __FILE__);
                return false;
            }
            $row = $oDBSt_res_fields->fetch(PDO::FETCH_ASSOC);
            if ($row['length'] > 0) {
                $llarg = $row['length'];
            } else if ($row['lengthvar'] > 0) {
                $llarg = $row['lengthvar'] - 4;
            } else {
                $llarg = "var";
            }
            if ($row['type'] == "date") {
                $llarg = 8;
            }
            $null = $row['notnull'];

            //respuesta a lo que se pide
            switch ($que) {
                case "longitud" :
                    return $llarg;
                    break;
                case "nulo":
                    return $null;
                    break;
                case "tipo":
                    return $row['type'];
                    break;
                case "valor":
                    $rowdefault = '';
                    if (is_true($row['atthasdef'])) {
                        /*  valores por defecto
                        / creo  que las posibilidades son:
                            número
                            'txto'::character...
                            true, false
                            nextval(),idlocal()... -> funcion
                        */
                        $sql_get_default = "
							SELECT pg_get_expr(adbin, adrelid) as defvalor
							FROM pg_attrdef d
						   	WHERE d.adnum =" . $row['attnum'] . " AND 
							d.adrelid =" . $row['attrelid'];

                        $oDBSt_def_res = $oDB->query($sql_get_default);
                        if (!$oDBSt_def_res->rowCount()) {
                            $rowdefault = "";
                        } else {
                            $rowdefault = $oDBSt_def_res->fetchColumn();
                            $matches = [];
                            $rta = preg_match_all("/^'([\w]+)'::(.*)/", $rowdefault, $matches, PREG_SET_ORDER);
                            if (!empty($rta)) {
                                $rowdefault = $matches[0][1];
                            } elseif (strstr($rowdefault, '(')) {
                                $rowdefault = "function";
                            }
                        }
                        return $rowdefault;
                    } else {
                        return '';
                    }
                    break;
                default:
                    $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                    exit ($err_switch);
            }
        }
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * Recupera l'atribut snom_tabla de DatosCampo
     *
     * @return string snom_tabla
     */
    function getNom_tabla()
    {
        return $this->snom_tabla;
    }

    /**
     * Recupera l'atribut snom_camp de DatosCampo
     *
     * @return string snom_camp
     */
    function getNom_camp()
    {
        return $this->snom_camp;
    }

    /**
     * Recupera tots els ATRIBUTOS de TelecoUbi en un array
     *
     * @return array aDades
     */
    function getTot()
    {
        if (!is_array($this->aDades)) {
            $this->DBCargar('tot');
        }
        return $this->aDades;
    }

    /**
     * estableix el valor de l'atribut snom_tabla de DatosCampo
     *
     * @param string snom_tabla
     */
    function setNom_tabla($snom_tabla)
    {
        $this->snom_tabla = $snom_tabla;
    }

    /**
     * estableix el valor de l'atribut snom_camp de DatosCampo
     *
     * @param string snom_camp
     */
    function setNom_camp($snom_camp)
    {
        $this->snom_camp = $snom_camp;
    }

    /**
     * Recupera l'atribut setiqueta de DatosCampo
     *
     * @return string setiqueta
     */
    function getEtiqueta()
    {
        return $this->setiqueta;
    }

    /**
     * estableix el valor de l'atribut setiqueta de DatosCampo
     *
     * @param string setiqueta
     */
    function setEtiqueta($setiqueta)
    {
        $this->setiqueta = $setiqueta;
    }

    /**
     * Recupera l'atribut baviso de DatosCampo
     *
     * @return boolean baviso
     */
    function getAviso()
    {
        if (!isset($this->baviso)) {
            $this->baviso = TRUE;
        }
        return $this->baviso;
    }

    /**
     * estableix el valor de l'atribut baviso de DatosCampo
     *
     * @param boolean baviso
     */
    function setAviso($baviso)
    {
        $this->baviso = $baviso;
    }

    /**
     * Recupera l'atribut stipodb de DatosCampo
     *
     * @return string stipodb
     */
    function getTipoDb()
    {
        return $this->stipodb;
    }

    /**
     * estableix el valor de l'atribut stipodb de DatosCampo
     *
     * @param string stipodb
     */
    function setTipoDb($stipodb)
    {
        $this->stipodb = $stipodb;
    }

    /**
     * Recupera l'atribut stipo de DatosCampo
     *
     * @return string stipo
     */
    function getTipo()
    {
        return $this->stipo;
    }

    /**
     * estableix el valor de l'atribut stipo de DatosCampo
     *
     * @param string stipo
     */
    function setTipo($stipo)
    {
        $this->stipo = $stipo;
    }

    /**
     * Recupera l'atribut sargument de DatosCampo
     *
     * @return string sargument
     */
    function getArgument()
    {
        return $this->sargument;
    }

    /**
     * estableix el valor de l'atribut sargument de DatosCampo
     *
     * @param string sargument
     */
    function setArgument($sargument)
    {
        $this->sargument = $sargument;
    }

    /**
     * Recupera l'atribut sargument2 de DatosCampo
     *
     * @return string sargument2
     */
    function getArgument2()
    {
        return $this->sargument2;
    }

    /**
     * estableix el valor de l'atribut sargument2 de DatosCampo
     *
     * @param string sargument2
     */
    function setArgument2($sargument2)
    {
        $this->sargument2 = $sargument2;
    }

    /**
     * Recupera l'atribut sargument3 de DatosCampo
     *
     * @return string sargument3
     */
    function getArgument3()
    {
        return $this->sargument3;
    }

    /**
     * estableix el valor de l'atribut sargument3 de DatosCampo
     *
     * @param string sargument3
     */
    function setArgument3($sargument3)
    {
        $this->sargument3 = $sargument3;
    }

    /**
     * Recupera l'atribut saccion de DatosCampo
     *
     * @return string saccion
     */
    function getAccion()
    {
        return $this->saccion;
    }

    /**
     * estableix el valor de l'atribut saccion de DatosCampo
     *
     * @param string saccion
     */
    function setAccion($saccion)
    {
        $this->saccion = $saccion;
    }

    /**
     * Recupera l'atribut sdepende de DatosCampo
     *
     * @return string sdepende
     */
    function getDepende()
    {
        return $this->sdepende;
    }

    /**
     * estableix el valor de l'atribut sdepende de DatosCampo
     *
     * @param string sdepende
     */
    function setDepende($sdepende)
    {
        $this->sdepende = $sdepende;
    }

    /**
     * Recupera l'atribut alista de DatosCampo
     *
     * @return array alista
     */
    function getLista()
    {
        return $this->alista;
    }

    /**
     * estableix el valor de l'atribut alista de DatosCampo
     *
     * @param array alista
     */
    function setLista($alista)
    {
        $this->alista = $alista;
    }

    /**
     * Recupera l'atribut sRegExp de DatosCampo
     *
     * @return string sRegExp
     */
    function getRegExp()
    {
        return $this->sRegExp;
    }

    /**
     * estableix el valor de l'atribut sRegExp de DatosCampo
     *
     * @param string sRegExp
     */
    function setRegExp($sRegExp)
    {
        $this->sRegExp = $sRegExp;
    }

    /**
     * Recupera l'atribut sRegExpText de DatosCampo
     *
     * @return string sRegExpText
     */
    function getRegExpText()
    {
        return $this->sRegExpText;
    }

    /**
     * estableix el valor de l'atribut sRegExpText de DatosCampo
     *
     * @param string sRegExpText
     */
    function setRegExpText($sRegExpText)
    {
        $this->sRegExpText = $sRegExpText;
    }
}
