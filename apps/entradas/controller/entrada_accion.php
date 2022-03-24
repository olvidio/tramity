<?php
use core\ConfigGlobal;
use core\ViewTwig;
use entradas\model\Entrada;
use usuarios\model\entity\GestorCargo;
use web\Desplegable;
use web\Hash;
use web\Protocolo;

// INICIO Cabecera global de URL de controlador *********************************

require_once ("apps/core/global_header.inc");
// Arxivos requeridos por esta url **********************************************

// Crea los objectos de uso global **********************************************
require_once ("apps/core/global_object.inc");
// Crea los objectos para esta url  **********************************************

// FIN de  Cabecera global de URL de controlador ********************************



$Qfiltro = (string) \filter_input(INPUT_POST, 'filtro');
$Qid_entrada = (integer) \filter_input(INPUT_POST, 'id_entrada');
$Qoficina = (string) \filter_input(INPUT_POST, 'oficina');

$oEntrada = new Entrada($Qid_entrada);
$asunto = $oEntrada->getAsunto();

$oProtOrigen = new Protocolo();
$oProtOrigen->setJson($oEntrada->getJson_prot_origen());
$protocolo = $oProtOrigen->ver_txt();

$url_cancel = 'apps/entradas/controller/entrada_lista.php';
$pagina_cancel = Hash::link($url_cancel.'?'.http_build_query(['filtro' => $Qfiltro, 'oficina'  => $Qoficina]));

$oDesplCargosOficinaEncargado = [];
$a_botones = [];
if ($Qoficina == 'propia') { // encargar
    $a_botones[0] = ['accion' => 'en_add_encargado',
                'txt'    => _("Encargar a"),
                'tipo'    => 'modal',
            ];
    $a_botones[1] = ['accion' => 'en_visto',
                'txt'    => _("marcar como visto"),
            ];
}
if ($Qoficina == 'resto') { // marcar como visto
    $a_botones[0] = ['accion' => 'en_visto',
                'txt'    => _("marcar como visto"),
            ];
}

$gesCargos = new GestorCargo();
$a_posibles_cargos_oficina = $gesCargos->getArrayUsuariosOficina(ConfigGlobal::role_id_oficina());
$oDesplCargosOficinaEncargado = new Desplegable('id_cargo_encargado',$a_posibles_cargos_oficina,'','');
        
if (empty($a_botones)) {
    $a_botones[] = ['accion' => '',
                    'txt'    => _("no tiene permiso"),
                ];
}

$a_campos = [
    'id_entrada' => $Qid_entrada,
    'filtro' => $Qfiltro,
    //'oHash' => $oHash,
    'protocolo' => $protocolo,
    'asunto' => $asunto,
    'a_botones' => $a_botones,
    'pagina_cancel' => $pagina_cancel,
    'oDesplCargosOficinaEncargado' => $oDesplCargosOficinaEncargado,
];

$oView = new ViewTwig('entradas/controller');
echo $oView->renderizar('entrada_accion.html.twig',$a_campos);