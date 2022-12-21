<?php

use core\ConfigGlobal;
use core\ViewTwig;
use entradas\domain\entity\EntradaRepository;
use lugares\domain\repositories\LugarRepository;
use usuarios\domain\PermRegistro;
use usuarios\domain\repositories\OficinaRepository;
use web\DateTimeLocal;
use web\Desplegable;
use web\Lista;
use web\Protocolo;
use function core\is_true;

// INICIO Cabecera global de URL de controlador *********************************

require_once("src_org/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("src_org/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Qid_expediente = (string)filter_input(INPUT_POST, 'id_expediente');
$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');

$Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
$Qperiodo = (string)filter_input(INPUT_POST, 'periodo');
$Qorigen_id_lugar = (integer)filter_input(INPUT_POST, 'origen_id_lugar');
$Qorigen_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
$Qorigen_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
$Qchk_anulados = (bool)filter_input(INPUT_POST, 'chk_anulados');

$Qoficina_buscar = ConfigGlobal::role_id_oficina();

$OficinaRepository = new OficinaRepository();
$a_posibles_oficinas = $OficinaRepository->getArrayOficinas();
$EntradaRepository = new EntradaRepository();
$aWhere = [];
$aOperador = [];
if (!empty($Qoficina_buscar)) {
    $aWhere['ponente'] = $Qoficina_buscar;
}
if (!empty($Q_asunto)) {
    // en este caso el operador es 'sin_acentos'
    $aWhere['asunto_detalle'] = $Q_asunto;
}

switch ($Qperiodo) {
    case "mes":
        $periodo = 'P1M';
        break;
    case "mes_6":
        $periodo = 'P6M';
        break;
    case "any_1":
        $periodo = 'P1Y';
        break;
    case "any_2":
        $periodo = 'P2Y';
        break;
    case "siempre":
        $periodo = '';
        break;
    default:
        $periodo = 'P1M';
}
if (!empty($periodo)) {
    $oFecha = new DateTimeLocal();
    $oFecha->sub(new DateInterval($periodo));
    $aWhere['f_entrada'] = $oFecha->getIso();
    $aOperador['f_entrada'] = '>';
}

// por defecto, buscar sólo 50.
if (empty($Q_asunto && empty($Qoficina_buscar))) {
    $aWhere['_limit'] = 50;
}
$aWhere['_ordre'] = 'f_entrada DESC';

if (!empty($Qorigen_id_lugar)) {
    $EntradaRepository = new EntradaRepository();
    $id_lugar = $Qorigen_id_lugar;
    if (!empty($Qorigen_prot_num) && !empty($Qorigen_prot_any)) {
        // No tengo en cuenta las otras condiciones de la búsqueda
        $aProt_origen = ['id_lugar' => $Qorigen_id_lugar,
            'num' => $Qorigen_prot_num,
            'any' => $Qorigen_prot_any,
        ];
        $cEntradas = $EntradaRepository->getEntradasByProtOrigenDB($aProt_origen);
    } else {
        $cEntradas = $EntradaRepository->getEntradasByLugarDB($id_lugar, $aWhere, $aOperador);
    }
} else {
    $cEntradas = $EntradaRepository->getEntradas($aWhere, $aOperador);
}

$a_cabeceras = ['', ['width' => 200, 'name' => _("protocolo")],
    ['width' => 100, 'name' => _("fecha")],
    ['width' => 600, 'name' => _("asunto")],
    ['width' => 50, 'name' => _("ponente")],
    ''];
$a_valores = [];
$a = 0;
$oProtOrigen = new Protocolo();
$oPermRegistro = new PermRegistro();
foreach ($cEntradas as $oEntrada) {
    $perm_ver_entrada = $oPermRegistro->permiso_detalle($oEntrada, 'escrito');
    if ($perm_ver_entrada < PermRegistro::PERM_VER) {
        continue;
    }
    $a++;
    $id_entrada = $oEntrada->getId_entrada();
    $fecha_txt = $oEntrada->getF_entrada()->getFromLocal();
    $id_of_ponente = $oEntrada->getPonente();
    $oProtOrigen->setJson($oEntrada->getJson_prot_origen());
    $proto_txt = $oProtOrigen->ver_txt();

    $ponente_txt = empty($a_posibles_oficinas[$id_of_ponente]) ? '?' : $a_posibles_oficinas[$id_of_ponente];

    $ver = "<span class=\"btn btn-link\" onclick=\"fnjs_ver_entrada('$id_entrada');\" >ver</span>";
    $add = "<span class=\"btn btn-link\" onclick=\"fnjs_adjuntar_entrada('$id_entrada','$Qid_expediente','$Q_filtro');\" >adjuntar</span>";

    $a_valores[$a][1] = $ver;
    $a_valores[$a][2] = $proto_txt;
    $a_valores[$a][3] = $fecha_txt;
    $a_valores[$a][4] = $oEntrada->getAsuntoDetalle();
    $a_valores[$a][5] = $ponente_txt;
    $a_valores[$a][6] = $add;
}


$oLista = new Lista();
$oLista->setCabeceras($a_cabeceras);
$oLista->setDatos($a_valores);

$OficinaRepository = new OficinaRepository();
$a_posibles_oficinas = $OficinaRepository->getArrayOficinas();
$oDesplOficinas = new web\Desplegable('oficina_buscar', $a_posibles_oficinas, $Qoficina_buscar, TRUE);

$LugarRepository = new LugarRepository();
$a_lugares = $LugarRepository->getArrayBusquedas($Qchk_anulados);

$oDesplOrigen = new Desplegable();
$oDesplOrigen->setNombre('origen_id_lugar');
$oDesplOrigen->setBlanco(TRUE);
$oDesplOrigen->setOpciones($a_lugares);
$oDesplOrigen->setAction("fnjs_sel_periodo('#origen_id_lugar')");
$oDesplOrigen->setOpcion_sel($Qorigen_id_lugar);

if (is_true($Qchk_anulados)) {
    $chk_ctr_anulados = 'checked';
} else {
    $chk_ctr_anulados = '';
}

$a_cosas = ['id_expediente' => $Qid_expediente,
    'filtro' => $Q_filtro,
];
$pagina_cancel = web\Hash::link('src/expedientes/controller/expediente_form.php?' . http_build_query($a_cosas));
$pagina_buscar = web\Hash::link('src/entradas/controller/entrada_ajax.php?' . http_build_query([$a_cosas]));
$url_escrito = 'src/escritos/controller/escrito_form.php';

// para que no ponga '0'
$Qorigen_prot_num = empty($Qorigen_prot_num) ? '' : $Qorigen_prot_num;
$a_campos = [
    'id_expediente' => $Qid_expediente,
    'oDesplOrigen' => $oDesplOrigen,
    'oDesplOficinas' => $oDesplOficinas,
    'oLista' => $oLista,
    'asunto' => $Q_asunto,
    'prot_num' => $Qorigen_prot_num,
    'prot_any' => $Qorigen_prot_any,
    'chk_ctr_anulados' => $chk_ctr_anulados,

    'pagina_cancel' => $pagina_cancel,
    'pagina_buscar' => $pagina_buscar,
    'url_escrito' => $url_escrito,
];
$oView = new ViewTwig('entradas/controller');
$oView->renderizar('buscar_form.html.twig', $a_campos);
