<?php

use core\ConfigGlobal;
use core\ViewTwig;
use davical\model\Davical;
use etiquetas\model\entity\GestorEtiqueta;
use pendientes\model\GestorPendiente;
use pendientes\model\Rrule;
use usuarios\model\entity\Cargo;
use usuarios\model\entity\GestorCargo;
use usuarios\model\entity\GestorOficina;
use usuarios\model\PermRegistro;
use web\DateTimeLocal;
use web\Desplegable;
use web\Lista;

// INICIO Cabecera global de URL de controlador *********************************
require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************
require_once("/usr/share/awl/inc/iCalendar.php");

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos por esta url  **********************************************
// FIN de  Cabecera global de URL de controlador ********************************

$oPosicion->recordar();

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$Q_periodo = (string)filter_input(INPUT_POST, 'periodo');
$Q_id_oficina = (string)filter_input(INPUT_POST, 'id_oficina');
$Q_despl_calendario = (string)filter_input(INPUT_POST, 'despl_calendario');
$Q_calendario = (string)filter_input(INPUT_POST, 'calendario');
$Q_encargado = (string)filter_input(INPUT_POST, 'encargado');

if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
    $aOpciones = [
        'registro' => _("registro"),
        'oficina' => _("oficina"),
    ];
    $op_calendario_default = empty($Q_despl_calendario) ? 'registro' : $Q_despl_calendario;
} else {
    // oficina = nombre del centro
    $sigla = $_SESSION['oConfig']->getSigla();
    $aOpciones = [
        'oficina' => $sigla,
    ];
    $op_calendario_default = empty($Q_despl_calendario) ? 'oficina' : $Q_despl_calendario;
}

if (!empty($Q_calendario)) {
    $op_calendario_default = $Q_calendario;
}

$oDesplCalendarios = new Desplegable();
$oDesplCalendarios->setNombre('despl_calendario');
$oDesplCalendarios->setOpciones($aOpciones);
$oDesplCalendarios->setOpcion_sel($op_calendario_default);
$oDesplCalendarios->setAction('fnjs_calendario()');

// Para dl, Hace falta el nombre de la oficina;
// para ctr, uso el nombre del esquema:
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_DL) {
    // solo secretaría puede ver/crear pendientes de otras oficinas
    $role_actual = ConfigGlobal::role_actual();
    if ($role_actual === 'secretaria') {
        $secretaria = 1; // NO TRUE, para eljavascript;
        $perm_periodico = 1; // NO TRUE, para eljavascript;
        $gesOficinas = new GestorOficina();
        $oDesplOficinas = $gesOficinas->getListaOficinas();
        $oDesplOficinas->setOpcion_sel($Q_id_oficina);
        $oDesplOficinas->setNombre('id_oficina');
        $id_oficina = '';
    } else {
        $oDesplOficinas = []; // para evitar errores
        $secretaria = 0; // NO FALSE, para eljavascript;
        $perm_periodico = 0; // NO TRUE, para eljavascript;
        $oCargo = new Cargo(ConfigGlobal::role_id_cargo());
        $id_oficina = $oCargo->getId_oficina();
    }

    if (!empty($Q_id_oficina)) {
        $id_oficina = $Q_id_oficina;
    }
} else {
    $oDesplOficinas = []; // para evitar errores
    $role_actual = ConfigGlobal::role_actual();
    $secretaria = 0; // NO TRUE, para eljavascript;
    $perm_periodico = 1; // NO TRUE, para eljavascript;
    $id_oficina = Cargo::OFICINA_ESQUEMA;
}
$gesCargos = new GestorCargo();
$a_usuarios_oficina = $gesCargos->getArrayUsuariosOficina($id_oficina);

$oDavical = new Davical($_SESSION['oConfig']->getAmbito());
$cal_oficina = $oDavical->getNombreRecurso($id_oficina);


// para el dialogo de búsquedas:
$oDesplEncargados = new Desplegable('encargado', $a_usuarios_oficina, $Q_encargado, TRUE);


$gesEtiquetas = new GestorEtiqueta();
$cEtiquetas = $gesEtiquetas->getMisEtiquetas();
$a_posibles_etiquetas = [];
foreach ($cEtiquetas as $oEtiqueta) {
    $id_etiqueta = $oEtiqueta->getId_etiqueta();
    $nom_etiqueta = $oEtiqueta->getNom_etiqueta();
    $a_posibles_etiquetas[$id_etiqueta] = $nom_etiqueta;
}

$sel_hoy = "";
$sel_semana = "";
$sel_mes = "";
$sel_trimestre = "";
$sel_any = "";
if (!empty($Q_periodo)) {
    $var_sel = "sel_" . $Q_periodo;
    $$var_sel = "selected";
    switch ($Q_periodo) {
        case "hoy":
            $limite = date("Ymd", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
            break;
        case "semana":
            $limite = date("Ymd", mktime(0, 0, 0, date("m"), date("d") + 7, date("Y")));
            break;
        case "mes":
            $limite = date("Ymd", mktime(0, 0, 0, date("m") + 1, date("d"), date("Y")));
            break;
        case "trimestre":
            $limite = date("Ymd", mktime(0, 0, 0, date("m") + 3, date("d"), date("Y")));
            break;
        case "any":
            $limite = date("Ymd", mktime(0, 0, 0, date("m"), date("d"), date("Y") + 1));
            break;
        default:
            $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
            exit ($err_switch);
    }
}

$a_botones[] = array('txt' => _('marcar como contestado'), 'click' => "fnjs_marcar(\"#seleccionados\")");
$a_botones[] = array('txt' => _('modificar'), 'click' => "fnjs_modificar(\"#seleccionados\")");
if ($secretaria) {
    $a_botones[] = array('txt' => _('eliminar'), 'click' => "fnjs_borrar(\"#seleccionados\")");
}

$a_cabeceras = array(ucfirst(_("protocolo")),
    ucfirst(_("etiquetas")),
    _("p"),
    array('name' => ucfirst(_("asunto")), 'formatter' => 'clickFormatter'),
    array('name' => ucfirst(_("fecha plazo")), 'class' => 'fecha'),
    ucfirst(_("oficinas")),
    ucfirst(_("encargado")),
    ucfirst(_("calendario")),
);

// para los ctr quitar columna oficina y calendario
if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
    unset($a_cabeceras[5]);
    unset($a_cabeceras[7]);
}

// Fetch all todos
$f_inicio = "19950101T000000Z";
if (empty($limite)) {
    $f_plazo = date("Ymd\T230000\Z");
} else {
    $f_plazo = $limite . "T000000Z";
}

//echo "get: ini$f_inicio="19950101T000000Z";: $f_inicio, fin: $f_plazo<br>";
$completed = false; //no veo los "COMPLETED"
$cancelled = false; //no veo los "CANCELLED"

$aWhere = [
    'f_inicio' => $f_inicio,
    'f_plazo' => $f_plazo,
    'completed' => $completed,
    'cancelled' => $cancelled,
];
$id_cargo_role = ConfigGlobal::role_id_cargo();
$gesPendientes = new GestorPendiente($cal_oficina, $op_calendario_default, $id_cargo_role);
$cPendientes = $gesPendientes->getPendientes($aWhere);

$a_valores = [];
$t = 0;
$oPermisoregistro = new PermRegistro();
foreach ($cPendientes as $oPendiente) {
    $calendario = $oPendiente->getCalendario();
    $id_encargado = $oPendiente->getEncargado();
    $perm_detalle = $oPermisoregistro->permiso_detalle($oPendiente, 'detalle');
    if (!empty($Q_encargado) && $id_encargado != $Q_encargado) {
        continue;
    }
    $encargado = !empty($id_encargado) ? $a_usuarios_oficina[$id_encargado] : '';
    $t++;
    $protocolo = $oPendiente->getProtocolo();
    $rrule = $oPendiente->getRrule();
    $asunto = $oPendiente->getAsuntoDetalle();
    if (!empty($asunto)) {
        $asunto = htmlspecialchars(stripslashes($asunto), ENT_QUOTES, 'utf-8');
    }
    $plazo = $oPendiente->getF_plazo()->getFromLocal();
    $plazo_iso = $oPendiente->getF_plazo()->format('Ymd'); // sólo números, para poder ordenar.

    $oficinas_txt = $oPendiente->getOficinasTxtcsv();


    $aEtiquetas = $oPendiente->getEtiquetasArray();
    $str_etiquetas = '';
    foreach ($aEtiquetas as $id_etiqueta) {
        $str_etiquetas .= empty($str_etiquetas) ? '' : ', ';
        $str_etiquetas .= empty($a_posibles_etiquetas[$id_etiqueta]) ? '' : $a_posibles_etiquetas[$id_etiqueta];
    }

    if (!empty($rrule)) {
        $periodico = "p";
        $uid = $oPendiente->getUid();
        // calcular las recurrencias que tocan.
        $dtstart = $oPendiente->getF_inicio()->getIso();
        $dtend = $oPendiente->getF_end()->getIso();
        $a_exdates = $oPendiente->getExdates();
        $f_recurrentes = Rrule::recurrencias($rrule, $dtstart, $dtend, $f_plazo);
        foreach ($f_recurrentes as $key => $f_iso) {
            $oF_recurrente = new DateTimeLocal($f_iso);
            $t++;
            // Quito las excepciones.
            if (is_array($a_exdates)) {
                foreach ($a_exdates as $icalprop) {
                    // si hay más de uno separados por coma
                    $a_fechas = preg_split('/,/', $icalprop->content);
                    foreach ($a_fechas as $f_ex) {
                        $oF_exception = new DateTimeLocal($f_ex);
                        if ($oF_recurrente == $oF_exception) {
                            continue(3);
                        }
                    }
                }
            }
            if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
                $a_valores[$t]['sel'] = "$uid#$cal_oficina#$f_iso";
            } else {
                $a_valores[$t]['sel'] = "x";
            }
            $a_valores[$t][1] = $protocolo;
            $a_valores[$t][2] = $str_etiquetas;
            $a_valores[$t][3] = $periodico;
            $a_valores[$t][4] = $asunto;
            $a_valores[$t][5] = $oF_recurrente->getFromLocal();
            $a_valores[$t][6] = $oficinas_txt;
            $a_valores[$t][7] = $encargado;
            $a_valores[$t][8] = $calendario;
            // para el orden
            $a_valores[$t]['order'] = $key; // (es la fecha iso sin separador)
        }
    } else {
        $periodico = "";
        $uid = $oPendiente->getUid();

        if ($perm_detalle >= PermRegistro::PERM_MODIFICAR) {
            $a_valores[$t]['sel'] = "$uid#$cal_oficina";
        } else {
            $a_valores[$t]['sel'] = "x";
        }
        $a_valores[$t][1] = $protocolo;
        $a_valores[$t][2] = $str_etiquetas;
        $a_valores[$t][3] = $periodico;
        $a_valores[$t][4] = $asunto;
        $a_valores[$t][5] = $plazo;
        $a_valores[$t][6] = $oficinas_txt;
        $a_valores[$t][7] = $encargado;
        $a_valores[$t][8] = $calendario;
        // para el orden
        if ($plazo !== 'x') {
            $a_valores[$t]['order'] = $plazo_iso;
        }
    }
}

if (!empty($a_valores)) {
    // ordenar por f_plazo:
    // Obtain a list of columns
    foreach ($a_valores as $key => $row) {
        $fechas[$key] = $row['order'];
        // para los ctr quitar columna oficina y calendario
        if ($_SESSION['oConfig']->getAmbito() === Cargo::AMBITO_CTR) {
            unset($a_valores[$key][6]);
            unset($a_valores[$key][8]);
        }
    }
    // Sort the data with fechas descending
    // Add $a_valores as the last parameter, to sort by the common key
    array_multisort($fechas, SORT_NUMERIC, SORT_ASC, $a_valores);
}


$oTabla = new Lista();
$oTabla->setId_tabla('pen_tabla');
$oTabla->setCabeceras($a_cabeceras);
$oTabla->setBotones($a_botones);
$oTabla->setDatos($a_valores);


$a_cosas = [
    'filtro' => $Q_filtro,
    'periodo' => $Q_periodo,
    'id_oficina' => $Q_id_oficina,
    'calendario' => $op_calendario_default,
];
$pagina_cancel = web\Hash::link('apps/pendientes/controller/pendiente_tabla.php?' . http_build_query($a_cosas));

$vista = ConfigGlobal::getVista();

$a_campos = [
    'secretaria' => $secretaria,
    'perm_periodico' => $perm_periodico,
    'sel_hoy' => $sel_hoy,
    'sel_semana' => $sel_semana,
    'sel_mes' => $sel_mes,
    'sel_trimestre' => $sel_trimestre,
    'sel_any' => $sel_any,
    'oDesplOficinas' => $oDesplOficinas,
    'id_oficina' => $id_oficina,
    'oDesplCalendarios' => $oDesplCalendarios,
    'oDesplEncargados' => $oDesplEncargados,
    'oExpedienteLista' => $oTabla,
    'filtro' => $Q_filtro,
    'periodo' => $Q_periodo,
    'op_calendario_default' => $op_calendario_default,
    'pagina_cancel' => $pagina_cancel,
    // tabs_show
    'vista' => $vista,
];

$oView = new ViewTwig('pendientes/controller');
$oView->renderizar('pendiente_tabla.html.twig', $a_campos);
