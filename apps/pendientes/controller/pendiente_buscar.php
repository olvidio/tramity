<?php

use busquedas\model\Buscar;
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\Entrada;
use etiquetas\model\entity\GestorEtiqueta;
use lugares\domain\repositories\LugarRepository;
use pendientes\model\BuscarPendiente;
use pendientes\model\Pendiente;
use usuarios\domain\entity\Cargo;
use usuarios\domain\PermRegistro;
use usuarios\domain\repositories\CargoRepository;
use usuarios\domain\repositories\OficinaRepository;
use web\DateTimeLocal;
use web\Desplegable;
use web\Lista;

// INICIO Cabecera global de URL de controlador *********************************


require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$explicacion_bypass = _("Está mal! No deberia asociar un pendiente a una entrada de distribución cr (bypass)");

$Q_que = (string)filter_input(INPUT_POST, 'que');
$Q_calendario = (string)filter_input(INPUT_POST, 'calendario');
$Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
$Q_status = (string)filter_input(INPUT_POST, 'status');
$Q_id_lugar = (integer)filter_input(INPUT_POST, 'id_lugar');
$Q_prot_num = (integer)filter_input(INPUT_POST, 'prot_num');
$Q_prot_any = (string)filter_input(INPUT_POST, 'prot_any'); // string para distinguir el 00 (del 2000) de empty.
$Q_prot_mas = (string)filter_input(INPUT_POST, 'prot_mas');
$Q_id_oficina = (string)filter_input(INPUT_POST, 'id_oficina');
$Q_f_min_enc = (string)filter_input(INPUT_POST, 'f_min');
$Q_f_min = urldecode($Q_f_min_enc);
$Q_f_max_enc = (string)filter_input(INPUT_POST, 'f_max');
$Q_f_max = urldecode($Q_f_max_enc);

$LugarRepository = new LugarRepository();
$a_lugares = $LugarRepository->getArrayBusquedas();

$oDesplLugar = new Desplegable();
$oDesplLugar->setNombre('id_lugar');
$oDesplLugar->setBlanco(TRUE);
$oDesplLugar->setOpciones($a_lugares);
$oDesplLugar->setOpcion_sel($Q_id_lugar);

$a_opciones_status = Pendiente::getArrayStatus();
// añadr la opción de 'caulquiera' al inicio
$all_traducido = _("cualquiera");
$Q_status = empty($Q_status) ? 'all' : $Q_status;
$a_opciones_status = array_merge(array("all" => $all_traducido), $a_opciones_status);
$oDesplStatus = new Desplegable();
$oDesplStatus->setNombre('status');
$oDesplStatus->setOpciones($a_opciones_status);
$oDesplStatus->setOpcion_sel($Q_status);

$OficinaRepository = new OficinaRepository();
$a_oficinas = $OficinaRepository->getArrayOficinas();
// solo secretaría puede ver/crear pendientes de otras oficinas
$role_actual = ConfigGlobal::role_actual();
if ($role_actual === 'secretaria') {
    $secretaria = 1; // NO TRUE, para eljavascript;
    $oDesplOficinas = new Desplegable();
    $oDesplOficinas->setNombre('id_oficina');
    $oDesplOficinas->setOpciones($a_oficinas);
    $oDesplOficinas->setOpcion_sel($Q_id_oficina);
    $oDesplOficinas->setBlanco(TRUE);
    $id_oficina = '';
} else {
    $oDesplOficinas = []; // para evitar errores
    $secretaria = 0; // NO FALSE, para eljavascript;
    $CargoRepository = new CargoRepository();
    $oCargo = $CargoRepository->findById(ConfigGlobal::role_id_cargo());
    $id_oficina = $oCargo->getId_oficina();
}

if ($Q_que === 'buscar') {
    $oBuscarPendiente = new BuscarPendiente();
    $oBuscarPendiente->setCalendario($Q_calendario);

    if ($Q_calendario === 'registro' && !empty($Q_id_lugar)) {
        // buscar en el registro:
        $Q_prot_any = empty($Q_prot_any) ? '' : core\any_2($Q_prot_any);

        $oBuscar = new Buscar();
        $oBuscar->setId_lugar($Q_id_lugar);
        $oBuscar->setProt_num($Q_prot_num);
        $oBuscar->setProt_any($Q_prot_any);

        $aCollection = $oBuscar->getCollection(7);
        $aIds = [];
        foreach ($aCollection as $key => $cCollection) {
            foreach ($cCollection as $oEntrada) { // También puede ser un Escrito, pero en principio son entradas.
                if ($key === 'entradas') {
                    $id_reg = $oEntrada->getId_entrada();
                }
                $aIds[] = $id_reg;
            }
        }
        if (!empty($aIds)) {
            $oBuscarPendiente->setId_reg($aIds);
        }

    }

    if (!empty($Q_id_oficina)) {
        $oBuscarPendiente->setId_oficina($Q_id_oficina);
    }
    if (!empty($Q_asunto)) {
        $oBuscarPendiente->setAsunto($Q_asunto);
    }
    if (!empty($Q_f_min)) {
        $oBuscarPendiente->setF_min($Q_f_min);
    }
    if (!empty($Q_f_max)) {
        $oBuscarPendiente->setF_max($Q_f_max);
    }
    if (!empty($Q_status)) {
        $oBuscarPendiente->setStatus($Q_status);
    }

    $gesEtiquetas = new GestorEtiqueta();
    $cEtiquetas = $gesEtiquetas->getMisEtiquetas();
    $a_posibles_etiquetas = [];
    foreach ($cEtiquetas as $oEtiqueta) {
        $id_etiqueta = $oEtiqueta->getId_etiqueta();
        $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
        $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
    }

    $cPendientes = $oBuscarPendiente->getPendientes();
    $a_valores = [];
    $t = 0;
    $oPermisoregistro = new PermRegistro();
    $a_status = Pendiente::getArrayStatus();
    foreach ($cPendientes as $oPendiente) {
        $t++;
        $perm_detalle = $oPermisoregistro->permiso_detalle($oPendiente, 'detalle');
        $uid = $oPendiente->getUid();
        // uid = REN33-20210211T172224@registro_oficina_agd
        $pos = strpos($uid, '_', 1);
        $parent_container = substr($uid, $pos + 1);

        $protocolo = $oPendiente->getProtocolo();
        $rrule = $oPendiente->getRrule();
        $asunto = $oPendiente->getAsuntoDetalle();
        if (!empty($asunto)) {
            $asunto = htmlspecialchars(stripslashes($asunto), ENT_QUOTES, 'utf-8');
        }

        /* Para mostrar errores algunos se han asociado al bypass */
        $matches = [];
        preg_match('/REN(\d*?)-.*/', $uid, $matches);
        $id_entrada = empty($matches[1]) ? '' : $matches[1];
        if (!empty($id_entrada)) {
            $oEntrada = new Entrada($id_entrada);
            $bypass = $oEntrada->getBypass();
            if ($bypass) {
                $asunto = "<span class=\"text-danger\" title=\"$explicacion_bypass\">BYPASS</span> " . $asunto;
            }
        }

        $plazo = $oPendiente->getF_plazo()->getFromLocal();
        $plazo_iso = $oPendiente->getF_plazo()->format('Ymd'); // sólo números, para poder ordenar.

        $status = $oPendiente->getStatus();
        $estado = empty($a_status[$status]) ? '?' : $a_status[$status];

        $of_ponente = $oPendiente->getPonente();
        $ponente = $a_oficinas[$of_ponente];

        $oficinas_txt = $oPendiente->getOficinasTxtcsv();

        $aEtiquetas = $oPendiente->getEtiquetasArray();
        $str_etiquetas = '';
        foreach ($aEtiquetas as $id_etiqueta) {
            $str_etiquetas .= empty($str_etiquetas) ? '' : ', ';
            $str_etiquetas .= empty($a_posibles_etiquetas[$id_etiqueta]) ? '' : $a_posibles_etiquetas[$id_etiqueta];
        }

        if (!empty($rrule)) {
            $periodico = "p";
        } else {
            $periodico = "";
        }

        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $a_valores[$t]['sel'] = "$uid#$parent_container";
        } else {
            $a_valores[$t]['sel'] = "x";
        }
        $a_valores[$t][1] = $protocolo;
        $a_valores[$t][2] = $str_etiquetas;
        $a_valores[$t][3] = $periodico;
        $a_valores[$t][4] = $asunto;
        $a_valores[$t][5] = $plazo;
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
            $a_valores[$t][6] = $ponente;
            $a_valores[$t][7] = $oficinas_txt;
        }
        $a_valores[$t][8] = $estado;
        // para el orden
        if ($plazo !== 'x') {
            $a_valores[$t]['order'] = $plazo_iso;
        }
    }
} else {
    $a_valores = [];
}

$a_botones[] = array('txt' => _('nuevo pendiente'), 'click' => "fnjs_nuevo_pendiente(\"#seleccionados\")");
$a_botones[] = array('txt' => _('marcar como terminado'), 'click' => "fnjs_marcar(\"#seleccionados\")");
$a_botones[] = array('txt' => _('modificar'), 'click' => "fnjs_modificar(\"#seleccionados\")");
$a_botones[] = array('txt' => _('eliminar'), 'click' => "fnjs_borrar(\"#seleccionados\")");

$a_cabeceras = array(ucfirst(_("protocolo")),
    ucfirst(_("etiquetas")),
    _("p"),
    array('name' => ucfirst(_("asunto")), 'formatter' => 'clickFormatter'),
    array('name' => ucfirst(_("fecha plazo")), 'class' => 'fecha'),
);
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
    $a_cabeceras[] = ucfirst(_("ponente"));
    $a_cabeceras[] = ucfirst(_("oficinas"));
}
$a_cabeceras[] = ucfirst(_("estado"));

$oTabla = new Lista();
$oTabla->setId_tabla('pen_tabla');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);


// datepicker
$oFecha = new DateTimeLocal();
$format = $oFecha::getFormat();

$aGoBack = [
    'que' => $Q_que,
    'calendario' => $Q_calendario,
    'asunto' => $Q_asunto,
    'status' => $Q_status,
    'id_lugar' => $Q_id_lugar,
    'prot_num' => $Q_prot_num,
    'prot_any' => $Q_prot_any,
    'prot_mas' => $Q_prot_mas,
    'id_oficina' => $Q_id_oficina,
    'f_min_enc' => $Q_f_min_enc,
    'f_max_enc' => $Q_f_max_enc,
];
$oPosicion->setParametros($aGoBack, 1);

$a_campos = [
    'oPosicion' => $oPosicion,
    'calendario' => $Q_calendario,
    'secretaria' => $secretaria,
    'oDesplLugar' => $oDesplLugar,
    'oDesplOficinas' => $oDesplOficinas,
    'oDesplStatus' => $oDesplStatus,
    'asunto' => $Q_asunto,
    'id_oficina' => $id_oficina,
    'oTabla' => $oTabla,
    'f_min' => $Q_f_min,
    'f_max' => $Q_f_max,
    'prot_num' => $Q_prot_num,
    'prot_any' => $Q_prot_any,
    'prot_mas' => $Q_prot_mas,
    // datepicker
    'format' => $format,
];

$oView = new ViewTwig('pendientes/controller');
$oView->renderizar('pendiente_buscar.html.twig', $a_campos);


