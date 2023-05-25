<?php

namespace expedientes\model;

use core\ConfigGlobal;
use documentos\model\Documento;
use documentos\model\GestorDocumento;
use entradas\model\Entrada;
use escritos\model\entity\EscritoDB;
use escritos\model\Escrito;
use etiquetas\model\entity\Etiqueta;
use etiquetas\model\entity\EtiquetaExpediente;
use etiquetas\model\entity\GestorEtiqueta;
use etiquetas\model\entity\GestorEtiquetaExpediente;
use expedientes\model\entity\ExpedienteDB;
use expedientes\model\entity\GestorAccion;
use tramites\model\entity\Firma;
use tramites\model\entity\GestorTramiteCargo;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorCargoGrupo;
use web\DateTimeLocal;


class Expediente extends expedienteDB
{

    /* CONST -------------------------------------------------------------- */

    // prioridad (relacionado con f_contestar)
    /*
    - Urgente (3 días)
    - Rápido (1 semana)
    - Normal (2 semanas)
    - A determinar
    */
    public const PRIORIDAD_URGENTE = 1;
    public const PRIORIDAD_RAPIDO = 2;
    public const PRIORIDAD_NORMAL = 3;
    public const PRIORIDAD_UNKNOW = 4;
    public const PRIORIDAD_FECHA = 5;
    public const PRIORIDAD_ESPERA = 6;
    // estado
    /*
    - Borrador (antes de circular, mientras lo trabaja el ponenente)
    - Circulando (está pasando a firmas)
    - Fijar reunión
    - Acabado (una vez firmado -aprobado o rechazado-, antes de enviar escritos...)
    - Terminado (una vez hechas todas las "acciones": enviar escritos...)
    - Copias (para marcar que es copia de otro, y para que salga en la selección de copias).
     */
    public const ESTADO_BORRADOR = 1;
    public const ESTADO_CIRCULANDO = 2;
    public const ESTADO_FIJAR_REUNION = 3;
    public const ESTADO_ACABADO = 4;
    public const ESTADO_ACABADO_ENCARGADO = 5;
    public const ESTADO_ACABADO_SECRETARIA = 6;
    public const ESTADO_ARCHIVADO = 7;
    public const ESTADO_COPIAS = 8;
    public const ESTADO_RECHAZADO = 10;
    public const ESTADO_DILATA = 11;
    public const ESTADO_ESPERA = 12;
    public const ESTADO_NO = 13;

    // vida (a criterio del ponente):
    /*
    - Permanente (no borrar)
    - Experiencia (5 años)
    - Normal (1 mes)
    - Temporal (1 semana)
    - Borrable (1 día)
    */
    public const VIDA_PERMANENTE = 1;
    public const VIDA_EXPERIENCIA = 2;
    public const VIDA_NORMAL = 3;
    public const VIDA_TEMPORAL = 4;
    public const VIDA_BORRABLE = 5;

    /* PROPIEDADES -------------------------------------------------------------- */


    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     * Si només necessita un valor, se li pot passar un integer.
     * En general se li passa un array amb les claus primàries.
     *
     * @param integer|array iid_expediente
     *                        $a_id. Un array con los nombres=>valores de las claves primarias.
     */
    function __construct($a_id = null)
    {
        $oDbl = $GLOBALS['oDBT'];
        if (is_array($a_id)) {
            $this->aPrimary_key = $a_id;
            foreach ($a_id as $nom_id => $val_id) {
                if (($nom_id === 'id_expediente') && $val_id !== '') {
                    $this->iid_expediente = (int)$val_id;
                }
            }
        } else {
            if (isset($a_id) && $a_id !== '') {
                $this->iid_expediente = (int)$a_id;
                $this->aPrimary_key = array('iid_expediente' => $this->iid_expediente);
            }
        }
        $this->setoDbl($oDbl);
        $this->setNomTabla('expedientes');
    }

    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function isDevueltoAlguno(): bool
    {
        // acciones: propuestas, escritos.
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $this->iid_expediente]);
        $bDevuelto = FALSE;
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            $comentarios = $oEscrito->getComentarios();
            $ok = $oEscrito->getOk();
            if ($ok === EscritoDB::OK_NO && !empty($comentarios)) {
                $bDevuelto = TRUE;
                break;
            }
        }
        return $bDevuelto;
    }

    public function isVistoTodos(): bool
    {
        // mirar si alguno que NO tiene el visto:
        $json_preparar = $this->getJson_preparar();
        $marca = TRUE;
        if (empty((array)$json_preparar)) {
            $marca = FALSE;
        } else {
            foreach ($json_preparar as $oficial) {
                if (empty($oficial->visto)) {
                    $marca = FALSE;
                }
            }
        }
        return $marca;
    }

    public function copiar($destino = '')
    {
        // por defecto va al borrador del mismo ponente
        if (!empty($destino)) {
            if ($destino === 'copias') {
                $ponente = ConfigGlobal::role_id_cargo();
                $estado = self::ESTADO_COPIAS;
            } else {
                $ponente = $destino;
                $estado = self::ESTADO_BORRADOR;
            }
        } else {
            $ponente = $this->getPonente();
            $estado = self::ESTADO_BORRADOR;
        }

        $oNewExpediente = new Expediente();
        $oNewExpediente->setPonente($ponente);
        $oNewExpediente->setId_tramite($this->getId_tramite());
        $oNewExpediente->setEstado($estado);
        $oNewExpediente->setPrioridad($this->getPrioridad());
        $oNewExpediente->setAsunto($this->getAsunto());
        $oNewExpediente->setEntradilla($this->getEntradilla());

        // copiar antecedentes
        $aAntecedentes = $this->getJson_antecedentes(TRUE);
        if (!empty($aAntecedentes)) {
            $oNewExpediente->setJson_antecedentes($aAntecedentes);
        }
        // buscar escritos para ponerlos como antecedentes
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $this->iid_expediente]);
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            // tipos de antecedentes:
            //n = 1 -> Entradas
            //n = 2 -> Expedientes
            //n = 3 -> Escritos-propuesta
            $tipo_antecedente = 'escrito';
            $a_antecedente = ['tipo' => $tipo_antecedente, 'id' => $id_escrito];

            $oNewExpediente->addAntecedente($a_antecedente);
        }

        $oNewExpediente->DBGuardar();
    }

    public function addAntecedente($a_antecedente)
    {
        // obtener los antecedentes actuales:
        $aAntecedentes = $this->getJson_antecedentes(TRUE);
        $aAntecedentes[] = $a_antecedente;

        // evitar repeticiones:
        $aAntecedentes_uniq = [];
        $key_array = [];
        $i = 0;
        foreach ($aAntecedentes as $aAntecedente) {
            if (!in_array($aAntecedente['id'], $key_array)) {
                $key_array[$i] = $aAntecedente['id'];
                $aAntecedentes_uniq[$i] = $aAntecedente;
            }
            $i++;
        }
        $this->setJson_antecedentes($aAntecedentes_uniq);
    }

    public function getContenido()
    {
        $str_contenido = '';
        $separador = '; ';
        // antecedentes:
        $aAntecedentes = $this->getJson_antecedentes(TRUE);
        $a = count($aAntecedentes);
        if ($a > 0) {
            $str_contenido .= empty($str_contenido) ? '' : $separador;
            $str_contenido .= "A($a)";
        }
        // acciones: propuestas, escritos.
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $this->iid_expediente]);
        $e = 0;
        $p = 0;
        foreach ($cAcciones as $oAccion) {
            /* Accion
            const ACCION_PROPUESTA  = 1;
            const ACCION_ESCRITO    = 2;
            const ACCION_PLANTILLA  = 3;
            */
            $tipo_accion = $oAccion->getTipo_accion();
            if ($tipo_accion == Escrito::ACCION_ESCRITO) {
                $e++;
            }
            if ($tipo_accion == Escrito::ACCION_PROPUESTA) {
                $p++;
            }
        }
        if ($e > 0) {
            $str_contenido .= empty($str_contenido) ? '' : $separador;
            $str_contenido .= "E($e)";
        }
        if ($p > 0) {
            $str_contenido .= empty($str_contenido) ? '' : $separador;
            $str_contenido .= "P($p)";
        }

        return $str_contenido;
    }

    public function getEtiquetasVisiblesArray($id_cargo = '')
    {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $a_etiquetas = [];
        foreach ($cEtiquetas as $oEtiqueta) {
            $a_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        return $a_etiquetas;
    }

    public function getEtiquetasVisibles($id_cargo = NULL)
    {
        if (empty($id_cargo)) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $gesEtiquetas = new GestorEtiqueta();
        $cMisEtiquetas = $gesEtiquetas->getMisEtiquetas($id_cargo);
        $a_mis_etiquetas = [];
        foreach ($cMisEtiquetas as $oEtiqueta) {
            $a_mis_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
        $aWhere = ['id_expediente' => $this->iid_expediente];
        $cEtiquetasExp = $gesEtiquetasExpediente->getEtiquetasExpediente($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            if (in_array($id_etiqueta, $a_mis_etiquetas)) {
                $cEtiquetas[] = new Etiqueta($id_etiqueta);
            }
        }

        return $cEtiquetas;
    }

    public function getEtiquetasVisiblesTxt($id_cargo = '')
    {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $str_etiquetas = '';
        foreach ($cEtiquetas as $oEtiqueta) {
            $str_etiquetas .= empty($str_etiquetas) ? '' : ', ';
            $str_etiquetas .= $oEtiqueta->getNom_etiqueta();
        }
        return $str_etiquetas;
    }

    public function getEtiquetas()
    {
        $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
        $aWhere = ['id_expediente' => $this->iid_expediente];
        $cEtiquetasExp = $gesEtiquetasExpediente->getEtiquetasExpediente($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            $cEtiquetas[] = new Etiqueta($id_etiqueta);
        }

        return $cEtiquetas;
    }

    public function setEtiquetas($aEtiquetas)
    {
        $this->delEtiquetas();
        $a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
        foreach ($a_filter_etiquetas as $id_etiqueta) {
            $EtiquetaExpediente = new EtiquetaExpediente(['id_expediente' => $this->iid_expediente, 'id_etiqueta' => $id_etiqueta]);
            $EtiquetaExpediente->DBGuardar();
        }
    }

    public function delEtiquetas()
    {
        $gesEtiquetasExpediente = new GestorEtiquetaExpediente();
        if ($gesEtiquetasExpediente->deleteEtiquetasExpediente($this->iid_expediente) === FALSE) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * pone la fecha de aprobación en todos los escritos del expediente.
     *
     * @param DateTimeLocal|string df_escrito.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_aprobacion_escritos($df_aprobacion, $convert = TRUE)
    {
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $this->iid_expediente]);

        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            $oEscrito->DBCargar();
            $oEscrito->setF_aprobacion($df_aprobacion, $convert);
            $oEscrito->DBGuardar();
        }
    }

    /**
     * pone la fecha en todos los escritos del expediente.
     *
     * @param DateTimeLocal|string df_escrito.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_escritos($df_escrito, $convert = TRUE)
    {
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $this->iid_expediente]);

        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            $oEscrito->DBCargar();
            $oEscrito->setF_escrito($df_escrito, $convert);
            $oEscrito->DBGuardar();
        }
    }

    /**
     * anula todos los escritos del expediente.
     *
     */
    public function anularEscritos()
    {
        $gesAcciones = new GestorAccion();
        $cAcciones = $gesAcciones->getAcciones(['id_expediente' => $this->iid_expediente]);

        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = new Escrito($id_escrito);
            $oEscrito->DBCargar();
            $oEscrito->setAnulado(TRUE);
            $oEscrito->DBGuardar();
        }
    }

    public function getArrayPrioridad()
    {
        return [
            self::PRIORIDAD_NORMAL => _("normal"),
            self::PRIORIDAD_RAPIDO => _("rápido"),
            self::PRIORIDAD_URGENTE => _("urgente"),
            self::PRIORIDAD_UNKNOW => _("desconocido"),
            self::PRIORIDAD_FECHA => _("otra"),
            self::PRIORIDAD_ESPERA => _("en espera"),
        ];
    }

    public function getArrayEstado()
    {
        return [
            self::ESTADO_BORRADOR => _("borrador"),
            self::ESTADO_CIRCULANDO => _("circulando"),
            self::ESTADO_FIJAR_REUNION => _("fijar reunión"),
            self::ESTADO_ACABADO => _("acabado"),
            self::ESTADO_ACABADO_ENCARGADO => _("encargado"),
            self::ESTADO_ACABADO_SECRETARIA => _("ok secretaria / distribuir"),
            self::ESTADO_ARCHIVADO => _("archivado"),
            self::ESTADO_COPIAS => _("copias"),
            self::ESTADO_RECHAZADO => _("rechazado"),
            self::ESTADO_DILATA => _("dilata"),
            self::ESTADO_ESPERA => _("espera"),
            self::ESTADO_NO => _("no"),
        ];
    }

    public function getArrayVida()
    {
        return [
            self::VIDA_NORMAL => _("normal"),
            self::VIDA_PERMANENTE => _("permanente"),
            self::VIDA_EXPERIENCIA => _("experiencia"),
            self::VIDA_TEMPORAL => _("temporal"),
            self::VIDA_BORRABLE => _("borrable"),
        ];
    }

    /**
     *  Añadir al asunto el estado del expediente.
     */
    public function getAsuntoEstado()
    {
        $estado = $this->getEstado();
        $asunto = $this->getAsunto();

        switch ($estado) {
            case self::ESTADO_DILATA:
                $asunto_estado = _("DILATA") . " " . $asunto;
                break;
            case self::ESTADO_RECHAZADO:
                $asunto_estado = _("RECHAZADO") . " " . $asunto;
                break;
            case self::ESTADO_NO:
                $asunto_estado = _("NO") . " " . $asunto;
                break;
            default:
                $asunto_estado = $asunto;
        }

        return $asunto_estado;
    }

    public function delAntecedente($a_antecedente)
    {
        // obtener los antecedentes actuales:
        $aAntecedentes = $this->getJson_antecedentes(TRUE);
        $a_2 = [];
        $id_ref = $a_antecedente['id'];
        $tipo_ref = $a_antecedente['tipo'];
        foreach ($aAntecedentes as $antecedente) {
            $id = $antecedente['id'];
            $tipo = $antecedente['tipo'];
            if ($id == $id_ref && $tipo == $tipo_ref) {
                // OJO con la función unset no va bien, porque el array resultante consta de indices.
                //unset($aAntecedentes[$k]);
            } else {
                $a_2[] = $antecedente;
            }
        }
        $this->setJson_antecedentes($a_2);
    }

    public function getHtmlAntecedentes($quitar = TRUE)
    {
        // devolver la lista completa (para sobreescribir)
        $html = '';
        $aAntecedentes = $this->getJson_antecedentes(TRUE);
        if (!empty($aAntecedentes)) {
            $html = '<ol>';
            foreach ($aAntecedentes as $antecedente) {
                $link_mod = '-';
                $link_del = '';
                $id = $antecedente['id'];
                $tipo = $antecedente['tipo'];
                switch ($tipo) {
                    case 'entrada':
                        $oEntrada = new Entrada($id);
                        $asunto = $oEntrada->getAsuntoDetalle();
                        $prot_local = $oEntrada->cabeceraDerecha();
                        $nom = empty($prot_local) ? '' : $prot_local;
                        $nom .= empty($nom) ? "$asunto" : ": $asunto";
                        $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada($id);\" >$nom</span>";
                        $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                        break;
                    case 'expediente':
                        $oExpediente = new Expediente($id);
                        $asunto = $oExpediente->getAsunto();
                        $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente($id);\" >$asunto</span>";
                        $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                        break;
                    case 'escrito':
                        $oEscrito = new Escrito($id);
                        $asunto = $oEscrito->getAsuntoDetalle();
                        $prot_local = $oEscrito->cabeceraDerecha();
                        $nom = empty($prot_local) ? '' : $prot_local;
                        $nom .= empty($nom) ? "$asunto" : ": $asunto";
                        $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito($id);\" >$nom</span>";
                        $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                        break;
                    case 'documento':
                        $oDocumento = new Documento($id);
                        $gesDocumentos = new GestorDocumento();
                        $cDocumentos = $gesDocumentos->getDocumentos(['id_doc' => $id]);
                        if (empty($cDocumentos)) {
                            $nom = _("este documento se ha eliminado");
                            $tipo_doc = 0;
                        } else {
                            $tipo_doc = $cDocumentos[0]->getTipo_doc();
                            $nom = $oDocumento->getNom();
                        }
                        $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_documento($id,$tipo_doc);\" >$nom</span>";
                        $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
                if ($quitar === TRUE) {
                    $html .= "<li>$link_mod   $link_del</li>";
                } else {
                    $html .= "<li>$link_mod</li>";
                }
            }
            $html .= '</ol>';
        }
        return $html;
    }

    public function generarFirmas()
    {
        $id_tramite = $this->getId_tramite();
        $id_ponente = $this->getPonente();
        $gesTramiteCargo = new GestorTramiteCargo();
        $cTramiteCargos = $gesTramiteCargo->getTramiteCargos(['id_tramite' => $id_tramite, '_ordre' => 'orden_tramite']);

        foreach ($cTramiteCargos as $oTramiteCargo) {
            $id_cargo = $oTramiteCargo->getId_cargo();
            $orden_tramite = $oTramiteCargo->getOrden_tramite();

            // Para los ctr, comprobar que el cargo esta como oficial
            if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
                $a_firmas_oficina = $this->getFirmas_oficina();
                // añadir la del dtor.
                array_unshift($a_firmas_oficina, $id_ponente);
                if (!in_array($id_cargo, $a_firmas_oficina)) {
                    continue;
                }
            }

            // comprobar la oficina para los cargos especiales:
            // 1 => ponente
            // 2 => oficiales
            // 3 => varias
            // 4 => todos d.
            // 5 => vº bº vcd.
            // 6 => secretaria distribuir
            // 7 => secretaria reunion
            $oCargo = new Cargo($id_cargo);
            $id_oficina = $oCargo->getId_oficina();
            if (empty($id_oficina)) {
                switch ($id_cargo) {
                    case Cargo::CARGO_REUNION:
                    case Cargo::CARGO_DISTRIBUIR:
                        $gesCargos = new GestorCargo();
                        $cCargos = $gesCargos->getCargos(['cargo' => 'scdl']);
                        $oCargoDtor = $cCargos[0];
                        $id_dtor_scdl = $oCargoDtor->getId_cargo();
                        $oFirma = new Firma();
                        $oFirma->setId_expediente($this->iid_expediente);
                        $oFirma->setId_tramite($id_tramite);
                        $oFirma->setId_cargo_creador($id_ponente);
                        $oFirma->setCargo_tipo($id_cargo);
                        $oFirma->setId_cargo($id_dtor_scdl);
                        $oFirma->setOrden_tramite($orden_tramite);
                        // Al inicializar, sólo pongo los votos.
                        $oFirma->setTipo(Firma::TIPO_VOTO);
                        $oFirma->DBGuardar();
                        break;
                    case Cargo::CARGO_VB_VCD: // el vº bº lo tiene que dar el vcd.
                        $gesCargos = new GestorCargo();
                        $cCargos = $gesCargos->getCargos(['cargo' => 'vcd']);
                        $oCargoDtor = $cCargos[0];
                        $id_dtor_vcd = $oCargoDtor->getId_cargo();
                        $oFirma = new Firma();
                        $oFirma->setId_expediente($this->iid_expediente);
                        $oFirma->setId_tramite($id_tramite);
                        $oFirma->setId_cargo_creador($id_ponente);
                        $oFirma->setCargo_tipo($id_cargo);
                        $oFirma->setId_cargo($id_dtor_vcd);
                        $oFirma->setOrden_tramite($orden_tramite);
                        // Al inicializar, sólo pongo los votos.
                        $oFirma->setTipo(Firma::TIPO_VOTO);
                        $oFirma->DBGuardar();
                        break;
                    case Cargo::CARGO_PONENTE: // si es el ponente hay que poner su id_cargo.
                        // El ponente es el director de la oficina del creador.
                        $oCargo = new Cargo($id_ponente);
                        $id_oficina = $oCargo->getId_oficina();
                        $gesCargos = new GestorCargo();
                        $cCargos = $gesCargos->getCargos(['id_oficina' => $id_oficina, 'director' => 't']);
                        $oCargoDtor = $cCargos[0];
                        $id_dtor_ponente = $oCargoDtor->getId_cargo();
                        $oFirma = new Firma();
                        $oFirma->setId_expediente($this->iid_expediente);
                        $oFirma->setId_tramite($id_tramite);
                        $oFirma->setId_cargo_creador($id_ponente);
                        $oFirma->setCargo_tipo($id_cargo);
                        $oFirma->setId_cargo($id_dtor_ponente);
                        $oFirma->setOrden_tramite($orden_tramite);
                        // Al inicializar, sólo pongo los votos.
                        $oFirma->setTipo(Firma::TIPO_VOTO);
                        $oFirma->DBGuardar();
                        break;
                    case Cargo::CARGO_OFICIALES: // para los oficiales de la oficina
                        $a_firmas_oficina = $this->getFirmas_oficina();
                        $orden_oficina = 0;
                        foreach ($a_firmas_oficina as $id_cargo_of) {
                            $orden_oficina++;
                            $oFirma = new Firma();
                            $oFirma->setId_expediente($this->iid_expediente);
                            $oFirma->setId_tramite($id_tramite);
                            $oFirma->setId_cargo_creador($id_ponente);
                            $oFirma->setCargo_tipo($id_cargo);
                            $oFirma->setId_cargo($id_cargo_of);
                            $oFirma->setOrden_tramite($orden_tramite);
                            $oFirma->setOrden_oficina($orden_oficina);
                            // Al inicializar, sólo pongo los votos.
                            $oFirma->setTipo(Firma::TIPO_VOTO);
                            $oFirma->DBGuardar();
                        }
                        break;
                    case Cargo::CARGO_VARIAS: // si es para varias oficinas
                        $a_resto_oficinas = $this->getResto_oficinas();
                        $orden_oficina = 0;
                        foreach ($a_resto_oficinas as $id_cargo_of) {
                            $orden_oficina++;
                            $oFirma = new Firma();
                            $oFirma->setId_expediente($this->iid_expediente);
                            $oFirma->setId_tramite($id_tramite);
                            $oFirma->setId_cargo_creador($id_ponente);
                            $oFirma->setCargo_tipo($id_cargo);
                            $oFirma->setId_cargo($id_cargo_of);
                            $oFirma->setOrden_tramite($orden_tramite);
                            $oFirma->setOrden_oficina($orden_oficina);
                            // Al inicializar, sólo pongo los votos.
                            $oFirma->setTipo(Firma::TIPO_VOTO);
                            $oFirma->DBGuardar();
                        }
                        break;
                    case Cargo::CARGO_TODOS_DIR:  // si es para todos los dir menos vcd
                        $gesCargoGrupo = new GestorCargoGrupo();
                        $cGrupos = $gesCargoGrupo->getCargoGrupos(['id_cargo_ref' => Cargo::CARGO_TODOS_DIR]);
                        $aMiembros = $cGrupos[0]->getMiembros();
                        $orden_oficina = 0;
                        foreach ($aMiembros as $id_cargo) {
                            $oCargo = new Cargo($id_cargo);
                            $orden_oficina++;
                            $id_cargo_of = $oCargo->getId_cargo();
                            $oFirma = new Firma();
                            $oFirma->setId_expediente($this->iid_expediente);
                            $oFirma->setId_tramite($id_tramite);
                            $oFirma->setId_cargo_creador($id_ponente);
                            $oFirma->setCargo_tipo(Cargo::CARGO_TODOS_DIR);
                            $oFirma->setId_cargo($id_cargo_of);
                            $oFirma->setOrden_tramite($orden_tramite);
                            $oFirma->setOrden_oficina($orden_oficina);
                            // Al inicializar, sólo pongo los votos.
                            $oFirma->setTipo(Firma::TIPO_VOTO);
                            $oFirma->DBGuardar();
                        }
                        break;
                    default:
                        $oCargo = new Cargo($id_cargo);
                        $nom_cargo = $oCargo->getCargo();
                        $err_switch = sprintf(_("opción cargo: %s. no definida en switch en %s, linea %s"), $nom_cargo, __FILE__, __LINE__);
                        exit ($err_switch);
                }
            } else {
                $oFirma = new Firma();
                $oFirma->setId_expediente($this->iid_expediente);
                $oFirma->setId_tramite($id_tramite);
                $oFirma->setId_cargo_creador($id_ponente);
                $oFirma->setCargo_tipo($id_cargo);
                $oFirma->setId_cargo($id_cargo);
                $oFirma->setOrden_tramite($orden_tramite);
                // Al inicializar, sólo pongo los votos.
                $oFirma->setTipo(Firma::TIPO_VOTO);
                $oFirma->DBGuardar();
            }
        }
    }

}