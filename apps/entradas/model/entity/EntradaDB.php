<?php

namespace entradas\model\entity;

use core\ClasePropiedades;
use core\ConverterDate;
use core\ConverterJson;
use core\DatosCampo;
use core\Set;
use entradas\model\Entrada;
use JsonException;
use PDO;
use PDOException;
use stdClass;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use function core\array_pgInteger2php;
use function core\array_php2pg;
use function core\is_true;

/**
 * Fitxer amb la Classe que accedeix a la taula entradas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

/**
 * Classe que implementa l'entitat entradas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */
class EntradaDB extends ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * aPrimary_key de EntradaDB
     *
     * @var array
     */
    protected array $aPrimary_key;

    /**
     * aDades de EntradaDB
     *
     * @var array
     */
    protected array $aDades;

    /**
     * bLoaded de EntradaDB
     *
     * @var boolean
     */
    protected bool $bLoaded = FALSE;

    /**
     * Id_schema de EntradaDB
     *
     * @var integer
     */
    protected $iid_schema;

    /**
     * Id_entrada de EntradaDB
     *
     * @var integer
     */
    protected int $iid_entrada;
    /**
     * Id_entrada_compartida de EntradaDB
     *
     * @var integer|null
     */
    protected ?int $iid_entrada_compartida = null;
    /**
     * Modo_entrada de EntradaDB
     *
     * @var integer
     */
    protected int $imodo_entrada;
    /**
     * Json_prot_origen de EntradaDB
     *
     * @var string|null
     */
    protected ?string $json_prot_origen = null;
    /**
     * Asunto_entrada de EntradaDB
     *
     * @var string
     */
    protected ?string $sasunto_entrada = null;
    /**
     * Json_prot_ref de EntradaDB
     *
     * @var string|null
     */
    protected ?string $json_prot_ref = null;
    /**
     * Ponente de EntradaDB
     *
     * @var integer|null
     */
    protected ?int $iponente = null;
    /**
     * Resto_oficinas de EntradaDB
     *
     * @var string|null
     */
    protected ?string $a_resto_oficinas = null;
    /**
     * Asunto de EntradaDB
     *
     * @var string|null
     */
    protected ?string $sasunto = null;
    /**
     * F_entrada de EntradaDB
     *
     * @var string|null
     */
    protected ?string $df_entrada = null;
    /**
     * Detalle de EntradaDB
     *
     * @var string|null
     */
    protected ?string $sdetalle = null;
    /**
     * Categoria de EntradaDB
     *
     * @var integer|null
     */
    protected ?int $icategoria = null;
    /**
     * Visibilidad de EntradaDB
     *
     * @var integer|null
     */
    protected ?int $ivisibilidad = null;
    /**
     * F_contestar de EntradaDB
     *
     * @var string|null
     */
    protected ?string $df_contestar = null;
    /**
     * Bypass de EntradaDB
     *
     * @var boolean
     */
    protected bool $bbypass = FALSE;
    /**
     * Estado de EntradaDB
     *
     * @var integer|null
     */
    protected ?int $iestado = null;
    /**
     * Anulado de EntradaDB
     *
     * @var string|null
     */
    protected ?string $sanulado = null;
    /**
     * Encargado de EntradaDB
     *
     * @var integer|null
     */
    protected ?int $iencargado = null;
    /**
     * Json_visto de EntradaDB
     *
     * @var string|null
     */
    protected ?string $json_visto = null;

    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * oDbl de EntradaDB
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de EntradaDB
     *
     * @var string
     */
    protected $sNomTabla;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|null $iid_entrada
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    public function __construct(?int $iid_entrada = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if ($iid_entrada !== null) {
            $this->iid_entrada = $iid_entrada;
            $this->aPrimary_key = array('iid_entrada' => $this->iid_entrada);
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('entradas');
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     * Elimina el registre de la base de dades corresponent a l'objecte.
     *
     */
    public function DBEliminar(): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
            $sClauError = 'EntradaDB.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Comprueba si lo han visto todos y lo pone en estado Archivado
     *
     * @throws JsonException
     */
    public function comprobarVisto(): void
    {
        $ponente = $this->getPonente();
        $resto_oficinas = $this->getResto_oficinas();

        $a_json_visto = $this->getJson_visto(TRUE);
        foreach ($a_json_visto as $json_visto) {
            $id_oficina = $json_visto['oficina'];
            $visto = $json_visto['visto'];
            if ($visto === 'true') {
                if ($id_oficina === $ponente) {
                    $ponente = '';
                } else {
                    $key_of = array_search($id_oficina, $resto_oficinas, true);
                    unset($resto_oficinas[$key_of]);
                }
            }
        }

        if (empty($ponente) && empty($resto_oficinas)) {
            $this->setEstado(Entrada::ESTADO_ARCHIVADO);
            $this->DBGuardar();
        }

    }

    /**
     * Recupera l'atribut iponente de EntradaDB
     *
     * @return integer|null iponente
     * @throws JsonException
     */
    public function getPonente(): ?int
    {
        if (!isset($this->iponente) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iponente;
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * Carga los campos de la tabla como atributos de la clase.
     *
     * @throws JsonException
     */
    public function DBCargar($que = null): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_entrada)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
                $sClauError = 'EntradaDB.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $aDades = $oDblSt->fetch(PDO::FETCH_ASSOC);
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
            switch ($que) {
                case 'tot':
                    $this->setAllAtributes($aDades);
                    break;
                case 'guardar':
                    if (!$oDblSt->rowCount()) {
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

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDades
     * @param bool $convert
     * @throws JsonException
     */
    private function setAllAtributes(array $aDades, bool $convert = FALSE): void
    {
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('id_entrada', $aDades)) {
            $this->setId_entrada($aDades['id_entrada']);
        }
        if (array_key_exists('id_entrada_compartida', $aDades)) {
            $this->setId_entrada_compartida($aDades['id_entrada_compartida']);
        }
        if (array_key_exists('modo_entrada', $aDades)) {
            $this->setModo_entrada($aDades['modo_entrada']);
        }
        if (array_key_exists('json_prot_origen', $aDades)) {
            $this->setJson_prot_origen($aDades['json_prot_origen'], TRUE);
        }
        if (array_key_exists('asunto_entrada', $aDades)) {
            $this->setAsunto_entrada($aDades['asunto_entrada']);
        }
        if (array_key_exists('json_prot_ref', $aDades)) {
            $this->setJson_prot_ref($aDades['json_prot_ref'], TRUE);
        }
        if (array_key_exists('ponente', $aDades)) {
            $this->setPonente($aDades['ponente']);
        }
        if (array_key_exists('resto_oficinas', $aDades)) {
            $this->setResto_oficinas($aDades['resto_oficinas'], TRUE);
        }
        if (array_key_exists('asunto', $aDades)) {
            $this->setAsunto($aDades['asunto']);
        }
        if (array_key_exists('f_entrada', $aDades)) {
            $this->setF_entrada($aDades['f_entrada'], $convert);
        }
        if (array_key_exists('detalle', $aDades)) {
            $this->setDetalle($aDades['detalle']);
        }
        if (array_key_exists('categoria', $aDades)) {
            $this->setCategoria($aDades['categoria']);
        }
        if (array_key_exists('visibilidad', $aDades)) {
            $this->setVisibilidad($aDades['visibilidad']);
        }
        if (array_key_exists('f_contestar', $aDades)) {
            $this->setF_contestar($aDades['f_contestar'], $convert);
        }
        if (array_key_exists('bypass', $aDades)) {
            $this->setBypass(is_true($aDades['bypass']));
        }
        if (array_key_exists('estado', $aDades)) {
            $this->setEstado($aDades['estado']);
        }
        if (array_key_exists('anulado', $aDades)) {
            $this->setAnulado($aDades['anulado']);
        }
        if (array_key_exists('encargado', $aDades)) {
            $this->setEncargado($aDades['encargado']);
        }
        if (array_key_exists('json_visto', $aDades)) {
            $this->setJson_visto($aDades['json_visto'], TRUE);
        }
    }

    /**
     * @param integer $iid_entrada
     */
    public function setId_entrada(int $iid_entrada): void
    {
        $this->iid_entrada = $iid_entrada;
    }


    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param integer|null $iid_entrada_compartida
     */
    public function setId_entrada_compartida(?int $iid_entrada_compartida): void
    {
        $this->iid_entrada_compartida = $iid_entrada_compartida;
    }

    /**
     * @param integer $imodo_entrada
     */
    public function setModo_entrada(int $imodo_entrada): void
    {
        $this->imodo_entrada = $imodo_entrada;
    }

    /**
     * @param string $sasunto_entrada
     */
    public function setAsunto_entrada(string $sasunto_entrada): void
    {
        $this->sasunto_entrada = $sasunto_entrada;
    }

    /**
     * @param integer|null $iponente
     */
    public function setPonente(?int $iponente = null): void
    {
        $this->iponente = $iponente;
    }

    /**
     * @param array|string|null $a_resto_oficinas (si viene de la db es un string)
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    public function setResto_oficinas(array|string $a_resto_oficinas = null, bool $db = FALSE): void
    {
        if ($db === FALSE) {
            $postgresArray = array_php2pg($a_resto_oficinas);
        } else {
            $postgresArray = $a_resto_oficinas;
        }
        $this->a_resto_oficinas = $postgresArray;
    }

    /**
     * @param string|null $sasunto
     */
    public function setAsunto(?string $sasunto = null): void
    {
        $this->sasunto = $sasunto;
    }

    /**
     * Si df_entrada es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_entrada debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param string|DateTimeLocal|null $df_entrada df_entrada='' optional.
     * @param boolean $convert =TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_entrada(DateTimeLocal|string|null $df_entrada = '', bool $convert = TRUE): void
    {
        if ($convert === TRUE && !empty($df_entrada)) {
            $oConverter = new ConverterDate('date', $df_entrada);
            $this->df_entrada = $oConverter->toPg();
        } else {
            $this->df_entrada = $df_entrada;
        }
    }

    /**
     * @param string|null $sdetalle
     */
    public function setDetalle(?string $sdetalle = null): void
    {
        $this->sdetalle = $sdetalle;
    }

    /**
     * @param integer|null $icategoria
     */
    public function setCategoria(?int $icategoria = null): void
    {
        $this->icategoria = $icategoria;
    }

    /**
     * @param integer|null $ivisibilidad =null
     */
    public function setVisibilidad(?int $ivisibilidad = null): void
    {
        $this->ivisibilidad = $ivisibilidad;
    }

    /**
     * Si df_contestar es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_contestar debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param string|DateTimeLocal|null $df_contestar ='' optional.
     * @param boolean $convert =TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_contestar(DateTimeLocal|string|null $df_contestar = '', bool $convert = TRUE): void
    {
        if ($convert === TRUE && !empty($df_contestar)) {
            $oConverter = new ConverterDate('date', $df_contestar);
            $this->df_contestar = $oConverter->toPg();
        } else {
            $this->df_contestar = $df_contestar;
        }
    }

    /**
     * @param boolean $bbypass =FALSE optional
     */
    public function setBypass(bool $bbypass = FALSE): void
    {
        $this->bbypass = $bbypass;
    }

    /**
     * @param integer|null $iestado
     */
    public function setEstado(?int $iestado): void
    {
        $this->iestado = $iestado;
    }

    /**
     * @param string|null $sanulado
     */
    public function setAnulado(?string $sanulado): void
    {
        $this->sanulado = $sanulado;
    }

    /**
     * @param integer|null $iencargado
     */
    public function setEncargado(?int $iencargado): void
    {
        $this->iencargado = $iencargado;
    }


    /**
     * Recupera las claus primàries de EntradaDB en un array
     *
     * @return array aPrimary_key
     */
    public function getPrimary_key(): array
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_entrada' => $this->iid_entrada);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de EntradaDB en un array
     *
     */
    public function setPrimary_key($a_id = null): void
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_entrada') && $val_id !== '') {
                    $this->iid_entrada = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_entrada = (int)$a_id;
                $this->aPrimary_key = array('iid_entrada' => $this->iid_entrada);
            }
        }
    }

    /**
     * Recupera l'atribut a_resto_oficinas de EntradaDB
     *
     * @return array|null $a_resto_oficinas
     * @throws JsonException
     */
    public function getResto_oficinas(): ?array
    {
        if (!isset($this->a_resto_oficinas) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return array_pgInteger2php($this->a_resto_oficinas);
    }

    /**
     * Recupera l'atribut json_visto de EntradaDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return array|stdClass|null $json_visto
     * @throws JsonException
     */
    public function getJson_visto(bool $bArray = FALSE): array|stdClass|null
    {
        if (!isset($this->json_visto) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return (new ConverterJson($this->json_visto, $bArray))->fromPg();
    }

    /**
     * @param string|array|null $oJSON json_visto (los arrays del postgres som como strings)
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_visto(string|array|null $oJSON, bool $db = FALSE): void
    {
        $this->json_visto = (new ConverterJson($oJSON, FALSE))->toPg($db);
    }

    /**
     * Desa els ATRIBUTOS de l'objecte a la base de dades.
     * Si no hi ha el registre, fa el insert, si hi es fa el update.
     *
     * @throws JsonException
     */
    public function DBGuardar(): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if ($this->DBCargar('guardar') === FALSE) {
            $bInsert = TRUE;
        } else {
            $bInsert = FALSE;
        }
        $aDades = array();
        $aDades['id_entrada_compartida'] = $this->iid_entrada_compartida;
        $aDades['modo_entrada'] = $this->imodo_entrada;
        $aDades['json_prot_origen'] = $this->json_prot_origen;
        $aDades['asunto_entrada'] = $this->sasunto_entrada;
        $aDades['json_prot_ref'] = $this->json_prot_ref;
        $aDades['ponente'] = $this->iponente;
        $aDades['resto_oficinas'] = $this->a_resto_oficinas;
        $aDades['asunto'] = $this->sasunto;
        $aDades['f_entrada'] = $this->df_entrada;
        $aDades['detalle'] = $this->sdetalle;
        $aDades['categoria'] = $this->icategoria;
        $aDades['visibilidad'] = $this->ivisibilidad;
        $aDades['f_contestar'] = $this->df_contestar;
        $aDades['bypass'] = $this->bbypass;
        $aDades['estado'] = $this->iestado;
        $aDades['anulado'] = $this->sanulado;
        $aDades['encargado'] = $this->iencargado;
        $aDades['json_visto'] = $this->json_visto;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDades['bypass'])) {
            $aDades['bypass'] = 'true';
        } else {
            $aDades['bypass'] = 'false';
        }
        // asegurar que tiene fecha de entrada:
        if (empty($aDades['f_entrada'])) {
            $aDades['f_entrada'] = date('Y-m-d');
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_entrada_compartida    = :id_entrada_compartida,
					modo_entrada             = :modo_entrada,
					json_prot_origen         = :json_prot_origen,
					asunto_entrada           = :asunto_entrada,
					json_prot_ref            = :json_prot_ref,
					ponente                  = :ponente,
					resto_oficinas           = :resto_oficinas,
					asunto                   = :asunto,
					f_entrada                = :f_entrada,
					detalle                  = :detalle,
					categoria                = :categoria,
					visibilidad              = :visibilidad,
					f_contestar              = :f_contestar,
					bypass                   = :bypass,
					estado                   = :estado,
					anulado                  = :anulado,
					encargado                = :encargado,
					json_visto               = :json_visto";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
                $sClauError = 'EntradaDB.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'EntradaDB.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $campos = "(id_entrada_compartida,modo_entrada,json_prot_origen,asunto_entrada,json_prot_ref,ponente,resto_oficinas,asunto,f_entrada,detalle,categoria,visibilidad,f_contestar,bypass,estado,anulado,encargado,json_visto)";
            $valores = "(:id_entrada_compartida,:modo_entrada,:json_prot_origen,:asunto_entrada,:json_prot_ref,:ponente,:resto_oficinas,:asunto,:f_entrada,:detalle,:categoria,:visibilidad,:f_contestar,:bypass,:estado,:anulado,:encargado,:json_visto)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'EntradaDB.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'EntradaDB.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $this->iid_entrada = $oDbl->lastInsertId('entradas_id_entrada_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_entrada de EntradaDB
     *
     * @return integer $iid_entrada
     * @throws JsonException
     */
    public function getId_entrada(): int
    {
        if (!isset($this->iid_entrada) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_entrada;
    }

    /**
     * Recupera l'atribut iid_entrada_compartida de EntradaDB
     *
     * @return integer|null $iid_entrada_compartida
     * @throws JsonException
     */
    public function getId_entrada_compartida(): ?int
    {
        if (!isset($this->iid_entrada_compartida) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_entrada_compartida;
    }

    /**
     * Recupera l'atribut imodo_entrada de EntradaDB
     *
     * @return integer $imodo_entrada
     * @throws JsonException
     */
    public function getModo_entrada(): int
    {
        if (!isset($this->imodo_entrada) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->imodo_entrada;
    }

    /**
     * Recupera l'atribut json_prot_origen de EntradaDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return array|stdClass|null
     * @throws JsonException
     */
    public function getJson_prot_origen(bool $bArray = FALSE): array|stdClass|null
    {
        if (!isset($this->json_prot_origen) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return (new ConverterJson($this->json_prot_origen, $bArray))->fromPg();
    }

    /**
     * @param string|stdClass|null $oJSON json_prot_origen
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_prot_origen(string|stdClass|null $oJSON, bool $db = FALSE): void
    {
        $this->json_prot_origen = (new ConverterJson($oJSON, FALSE))->toPg($db);
    }

    /**
     * Recupera l'atribut sasunto_entrada de EntradaDB
     *
     * @return string $sasunto_entrada
     * @throws JsonException
     */
    public function getAsunto_entrada(): string
    {
        if (!isset($this->sasunto_entrada) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sasunto_entrada;
    }

    /**
     * Recupera l'atribut json_prot_ref de EntradaDB
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return array|stdClass|null $oJSON json_prot_ref
     * @throws JsonException
     */
    public function getJson_prot_ref(bool $bArray = FALSE): array|stdClass|null
    {
        if (!isset($this->json_prot_ref) && !$this->bLoaded) {
            $this->DBCargar();
        }

        return (new ConverterJson($this->json_prot_ref, $bArray))->fromPg();
    }

    /**
     * @param string|array|null $oJSON json_prot_ref (los arrays del postgres som como strings)
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_prot_ref(string|array|null $oJSON, bool $db = FALSE): void
    {
        $this->json_prot_ref = (new ConverterJson($oJSON, FALSE))->toPg($db);
    }

    /**
     * Recupera l'atribut sasunto de EntradaDB
     *
     * @return string|null sasunto
     * @throws JsonException
     */
    public function getAsuntoDB(): ?string
    {
        if (!isset($this->sasunto) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sasunto;
    }

    /**
     * Recupera l'atribut df_entrada de EntradaDB
     *
     * @return DateTimeLocal|NullDateTimeLocal df_entrada
     * @throws JsonException
     */
    public function getF_entrada(): DateTimeLocal|NullDateTimeLocal
    {
        if (!isset($this->df_entrada) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_entrada)) {
            return new NullDateTimeLocal();
        }
        return (new ConverterDate('date', $this->df_entrada))->fromPg();
    }

    /**
     * Recupera l'atribut sdetalle de EntradaDB
     *
     * @return string|null sdetalle
     * @throws JsonException
     */
    public function getDetalleDB(): ?string
    {
        if (!isset($this->sdetalle) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdetalle;
    }

    /**
     * Recupera l'atribut icategoria de EntradaDB
     *
     * @return integer|null icategoria
     * @throws JsonException
     */
    public function getCategoria(): ?int
    {
        if (!isset($this->icategoria) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->icategoria;
    }

    /**
     * Recupera l'atribut ivisibilidad de EntradaDB
     *
     * @return integer|null ivisibilidad
     * @throws JsonException
     */
    public function getVisibilidad(): ?int
    {
        if (!isset($this->ivisibilidad) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ivisibilidad;
    }

    /**
     * Recupera l'atribut df_contestar de EntradaDB
     *
     * @return DateTimeLocal|NullDateTimeLocal df_contestar
     * @throws JsonException
     */
    public function getF_contestar(): DateTimeLocal|NullDateTimeLocal
    {
        if (!isset($this->df_contestar) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_contestar)) {
            return new NullDateTimeLocal();
        }
        return (new ConverterDate('date', $this->df_contestar))->fromPg();
    }

    /**
     * Recupera l'atribut bbypass de EntradaDB
     *
     * @return boolean bbypass
     * @throws JsonException
     */
    public function getBypass(): bool
    {
        if (!isset($this->bbypass) && !$this->bLoaded) {
            // Puede ser que no sea una entradaBypass, al cargar da false (no hay ninguna fila)
            if ($this->DBCargar() === FALSE) {
                return FALSE;
            }
        }
        return $this->bbypass;
    }

    /**
     * Recupera l'atribut iestado de EntradaDB
     *
     * @return integer|null iestado
     * @throws JsonException
     */
    public function getEstado(): ?int
    {
        if (!isset($this->iestado) && !$this->bLoaded) {
            if ($this->DBCargar() === FALSE) {
                return NULL;
            }
        }
        return $this->iestado;
    }

    /**
     * Recupera l'atribut sanulado de EntradaDB
     *
     * @return string|null sanulado
     * @throws JsonException
     */
    public function getAnulado(): ?string
    {
        if (!isset($this->sanulado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sanulado;
    }

    /**
     * Recupera l'atribut iencargado de EntradaDB
     *
     * @return integer|null iencargado
     * @throws JsonException
     */
    public function getEncargado(): ?int
    {
        if (!isset($this->iencargado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iencargado;
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    public function getDatosCampos(): array
    {
        $oEntradaDBSet = new Set();

        $oEntradaDBSet->add($this->getDatosId_entrada_compartida());
        $oEntradaDBSet->add($this->getDatosModo_entrada());
        $oEntradaDBSet->add($this->getDatosJson_prot_origen());
        $oEntradaDBSet->add($this->getDatosAsunto_entrada());
        $oEntradaDBSet->add($this->getDatosJson_prot_ref());
        $oEntradaDBSet->add($this->getDatosPonente());
        $oEntradaDBSet->add($this->getDatosResto_oficinas());
        $oEntradaDBSet->add($this->getDatosAsunto());
        $oEntradaDBSet->add($this->getDatosF_entrada());
        $oEntradaDBSet->add($this->getDatosDetalle());
        $oEntradaDBSet->add($this->getDatosCategoria());
        $oEntradaDBSet->add($this->getDatosVisibilidad());
        $oEntradaDBSet->add($this->getDatosF_contestar());
        $oEntradaDBSet->add($this->getDatosBypass());
        $oEntradaDBSet->add($this->getDatosEstado());
        $oEntradaDBSet->add($this->getDatosAnulado());
        $oEntradaDBSet->add($this->getDatosEncargado());
        $oEntradaDBSet->add($this->getDatosJson_visto());
        return $oEntradaDBSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iid_entrada_compartida de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosId_entrada_compartida(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_entrada_compartida'));
        $oDatosCampo->setEtiqueta(_("id_entrada_compartida"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut imodo_entrada de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosModo_entrada(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'modo_entrada'));
        $oDatosCampo->setEtiqueta(_("modo_entrada"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_prot_origen de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosJson_prot_origen(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_prot_origen'));
        $oDatosCampo->setEtiqueta(_("json_prot_origen"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sasunto_entrada de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosAsunto_entrada(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'asunto_entrada'));
        $oDatosCampo->setEtiqueta(_("asunto_entrada"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_prot_ref de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosJson_prot_ref(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_prot_ref'));
        $oDatosCampo->setEtiqueta(_("json_prot_ref"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iponente de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosPonente(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'ponente'));
        $oDatosCampo->setEtiqueta(_("ponente"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_resto_oficinas de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosResto_oficinas(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'resto_oficinas'));
        $oDatosCampo->setEtiqueta(_("resto_oficinas"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sasunto de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosAsunto(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'asunto'));
        $oDatosCampo->setEtiqueta(_("asunto"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_entrada de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosF_entrada(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_entrada'));
        $oDatosCampo->setEtiqueta(_("f_entrada"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdetalle de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosDetalle(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'detalle'));
        $oDatosCampo->setEtiqueta(_("detalle"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut icategoria de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosCategoria(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'categoria'));
        $oDatosCampo->setEtiqueta(_("categoria"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ivisibilidad de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosVisibilidad(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'visibilidad'));
        $oDatosCampo->setEtiqueta(_("visibilidad"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_contestar de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosF_contestar(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_contestar'));
        $oDatosCampo->setEtiqueta(_("f_contestar"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut bbypass de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosBypass(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'bypass'));
        $oDatosCampo->setEtiqueta(_("bypass"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iestado de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosEstado(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'estado'));
        $oDatosCampo->setEtiqueta(_("estado"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sanulado de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosAnulado(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'anulado'));
        $oDatosCampo->setEtiqueta(_("anulado"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iencargado de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosEncargado(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'encargado'));
        $oDatosCampo->setEtiqueta(_("encargado"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_visto de EntradaDB
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    public function getDatosJson_visto(): DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_visto'));
        $oDatosCampo->setEtiqueta(_("json_visto"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de EntradaDB en un array
     *
     * @return array aDades
     */
    public function getTot(): array
    {
        return $this->aDades;
    }

    /**
     * @param int $iid_schema
     * @return EntradaDB
     */
    public function setIidSchema(int $iid_schema): EntradaDB
    {
        $this->iid_schema = $iid_schema;
        return $this;
    }

    /**
     * @param string $sNomTabla
     * @return EntradaDB
     */
    public function setSNomTabla(string $sNomTabla): EntradaDB
    {
        $this->sNomTabla = $sNomTabla;
        return $this;
    }
}
