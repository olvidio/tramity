<?php

namespace entradas\model;

use core\ConfigGlobal;
use entradas\model\entity\EntradaCompartida;
use entradas\model\entity\EntradaDB;
use entradas\model\entity\EntradaDocDB;
use entradas\model\entity\GestorEntradaAdjunto;
use entradas\model\entity\GestorEntradaBypass;
use etiquetas\model\entity\Etiqueta;
use etiquetas\model\entity\EtiquetaEntrada;
use etiquetas\model\entity\GestorEtiqueta;
use etiquetas\model\entity\GestorEtiquetaEntrada;
use JsonException;
use lugares\model\entity\GestorLugar;
use lugares\model\entity\Lugar;
use usuarios\model\entity\Cargo;
use usuarios\model\PermRegistro;
use usuarios\model\Visibilidad;
use web\DateTimeLocal;
use web\NullDateTimeLocal;
use web\Protocolo;
use web\ProtocoloArray;
use function core\is_true;


class Entrada extends EntradaDB
{

    /* CONST -------------------------------------------------------------- */

    // modo entrada
    public const MODO_MANUAL = 1;
    public const MODO_XML = 2;
    public const MODO_PROVISIONAL = 10;

    // estado
    /*
     - Ingresa (secretaría introduce los datos de la entrada)
     - Admitir (vcd los mira y da el ok)
     - Asignar (secretaría añade datos tipo: ponente... Puede que no se haya hecho el paso de ingresar)
     - Aceptar (scdl ok)
     - Oficinas (Las oficinas puede ver lo suyo)
     - Archivado (Ya no sale en las listas de la oficina)
     - Enviado cr (Cuando se han enviado los bypass)
     */
    public const ESTADO_INGRESADO = 1;
    public const ESTADO_ADMITIDO = 2;
    public const ESTADO_ASIGNADO = 3;
    public const ESTADO_ACEPTADO = 4;
    //const ESTADO_OFICINAS           = 5;
    public const ESTADO_ARCHIVADO = 6;
    public const ESTADO_ENVIADO_CR = 10;

    /* PROPIEDADES -------------------------------------------------------------- */

    protected string|DateTimeLocal|NullDateTimeLocal|null $df_doc = NULL;
    protected bool $convert = FALSE;
    protected ?int $itipo_doc = NULL;

    protected string $nombre_escrito;

    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * @throws JsonException
     */
    public function cabeceraIzquierda(): string
    {
        // sigla + ref
        $sigla = $_SESSION['oConfig']->getSigla();
        $destinos_txt = $sigla;
        // segunda región, para entrada cabecera izquierda es la región origen
        $json_prot_origen = $this->getJson_prot_origen();
        if (!empty((array)$json_prot_origen)) {
            $id_org = $json_prot_origen->id_lugar;
            $oLugar = new Lugar($id_org);
            $segundaRegion = $oLugar->getSigla();
            $oProtocolo = new Protocolo();
            $destinos_txt = $oProtocolo->addSegundaRegion($destinos_txt, $segundaRegion, TRUE);
        }


        $gesLugares = new GestorLugar();
        $cLugares = $gesLugares->getLugares(['sigla' => $sigla]);
        if (!empty($cLugares)) {
            $id_sigla = $cLugares[0]->getId_lugar();

            // referencias
            $a_json_prot_ref = $this->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($a_json_prot_ref, '', 'referencias');
            $oArrayProtRef->setRef(TRUE);
            $aRef = $oArrayProtRef->ArrayListaTxtBr($id_sigla);
            // segunda región, para entrada cabecera izquierda es: origen
            if (!empty($aRef) && !empty($segundaRegion)) {
                $aRef = $oArrayProtRef->addSegundaRegionEnArray($aRef, $segundaRegion);
            }
        } else {
            $aRef['dst_org'] = '??';
        }

        if (!empty($aRef['dst_org'])) {
            $destinos_txt .= ($destinos_txt !== '<br>')? '<br>' : '';
            $destinos_txt .= $aRef['dst_org'];
        }
        return $destinos_txt;
    }

    /**
     * @throws JsonException
     */
    public function cabeceraDerecha(): string
    {
        // origen + ref
        $json_prot_origen = $this->getJson_prot_origen();
        if (!empty((array)$json_prot_origen)) {
            $id_org = $json_prot_origen->id_lugar;

            // referencias
            $a_json_prot_ref = $this->getJson_prot_ref();
            $oArrayProtRef = new ProtocoloArray($a_json_prot_ref, '', 'referencias');
            $oArrayProtRef->setRef(TRUE);
            $aRef = $oArrayProtRef->ArrayListaTxtBr($id_org);
            // segunda región, para escrito cabecera izquierda es: mi_dl
            if (!empty($aRef)) {
                $segundaRegion = $_SESSION['oConfig']->getSigla();
                $aRef = $oArrayProtRef->addSegundaRegionEnArray($aRef, $segundaRegion);
            }

            $oProtOrigen = new Protocolo();
            $oProtOrigen->setLugar($json_prot_origen->id_lugar);
            $oProtOrigen->setProt_num($json_prot_origen->num);
            $oProtOrigen->setProt_any($json_prot_origen->any);
            $oProtOrigen->setMas($json_prot_origen->mas);

            $origen_txt = $oProtOrigen->ver_txt();
            // segunda región, para escrito cabecera izquierda es: mi_dl
            $segundaRegion = $_SESSION['oConfig']->getSigla();
            $origen_txt = $oProtOrigen->addSegundaRegion($origen_txt, $segundaRegion);
        } else {
            $origen_txt = '??';
        }

        if (!empty($aRef['dst_org'])) {
            $origen_txt .= '<br>';
            $origen_txt .= $aRef['dst_org'];
        }

        return $origen_txt;
    }

    /**
     * añadir el detalle en el asunto.
     * también el grupo de destinos (si es distrbución cr)
     * tener en cuenta los permisos...
     *
     * return string
     * @throws JsonException
     */
    public function getAsuntoDetalle(): string
    {
        //
        $txt_grupos = '';
        if ($this->getBypass()) {
            $lista_grupos = $this->cabeceraDistribucion_cr();
            $lista_grupos = empty($lista_grupos) ? _("No hay destinos") : $lista_grupos;
            $txt_grupos = "<span class=\"text-success\"> ($lista_grupos)</span>";
        }
        $asunto = $this->getAsunto();
        $detalle = $this->getDetalle();
        $asunto_detalle = empty($detalle) ? $asunto : $asunto . " [$detalle]";

        $asunto_detalle .= $txt_grupos;

        return $asunto_detalle;
    }

    public function cabeceraDistribucion_cr(): string
    {
        // a ver si ya está
        $gesEntradasBypass = new GestorEntradaBypass();
        $cEntradasBypass = $gesEntradasBypass->getEntradasBypass(['id_entrada' => $this->iid_entrada]);
        if (!empty($cEntradasBypass)) {
            // solo debería haber una:
            $oEntradaBypass = $cEntradasBypass[0];

            // poner los destinos
            $a_grupos = $oEntradaBypass->getId_grupos();
            $descripcion = $oEntradaBypass->getDescripcion();

            if (!empty($a_grupos)) {
                //(según los grupos seleccionados)
                $destinos_txt = $descripcion;
            } else {
                //(según individuales)
                $destinos_txt = '';
                if (!empty($descripcion)) {
                    $destinos_txt = $descripcion;
                } else {
                    $a_json_prot_dst = $oEntradaBypass->getJson_prot_destino();
                    foreach ($a_json_prot_dst as $json_prot_dst) {
                        $oLugar = new Lugar($json_prot_dst->id_lugar);
                        $destinos_txt .= empty($destinos_txt) ? '' : ', ';
                        $destinos_txt .= $oLugar->getNombre();
                    }
                }
            }
        } else {
            // No hay destinos definidos.
            $destinos_txt = _("No hay destinos");
        }

        return $destinos_txt;
    }

    /**
     * Recupera l'atribut sasunto de Entrada teniendo en cuenta los permisos
     *
     * @return string sasunto
     * @throws JsonException
     */
    public function getAsunto(): string
    {
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this, 'asunto');

        $asunto = _("reservado");
        if ($perm > 0) {
            $asunto = '';
            $anulado = $this->getAnulado();
            if (!empty($anulado)) {
                $asunto = _("ANULADO") . "($anulado) ";
            }
            $asunto .= $this->getAsuntoDB();
        }
        return $asunto;
    }

    /**
     * Recupera l'atribut sdetalle de Entrada teniendo en cuenta los permisos
     *
     * @return string|null sdetalle
     * @throws JsonException
     */
    public function getDetalle(): ?string
    {
        $oPermiso = new PermRegistro();
        $perm = $oPermiso->permiso_detalle($this, 'detalle');

        $detalle = _("reservado");
        if ($perm > 0) {
            $detalle = $this->getDetalleDB();
        }
        return $detalle;
    }

    /**
     * Hay que guardar dos objetos.
     * {@inheritDoc}
     * @see \entradas\model\entity\EntradaDB::DBGuardar()
     */
    public function DBCargar($que = NULL): bool
    {
        // El objeto padre:
        if (parent::DBCargar($que) === FALSE) {
            return FALSE;
        }
        // El tipo y fecha documento:
        if (!empty($this->iid_entrada)) {
            if ($this->getId_entrada_compartida() !== NULL) {
                $oEntradaCompartida = new EntradaCompartida($this->iid_entrada_compartida);
                $oFdoc = $oEntradaCompartida->getF_documento();
                $this->df_doc = $oFdoc;
            } else {
                $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
                $this->df_doc = $oEntradaDocDB->getF_doc();
                $this->itipo_doc = $oEntradaDocDB->getTipo_doc();
            }
        }
        return TRUE;
    }

    /**
     * Recupera l'atribut df_doc de Entrada
     * de EntradaDocDB, o si es una entrada compartida de 'EntradaCompartida'
     *
     * @return DateTimeLocal|NullDateTimeLocal df_doc
     * @throws JsonException
     */
    public function getF_documento(): DateTimeLocal|NullDateTimeLocal
    {
        if (!isset($this->df_doc) && !empty($this->iid_entrada)) {
            if ($this->getId_entrada_compartida() !== NULL) {
                $oEntradaCompartida = new EntradaCompartida($this->iid_entrada_compartida);
                $oFdoc = $oEntradaCompartida->getF_documento();
                $this->df_doc = $oFdoc;
            } else {
                $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
                $oFdoc = $oEntradaDocDB->getF_doc();
                $this->df_doc = $oFdoc;
            }
        }
        if (empty($this->df_doc)) {
            return new NullDateTimeLocal();
        }
        return $this->df_doc;
    }

    /**
     * Si df_doc es string, y convert=TRUE se convierte usando el formato web\DateTimeLocal->getFormat().
     * Si convert es FALSE, df_entrada debe ser un string en formato ISO (Y-m-d). Corresponde al pgstyle de la base de datos.
     *
     * @param string|null $df_doc ='' optional.
     * @param boolean $convert TRUE optional. Si es FALSE, df_ini debe ser un string en formato ISO (Y-m-d).
     */
    public function setF_documento(?string $df_doc = '', bool $convert = TRUE): void
    {
        $this->convert = $convert;
        $this->df_doc = $df_doc;
    }

    public function getTipo_documento(): ?int
    {
        if (!isset($this->itipo_doc) && !empty($this->iid_entrada)) {
            $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
            $this->itipo_doc = $oEntradaDocDB->getTipo_doc();
        }
        return $this->itipo_doc;
    }

    public function setTipo_documento($itipo_doc): void
    {
        $this->itipo_doc = $itipo_doc;
    }

    public function getArrayIdAdjuntos(): bool|array
    {
        return (new GestorEntradaAdjunto())->getArrayIdAdjuntos($this->iid_entrada);
    }

    /**
     * Devuelve el nombre del escrito (sigla_num_año): cr_15_05
     *
     * @param string $parentesi si existe se añade al nombre, entre parentesis
     * @return string
     * @throws JsonException
     */
    public function getNombreEscrito(string $parentesi = ''): string
    {
        $json_prot_local = $this->getJson_prot_origen();
        // nombre del archivo
        if (empty((array)$json_prot_local)) {
            // genero un id: fecha
            $f_hoy = date('Y-m-d');
            $hora = date('His');
            $this->nombre_escrito = $f_hoy . '_' . _("E12") . "($hora)";
        } else {
            $oProtOrigen = new Protocolo();
            $oProtOrigen->setLugar($json_prot_local->id_lugar);
            $oProtOrigen->setProt_num($json_prot_local->num);
            $oProtOrigen->setProt_any($json_prot_local->any);
            $oProtOrigen->setMas($json_prot_local->mas);
            $this->nombre_escrito = $this->renombrar($oProtOrigen->ver_txt());
        }
        if (!empty($parentesi)) {
            $this->nombre_escrito .= "($parentesi)";
        }
        return $this->nombre_escrito;
    }

    private function renombrar($string): string
    {
        //cambiar ' ' por '_':
        //cambiar '/' por '_':
        return str_replace(array(' ', '/'), '_', $string);
    }

    public function getEtiquetasVisiblesArray(?int $id_cargo = NULL): array
    {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $a_etiquetas = [];
        foreach ($cEtiquetas as $oEtiqueta) {
            $a_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        return $a_etiquetas;
    }

    public function getEtiquetasVisibles(?int $id_cargo = NULL): array
    {
        if ($id_cargo === NULL) {
            $id_cargo = ConfigGlobal::role_id_cargo();
        }
        $gesEtiquetas = new GestorEtiqueta();
        $cMisEtiquetas = $gesEtiquetas->getMisEtiquetas($id_cargo);
        $a_mis_etiquetas = [];
        foreach ($cMisEtiquetas as $oEtiqueta) {
            $a_mis_etiquetas[] = $oEtiqueta->getId_etiqueta();
        }
        $gesEtiquetasEntrada = new GestorEtiquetaEntrada();
        $aWhere = ['id_entrada' => $this->iid_entrada];
        $cEtiquetasEnt = $gesEtiquetasEntrada->getEtiquetasEntrada($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasEnt as $oEtiquetaEnt) {
            $id_etiqueta = $oEtiquetaEnt->getId_etiqueta();
            if (in_array($id_etiqueta, $a_mis_etiquetas, TRUE)) {
                $cEtiquetas[] = new Etiqueta($id_etiqueta);
            }
        }

        return $cEtiquetas;
    }

    public function getEtiquetasVisiblesTxt($id_cargo = ''): string
    {
        $cEtiquetas = $this->getEtiquetasVisibles($id_cargo);
        $str_etiquetas = '';
        foreach ($cEtiquetas as $oEtiqueta) {
            $str_etiquetas .= empty($str_etiquetas) ? '' : ', ';
            $str_etiquetas .= $oEtiqueta->getNom_etiqueta();
        }
        return $str_etiquetas;
    }

    public function getEtiquetas(): array
    {
        $gesEtiquetasEntrada = new GestorEtiquetaEntrada();
        $aWhere = ['id_entrada' => $this->iid_entrada];
        $cEtiquetasExp = $gesEtiquetasEntrada->getEtiquetasEntrada($aWhere);
        $cEtiquetas = [];
        foreach ($cEtiquetasExp as $oEtiquetaExp) {
            $id_etiqueta = $oEtiquetaExp->getId_etiqueta();
            $cEtiquetas[] = new Etiqueta($id_etiqueta);
        }

        return $cEtiquetas;
    }

    public function setEtiquetas($aEtiquetas): void
    {
        $this->delEtiquetas();
        $a_filter_etiquetas = array_filter($aEtiquetas); // Quita los elementos vacíos y nulos.
        foreach ($a_filter_etiquetas as $id_etiqueta) {
            $EtiquetaEntrada = new EtiquetaEntrada(['id_entrada' => $this->iid_entrada, 'id_etiqueta' => $id_etiqueta]);
            $EtiquetaEntrada->DBGuardar();
        }
    }

    public function delEtiquetas(): bool
    {
        $gesEtiquetasEntrada = new GestorEtiquetaEntrada();
        return $gesEtiquetasEntrada->deleteEtiquetasEntrada($this->iid_entrada) !== FALSE;
    }

    /**
     * Hay que guardar dos objetos.
     * {@inheritDoc}
     * @see \entradas\model\entity\EntradaDB::DBGuardar()
     */
    public function DBGuardar(): bool
    {
        // El tipo y fecha documento: (excepto si es nuevo)
        if (!empty($this->iid_entrada) && !empty($this->itipo_doc)) {
            $oEntradaDocDB = new EntradaDocDB($this->iid_entrada);
            $oEntradaDocDB->setF_doc($this->df_doc, TRUE);
            $oEntradaDocDB->setTipo_doc($this->itipo_doc);
            if ($oEntradaDocDB->DBGuardar() === FALSE) {
                $this->setErrorTxt($oEntradaDocDB->getErrorTxt());
                return FALSE;
            }

        }
        // El objeto padre:
        return parent::DBGuardar();
    }


}

