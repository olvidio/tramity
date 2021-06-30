<?php
namespace documentos\model;

use core\ConfigGlobal;
use core\ViewTwig;
use usuarios\model\PermRegistro;
use web\Hash;
use web\ProtocoloArray;


class DocumentoLista {
    /**
     * 
     * @var string
     */
    private $filtro;
    /**
     * 
     * @var integer
     */
    private $id_doc;
    /**
     * 
     * @var array
     */
    private $aWhere;
    /**
     * 
     * @var array
     */
    private $aOperador;
    /**
     * 
     * @var array
     */
    private $aEtiquetas;
    /**
     * 
     * @var string
     */
    private $andOr;
    

    public function mostrarTabla() {
        $pagina_nueva = '';
        $filtro = $this->getFiltro();
        
        $oDocumento = new Documento();
        $a_visibilidad = $oDocumento->getArrayVisibilidad();
        
        $pagina_accion = ConfigGlobal::getWeb().'/apps/documentos/controller/expediente_accion.php';
        $pagina_mod = ConfigGlobal::getWeb().'/apps/documentos/controller/documento_form.php';
        
        $a_documentos = [];
        $id_doc = '';
        if (!empty($this->aWhere)) {
            $oDocumento = new Documento();
            $aTipoDoc = $oDocumento->getArrayTipos();
            $gesDocumentos = new GestorDocumento();
            $cDocumentos = $gesDocumentos->getDocumentos($this->aWhere,$this->aOperador);
            foreach ($cDocumentos as $oDocumento) {
                $row = [];
                // mirar permisos...
                $visibilidad = $oDocumento->getVisibilidad();
                $visibilidad_txt = empty($a_visibilidad[$visibilidad])? '?' : $a_visibilidad[$visibilidad];
                
                $id_doc = $oDocumento->getId_doc();
                $row['id_doc'] = $id_doc;
                
                $a_cosas = [ 'id_doc' => $id_doc,
                              'filtro' => $filtro,
                              'etiquetas' => $this->aEtiquetas,
                              'andOr' => $this->andOr,
                ];
                
                $link_accion = Hash::link($pagina_accion.'?'.http_build_query($a_cosas));
                $link_mod = Hash::link($pagina_mod.'?'.http_build_query($a_cosas));
                
                $tipo_doc = $oDocumento->getTipo_doc();
                $contenido_encoded = $oDocumento->getDocumentoTxt();
                $contenido = base64_decode($contenido_encoded);

                if ( $tipo_doc == Documento::DOC_ETHERPAD ){
                    $url_download = '';
                    $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_revisar_documento('$id_doc');\" >"._("editar")."</span>";
                } elseif ($tipo_doc == Documento::DOC_UPLOAD && !empty($contenido)) {
                    $url_download = Hash::link('apps/documentos/controller/adjunto_download.php?'.http_build_query(['key' => $id_doc]));
                    $row['link_ver'] = "<span role=\"button\" class=\"btn-link\" onclick=\"window.open('$url_download');\" >"._("descargar")."</span>";
                }
                $row['link_mod'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_mod');\" >"._("datos")."</span>";
                
                $row['link_accion'] = "<span role=\"button\" class=\"btn-link\" onclick=\"fnjs_update_div('#main','$link_accion');\" >"._("acción")."</span>";
                
                $tipo_doc = $oDocumento->getTipo_doc(); 
                $tipo_doc_txt = empty($aTipoDoc[$tipo_doc])? $tipo_doc : $aTipoDoc[$tipo_doc]; 
                $id_creador =  $oDocumento->getCreador();
                
                $row['creador'] = $id_creador;
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
        krsort($a_documentos,SORT_STRING);
            
        $url_update = 'apps/documentos/controller/documento_update.php';
        $server = ConfigGlobal::getWeb(); //http://tramity.local
        
        $a_cosas = [ 'filtro' => $filtro,
                      'etiquetas' => $this->aEtiquetas,
                      'andOr' => $this->andOr,
        ];
        $pagina_cancel = Hash::link('apps/documentos/controller/documentos_lista.php?'.http_build_query($a_cosas));
        $pagina_nueva = Hash::link('apps/documentos/controller/documento_form.php?'.http_build_query($a_cosas));
        
        $a_campos = [
            //'oHash' => $oHash,
            'a_documentos' => $a_documentos,
            'url_update' => $url_update,
            'pagina_nueva' => $pagina_nueva,
            'filtro' => $filtro,
            'server' => $server,
            'pagina_cancel' => $pagina_cancel,
        ];
        
        $oView = new ViewTwig('documentos/controller');
        return $oView->renderizar('documentos_lista.html.twig',$a_campos);
    }
    
    public function getNumero() {
        $this->setCondicion();
        if (!empty($this->aWhere)) {
            $gesDocumentos = new GestorDocumento();
            $cDocumentos = $gesDocumentos->getDocumentos($this->aWhere,$this->aOperador);
            $num = count($cDocumentos);
        } else {
            $num = '';            
        }
        return $num;
    }

    /**
     * @return string
     */
    public function getFiltro()
    {
        return $this->filtro;
    }

    /**
     * @return number
     */
    public function getId_doc()
    {
        return $this->id_doc;
    }

    /**
     * @param string $filtro
     */
    public function setFiltro($filtro)
    {
        $this->filtro = $filtro;
    }

    /**
     * @param number $id_doc
     */
    public function setId_documento($id_doc)
    {
        $this->id_doc = $id_doc;
    }
    /**
     * @return array
     */
    public function getAWhere()
    {
        return $this->aWhere;
    }

    /**
     * @param array $aWhere
     */
    public function setAWhere($aWhere)
    {
        $this->aWhere = $aWhere;
    }

    /**
     * @return array
     */
    public function getAOperador()
    {
        return $this->aOperador;
    }

    /**
     * @param array $aOperador
     */
    public function setAOperador($aOperador)
    {
        $this->aOperador = $aOperador;
    }
    /**
     * @return array
     */
    public function getEtiquetas()
    {
        return $this->aEtiquetas;
    }

    /**
     * @param array $aEtiquetas
     */
    public function setEtiquetas($aEtiquetas)
    {
        $this->aEtiquetas = $aEtiquetas;
    }

    /**
     * @return string
     */
    public function getAndOr()
    {
        return $this->andOr;
    }

    /**
     * @param string $andOr
     */
    public function setAndOr($andOr)
    {
        $this->andOr = $andOr;
    }



}