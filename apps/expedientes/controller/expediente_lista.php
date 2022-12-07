<?php

use expedientes\model\DialogoBusquedaArchivados;
use expedientes\model\DialogoBusquedaBorrador;
use expedientes\model\ExpedienteAcabadosEncargadosLista;
use expedientes\model\ExpedienteAcabadosLista;
use expedientes\model\ExpedienteArchivadosLista;
use expedientes\model\ExpedienteBorradorLista;
use expedientes\model\ExpedienteCirculandoLista;
use expedientes\model\ExpedienteCopiasLista;
use expedientes\model\ExpedienteDistribuirLista;
use expedientes\model\ExpedienteParaFirmarLista;
use expedientes\model\ExpedientepermanentesClLista;
use expedientes\model\ExpedienteReunionFijarLista;
use expedientes\model\ExpedienteReunionLista;
use expedientes\model\ExpedienteReunionSeguimientoLista;

// INICIO Cabecera global de URL de controlador *********************************

require_once("apps/core/global_header.inc");
// Archivos requeridos por esta url **********************************************

// Crea los objetos de uso global **********************************************
require_once("apps/core/global_object.inc");
// Crea los objetos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************

$Q_filtro = (string)filter_input(INPUT_POST, 'filtro');
$msg = '';
switch ($Q_filtro) {
    case 'borrador_propio':
    case 'borrador_oficina':
        $oExpedienteLista = new ExpedienteBorradorLista($Q_filtro);
        // añadir dialogo de búsquedas
        $Q_prioridad_sel = (integer)filter_input(INPUT_POST, 'prioridad_sel');
        $Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        $oDialogoBusqueda = new DialogoBusquedaBorrador($Q_prioridad_sel, $Q_andOr, $Q_a_etiquetas, $Q_filtro);
        $aCondicion = $oDialogoBusqueda->generarCondicion();
        if ($aCondicion['success'] === FALSE) {
            $msg = $aCondicion['message'];
        } else {
            $aWhereADD = $aCondicion['aWhereADD'];
            $aOperadorADD = $aCondicion['aOperadorADD'];
            $oExpedienteLista->setAWhereADD($aWhereADD);
            $oExpedienteLista->setAOperadorADD($aOperadorADD);
        }
        $oExpedienteLista->setPrioridad_sel($Q_prioridad_sel);
        $oDialogoBusqueda->mostrarDialogo();
        break;
    case 'firmar':
        $oExpedienteLista = new ExpedienteParaFirmarLista($Q_filtro);
        break;
    case 'circulando':
        $oExpedienteLista = new ExpedienteCirculandoLista($Q_filtro);
        break;
    case 'permanentes_cl':
        $oExpedienteLista = new ExpedientepermanentesClLista($Q_filtro);
        break;
    case 'fijar_reunion':
        $oExpedienteLista = new ExpedienteReunionFijarLista($Q_filtro);
        break;
    case 'reunion':
        $oExpedienteLista = new ExpedienteReunionLista($Q_filtro);
        break;
    case 'seg_reunion':
        $oExpedienteLista = new ExpedienteReunionSeguimientoLista($Q_filtro);
        break;
    case 'distribuir':
        $oExpedienteLista = new ExpedienteDistribuirLista($Q_filtro);
        break;
    case 'acabados':
        $oExpedienteLista = new ExpedienteAcabadosLista($Q_filtro);
        break;
    case 'acabados_encargados':
        $oExpedienteLista = new ExpedienteAcabadosEncargadosLista($Q_filtro);
        break;
    case 'archivados':
        $oExpedienteLista = new ExpedienteArchivadosLista($Q_filtro);

        $Q_asunto = (string)filter_input(INPUT_POST, 'asunto');
        $Q_andOr = (string)filter_input(INPUT_POST, 'andOr');
        $Q_periodo = (string)filter_input(INPUT_POST, 'periodo');
        $Q_a_etiquetas = (array)filter_input(INPUT_POST, 'etiquetas', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $a_etiquetas_filtered = array_filter($Q_a_etiquetas);

        // para evitar que salgan todos, por defecto poner periodo un mes.
        $Q_periodo = empty($Q_periodo) ? 'mes' : $Q_periodo;
        $oDialogoBusqueda = new DialogoBusquedaArchivados($Q_asunto, $Q_andOr, $Q_a_etiquetas, $Q_filtro, $Q_periodo);
        $aCondicion = $oDialogoBusqueda->generarCondicion();
        $aWhereADD = $aCondicion['aWhereADD'];
        $aOperadorADD = $aCondicion['aOperadorADD'];

        $oExpedienteLista->setAWhereADD($aWhereADD);
        $oExpedienteLista->setAOperadorADD($aOperadorADD);

        $oDialogoBusqueda->mostrarDialogo();

        $a_condiciones = [
            'asunto' => $Q_asunto,
            'andOr' => $Q_andOr,
            'etiquetas' => $Q_a_etiquetas,
            'periodo' => $Q_periodo,
        ];
        $oExpedienteLista->setCondicionesBusqueda($a_condiciones);
        break;
    case 'copias':
        $oExpedienteLista = new ExpedienteCopiasLista($Q_filtro);
        break;
    default:
        $err_switch = sprintf(_("opción no definida en switch en %s, linea %s"), __FILE__, __LINE__);
        exit ($err_switch);
}

if (empty($msg)) {
    $oExpedienteLista->mostrarTabla();
} else {
    echo $msg;
}