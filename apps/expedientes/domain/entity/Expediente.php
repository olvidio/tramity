<?php

namespace expedientes\domain\entity;

use core\ConfigGlobal;
use documentos\domain\repositories\DocumentoRepository;
use entradas\domain\entity\EntradaRepository;
use escritos\domain\entity\Escrito;
use escritos\domain\entity\EscritoDB;
use escritos\domain\repositories\EscritoRepository;
use etiquetas\domain\entity\EtiquetaExpediente;
use etiquetas\domain\repositories\EtiquetaExpedienteRepository;
use etiquetas\domain\repositories\EtiquetaRepository;
use expedientes\domain\repositories\AccionRepository;
use expedientes\domain\repositories\ExpedienteRepository;
use stdClass;
use tramites\domain\entity\Firma;
use tramites\domain\repositories\FirmaRepository;
use tramites\domain\repositories\TramiteCargoRepository;
use usuarios\domain\entity\Cargo;
use usuarios\domain\repositories\CargoGrupoRepository;
use usuarios\domain\repositories\CargoRepository;
use web\DateTimeLocal;


class Expediente extends ExpedienteDB
{

    /* CONST -------------------------------------------------------------- */

    // prioridad (relacionado con oF_contestar)
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


    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function isDevueltoAlguno(): bool
    {
        // acciones: propuestas, escritos.
        $AccionRepository = new AccionRepository();
        $cAcciones = $AccionRepository->getAcciones(['id_expediente' => $this->iid_expediente]);
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

        $oNewExpediente = new self();
        $oNewExpediente->setPonente($ponente);
        $oNewExpediente->setId_tramite($this->getId_tramite());
        $oNewExpediente->setEstado($estado);
        $oNewExpediente->setPrioridad($this->getPrioridad());
        $oNewExpediente->setAsunto($this->getAsunto());
        $oNewExpediente->setEntradilla($this->getEntradilla());

        // copiar antecedentes
        $aAntecedentes = $this->getJson_antecedentes();
        if (!empty($aAntecedentes)) {
            $oNewExpediente->setJson_antecedentes($aAntecedentes);
        }
        // buscar escritos para ponerlos como antecedentes
        $AccionRepository = new AccionRepository();
        $cAcciones = $AccionRepository->getAcciones(['id_expediente' => $this->iid_expediente]);
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            // tipos de antecedentes:
            //n = 1 -> Entradas
            //n = 2 -> Expedientes
            //n = 3 -> Escritos-propuesta
            $Antecedente = new stdClass();
            $Antecedente->tipo = 'escrito';
            $Antecedente->id = $id_escrito;
            $oNewExpediente->addAntecedente($Antecedente);
        }

        $ExpedienteRepository = new ExpedienteRepository();
        $ExpedienteRepository->Guardar($oNewExpediente);
    }

    public function addAntecedente(stdClass $Antecedente): void
    {
        // obtener los antecedentes actuales:
        $aAntecedentes = (array)$this->getJson_antecedentes();
        $aAntecedentes[] = $Antecedente;

        // evitar repeticiones:
        $aAntecedentes_uniq = [];
        $key_array = [];
        $i = 0;
        foreach ($aAntecedentes as $Antecedente_i) {
            if (!in_array($Antecedente_i->id, $key_array)) {
                $key_array[$i] = $Antecedente_i->id;
                $aAntecedentes_uniq[$i] = $Antecedente_i;
            }
            $i++;
        }
        $this->setJson_antecedentes($aAntecedentes_uniq);
    }

    public function getContenido(): string
    {
        $str_contenido = '';
        $separador = '; ';
        // antecedentes:
        $aAntecedentes = (array)$this->getJson_antecedentes();
        $a = count($aAntecedentes);
        if ($a > 0) {
            $str_contenido .= empty($str_contenido) ? '' : $separador;
            $str_contenido .= "A($a)";
        }
        // acciones: propuestas, escritos.
        $AccionRepository = new AccionRepository();
        $cAcciones = $AccionRepository->getAcciones(['id_expediente' => $this->iid_expediente]);
        $e = 0;
        $p = 0;
        foreach ($cAcciones as $oAccion) {
            /* Accion
            const ACCION_PROPUESTA  = 1;
            const ACCION_ESCRITO    = 2;
            const ACCION_PLANTILLA  = 3;
            */
            $tipo_accion = $oAccion->getTipo_accion();
            if ($tipo_accion === Escrito::ACCION_ESCRITO) {
                $e++;
            }
            if ($tipo_accion === Escrito::ACCION_PROPUESTA) {
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

    public function getEtiquetasVisiblesArray(int $id_cargo = null): array
    {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $a_etiquetas = [];
        foreach ($cEtiquetas as $oEtiqueta) {
            $a_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        return $a_etiquetas;
    }

    public function getEtiquetasVisibles(int $id_cargo = null)
    {
        if ($id_cargo === null) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $etiquetaRepository = new EtiquetaRepository();
        $cMisEtiquetas = $etiquetaRepository->getMisEtiquetas($id_cargo);
        $a_mis_etiquetas = [];
        foreach ($cMisEtiquetas as $oEtiqueta) {
            $a_mis_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        $etiquetaExpedienteRepository = new EtiquetaExpedienteRepository();
        $aWhere = ['id_expediente' => $this->iid_expediente];
        $cEtiquetasExp = $etiquetaExpedienteRepository->getEtiquetasExpediente($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            if (in_array($id_etiqueta, $a_mis_etiquetas)) {
                $cEtiquetas[] = $etiquetaRepository->findById($id_etiqueta);
            }
        }

        return $cEtiquetas;
    }

    public function getEtiquetasVisiblesTxt(int $id_cargo = null): string
    {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $str_etiquetas = '';
        foreach ($cEtiquetas as $oEtiqueta) {
            $str_etiquetas .= empty($str_etiquetas) ? '' : ', ';
            $str_etiquetas .= $oEtiqueta->getNom_etiqueta();
        }
        return $str_etiquetas;
    }

    public function getEtiquetas(): array|null
    {
        $etiquetaRepository = new EtiquetaRepository();
        $etiquetaExpedienteRepository = new EtiquetaExpedienteRepository();
        $aWhere = ['id_expediente' => $this->iid_expediente];
        $cEtiquetasExp = $etiquetaExpedienteRepository->getEtiquetasExpediente($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            $cEtiquetas[] = $etiquetaRepository->findById($id_etiqueta);
        }

        return $cEtiquetas;
    }

    public function setEtiquetas(array $aEtiquetas = null): void
    {
        $etiquetaExpedienteRepository = new EtiquetaExpedienteRepository();
        $this->delEtiquetas();
        $a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
        foreach ($a_filter_etiquetas as $id_etiqueta) {
            $EtiquetaExpediente = new EtiquetaExpediente();
            $EtiquetaExpediente->setId_etiqueta($id_etiqueta);
            $EtiquetaExpediente->setId_expediente($this->iid_expediente);
            $etiquetaExpedienteRepository->Guardar($EtiquetaExpediente);
        }
    }

    public function delEtiquetas(): bool
    {
        $etiquetaExpedienteRepository = new EtiquetaExpedienteRepository();
        return $etiquetaExpedienteRepository->deleteEtiquetasExpediente($this->iid_expediente) !== FALSE;
    }

    /**
     * pone la fecha de aprobación en todos los escritos del expediente.
     *
     * @param DateTimeLocal|string df_escrito.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_aprobacion_escritos($oF_aprobacion): void
    {
        $AccionRepository = new AccionRepository();
        $cAcciones = $AccionRepository->getAcciones(['id_expediente' => $this->iid_expediente]);

        $escritoRepository = new EscritoRepository();
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = $escritoRepository->findById($id_escrito);
            $oEscrito->setF_aprobacion($oF_aprobacion);
            $escritoRepository->Guardar($oEscrito);
        }
    }

    /**
     * pone la fecha en todos los escritos del expediente.
     *
     * @param DateTimeLocal|string df_escrito.
     * @param boolean convert=TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_escritos($oF_escrito): void
    {
        $AccionRepository = new AccionRepository();
        $cAcciones = $AccionRepository->getAcciones(['id_expediente' => $this->iid_expediente]);

        $escritoRepository = new EscritoRepository();
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = $escritoRepository->findById($id_escrito);
            $oEscrito->setF_escrito($oF_escrito);
            $escritoRepository->Guardar($oEscrito);
        }
    }

    /**
     * anula todos los escritos del expediente.
     *
     */
    public function anularEscritos()
    {
        $AccionRepository = new AccionRepository();
        $cAcciones = $AccionRepository->getAcciones(['id_expediente' => $this->iid_expediente]);

        $escritoRepository = new EscritoRepository();
        foreach ($cAcciones as $oAccion) {
            $id_escrito = $oAccion->getId_escrito();
            $oEscrito = $escritoRepository->findById($id_escrito);
            $oEscrito->setAnulado(TRUE);
            $escritoRepository->Guardar($oEscrito);
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
    public function getAsuntoEstado(): ?string
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

    public function delAntecedente($a_antecedente): void
    {
        // obtener los antecedentes actuales:
        $aAntecedentes = $this->getJson_antecedentes();
        $a_2 = [];
        $id_ref = $a_antecedente['id'];
        $tipo_ref = $a_antecedente['tipo'];
        foreach ($aAntecedentes as $antecedente) {
            $id = $antecedente->id;
            $tipo = $antecedente->tipo;
            if ($id === $id_ref && $tipo === $tipo_ref) {
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
        $aAntecedentes = $this->getJson_antecedentes();
        if (!empty($aAntecedentes)) {
            $html = '<ol>';
            foreach ($aAntecedentes as $antecedente) {
                $link_mod = '-';
                $link_del = '';
                $id = $antecedente->id;
                $tipo = $antecedente->tipo;
                switch ($tipo) {
                    case 'entrada':
                        $EntradaRepository = new EntradaRepository();
                        $oEntrada = $EntradaRepository->findById($id);
                        if ($oEntrada !== null) {
                            $asunto = $oEntrada->getAsuntoDetalle();
                            $prot_local = $oEntrada->cabeceraDerecha();
                            $nom = empty($prot_local) ? '' : $prot_local;
                            $nom .= empty($nom) ? "$asunto" : ": $asunto";
                            $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada($id);\" >$nom</span>";
                            $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                        }
                        break;
                    case 'expediente':
                        $ExpedienteRepository = new ExpedienteRepository();
                        $oExpediente = $ExpedienteRepository->findById($id);
                        $asunto = $oExpediente->getAsunto();
                        $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_expediente($id);\" >$asunto</span>";
                        $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                        break;
                    case 'escrito':
                        $escritoRepository = new EscritoRepository();
                        $oEscrito = $escritoRepository->findById($id);
                        $asunto = $oEscrito->getAsuntoDetalle();
                        $prot_local = $oEscrito->cabeceraDerecha();
                        $nom = empty($prot_local) ? '' : $prot_local;
                        $nom .= empty($nom) ? "$asunto" : ": $asunto";
                        $link_mod = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_escrito($id);\" >$nom</span>";
                        $link_del = "<span class=\"btn btn-outline-danger btn-sm \" onclick=\"fnjs_del_antecedente('$tipo','$id');\" >" . _("quitar") . "</span>";
                        break;
                    case 'documento':
                        $documentoRepository = new DocumentoRepository();
                        $oDocumento = $documentoRepository->findById($id);
                        $tipo_doc = $oDocumento->getTipo_doc();
                        $nom = $oDocumento->getNom();
                        $nom = empty($nom) ? _("este documento se ha eliminado") : $nom;
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
        $TramiteCargoRepository = new TramiteCargoRepository();
        $cTramiteCargos = $TramiteCargoRepository->getTramiteCargos(['id_tramite' => $id_tramite, '_ordre' => 'orden_tramite']);

        $FirmaRepository = new FirmaRepository();
        foreach ($cTramiteCargos as $oTramiteCargo) {
            $id_cargo = $oTramiteCargo->getId_cargo();
            $orden_tramite = $oTramiteCargo->getOrden_tramite();

            // comprobar la oficina para los cargos especiales:
            // 1 => ponente
            // 2 => oficiales
            // 3 => varias
            // 4 => todos d.
            // 5 => vº bº vcd.
            // 6 => secretaria distribuir
            // 7 => secretaria reunion
            $CargoRepository = new CargoRepository();
            $oCargo = $CargoRepository->findById($id_cargo);
            $id_oficina = $oCargo->getId_oficina();
            if (empty($id_oficina)) {
                switch ($id_cargo) {
                    case Cargo::CARGO_REUNION:
                    case Cargo::CARGO_DISTRIBUIR:
                        $cCargos = $CargoRepository->getCargos(['cargo' => 'scdl']);
                        $oCargoDtor = $cCargos[0];
                        $id_dtor_scdl = $oCargoDtor->getId_cargo();
                        $id_item = $FirmaRepository->getNewId_item();
                        $oFirma = new Firma();
                        $oFirma->setId_item($id_item);
                        $oFirma->setId_expediente($this->iid_expediente);
                        $oFirma->setId_tramite($id_tramite);
                        $oFirma->setId_cargo_creador($id_ponente);
                        $oFirma->setCargo_tipo($id_cargo);
                        $oFirma->setId_cargo($id_dtor_scdl);
                        $oFirma->setOrden_tramite($orden_tramite);
                        // Al inicializar, sólo pongo los votos.
                        $oFirma->setTipo(Firma::TIPO_VOTO);
                        $FirmaRepository->Guardar($oFirma);
                        break;
                    case Cargo::CARGO_VB_VCD: // el vº bº lo tiene que dar el vcd.
                        $cCargos = $CargoRepository->getCargos(['cargo' => 'vcd']);
                        $oCargoDtor = $cCargos[0];
                        $id_dtor_vcd = $oCargoDtor->getId_cargo();
                        $id_item = $FirmaRepository->getNewId_item();
                        $oFirma = new Firma();
                        $oFirma->setId_item($id_item);
                        $oFirma->setId_expediente($this->iid_expediente);
                        $oFirma->setId_tramite($id_tramite);
                        $oFirma->setId_cargo_creador($id_ponente);
                        $oFirma->setCargo_tipo($id_cargo);
                        $oFirma->setId_cargo($id_dtor_vcd);
                        $oFirma->setOrden_tramite($orden_tramite);
                        // Al inicializar, sólo pongo los votos.
                        $oFirma->setTipo(Firma::TIPO_VOTO);
                        $FirmaRepository->Guardar($oFirma);
                        break;
                    case Cargo::CARGO_PONENTE: // si es el ponente hay que poner su id_cargo.
                        // El ponente es el director de la oficina del creador.
                        $oCargo = $CargoRepository->findById($id_ponente);
                        $id_oficina = $oCargo->getId_oficina();
                        $cCargos = $CargoRepository->getCargos(['id_oficina' => $id_oficina, 'director' => 't']);
                        $oCargoDtor = $cCargos[0];
                        $id_dtor_ponente = $oCargoDtor->getId_cargo();
                        $id_item = $FirmaRepository->getNewId_item();
                        $oFirma = new Firma();
                        $oFirma->setId_item($id_item);
                        $oFirma->setId_expediente($this->iid_expediente);
                        $oFirma->setId_tramite($id_tramite);
                        $oFirma->setId_cargo_creador($id_ponente);
                        $oFirma->setCargo_tipo($id_cargo);
                        $oFirma->setId_cargo($id_dtor_ponente);
                        $oFirma->setOrden_tramite($orden_tramite);
                        // Al inicializar, sólo pongo los votos.
                        $oFirma->setTipo(Firma::TIPO_VOTO);
                        $FirmaRepository->Guardar($oFirma);
                        break;
                    case Cargo::CARGO_OFICIALES: // para los oficiales de la oficina
                        $a_firmas_oficina = $this->getFirmas_oficina();
                        $orden_oficina = 0;
                        foreach ($a_firmas_oficina as $id_cargo_of) {
                            $orden_oficina++;
                            $id_item = $FirmaRepository->getNewId_item();
                            $oFirma = new Firma();
                            $oFirma->setId_item($id_item);
                            $oFirma->setId_expediente($this->iid_expediente);
                            $oFirma->setId_tramite($id_tramite);
                            $oFirma->setId_cargo_creador($id_ponente);
                            $oFirma->setCargo_tipo($id_cargo);
                            $oFirma->setId_cargo($id_cargo_of);
                            $oFirma->setOrden_tramite($orden_tramite);
                            $oFirma->setOrden_oficina($orden_oficina);
                            // Al inicializar, sólo pongo los votos.
                            $oFirma->setTipo(Firma::TIPO_VOTO);
                            $FirmaRepository->Guardar($oFirma);
                        }
                        break;
                    case Cargo::CARGO_VARIAS: // si es para varias oficinas
                        $a_resto_oficinas = $this->getResto_oficinas();
                        $orden_oficina = 0;
                        foreach ($a_resto_oficinas as $id_cargo_of) {
                            $orden_oficina++;
                            $id_item = $FirmaRepository->getNewId_item();
                            $oFirma = new Firma();
                            $oFirma->setId_item($id_item);
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
                            $FirmaRepository->Guardar($oFirma);
                        }
                        break;
                    case Cargo::CARGO_TODOS_DIR:  // si es para todos los dir menos vcd
                        $CargoGrupoRepository = new CargoGrupoRepository();
                        $cGrupos = $CargoGrupoRepository->getCargoGrupos(['id_cargo_ref' => Cargo::CARGO_TODOS_DIR]);
                        $aMiembros = $cGrupos[0]->getMiembros();
                        $orden_oficina = 0;
                        foreach ($aMiembros as $id_cargo) {
                            $oCargo = $CargoRepository->findById($id_cargo);
                            $orden_oficina++;
                            $id_cargo_of = $oCargo->getId_cargo();
                            $id_item = $FirmaRepository->getNewId_item();
                            $oFirma = new Firma();
                            $oFirma->setId_item($id_item);
                            $oFirma->setId_expediente($this->iid_expediente);
                            $oFirma->setId_tramite($id_tramite);
                            $oFirma->setId_cargo_creador($id_ponente);
                            $oFirma->setCargo_tipo(Cargo::CARGO_TODOS_DIR);
                            $oFirma->setId_cargo($id_cargo_of);
                            $oFirma->setOrden_tramite($orden_tramite);
                            $oFirma->setOrden_oficina($orden_oficina);
                            // Al inicializar, sólo pongo los votos.
                            $oFirma->setTipo(Firma::TIPO_VOTO);
                            $FirmaRepository->Guardar($oFirma);
                        }
                        break;
                    default:
                        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
                        exit ($err_switch);
                }
            } else {
                $id_item = $FirmaRepository->getNewId_item();
                $oFirma = new Firma();
                $oFirma->setId_item($id_item);
                $oFirma->setId_expediente($this->iid_expediente);
                $oFirma->setId_tramite($id_tramite);
                $oFirma->setId_cargo_creador($id_ponente);
                $oFirma->setCargo_tipo($id_cargo);
                $oFirma->setId_cargo($id_cargo);
                $oFirma->setOrden_tramite($orden_tramite);
                // Al inicializar, sólo pongo los votos.
                $oFirma->setTipo(Firma::TIPO_VOTO);
                $FirmaRepository->Guardar($oFirma);
            }
        }
    }

}