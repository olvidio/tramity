<?php

namespace tramites\model\entity;

use core;
use web\DateTimeLocal;
use web\NullDateTimeLocal;

/**
 * Fitxer amb la Classe que accedeix a la taula expediente_firmas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 14/11/2020
 */

/**
 * Classe que implementa l'entitat expediente_firmas
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 14/11/2020
 */
class Firma extends core\ClasePropiedades
{

    /* CONST -------------------------------------------------------------- */
    // tipo, valor

    // tipo
    public const TIPO_VOTO = 1;
    public const TIPO_ACLARACION = 2;
    // valor. Finalmente distingo los votos del vcd (_D_: director) para poder 
    // hacer cambios de estado en el expediente más fácilmente.
    public const V_VISTO = 1;  // leído, pensando
    public const V_ESPERA = 2;  // distinto a no leído
    public const V_NO = 3;  // voto negativo
    public const V_OK = 4;  // voto positivo
    public const V_D_ESPERA = 22;  // distinto a no leído
    public const V_D_NO = 23;  // voto negativo
    public const V_D_OK = 24;  // voto positivo
    public const V_D_DILATA = 25;  // sólo vcd
    public const V_D_RECHAZADO = 26;  // sólo vcd
    public const V_D_VISTO_BUENO = 27;  // sólo vcd VºBº
    // del tipo aclaración
    public const V_A_NUEVA = 51;  // Petición de aclaración
    public const V_A_RESPUESTA = 52;  // Respuesta a la petición de aclaración
    public const V_A_ESPERA = 53;  // en espera


    /* ATRIBUTOS ----------------------------------------------------------------- */
    /**
     * oDbl de Firma
     *
     * @var object
     */
    protected $oDbl;
    /**
     * NomTabla de Firma
     *
     * @var string
     */
    protected $sNomTabla;
    /**
     * aPrimary_key de Firma
     *
     * @var array
     */
    private $aPrimary_key;
    /**
     * bLoaded de Firma
     *
     * @var boolean
     */
    private $bLoaded = FALSE;
    /**
     * Id_schema de Firma
     *
     * @var integer
     */
    private $iid_schema;
    /**
     * Id_item de Firma
     *
     * @var integer
     */
    private $iid_item;
    /**
     * Id_expediente de Firma
     *
     * @var integer
     */
    private $iid_expediente;
    /**
     * Id_tramite de Firma
     *
     * @var integer
     */
    private $iid_tramite;
    /**
     * Id_cargo_creador de Firma
     *
     * @var integer
     */
    private $iid_cargo_creador;
    /**
     * Cargo_tipo de Firma
     *
     * @var integer
     */
    private $icargo_tipo;
    /**
     * Id_cargo de Firma
     *
     * @var integer
     */
    private $iid_cargo;
    /**
     * Id_usuario de Firma
     *
     * @var integer
     */
    private $iid_usuario;
    /**
     * Orden_tramite de Firma
     *
     * @var integer
     */
    private $iorden_tramite;
    /**
     * Orden_oficina de Firma
     *
     * @var integer
     */
    private $iorden_oficina;
    /**
     * Tipo de Firma
     *
     * @var integer
     */
    private $itipo;
    /**
     * Valor de Firma
     *
     * @var integer
     */
    private $ivalor;
    /**
     * Observ_creador de Firma
     *
     * @var string
     */
    private $sobserv_creador;
    /* ATRIBUTOS QUE NO SÓN CAMPS------------------------------------------------- */
    /**
     * Observ de Firma
     *
     * @var string
     */
    private $sobserv;
    /**
     * F_valor de Firma
     *
     * @var DateTimeLocal
     */
    private $df_valor;
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_item
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    public function __construct($a_id = '')
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_item') && $val_id !== '') {
                    $this->iid_item = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_item = (int)$a_id;
                $this->aPrimary_key = array('iid_item' => $this->iid_item);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('expediente_firmas');
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function getArrayTipo()
    {
        $a_tipos = [
            self::TIPO_VOTO => _("voto"),
            self::TIPO_ACLARACION => _("aclaración"),
        ];

        return $a_tipos;
    }

    public function getArrayValor($rango = 'voto')
    {
        switch ($rango) {
            case 'voto':
                $a_tipos = [
                    self::V_OK => _("ok"),
                    self::V_NO => _("no"),
                    self::V_ESPERA => _("espera"),
                ];
                break;
            case 'vb_vcd':
                $a_tipos = [
                    self::V_D_NO => _("no"),
                    self::V_D_ESPERA => _("espera"),
                    self::V_D_VISTO_BUENO => _("VºBº"),
                    self::V_D_DILATA => _("dilata"),
                    self::V_D_RECHAZADO => _("rechazado"),
                ];
                break;
            case 'vcd':
                $a_tipos = [
                    self::V_D_OK => _("ok"),
                    self::V_D_NO => _("no"),
                    self::V_D_ESPERA => _("espera"),
                    self::V_D_DILATA => _("dilata"),
                    self::V_D_RECHAZADO => _("rechazado"),
                ];
                break;
            case 'aclaracion':
                $a_tipos = [
                    self::V_A_NUEVA => _("nueva"),
                    self::V_A_RESPUESTA => _("respuesta"),
                    self::V_A_ESPERA => _("espera"),
                ];
                break;
            case 'all': // todos
                $a_tipos = [
                    self::V_OK => _("ok"),
                    self::V_NO => _("no"),
                    self::V_VISTO => _("visto"),
                    self::V_ESPERA => _("espera"),
                    self::V_D_OK => _("ok"),
                    self::V_D_NO => _("no"),
                    self::V_D_ESPERA => _("espera"),
                    self::V_D_VISTO_BUENO => _("VºBº"),
                    self::V_D_DILATA => _("dilata"),
                    self::V_D_RECHAZADO => _("rechazado"),
                    self::V_A_NUEVA => _("nueva"),
                    self::V_A_RESPUESTA => _("respuesta"),
                    self::V_A_ESPERA => _("espera"),
                ];
                break;
        }
        return $a_tipos;
    }

    /**
     * Desa els ATRIBUTOS de l'objecte a la base de dades.
     * Si no hi ha el registre, fa el insert, si hi es fa el update.
     *
     */
    public function DBGuardar()
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if ($this->DBCargar('guardar') === FALSE) {
            $bInsert = TRUE;
        } else {
            $bInsert = FALSE;
        }
        $aDades = array();
        $aDades['id_expediente'] = $this->iid_expediente;
        $aDades['id_tramite'] = $this->iid_tramite;
        $aDades['id_cargo_creador'] = $this->iid_cargo_creador;
        $aDades['cargo_tipo'] = $this->icargo_tipo;
        $aDades['id_cargo'] = $this->iid_cargo;
        $aDades['id_usuario'] = $this->iid_usuario;
        $aDades['orden_tramite'] = $this->iorden_tramite;
        $aDades['orden_oficina'] = $this->iorden_oficina;
        $aDades['tipo'] = $this->itipo;
        $aDades['valor'] = $this->ivalor;
        $aDades['observ_creador'] = $this->sobserv_creador;
        $aDades['observ'] = $this->sobserv;
        $aDades['f_valor'] = $this->df_valor;
        array_walk($aDades, 'core\poner_null');

        if ($bInsert === FALSE) {
            //UPDATE
            $update = "
					id_expediente            = :id_expediente,
					id_tramite               = :id_tramite,
					id_cargo_creador         = :id_cargo_creador,
					cargo_tipo               = :cargo_tipo,
					id_cargo                 = :id_cargo,
					id_usuario               = :id_usuario,
					orden_tramite            = :orden_tramite,
					orden_oficina            = :orden_oficina,
					tipo                     = :tipo,
					valor                    = :valor,
					observ_creador           = :observ_creador,
					observ                   = :observ,
					f_valor                  = :f_valor";
            if (($oDblSt = $oDbl->prepare("UPDATE $nom_tabla SET $update WHERE id_item='$this->iid_item'")) === FALSE) {
                $sClauError = 'Firma.update.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (\PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'Firma.update.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
        } else {
            // INSERT
            $campos = "(id_expediente,id_tramite,id_cargo_creador,cargo_tipo,id_cargo,id_usuario,orden_tramite,orden_oficina,tipo,valor,observ_creador,observ,f_valor)";
            $valores = "(:id_expediente,:id_tramite,:id_cargo_creador,:cargo_tipo,:id_cargo,:id_usuario,:orden_tramite,:orden_oficina,:tipo,:valor,:observ_creador,:observ,:f_valor)";
            if (($oDblSt = $oDbl->prepare("INSERT INTO $nom_tabla $campos VALUES $valores")) === FALSE) {
                $sClauError = 'Firma.insertar.prepare';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }

            try {
                $oDblSt->execute($aDades);
            } catch (\PDOException $e) {
                $err_txt = $e->errorInfo[2];
                $this->setErrorTxt($err_txt);
                $sClauError = 'Firma.insertar.execute';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDblSt, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $this->id_item = $oDbl->lastInsertId('expediente_firmas_id_item_seq');
        }
        $this->setAllAtributes($aDades);
        return TRUE;
    }

    /**
     * Carrega els camps de la base de dades com ATRIBUTOS de l'objecte.
     *
     */
    public function DBCargar($que = null): bool
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (isset($this->iid_item)) {
            if (($oDblSt = $oDbl->query("SELECT * FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
                $sClauError = 'Firma.carregar';
                $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
                return FALSE;
            }
            $aDades = $oDblSt->fetch(\PDO::FETCH_ASSOC);
            // Para evitar posteriores cargas
            $this->bLoaded = TRUE;
            switch ($que) {
                case 'tot':
                    $this->setAllAtributes($aDades);
                    break;
                case 'guardar':
                    if (!$oDblSt->rowCount()) return FALSE;
                    break;
                default:
                    // En el caso de no existir esta fila, $aDades = FALSE:
                    if ($aDades === FALSE) {
                        $this->setNullAllAtributes();
                    } else {
                        $this->setAllAtributes($aDades);
                    }
            }
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Estableix el valor de tots els ATRIBUTOS
     *
     * @param array $aDades
     */
    public function setAllAtributes($aDades, $convert = FALSE)
    {
        if (!is_array($aDades)) {
            return;
        }
        if (array_key_exists('id_schema', $aDades)) {
            $this->setId_schema($aDades['id_schema']);
        }
        if (array_key_exists('id_item', $aDades)) {
            $this->setId_item($aDades['id_item']);
        }
        if (array_key_exists('id_expediente', $aDades)) {
            $this->setId_expediente($aDades['id_expediente']);
        }
        if (array_key_exists('id_tramite', $aDades)) {
            $this->setId_tramite($aDades['id_tramite']);
        }
        if (array_key_exists('id_cargo_creador', $aDades)) {
            $this->setId_cargo_creador($aDades['id_cargo_creador']);
        }
        if (array_key_exists('cargo_tipo', $aDades)) {
            $this->setCargo_tipo($aDades['cargo_tipo']);
        }
        if (array_key_exists('id_cargo', $aDades)) {
            $this->setId_cargo($aDades['id_cargo']);
        }
        if (array_key_exists('id_usuario', $aDades)) {
            $this->setId_usuario($aDades['id_usuario']);
        }
        if (array_key_exists('orden_tramite', $aDades)) {
            $this->setOrden_tramite($aDades['orden_tramite']);
        }
        if (array_key_exists('orden_oficina', $aDades)) {
            $this->setOrden_oficina($aDades['orden_oficina']);
        }
        if (array_key_exists('tipo', $aDades)) {
            $this->setTipo($aDades['tipo']);
        }
        if (array_key_exists('valor', $aDades)) {
            $this->setValor($aDades['valor']);
        }
        if (array_key_exists('observ_creador', $aDades)) {
            $this->setObserv_creador($aDades['observ_creador']);
        }
        if (array_key_exists('observ', $aDades)) {
            $this->setObserv($aDades['observ']);
        }
        if (array_key_exists('f_valor', $aDades)) {
            $this->setF_valor($aDades['f_valor'], $convert);
        }
    }

    /* OTOS MÉTODOS  ----------------------------------------------------------*/
    /* MÉTODOS PRIVADOS ----------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_item de Firma
     *
     * @param integer iid_item
     */
    public function setId_item($iid_item)
    {
        $this->iid_item = $iid_item;
    }

    /**
     * estableix el valor de l'atribut iid_expediente de Firma
     *
     * @param integer iid_expediente='' optional
     */
    public function setId_expediente($iid_expediente = '')
    {
        $this->iid_expediente = (int)$iid_expediente;
    }

    /* MÉTODOS GET y SET --------------------------------------------------------*/

    /**
     * estableix el valor de l'atribut iid_tramite de Firma
     *
     * @param integer iid_tramite='' optional
     */
    public function setId_tramite($iid_tramite = '')
    {
        $this->iid_tramite = $iid_tramite;
    }

    /**
     * estableix el valor de l'atribut iid_cargo_creador de Firma
     *
     * @param integer iid_cargo_creador='' optional
     */
    public function setId_cargo_creador($iid_cargo_creador = '')
    {
        $this->iid_cargo_creador = $iid_cargo_creador;
    }

    /**
     * estableix el valor de l'atribut icargo_tipo de Firma
     *
     * @param integer icargo_tipo='' optional
     */
    public function setCargo_tipo($icargo_tipo = '')
    {
        $this->icargo_tipo = $icargo_tipo;
    }

    /**
     * estableix el valor de l'atribut iid_cargo de Firma
     *
     * @param integer iid_cargo='' optional
     */
    public function setId_cargo($iid_cargo = '')
    {
        $this->iid_cargo = $iid_cargo;
    }

    /**
     * estableix el valor de l'atribut iid_usuario de Firma
     *
     * @param integer iid_usuario='' optional
     */
    public function setId_usuario($iid_usuario = '')
    {
        $this->iid_usuario = $iid_usuario;
    }

    /**
     * estableix el valor de l'atribut iorden_tramite de Firma
     *
     * @param integer iorden_tramite='' optional
     */
    public function setOrden_tramite($iorden_tramite = '')
    {
        $this->iorden_tramite = $iorden_tramite;
    }

    /**
     * estableix el valor de l'atribut iorden_oficina de Firma
     *
     * @param integer iorden_oficina='' optional
     */
    public function setOrden_oficina($iorden_oficina = '')
    {
        $this->iorden_oficina = $iorden_oficina;
    }

    /**
     * estableix el valor de l'atribut itipo de Firma
     *
     * @param integer itipo='' optional
     */
    public function setTipo($itipo = '')
    {
        $this->itipo = $itipo;
    }

    /**
     * estableix el valor de l'atribut ivalor de Firma
     *
     * @param integer ivalor='' optional
     */
    public function setValor($ivalor = '')
    {
        $this->ivalor = (int)$ivalor;
    }

    /**
     * estableix el valor de l'atribut sobserv_creador de Firma
     *
     * @param string sobserv_creador='' optional
     */
    public function setObserv_creador($sobserv_creador = '')
    {
        $this->sobserv_creador = $sobserv_creador;
    }

    /**
     * estableix el valor de l'atribut sobserv de Firma
     *
     * @param string sobserv='' optional
     */
    public function setObserv($sobserv = '')
    {
        $this->sobserv = $sobserv;
    }

    /**
     * estableix el valor de l'atribut df_valor de Firma
     * Si df_valor es string, y convert=true se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es false, df_valor debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param DateTimeLocal|string df_valor='' optional.
     * @param boolean convert=true optional. Si es false, df_valor debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_valor($df_valor = '', $convert = true)
    {
        if ($convert === true && !empty($df_valor)) {
            $oConverter = new core\Converter('date', $df_valor);
            $this->df_valor = $oConverter->toPg();
        } else {
            $this->df_valor = $df_valor;
        }
    }

    /**
     * Estableix a empty el valor de tots els ATRIBUTOS
     *
     */
    public function setNullAllAtributes()
    {
        $aPK = $this->getPrimary_key();
        $this->setId_schema('');
        $this->setId_item('');
        $this->setId_expediente('');
        $this->setId_tramite('');
        $this->setId_cargo_creador('');
        $this->setCargo_tipo('');
        $this->setId_cargo('');
        $this->setId_usuario('');
        $this->setOrden_tramite('');
        $this->setOrden_oficina('');
        $this->setTipo('');
        $this->setValor('');
        $this->setObserv_creador('');
        $this->setObserv('');
        $this->setF_valor('');
        $this->setPrimary_key($aPK);
    }

    /**
     * Recupera las claus primàries de Firma en un array
     *
     * @return array aPrimary_key
     */
    public function getPrimary_key()
    {
        if (!isset($this->aPrimary_key)) {
            $this->aPrimary_key = array('id_item' => $this->iid_item);
        }
        return $this->aPrimary_key;
    }

    /**
     * Estableix las claus primàries de Firma en un array
     *
     */
    public function setPrimary_key($a_id = '')
    {
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_item') && $val_id !== '') {
                    $this->iid_item = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_item = (int)$a_id;
                $this->aPrimary_key = array('iid_item' => $this->iid_item);
            }
        }
    }

    /**
     * Elimina el registre de la base de dades corresponent a l'objecte.
     *
     */
    public function DBEliminar()
    {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        if (($oDbl->exec("DELETE FROM $nom_tabla WHERE id_item='$this->iid_item'")) === FALSE) {
            $sClauError = 'Firma.eliminar';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut iid_item de Firma
     *
     * @return integer iid_item
     */
    public function getId_item(): int
    {
        if (!isset($this->iid_item) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_item;
    }

    /**
     * Recupera l'atribut iid_expediente de Firma
     *
     * @return integer iid_expediente
     */
    public function getId_expediente(): int
    {
        if (!isset($this->iid_expediente) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_expediente;
    }

    /**
     * Recupera l'atribut iid_tramite de Firma
     *
     * @return integer iid_tramite
     */
    public function getId_tramite(): int
    {
        if (!isset($this->iid_tramite) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_tramite;
    }

    /**
     * Recupera l'atribut iid_cargo_creador de Firma
     *
     * @return integer iid_cargo_creador
     */
    public function getId_cargo_creador(): int
    {
        if (!isset($this->iid_cargo_creador) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_cargo_creador;
    }

    /**
     * Recupera l'atribut icargo_tipo de Firma
     *
     * @return integer icargo_tipo
     */
    function getCargo_tipo(): int
    {
        if (!isset($this->icargo_tipo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->icargo_tipo;
    }

    /**
     * Recupera l'atribut iid_cargo de Firma
     *
     * @return integer iid_cargo
     */
    public function getId_cargo(): int
    {
        if (!isset($this->iid_cargo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_cargo;
    }

    /**
     * Recupera l'atribut iid_usuario de Firma
     *
     * @return integer|null iid_usuario
     */
    public function getId_usuario(): ?int
    {
        if (!isset($this->iid_usuario) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iid_usuario;
    }

    /**
     * Recupera l'atribut iorden_tramite de Firma
     *
     * @return integer iorden_tramite
     */
    public function getOrden_tramite(): int
    {
        if (!isset($this->iorden_tramite) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iorden_tramite;
    }

    /**
     * Recupera l'atribut iorden_oficina de Firma
     *
     * @return integer|null iorden_oficina
     */
    public function getOrden_oficina(): ?int
    {
        if (!isset($this->iorden_oficina) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->iorden_oficina;
    }

    /**
     * Recupera l'atribut itipo de Firma
     *
     * @return integer itipo
     */
    public function getTipo(): int
    {
        if (!isset($this->itipo) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->itipo;
    }

    /**
     * Recupera l'atribut ivalor de Firma
     *
     * @return integer|null ivalor
     */
    public function getValor(): ?int
    {
        if (!isset($this->ivalor) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->ivalor;
    }

    /**
     * Recupera l'atribut sobserv_creador de Firma
     *
     * @return string|null sobserv_creador
     */
    public function getObserv_creador(): ?string
    {
        if (!isset($this->sobserv_creador) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sobserv_creador;
    }

    /**
     * Recupera l'atribut sobserv de Firma
     *
     * @return string|null sobserv
     */
    public function getObserv(): ?string
    {
        if (!isset($this->sobserv) && !$this->bLoaded) {
            $this->DBCargar();
        }
        return $this->sobserv;
    }

    /**
     * Recupera l'atribut df_valor de Firma
     *
     * @return DateTimeLocal|NullDateTimeLocal|null df_valor
     */
    public function getF_valor()
    {
        if (!isset($this->df_valor) && !$this->bLoaded) {
            $this->DBCargar();
        }
        if (empty($this->df_valor)) {
            return new NullDateTimeLocal();
        }
        $oConverter = new core\Converter('date', $this->df_valor);
        return $oConverter->fromPg();
    }

    /**
     * Retorna una col·lecció d'objectes del tipus DatosCampo
     *
     */
    public function getDatosCampos()
    {
        $oFirmaSet = new core\Set();

        $oFirmaSet->add($this->getDatosId_expediente());
        $oFirmaSet->add($this->getDatosId_tramite());
        $oFirmaSet->add($this->getDatosId_cargo_creador());
        $oFirmaSet->add($this->getDatosCargo_tipo());
        $oFirmaSet->add($this->getDatosId_cargo());
        $oFirmaSet->add($this->getDatosId_usuario());
        $oFirmaSet->add($this->getDatosOrden_tramite());
        $oFirmaSet->add($this->getDatosOrden_oficina());
        $oFirmaSet->add($this->getDatosTipo());
        $oFirmaSet->add($this->getDatosValor());
        $oFirmaSet->add($this->getDatosObserv_creador());
        $oFirmaSet->add($this->getDatosObserv());
        $oFirmaSet->add($this->getDatosF_valor());
        return $oFirmaSet->getTot();
    }
    /* MÉTODOS GET y SET D'ATRIBUTOS QUE NO SÓN CAMPS -----------------------------*/

    /**
     * Recupera les propietats de l'atribut iid_expediente de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_expediente(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_expediente'));
        $oDatosCampo->setEtiqueta(_("id_expediente"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_tramite de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_tramite(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_tramite'));
        $oDatosCampo->setEtiqueta(_("id_tramite"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_cargo_creador de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_cargo_creador(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_cargo_creador'));
        $oDatosCampo->setEtiqueta(_("id_cargo_creador"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut icargo_tipo de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosCargo_tipo(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'cargo_tipo'));
        $oDatosCampo->setEtiqueta(_("cargo_tipo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_cargo de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_cargo(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_cargo'));
        $oDatosCampo->setEtiqueta(_("id_cargo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iid_usuario de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosId_usuario(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'id_usuario'));
        $oDatosCampo->setEtiqueta(_("id_usuario"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iorden_tramite de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosOrden_tramite(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'orden_tramite'));
        $oDatosCampo->setEtiqueta(_("orden_tramite"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut iorden_oficina de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosOrden_oficina(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'orden_oficina'));
        $oDatosCampo->setEtiqueta(_("orden_oficina"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut itipo de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosTipo(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'tipo'));
        $oDatosCampo->setEtiqueta(_("tipo"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut ivalor de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosValor(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'valor'));
        $oDatosCampo->setEtiqueta(_("valor"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sobserv_creador de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosObserv_creador(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'observ_creador'));
        $oDatosCampo->setEtiqueta(_("observ_creador"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut sobserv de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosObserv(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'observ'));
        $oDatosCampo->setEtiqueta(_("observ"));
        return $oDatosCampo;
    }

    /**
     * Recupera les propietats de l'atribut df_valor de Firma
     * en una clase del tipus DatosCampo
     *
     * @return core\DatosCampo
     */
    function getDatosF_valor(): core\DatosCampo
    {
        $nom_tabla = $this->getNomTabla();
        $oDatosCampo = new core\DatosCampo(array('nom_tabla' => $nom_tabla, 'nom_camp' => 'f_valor'));
        $oDatosCampo->setEtiqueta(_("f_valor"));
        return $oDatosCampo;
    }
}
