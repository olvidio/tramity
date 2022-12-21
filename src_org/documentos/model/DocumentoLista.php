<?php

namespace documentos\model;

use core\ConfigGlobal;
use core\ViewTwig;
use documentos\domain\entity\Documento;
use documentos\domain\repositories\DocumentoRepository;
use usuarios\domain\repositories\CargoRepository;
use web\Hash;


class DocumentoLista
{
    /**
     *
     * @var string
     */
    private string $filtro;
    /**
     *
     * @var integer
     */
    private int $id_doc;
    /**
     *
     * @var array
     */
    private array $aWhere;
    /**
     *
     * @var array
     */
    private array $aOperador;
    /**
     *
     * @var array
     */
    private array $aEtiquetas;
    /**
     *
     * @var string
     */
    private string $andOr;
    /**
     *
     * @var string
     */
    private string $que;


    public function mostrarTabla(): void
    {
        $filtro = $this->getFiltro();

        $oDocumentoGenerico = new Documento();
        $a_visibilidad = $oDocumentoGenerico->getArrayVisibilidad();

        $pagina_mod = ConfigGlobal::getWeb() . '/src/documentos/controller/documento_form.php';

        $CargoRepository = new CargoRepository();
        $a_cargos = $CargoRepository->getArrayCargos();

        $a_documentos = [];
        if (!empty($this->aWhere)) {
            $aTipoDoc = $oDocumentoGenerico->getArrayTipos();
            $DocumentoRepository = new DocumentoRepository();
            $cDocumentos = $DocumentoRepository->getDocumentos($this->aWhere, $this->aOperador);
            foreach ($cDocumentos as $oDocumento) {
                $row = [];
                // mirar permisos...
                $visibilidad = $oDocumento->getVisibilidad();
                $creador = $oDocumento->getCreador();

                if (ConfigGlobal::soy_dtor() === FALSE &&
                    $creador !== ConfigGlobal::role_id_cargo() &&
                    $visibilidad === Documento::V_PERSONAL) {
                    continue;
                }

                $visibilidad_txt = empty($a_visibilidad[$visibilidad]) ? '?' : $a_visibilidad[$visibilidad];

                $id_doc = $oDocumento->getId_doc();
                $row['id_doc'] = $id_doc;

                $a_cosas = ['id_doc' => $id_doc,
                    'filtro' => $filtro,
                    'etiquetas' => $this->aEtiquetas,
                    'andOr' => $this->andOr,
                    'que' => $this->que,
                ];
                $link_mod = Hash::link($pagina_mod . '?' . http_build_query($a_cosas));

                $tipo_doc = $oDocumento->getTipo_doc();
                $documento_txt = $oDocumento->getDocumento();

                if ($tipo_doc === Documento::DOC_ETHERPAD) {
                    $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_revisar_documento('$id_doc');\" >" . _("editar") . "</span>";
                } elseif ($tipo_doc === Documento::DOC_UPLOAD && !empty($documento_txt)) {
                    $url_download = Hash::link('src/documentos/controller/adjunto_download.php?' . http_build_query(['key' => $id_doc]));
                    $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"window.open('$url_download');\" >" . _("descargar") . "</span>";
                }
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >" . _("datos") . "</span>";

                $row['link_accion'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_eliminar_documento('$id_doc');\" >" . _("eliminar") . "</span>";

                $tipo_doc_txt = empty($aTipoDoc[$tipo_doc]) ? $tipo_doc : $aTipoDoc[$tipo_doc];
                $id_creador = $oDocumento->getCreador();
                $creador = empty($a_cargos[$id_creador]) ? '' : $a_cargos[$id_creador];
                if (empty($creador)) {
                    echo "OJO! Corregir el documento. No se sabe quien lo ha creado";
                    echo "<br>";
                }

                $row['creador'] = $creador;
                $row['nom'] = $oDocumento->getNom();
                $row['f_mod'] = $oDocumento->getF_upload()->getFromLocal();
                $row['visibilidad'] = $visibilidad_txt;
                $row['etiquetas'] = $oDocumento->getEtiquetasVisiblesTxt();
                $row['tipo'] = $tipo_doc_txt;
                // para ordenar. Si no añado id_doc, sobre escribe.
                $f_mod_iso = $oDocumento->getF_upload()->getIso() . $id_doc;
                $a_documentos[$f_mod_iso] = $row;
            }
        }
        // ordenar por f_upload:
        krsort($a_documentos, SORT_STRING);

        $url_update = 'src/documentos/controller/documento_update.php';
        $server = ConfigGlobal::getWeb(); //http://tramity.local

        $a_cosas = ['filtro' => $filtro,
            'etiquetas' => $this->aEtiquetas,
            'andOr' => $this->andOr,
            'que' => $this->que,
        ];
        $pagina_cancel = Hash::link('src/documentos/controller/documentos_lista.php?' . http_build_query($a_cosas));
        $pagina_nueva = Hash::link('src/documentos/controller/documento_form.php?' . http_build_query($a_cosas));
        $pagina_converter_entradas = Hash::link('src/documentos/controller/convertir_protocolos.php?' . http_build_query(['que' => 'entradas']));
        $pagina_converter_escritos = Hash::link('src/documentos/controller/convertir_protocolos.php?' . http_build_query(['que' => 'escritos']));
        $pagina_converter_expedientes = Hash::link('src/documentos/controller/convertir_protocolos.php?' . http_build_query(['que' => 'expedientes']));

        $vista = ConfigGlobal::getVista();

        $a_campos = [
            //'oHash' => $oHash,
            'a_documentos' => $a_documentos,
            'url_update' => $url_update,
            'pagina_nueva' => $pagina_nueva,
            'filtro' => $filtro,
            'server' => $server,
            'pagina_cancel' => $pagina_cancel,
            'pagina_converter_entradas' => $pagina_converter_entradas,
            'pagina_converter_escritos' => $pagina_converter_escritos,
            'pagina_converter_expedientes' => $pagina_converter_expedientes,
            // tabs_show
            'vista' => $vista,
        ];

        $oView = new ViewTwig('documentos/controller');
        $oView->renderizar('documentos_lista.html.twig', $a_campos);
    }

    /**
     * @return string
     */
    public function getFiltro(): string
    {
        return $this->filtro;
    }

    /**
     * @param string $filtro
     */
    public function setFiltro(string $filtro): void
    {
        $this->filtro = $filtro;
    }

    /**
     * @return int
     */
    public function getId_doc(): int
    {
        return $this->id_doc;
    }

    public function getNumero(): ?int
    {
        if (!empty($this->aWhere)) {
            $DocumentoRepository = new DocumentoRepository();
            $cDocumentos = $DocumentoRepository->getDocumentos($this->aWhere, $this->aOperador);
            $num = count($cDocumentos);
        } else {
            $num = null;
        }
        return $num;
    }

    /**
     * @param int $id_doc
     */
    public function setId_documento(int $id_doc): void
    {
        $this->id_doc = $id_doc;
    }

    /**
     * @return array
     */
    public function getAWhere(): array
    {
        return $this->aWhere;
    }

    /**
     * @param array $aWhere
     */
    public function setAWhere(array $aWhere): void
    {
        $this->aWhere = $aWhere;
    }

    /**
     * @return array
     */
    public function getAOperador(): array
    {
        return $this->aOperador;
    }

    /**
     * @param array $aOperador
     */
    public function setAOperador(array $aOperador): void
    {
        $this->aOperador = $aOperador;
    }

    /**
     * @return array
     */
    public function getEtiquetas(): array
    {
        return $this->aEtiquetas;
    }

    /**
     * @param array $aEtiquetas
     */
    public function setEtiquetas(array $aEtiquetas): void
    {
        $this->aEtiquetas = $aEtiquetas;
    }

    /**
     * @return string
     */
    public function getAndOr(): string
    {
        return $this->andOr;
    }

    /**
     * @param string $andOr
     */
    public function setAndOr(string $andOr): void
    {
        $this->andOr = $andOr;
    }

    /**
     * @return string
     */
    public function getQue(): string
    {
        return $this->que;
    }

    /**
     * @param string $que
     */
    public function setQue(string $que): void
    {
        $this->que = $que;
    }
}