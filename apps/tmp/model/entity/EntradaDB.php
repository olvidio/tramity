<?php

namespace tmp\model\entity;

use core\ClasePropiedades;
use core\ConverterDate;
use core\ConverterJson;
use core\DatosCampo;
use core\Set;
use JsonException;
use PDO;
use PDOException;
use stdClass;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use function core\array_pg2php;
use function core\array_php2pg;
use function core\is_true;


/**
 * Fichero con la Clase que accede a la tabla entradas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 30/11/2022
 */

/**
 * Clase que implementa la entidad entradas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 2.0
 * @created 30/11/2022
 */
class EntradaDB extends ClasePropiedades
{
    /* ATRIBUTOS ----------------------------------------------------------------- */


    /**
     * aPrimary_key de EntradaDB
     *
     * @var array
     */
    private array $aPrimary_key;

    /**
     * bLoaded de EntradaDB
     *
     * @var bool
     */
    private bool $bLoaded = FALSE;


    /**
     * Id_entrada de EntradaDB
     *
     * @var int
     */
    private int $iid_entrada;
    /**
     * Modo_entrada de EntradaDB
     *
     * @var int
     */
    private int $imodo_entrada;
    /**
     * Json_prot_origen de EntradaDB
     *
     * @var string|null
     */
    private ?string $json_prot_origen = null;
    /**
     * Asunto_entrada de EntradaDB
     *
     * @var string
     */
    private string $sasunto_entrada;
    /**
     * Json_prot_ref de EntradaDB
     *
     * @var string|null
     */
    private ?string $json_prot_ref = null;
    /**
     * Ponente de EntradaDB
     *
     * @var int|null
     */
    private ?int $iponente = null;
    /**
     * Resto_oficinas de EntradaDB
     *
     * @var array|null
     */
    private ?array $a_resto_oficinas = null;
    /**
     * Asunto de EntradaDB
     *
     * @var string|null
     */
    private ?string $sasunto = null;
    /**
     * F_entrada de EntradaDB
     *
     * @var DateTimeLocal|null
     */
    private ?DateTimeLocal $df_entrada = null;
    /**
     * Detalle de EntradaDB
     *
     * @var string|null
     */
    private ?string $sdetalle = null;
    /**
     * Categoria de EntradaDB
     *
     * @var int|null
     */
    private ?int $icategoria = null;
    /**
     * Visibilidad de EntradaDB
     *
     * @var int|null
     */
    private ?int $ivisibilidad = null;
    /**
     * F_contestar de EntradaDB
     *
     * @var DateTimeLocal|null
     */
    private ?DateTimeLocal $df_contestar = null;
    /**
     * Bypass de EntradaDB
     *
     * @var bool|null
     */
    private ?bool $bbypass = null;
    /**
     * Estado de EntradaDB
     *
     * @var int|null
     */
    private ?int $iestado = null;
    /**
     * Anulado de EntradaDB
     *
     * @var string|null
     */
    private ?string $sanulado = null;
    /**
     * Encargado de EntradaDB
     *
     * @var int|null
     */
    private ?int $iencargado = null;
    /**
     * Json_visto de EntradaDB
     *
     * @var string|null
     */
    private ?string $json_visto = null;
    /**
     * Id_entrada_compartida de EntradaDB
     *
     * @var int|null
     */
    private ?int $iid_entrada_compartida = null;
    /* ATRIBUTOS QUE NO SON CAMPOS------------------------------------------------- */

    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * @param integer|null $iid_entrada
     */
    public function __construct(int $iid_entrada = null)
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
     * Si no existe el registro, hace un insert, si existe, se hace el update.
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
        $aDades['id_entrada_compartida'] = $this->iid_entrada_compartida;
        array_walk($aDades, 'core\poner_null');
        //para el caso de los boolean FALSE, el pdo(+postgresql) pone string '' en vez de 0. Lo arreglo:
        if (is_true($aDades['bypass'])) {
            $aDades['bypass'] = 'true';
        } else {
            $aDades['bypass'] = 'false';
        }

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
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
					json_visto               = :json_visto,
					id_entrada_compartida    = :id_entrada_compartida";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
                $sClaveError = 'EntradaDB.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'EntradaDB.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $campos = "(id_entrada,modo_entrada,json_prot_origen,asunto_entrada,json_prot_ref,ponente,resto_oficinas,asunto,f_entrada,detalle,categoria,visibilidad,f_contestar,bypass,estado,anulado,encargado,json_visto,id_entrada_compartida)";
            $valores = "(:id_entrada,:modo_entrada,:json_prot_origen,:asunto_entrada,:json_prot_ref,:ponente,:resto_oficinas,:asunto,:f_entrada,:detalle,:categoria,:visibilidad,:f_contestar,:bypass,:estado,:anulado,:encargado,:json_visto,:id_entrada_compartida)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClaveError = 'EntradaDB.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClaveError = 'EntradaDB.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClaveError, __LINE__, __FILE__);
                return FALSE;
            }
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carga los campos de la base de datos como ATRIBUTOS de la clase.
     * @throws JsonException
     */
    public function DBCargar($que = null): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_entrada)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
                $sClaveError = 'EntradaDB.cargar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
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

    public function DBEliminar(): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
            $sClaveError = 'EntradaDB.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClaveError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDades
     * @throws JsonException
     */
    private function setAllAtributes(array $aDades): void
    {
        if (array_key_exists('id_entrada', $aDades)) {
            $this->setId_entrada($aDades['id_entrada']);
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
            $this->setF_entrada($aDades['f_entrada'], FALSE);
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
            $this->setF_contestar($aDades['f_contestar'], FALSE);
        }
        if (array_key_exists('bypass', $aDades)) {
            $this->setBypass($aDades['bypass']);
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
        if (array_key_exists('id_entrada_compartida', $aDades)) {
            $this->setId_entrada_compartida($aDades['id_entrada_compartida']);
        }
    }
    /* MÉTODOS GET y SET --------------------------------------------------------*/


    /**
     * Recupera las claves primarias de EntradaDB en un array
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
     * Establece las claves primarias de EntradaDB en un array
     *
     */
    public function setPrimary_key(array $aPrimaryKey): void
    {
        $this->aPrimary_key = $aPrimaryKey;
    }


    /**
     *
     * @return int $iid_entrada
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
     *
     * @param int $iid_entrada
     */
    public function setId_entrada(int $iid_entrada): void
    {
        $this->iid_entrada = $iid_entrada;
    }

    /**
     *
     * @return int $imodo_entrada
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
     *
     * @param int $imodo_entrada
     */
    public function setModo_entrada(int $imodo_entrada): void
    {
        $this->imodo_entrada = $imodo_entrada;
    }

    /**
     *
     * @param bool $bArray si hay que devolver un array en vez de un objeto.
     * @return array|stdClass|null $json_prot_origen
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
     *
     * @param string|array|null $json_prot_origen
     * @param bool $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_prot_origen(string|array|null $json_prot_origen, bool $db = FALSE): void
    {
        $this->json_prot_origen = (new ConverterJson($json_prot_origen, FALSE))->toPg($db);
    }

    /**
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
     *
     * @param string $sasunto_entrada
     */
    public function setAsunto_entrada(string $sasunto_entrada): void
    {
        $this->sasunto_entrada = $sasunto_entrada;
    }

    /**
     *
     * @param bool $bArray si hay que devolver un array en vez de un objeto.
     * @return array|stdClass|null $json_prot_ref
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
     *
     * @param string|array|null $json_prot_ref
     * @param bool $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_prot_ref(string|array|null $json_prot_ref, bool $db = FALSE): void
    {
        $this->json_prot_ref = (new ConverterJson($json_prot_ref, FALSE))->toPg($db);
    }

    /**
     *
     * @return int|null $iponente
     * @throws JsonException
     */
    public function getPonente(): ?int
    {
        if (!isset($this->iponente) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iponente;
    }

    /**
     *
     * @param int|null $iponente
     */
    public function setPonente(?int $iponente = null): void
    {
        $this->iponente = $iponente;
    }

    /**
     *
     * @return array|null $a_resto_oficinas
     * @throws JsonException
     */
    public function getResto_oficinas(): array|null
    {
        if (!isset($this->a_resto_oficinas) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return array_pg2php($this->a_resto_oficinas);
    }

    /**
     *
     * @param array|string|null $a_resto_oficinas
     * @param bool $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
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
     *
     * @return string|null $sasunto
     * @throws JsonException
     */
    public function getAsunto(): ?string
    {
        if (!isset($this->sasunto) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sasunto;
    }

    /**
     *
     * @param string|null $sasunto
     */
    public function setAsunto(?string $sasunto = null): void
    {
        $this->sasunto = $sasunto;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal $df_entrada
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
     * Si $df_entrada es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, $df_entrada debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string|null $df_entrada
     * @param bool $convert =TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
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
     *
     * @return string|null $sdetalle
     * @throws JsonException
     */
    public function getDetalle(): ?string
    {
        if (!isset($this->sdetalle) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdetalle;
    }

    /**
     *
     * @param string|null $sdetalle
     */
    public function setDetalle(?string $sdetalle = null): void
    {
        $this->sdetalle = $sdetalle;
    }

    /**
     *
     * @return int|null $icategoria
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
     *
     * @param int|null $icategoria
     */
    public function setCategoria(?int $icategoria = null): void
    {
        $this->icategoria = $icategoria;
    }

    /**
     *
     * @return int|null $ivisibilidad
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
     *
     * @param int|null $ivisibilidad
     */
    public function setVisibilidad(?int $ivisibilidad = null): void
    {
        $this->ivisibilidad = $ivisibilidad;
    }

    /**
     *
     * @return DateTimeLocal|NullDateTimeLocal $df_contestar
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
     * Si $df_contestar es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, $df_contestar debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string|null $df_contestar
     * @param bool $convert =TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
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
     *
     * @return bool|null $bbypass
     * @throws JsonException
     */
    public function getBypass(): ?bool
    {
        if (!isset($this->bbypass) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->bbypass;
    }

    /**
     *
     * @param bool|null $bbypass
     */
    public function setBypass(?bool $bbypass = null): void
    {
        $this->bbypass = $bbypass;
    }

    /**
     *
     * @return int|null $iestado
     * @throws JsonException
     */
    public function getEstado(): ?int
    {
        if (!isset($this->iestado) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iestado;
    }

    /**
     *
     * @param int|null $iestado
     */
    public function setEstado(?int $iestado = null): void
    {
        $this->iestado = $iestado;
    }

    /**
     *
     * @return string|null $sanulado
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
     *
     * @param string|null $sanulado
     */
    public function setAnulado(?string $sanulado = null): void
    {
        $this->sanulado = $sanulado;
    }

    /**
     *
     * @return int|null $iencargado
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
     *
     * @param int|null $iencargado
     */
    public function setEncargado(?int $iencargado = null): void
    {
        $this->iencargado = $iencargado;
    }

    /**
     *
     * @param bool $bArray si hay que devolver un array en vez de un objeto.
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
     *
     * @param string|array|null $json_visto
     * @param bool $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_visto(string|array|null $json_visto, bool $db = FALSE): void
    {
        $this->json_visto = (new ConverterJson($json_visto, FALSE))->toPg($db);
    }

    /**
     *
     * @return int|null $iid_entrada_compartida
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
     *
     * @param int|null $iid_entrada_compartida
     */
    public function setId_entrada_compartida(?int $iid_entrada_compartida = null): void
    {
        $this->iid_entrada_compartida = $iid_entrada_compartida;
    }
    /* MÉTODOS GET y SET DE ATRIBUTOS QUE NO SON CAMPOS -----------------------------*/

    /**
     * Devuelve una colección de objetos del tipo DatosCampo
     */
    public function getDatosCampos(): array
    {
        $oEntradaDBSet = new Set();

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
        $oEntradaDBSet->add($this->getDatosId_entrada_compartida());
        return $oEntradaDBSet->getTot();
    }


    /**
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
}
