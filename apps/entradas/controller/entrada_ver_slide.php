<?php
use core\ViewTwig;
use entradas\model\Entrada;
use etherpad\model\Etherpad;
use web\Protocolo;
use entradas\model\GestorEntrada;
use web\DateTimeLocal;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************


// Para poder moverse de una entrada a otra:
$QSlide_mode =  (bool) \filter_input(INPUT_POST, 'slide_mode', FILTER_VALIDATE_BOOLEAN );

if ($QSlide_mode === TRUE) {
    $Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
    $Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
} else {
    // porque también se puede abrir como include
    $Qid_entrada = $id_entrada;
    $Qfiltro = $filtro;
}

if (!empty($Qid_entrada)) {
    // Paso los id siguiente y previo, porque si sólo paso los movimientos,
    // al haber guardado la entrada como admitida, ya no está en la lista y no puedo saber la siguiente.
    $aWhere = ['estado' => Entrada::ESTADO_INGRESADO,
                '_ordre' => 'f_entrada, id_entrada',
            ];
    $gesEntradas = new GestorEntrada();
    $cEntradas = $gesEntradas->getEntradas($aWhere);
    $a_lst_entradas = [];
    $i = 0;
    foreach ($cEntradas as $oEntrada) {
        $i++;
        $a_lst_entradas[$i] = $oEntrada->getId_entrada();
    }

    $key = array_search($Qid_entrada, $a_lst_entradas);
    // previo
    $k = $key-1;
    $id_prev = array_key_exists($k, $a_lst_entradas)? $a_lst_entradas[$k] : '';
    // siguiente
    $k = $key+1;
    $id_next = array_key_exists($k, $a_lst_entradas)? $a_lst_entradas[$k] : '';

    $sigla = $_SESSION['oConfig']->getSigla();

    $oProtOrigen = new Protocolo();
    $oProtOrigen->setEtiqueta('De');
    $oProtOrigen->setNombre('origen');
    $oProtOrigen->setBlanco(TRUE);
    $oProtOrigen->setTabIndex(10);

    $oProtRef = new Protocolo();
    $oProtRef->setEtiqueta('Ref');
    $oProtRef->setNombre('ref');
    $oProtRef->setBlanco(TRUE);

    $oEntrada = new Entrada($Qid_entrada);

    $pagina = core\ConfigGlobal::getWeb().'/apps/entradas/controller/entrada_ver_slide.php';
    $a_cosas = [ 'id_entrada' => $id_prev, 'slide_mode' => 'TRUE', 'filtro' => $Qfiltro];
    $pagina_prev = web\Hash::link($pagina.'?'.http_build_query($a_cosas));
    $a_cosas = [ 'id_entrada' => $id_next, 'slide_mode' => 'TRUE', 'filtro' => $Qfiltro];
    $pagina_next = web\Hash::link($pagina.'?'.http_build_query($a_cosas));

    $json_prot_origen = $oEntrada->getJson_prot_origen();
    if (count(get_object_vars($json_prot_origen)) == 0) {
        exit (_("No hay más"));
    }
    $oProtOrigen->setLugar($json_prot_origen->lugar);
    $oProtOrigen->setProt_num($json_prot_origen->num);
    $oProtOrigen->setProt_any($json_prot_origen->any);
    $oProtOrigen->setMas($json_prot_origen->mas);
        
    $json_prot_ref = $oEntrada->getJson_prot_ref();

    $oArrayProtRef = new web\ProtocoloArray($json_prot_ref,'','referencias');
    $oArrayProtRef ->setBlanco('t');
    $oArrayProtRef ->setRef(TRUE);
    $oArrayProtRef ->setAccionConjunto('fnjs_mas_referencias(event)');
    
    $asunto_e = $oEntrada->getAsunto_entrada();
    
    $a_adjuntos = $oEntrada->getArrayIdAdjuntos();
    
    // mirar si tienen escrito
    $f_escrito = $oEntrada->getF_documento()->getFromLocal();
    $f_entrada = $oEntrada->getF_entrada()->getFromLocal();
    
    $oEtherpad = new Etherpad();
    $oEtherpad->setId (Etherpad::ID_ENTRADA,$Qid_entrada);
    
    $escrito_html = $oEtherpad->generarHtml();
    $txt_alert = '';
} else {
    $oProtOrigen = [];
    $oArrayProtRef = [];
    $a_adjuntos = [];
    $asunto_e = '';
    $f_escrito = '';
    $f_entrada = '';
    $escrito_html = '';
    $txt_alert = _("No hay más registros");
    $sigla = '';
    $pagina_prev = '';
    $pagina_next = '';
}

if (!empty($f_entrada)) {
    $chk_leido = 'checked';
    $f_entrada_disabled = 'disabled';
} else {
    $chk_leido = '';
    $f_entrada_disabled = '';
    //$f_entrada = date(\DateTimeInterface::ISO8601); //hoy
    $oF_entrada = new DateTimeLocal();
    $f_entrada = $oF_entrada->getFromLocal();
}

$base_url = core\ConfigGlobal::getWeb();
$url_download = $base_url.'/apps/entradas/controller/download.php?plugin=1';
$url_download_pdf = $base_url.'/apps/entradas/controller/entrada_download.php';
// Si no pongo filtro ya va bien (si lo pongo va al slide...)
$pagina_cancel = web\Hash::link('apps/entradas/controller/entrada_lista.php?'.http_build_query(['filtro' => $Qfiltro]));

$a_campos = [
    //'oHash' => $oHash,
    'id_entrada' => $Qid_entrada,
    'sigla' => $sigla,
    'oProtOrigen' => $oProtOrigen,
    'oArrayProtRef' => $oArrayProtRef,
    'asunto_e' => $asunto_e,
    'f_escrito' => $f_escrito,
    'chk_leido' => $chk_leido,
    'f_entrada' => $f_entrada,
    'f_entrada_disabled' => $f_entrada_disabled,
    'a_adjuntos' => $a_adjuntos,
    
    'url_download' => $url_download,
    'pagina_prev' => $pagina_prev,
    'pagina_next' => $pagina_next,
    'escrito_html' => $escrito_html,
    'url_download_pdf' => $url_download_pdf,
    'pagina_cancel' => $pagina_cancel,
    'txt_alert' => $txt_alert,
];

$oView = new ViewTwig('entradas/controller');
echo $oView->renderizar('entrada_ver_slide.html.twig',$a_campos);