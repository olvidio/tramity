<?php

namespace entradas\model\entity;

use core\Converter;
use core\DatosCampo;
use core\Set;
use entradas\model\Entrada;
use JsonException;
use lugares\model\entity\Grupo;
use lugares\model\entity\Lugar;
use PDO;
use PDOException;
use stdClass;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use function core\array_pg2php;
use function core\array_php2pg;

/**
 * Fitxer amb la Classe que accedeix a la taula entradas_bypass
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */

/**
 * Classe que implementa l'entitat entradas_bypass
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 20/10/2020
 */
class EntradaBypass extends Entrada
{
    /* ATRIBUTOS ----------------------------------------------------------------- */

    /**
     * oDbl de EntradaBypass
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de EntradaBypass
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * Descripción de EntradaBypass
     *
     * @var string
     */
    private string $sdescripcion;
    /**
     * Json_prot_destino de EntradaBypass
     *
     * @var string|null
     */
    private ?string $json_prot_destino;
    /**
     * Id_grupos de EntradaBypass
     *
     * @var string|null
     */
    private ?string $a_id_grupos;

    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Destinos de EntradaBypass
     *
     * @var string|null
     */
    private ?string $a_destinos;
    /**
     * F_salida de EntradaBypass
     *
     * @var string|null
     */
    private ?string $df_salida;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|null $iid_entrada
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct(?int $iid_entrada = null)
    {
        parent::__construct($iid_entrada);
        $this->setNomTabla('entradas_bypass');
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
            $sClauError = 'EntradaBypass.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Carga los campos de la tabla como atributos de la clase.
     *
     */
    public function DBCargar($que = null): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_entrada)) {
            if (($oDblSt = $oDbl->query("SELECT * 
				FROM $nom_tabla JOIN entradas USING (id_entrada) WHERE id_entrada='$this->iid_entrada'"))
                === FALSE) {
                $sClauError = 'EntradaBypass.carregar';
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

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * Establece el valor de todos los atributos
     *
     * @param array $aDades
     * @param bool $convert
     * @throws \JsonException
     */
    private function setAllAtributes(array $aDades, bool $convert = FALSE): void
    {
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        //if (array_key_exists('id_item',$aDades)) { $this->setId_item($aDades['id_item']); }
        if (array_key_exists('id_entrada', $aDades)) {
            $this->setId_entrada($aDades['id_entrada']);
        }
        if (array_key_exists('descripcion', $aDades)) {
            $this->setDescripcion($aDades['descripcion']);
        }
        if (array_key_exists('json_prot_destino', $aDades)) {
            $this->setJson_prot_destino($aDades['json_prot_destino'], TRUE);
        }
        if (array_key_exists('id_grupos', $aDades)) {
            $this->setId_grupos($aDades['id_grupos'], TRUE);
        }
        if (array_key_exists('destinos', $aDades)) {
            $this->setDestinos($aDades['destinos'], TRUE);
        }
        if (array_key_exists('f_salida', $aDades)) {
            $this->setF_salida($aDades['f_salida'], $convert);
        }
        // añado los de entradas
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
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * @param string $sdescripcion
     */
    public function setDescripcion(string $sdescripcion): void
    {
        $this->sdescripcion = $sdescripcion;
    }

    /**
     * @param string|null $a_id_grupos
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    public function setId_grupos(?string $a_id_grupos = null, bool $db = FALSE): void
    {
        if ($db === FALSE) {
            $postgresArray = array_php2pg($a_id_grupos);
        } else {
            $postgresArray = $a_id_grupos;
        }
        $this->a_id_grupos = $postgresArray;
    }

    /**
     * @param array|string|null $a_destinos
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un array postgresql,
     *  o es una variable de php hay que convertirlo.
     */
    public function setDestinos(array|string $a_destinos = null, bool $db = FALSE): void
    {
        if ($db === FALSE) {
            $postgresArray = array_php2pg($a_destinos);
        } else {
            $postgresArray = $a_destinos;
        }
        $this->a_destinos = $postgresArray;
    }

    /**
     * Si df_salida es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_salida debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param string|DateTimeLocal $df_salida
     * @param boolean $convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_salida(DateTimeLocal|string $df_salida = '', bool $convert = TRUE): void
    {
        if ($convert === TRUE && !empty($df_salida)) {
            $oConverter = new Converter('date', $df_salida);
            $this->df_salida = $oConverter->toPg();
        } else {
            $this->df_salida = $df_salida;
        }
    }

    
    /**
     * Recupera las claus primàries de EntradaBypass en un array
     *
     * @return array aPrimary_key
     */
    function getPrimary_key(): array
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_entrada' => $this->iid_entrada);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de EntradaBypass en un array
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
     * Recupera l'atribut a_destinos de EntradaBypass
     *
     * @return array $a_destinos
     */
    public function getDestinos(): array
    {
        if (!isset($this->a_destinos) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return array_pg2php($this->a_destinos);
    }

    /**
     * Recupera l'atribut df_salida de EntradaBypass
     *
     * @return DateTimeLocal $df_salida
     */
    public function getF_salida(): DateTimeLocal|NullDateTimeLocal
    {
        if (!isset($this->df_salida) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_salida)) {
            return new NullDateTimeLocal();
        }
        return (new Converter('date', $this->df_salida))->fromPg();
    }

    public function getDestinosByPass(): array
    {
        $a_grupos = $this->getId_grupos();

        $aMiembros = [];
        $destinos_txt = '';

        if (!empty($a_grupos)) {
            $destinos_txt = $this->getDescripcion();
            //(según los grupos seleccionados)
            $a_miembros_g = [];
            foreach ($a_grupos as $id_grupo) {
                $oGrupo = new Grupo($id_grupo);
                $a_miembros_g[] = $oGrupo->getMiembros();
                //$aMiembros = array_merge($aMiembros, $a_miembros_g);
            }
            $aMiembros = array_merge([], ...$a_miembros_g);
            $aMiembros = array_unique($aMiembros);
            $this->setDestinos($aMiembros);
            if ($this->DBGuardar() === FALSE) {
                $error_txt = $this->getErrorTxt();
                exit ($error_txt);
            }
        } else {
            //(según individuales)
            $a_json_prot_dst = $this->getJson_prot_destino();
            foreach ($a_json_prot_dst as $json_prot_dst) {
                $aMiembros[] = $json_prot_dst->id_lugar;
                $oLugar = new Lugar($json_prot_dst->id_lugar);
                $destinos_txt .= empty($destinos_txt) ? '' : ', ';
                $destinos_txt .= $oLugar->getNombre();
            }
        }

        return ['miembros' => $aMiembros, 'txt' => $destinos_txt];
    }

    /**
     * Recupera l'atribut a_id_grupos de EntradaBypass
     *
     * @return array $a_id_grupos
     */
    public function getId_grupos(): array
    {
        if (!isset($this->a_id_grupos) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return array_pg2php($this->a_id_grupos);
    }

    /**
     * Recupera l'atribut sdescripcion de EntradaBypass
     *
     * @return string $sdescripcion
     */
    public function getDescripcion(): string
    {
        if (!isset($this->sdescripcion) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sdescripcion;
    }

    /**
     * Desa els ATRIBUTOS de l'objecte a la base de dades.
     * Si no hi ha el registre, fa el insert, si hi és fa el update.
     *
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
        $aDades['id_entrada'] = $this->iid_entrada;
        $aDades['descripcion'] = $this->sdescripcion;
        $aDades['json_prot_destino'] = $this->json_prot_destino;
        $aDades['id_grupos'] = $this->a_id_grupos;
        $aDades['destinos'] = $this->a_destinos;
        $aDades['f_salida'] = $this->df_salida;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_entrada               = :id_entrada,
					descripcion              = :descripcion,
					json_prot_destino        = :json_prot_destino,
					id_grupos                = :id_grupos,
					destinos                 = :destinos,
					f_salida                 = :f_salida";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_entrada='$this->iid_entrada'")) === FALSE) {
                $sClauError = 'EntradaBypass.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'EntradaBypass.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $campos = "(id_entrada,descripcion,json_prot_destino,id_grupos,destinos,f_salida)";
            $valores = "(:id_entrada,:descripcion,:json_prot_destino,:id_grupos,:destinos,:f_salida)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'EntradaBypass.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'EntradaBypass.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            //$this->id_item = $oDbl->lastInsertId('entradas_bypass_id_item_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Recupera l'atribut json_prot_destino de EntradaBypass
     *
     * @param boolean $bArray si hay que devolver un array en vez de un objeto.
     * @return stdClass $json_prot_destino
     */
    public function getJson_prot_destino($bArray = FALSE): array|stdClass
    {
        if (!isset($this->json_prot_destino) && !$this->bLoaded) {
            $this->DBCargar();
        }
        $oJSON = json_decode($this->json_prot_destino, $bArray);
        if (empty($oJSON) || $oJSON === '[]') {
            if ($bArray) {
                $oJSON = [];
            } else {
                $oJSON = new stdClass;
            }
        }
        //$this->json_prot_destino = $oJSON;
        return $oJSON;
    }

    /**
     * @param string|null $oJSON json_prot_destino
     * @param boolean $db =FALSE optional. Para determinar la variable que se le pasa es ya un objeto json,
     *  o es una variable de php hay que convertirlo. En la base de datos ya es json.
     * @throws JsonException
     */
    public function setJson_prot_destino(?string $oJSON, bool $db = FALSE): void
    {
        if ($db === FALSE) {
            $json = json_encode($oJSON, JSON_THROW_ON_ERROR);
        } else {
            $json = $oJSON;
        }
        $this->json_prot_destino = $json;
    }

    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    function getDatosCampos(): array
    {
        $oEntradaBypassSet = new Set();

        $oEntradaBypassSet->add($this->getDatosId_entrada());
        $oEntradaBypassSet->add($this->getDatosDescripcion());
        $oEntradaBypassSet->add($this->getDatosJson_prot_destino());
        $oEntradaBypassSet->add($this->getDatosId_grupos());
        $oEntradaBypassSet->add($this->getDatosDestinos());
        $oEntradaBypassSet->add($this->getDatosF_salida());
        return $oEntradaBypassSet->getTot();
    }

    /**
     * Recupera les propietats de l'atribut iid_entrada de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosId_entrada()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_entrada'));
        $oDatosCampo->setEtiqueta(_("id_entrada"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sdescripcion de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosDescripcion()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'descripcion'));
        $oDatosCampo->setEtiqueta(_("descripcion"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut json_prot_destino de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosJson_prot_destino()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'json_prot_destino'));
        $oDatosCampo->setEtiqueta(_("json_prot_destino"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_id_grupos de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosId_grupos()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_grupos'));
        $oDatosCampo->setEtiqueta(_("id_grupos"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut a_destinos de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosDestinos()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'destinos'));
        $oDatosCampo->setEtiqueta(_("destinos"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_salida de EntradaBypass
     * en una clase del tipus DatosCampo
     *
     * @return DatosCampo
     */
    function getDatosF_salida()
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_salida'));
        $oDatosCampo->setEtiqueta(_("f_salida"));
        return $oDatosCampo;
    }

    /**
     * Recupera tots els ATRIBUTOS de EntradaBypass en un array
     *
     * @return array aDades
     */
    function getTot(): array
    {
        if (!is_array($this->aDades)) {
            $this->DBCargar('tot');
        }
        return $this->aDades;
    }
}
