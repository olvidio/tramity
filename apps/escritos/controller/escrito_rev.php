<?php

use core\ConfigGlobal;
use core\ViewTwig;
use escritos\model\Escrito;
use etherpad\model\Etherpad;
use lugares\model\entity\GestorLugar;
use usuarios\model\Categoria;
use usuarios\model\entity\GestorCargo;
use usuarios\model\Visibilidad;
use web\Desplegable;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (integer)\filter_input(INPUT_POST, 'id_expediente');
$Qid_escrito = (integer)\filter_input(INPUT_POST, 'id_escrito');
$Qaccion = (integer)\filter_input(INPUT_POST, 'accion');
$Qfiltro = (string)\filter_input(INPUT_POST, 'filtro');

// ----------- Sigla local -------------------
$sigla_local = $_SESSION['oConfig']->getSigla();
$id_lugar_local = '';
$gesLugares = new GestorLugar();
$a_posibles_lugares = $gesLugares->getArrayLugares();

//$txt_option_ref = '';
foreach ($a_posibles_lugares as $id_lugar => $sigla) {
    //$txt_option_ref .= "<option value=$id_lugar >$sigla</option>";
    if ($sigla == $sigla_local) {
        $id_lugar_local = $id_lugar;
    }
}

$oProtLocal = new Protocolo();
$oProtLocal->setEtiqueta('De');
$oProtLocal->setNombre('origen');
$oProtLocal->setOpciones($a_posibles_lugares);
$oProtLocal->setBlanco(TRUE);
$oProtLocal->setTabIndex(10);

$oProtRef = new Protocolo();
$oProtRef->setEtiqueta('Ref');
$oProtRef->setNombre('ref');
$oProtRef->setOpciones($a_posibles_lugares);
$oProtRef->setBlanco(TRUE);

$txt_option_cargos = '';
$gesCargos = new GestorCargo();
$a_posibles_cargos = $gesCargos->getArrayCargos();
foreach ($a_posibles_cargos as $id_cargo => $cargo) {
    $txt_option_cargos .= "<option value=$id_cargo >$cargo</option>";
}

$oEscrito = new Escrito($Qid_escrito);
// categoria
$oCategoria = new Categoria();
$aOpciones = $oCategoria->getArrayCategoria();
$oDesplCategoria = new Desplegable();
$oDesplCategoria->setNombre('categoria');
$oDesplCategoria->setOpciones($aOpciones);
$oDesplCategoria->setTabIndex(80);

// visibilidad
$oVisibilidad = new Visibilidad();
$aOpciones = $oVisibilidad->getArrayVisibilidad();

if (!empty($Qid_escrito)) {

    $f_aprobacion = $oEscrito->getF_aprobacion();
    if (!empty($f_aprobacion)) {
        $tipo_documento = '';
        // si es un escrito, hay que generar el protocolo local:
        if ($tipo_documento == Escrito::ACCION_ESCRITO) {
            $json_prot_origen = $oEscrito->getJson_prot_local();
            if (!empty(get_object_vars($json_prot_origen))) {
                $oProtLocal->setLugar($json_prot_origen->id_lugar);
                $oProtLocal->setProt_num($json_prot_origen->num);
                $oProtLocal->setProt_any($json_prot_origen->any);
                if (property_exists($json_prot_origen, 'mas')) {
                    $oProtLocal->setMas($json_prot_origen->mas);
                }
            } else {
                $any = date('y');
                $oProtLocal->setLugar($id_lugar_local);
                $oProtLocal->setProt_num('345');
                $oProtLocal->setProt_any($any);
                $oProtLocal->setMas('res');
            }
        }
    }

    $cabeceraIzqd = $oEscrito->cabeceraIzquierda();
    $cabeceraDcha = $oEscrito->cabeceraDerecha();

    $entradilla = $oEscrito->getEntradilla();
    $asunto_detalle = $oEscrito->getAsuntoDetalle();

    // Ponente
    $id_ponente = $oEscrito->getCreador();

    $a_resto_of = $oEscrito->getResto_oficinas();
    $oArrayDesplFirmas = new web\DesplegableArray($a_resto_of, $a_posibles_cargos, 'oficinas');
    $oArrayDesplFirmas->setBlanco('t');
    $oArrayDesplFirmas->setAccionConjunto('fnjs_mas_oficinas()');

    $categoria = $oEscrito->getCategoria();
    $oDesplCategoria->setOpcion_sel($categoria);

    $a_adjuntos = [];
    $preview = [];
    $config = [];
    foreach ($a_adjuntos as $id_item => $nom) {
        $preview[] = "'$nom'";
        $config[] = [
            'key' => $id_item,
            'caption' => $nom,
            'url' => 'apps/entradas/controller/delete.php', // server api to delete the file based on key
        ];
    }
    $initialPreview = implode(',', $preview);
    $json_config = json_encode($config);

    // mirar si tienen escrito
    $f_escrito = $oEscrito->getF_escrito()->getFromLocal();

    $titulo = _("revisar");
    switch ($Qaccion) {
        case Escrito::ACCION_ESCRITO:
            $titulo = _("revisar escrito");
            break;
        case Escrito::ACCION_PROPUESTA:
            $titulo = _("revisar propuesta");
            break;
        case Escrito::ACCION_PLANTILLA:
            $titulo = _("revisar plantilla");
            break;
        default:
            $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_switch);
    }


    $oEtherpad = new Etherpad();
    $oEtherpad->setId(Etherpad::ID_ESCRITO, $Qid_escrito);
    $padID = $oEtherpad->getPadId();
    $url = $oEtherpad->getUrl();

    $iframe = "<iframe src='$url/p/$padID?showChat=false&showLineNumbers=false' width=1300 height=500></iframe>";

} else {
    $entradilla = '';
    $asunto_detalle = '';
    $f_escrito = '';
    $initialPreview = '';
    $json_config = '{}';
    $titulo = _("nuevo");
    switch ($Qaccion) {
        case Escrito::ACCION_ESCRITO:
            $titulo = _("nuevo escrito");
            break;
        case Escrito::ACCION_PROPUESTA:
            $titulo = _("nueva propuesta");
            break;
        case Escrito::ACCION_PLANTILLA:
            $titulo = _("nueva plantilla");
            break;
        default:
            $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_switch);
    }

    $cabeceraIzqd = '';
    $cabeceraDcha = '';

    $oArrayDesplFirmas = new web\DesplegableArray('', $a_posibles_cargos, 'oficinas');
    $oArrayDesplFirmas->setBlanco('t');
    $oArrayDesplFirmas->setAccionConjunto('fnjs_mas_oficinas()');

    $id_ponente = ConfigGlobal::role_id_cargo();
    $iframe = '';
}


$a_cosas = ['id_expediente' => $Qid_expediente,
    'filtro' => $Qfiltro
];
$pagina_cancel = web\Hash::link('apps/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));

$a_campos = [
    'titulo' => $titulo,
    'id_expediente' => $Qid_expediente,
    'id_escrito' => $Qid_escrito,
    'accion' => $Qaccion,
    'id_ponente' => $id_ponente,
    //'oHash' => $oHash,
    'cabeceraIzqd' => $cabeceraIzqd,
    'cabeceraDcha' => $cabeceraDcha,

    'f_escrito' => $f_escrito,
    'entradilla' => $entradilla,
    'asunto_detalle' => $asunto_detalle,
    'iframe' => $iframe,
    //'a_adjuntos' => $a_adjuntos,
    'initialPreview' => $initialPreview,
    'json_config' => $json_config,
    'txt_option_cargos' => $txt_option_cargos,
    //'txt_option_ref' => $txt_option_ref,

    'pagina_cancel' => $pagina_cancel,
];

$oView = new ViewTwig('escritos/controller');
echo $oView->renderizar('escrito_rev.html.twig', $a_campos);